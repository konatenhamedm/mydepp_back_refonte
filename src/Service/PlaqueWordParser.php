<?php

namespace App\Service;

/**
 * Lit une fiche Word (.docx) de plaque d'immatriculation.
 *
 * Structure attendue (fiches DEPPS "PILOTE QRCODE PLAQUES IMMATRICULATION") :
 *  - un titre "GRAND PUBLIC" suivi d'un tableau  → section publique
 *  - un titre "SERVICE DEPPS" suivi d'un tableau → section privée
 *  - un titre "SIGNES DE VISIBILITE" suivi d'une image (facultatif)
 *
 * Chaque tableau : 1re ligne = nom de l'établissement + "PRIVÉ AUTORISÉ",
 * puis une ligne par champ "Libellé | Valeur". La ligne "Services/Prestations"
 * peut avoir plusieurs colonnes (activités résidentes / en vacation).
 */
class PlaqueWordParser
{
    private const NS_W = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';
    private const NS_A = 'http://schemas.openxmlformats.org/drawingml/2006/main';
    private const NS_R = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';
    private const NS_REL = 'http://schemas.openxmlformats.org/package/2006/relationships';

    /**
     * @return array{
     *   publique: array, privee: array,
     *   signe: ?array{contenu: string, extension: string},
     *   resume: array{denomination: string, nature: ?string, immatriculation: ?string, numeroDossier: ?string, localisation: ?string}
     * }
     * @throws \RuntimeException si le fichier n'est pas une fiche exploitable
     */
    public function parse(string $cheminDocx): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($cheminDocx) !== true) {
            throw new \RuntimeException("Le fichier n'est pas un document Word (.docx) valide.");
        }

        $xml = $zip->getFromName('word/document.xml');
        if ($xml === false) {
            $zip->close();
            throw new \RuntimeException('Document Word illisible (contenu introuvable).');
        }

        $dom = new \DOMDocument();
        $dom->loadXML($xml, LIBXML_NONET);
        $xp = new \DOMXPath($dom);
        $xp->registerNamespace('w', self::NS_W);
        $xp->registerNamespace('a', self::NS_A);

        $body = $xp->query('/w:document/w:body')->item(0);
        if (!$body) {
            $zip->close();
            throw new \RuntimeException('Document Word vide.');
        }

        // Parcours du corps : on rattache chaque tableau à la dernière section rencontrée
        $section = null;
        $tables = ['publique' => null, 'privee' => null];
        $tablesSansSection = [];
        $signeRelId = null;

        foreach ($body->childNodes as $node) {
            if (!$node instanceof \DOMElement) {
                continue;
            }

            if ($node->localName === 'p') {
                $texte = $this->normaliserCle($this->texteParagraphe($node));
                if (str_contains($texte, 'grand public')) {
                    $section = 'publique';
                } elseif (str_contains($texte, 'service depps')) {
                    $section = 'privee';
                } elseif (str_contains($texte, 'signes de visibilit')) {
                    $section = 'signe';
                }

                if ($section === 'signe' && $signeRelId === null) {
                    $blip = $xp->query('.//a:blip', $node)->item(0);
                    if ($blip instanceof \DOMElement) {
                        $signeRelId = $blip->getAttributeNS(self::NS_R, 'embed') ?: null;
                    }
                }
            } elseif ($node->localName === 'tbl') {
                if (in_array($section, ['publique', 'privee'], true) && $tables[$section] === null) {
                    $tables[$section] = $node;
                } else {
                    $tablesSansSection[] = $node;
                }
            }
        }

        // Repli : fiches sans titres de section → 1er tableau public, 2e privé
        $tables['publique'] ??= array_shift($tablesSansSection);
        $tables['privee'] ??= array_shift($tablesSansSection);

        if (!$tables['publique'] || !$tables['privee']) {
            $zip->close();
            throw new \RuntimeException('Structure non reconnue : les tableaux "GRAND PUBLIC" et "SERVICE DEPPS" sont introuvables.');
        }

        $publique = $this->lireTableau($tables['publique'], $xp);
        $privee = $this->lireTableau($tables['privee'], $xp);
        $signe = $signeRelId ? $this->lireImage($zip, $signeRelId) : null;
        $zip->close();

        $denomination = $privee['titre'] ?: $publique['titre'];
        if (!$denomination) {
            throw new \RuntimeException("Nom de l'établissement introuvable dans la première ligne du tableau.");
        }

        return [
            'publique' => $publique,
            'privee' => $privee,
            'signe' => $signe,
            'resume' => [
                'denomination' => $denomination,
                'nature' => $this->valeurChamp([$privee, $publique], 'nature'),
                'immatriculation' => $this->valeurChamp([$privee], 'immatriculation'),
                'numeroDossier' => $this->valeurChamp([$privee], 'dossier'),
                'localisation' => $this->valeurChamp([$privee, $publique], 'localisation'),
            ],
        ];
    }

    /**
     * @return array{titre: string, statut: ?string, champs: array}
     */
    private function lireTableau(\DOMElement $table, \DOMXPath $xp): array
    {
        $lignes = $xp->query('./w:tr', $table);
        $titre = '';
        $statut = null;
        $champs = [];

        foreach ($lignes as $i => $ligne) {
            $cellules = [];
            foreach ($xp->query('./w:tc', $ligne) as $tc) {
                $cellules[] = $this->lignesCellule($tc, $xp);
            }

            if ($i === 0) {
                // Ligne d'en-tête : la cellule la plus remplie contient le nom (les autres, les logos)
                usort($cellules, fn ($a, $b) => strlen(implode(' ', $b)) <=> strlen(implode(' ', $a)));
                $titre = $this->espaces(implode(' ', $cellules[0] ?? []));
                if (preg_match('/\s*(PRIV[ÉE]E?\s+AUTORIS[ÉE]E?)\s*$/iu', $titre, $m)) {
                    $statut = mb_strtoupper($m[1]);
                    $titre = trim(mb_substr($titre, 0, mb_strlen($titre) - mb_strlen($m[0])));
                }
                continue;
            }

            $cellules = array_values(array_filter($cellules, fn ($c) => count($c) > 0));
            if (!$cellules) {
                continue;
            }

            // Ligne d'une seule cellule : "LIBELLÉ : valeur" (ex. CONVENTIONNEMENT CMU : NON)
            if (count($cellules) === 1) {
                $texte = $this->espaces(implode(' ', $cellules[0]));
                $pos = mb_strpos($texte, ':');
                $champs[] = $pos !== false
                    ? ['libelle' => trim(mb_substr($texte, 0, $pos)), 'valeur' => array_filter([trim(mb_substr($texte, $pos + 1))])]
                    : ['libelle' => $texte, 'valeur' => []];
                continue;
            }

            $libelle = $this->espaces(implode(' ', array_shift($cellules)));
            $estService = str_contains($this->normaliserCle($libelle), 'service') || count($cellules) > 1;

            if ($estService) {
                $groupes = [];
                foreach ($cellules as $lignesCellule) {
                    $premier = $lignesCellule[0];
                    $aUnTitre = count($lignesCellule) > 1
                        && mb_strlen($premier) <= 80
                        && $premier === mb_strtoupper($premier);
                    $groupes[] = [
                        'titre' => $aUnTitre ? $premier : null,
                        // Virgules de fin retirées : chaque élément est affiché séparément
                        'elements' => array_values(array_map(
                            fn ($e) => rtrim($e, " ,;"),
                            $aUnTitre ? array_slice($lignesCellule, 1) : $lignesCellule
                        )),
                    ];
                }
                $champs[] = ['libelle' => $libelle, 'valeur' => [], 'groupes' => $groupes];
            } else {
                $champs[] = ['libelle' => $libelle, 'valeur' => array_values($cellules[0])];
            }
        }

        foreach ($champs as &$champ) {
            $champ['valeur'] = array_values($champ['valeur']);
        }

        return ['titre' => $titre, 'statut' => $statut, 'champs' => $champs];
    }

    /**
     * Lignes de texte d'une cellule : un paragraphe Word = une ligne, et les
     * grands espaces / tabulations (ex. "Tél Fixe: …      Cel WhatsApp: …") séparent aussi.
     *
     * @return string[]
     */
    private function lignesCellule(\DOMElement $tc, \DOMXPath $xp): array
    {
        $lignes = [];
        foreach ($xp->query('.//w:p', $tc) as $p) {
            foreach (preg_split('/\n|\t|[ \x{00A0}]{2,}/u', $this->texteParagraphe($p)) as $morceau) {
                $morceau = $this->espaces($morceau);
                // Libellé seul sans valeur (ex. "Tél Fixe:" vide) → ignoré
                if ($morceau === '' || preg_match('/^[^:]{1,30}:$/u', $morceau)) {
                    continue;
                }
                $lignes[] = $morceau;
            }
        }
        return $lignes;
    }

    private function texteParagraphe(\DOMElement $p): string
    {
        $texte = '';
        $parcours = function (\DOMNode $n) use (&$parcours, &$texte) {
            foreach ($n->childNodes as $enfant) {
                if (!$enfant instanceof \DOMElement) {
                    continue;
                }
                if ($enfant->namespaceURI === self::NS_W) {
                    switch ($enfant->localName) {
                        case 't':
                            $texte .= $enfant->textContent;
                            continue 2;
                        case 'tab':
                            $texte .= "\t";
                            continue 2;
                        case 'br':
                        case 'cr':
                            $texte .= "\n";
                            continue 2;
                        case 'p':
                            continue 2; // paragraphes imbriqués traités séparément
                    }
                }
                $parcours($enfant);
            }
        };
        $parcours($p);
        return $texte;
    }

    private function lireImage(\ZipArchive $zip, string $relId): ?array
    {
        $rels = $zip->getFromName('word/_rels/document.xml.rels');
        if ($rels === false) {
            return null;
        }
        $dom = new \DOMDocument();
        $dom->loadXML($rels, LIBXML_NONET);
        foreach ($dom->getElementsByTagNameNS(self::NS_REL, 'Relationship') as $rel) {
            if ($rel->getAttribute('Id') !== $relId) {
                continue;
            }
            $cible = 'word/' . ltrim($rel->getAttribute('Target'), '/');
            $cible = str_replace('word/word/', 'word/', $cible);
            $extension = strtolower(pathinfo($cible, PATHINFO_EXTENSION));
            if (!in_array($extension, ['png', 'jpg', 'jpeg', 'gif', 'webp'], true)) {
                return null;
            }
            $contenu = $zip->getFromName($cible);
            return $contenu === false ? null : ['contenu' => $contenu, 'extension' => $extension];
        }
        return null;
    }

    /** Première valeur d'un champ dont le libellé contient $cle, cherchée dans les sections données. */
    private function valeurChamp(array $sections, string $cle): ?string
    {
        foreach ($sections as $section) {
            foreach ($section['champs'] as $champ) {
                if (str_contains($this->normaliserCle($champ['libelle']), $cle) && !empty($champ['valeur'])) {
                    return mb_substr(implode(' ', $champ['valeur']), 0, 255);
                }
            }
        }
        return null;
    }

    private function espaces(string $texte): string
    {
        return trim(preg_replace('/[\s\x{00A0}]+/u', ' ', $texte));
    }

    /** Minuscules sans accents, pour comparer des libellés. */
    private function normaliserCle(string $texte): string
    {
        $texte = mb_strtolower($this->espaces($texte));
        return strtr($texte, [
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e', 'à' => 'a', 'â' => 'a',
            'î' => 'i', 'ï' => 'i', 'ô' => 'o', 'û' => 'u', 'ù' => 'u', 'ç' => 'c',
        ]);
    }
}
