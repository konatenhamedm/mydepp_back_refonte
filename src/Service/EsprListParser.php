<?php

declare(strict_types=1);

namespace App\Service;

use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Parse et normalise la liste officielle des Établissements Sanitaires Privés (ESPR)
 * autorisés par la DEPPS (CSV extrait du PDF, fichier Excel .xlsx/.xls ou JSON).
 */
final class EsprListParser
{
    private const MOIS_FR = [
        'janvier' => 1,
        'fevrier' => 2,
        'mars' => 3,
        'avril' => 4,
        'mai' => 5,
        'juin' => 6,
        'juillet' => 7,
        'aout' => 8,
        'septembre' => 9,
        'octobre' => 10,
        'novembre' => 11,
        'decembre' => 12,
    ];

    /** Canonicalisation des natures d'établissement et codes courts (VARCHAR(10)). */
    private const NATURES_CANONIQUES = [
        'medicale' => ['Médicale', 'MED'],
        'paramedicale' => ['Paramédicale', 'PARAMED'],
        'laboratoire' => ['Laboratoire', 'LABO'],
        'medecine complementaire et alternative' => ['Médecine Complémentaire et Alternative', 'MCA'],
        'entreprise de prestation de service d appui a la sante' => ['Entreprise de Prestation de Service d’Appui à la Santé', 'EPSAS'],
    ];

    /** Canonicalisation des variantes de casse de NIVEAU D’INTERVENTION observées dans le fichier. */
    private const NIVEAUX_CANONIQUES = [
        'cabinet de masso kinesitherapie' => 'Cabinet de Masso-Kinésithérapie',
        'centre de masso kinesitherapie' => 'Centre de Masso-Kinésithérapie',
        'centre medical specialise de psychiatrie' => 'Centre Médical Spécialisé de Psychiatrie',
        'centre medical specialise de geriatrie' => 'Centre Médical Spécialisé de Gériatrie',
        'centre medical specialise de pediatrie' => 'Centre Médical Spécialisé de Pédiatrie',
        'centre medical specialise d ophtalmologie' => 'Centre Médical Spécialisé d’Ophtalmologie',
        'centre de soins de beaute et de bien etre' => 'Centre de Soins de Beauté et de Bien-Être',
        'centre de remise en forme et de bien etre' => 'Centre de Remise en Forme et de Bien-Être',
    ];

    /** Correspondance des en-têtes CSV / Excel vers nos clés internes. */
    private const HEADER_ALIASES = [
        'num' => 'num',
        'n' => 'num',
        'no' => 'num',
        'numero' => 'num',
        'nature' => 'nature',
        'niveau' => 'niveau',
        'niveau d intervention' => 'niveau',
        'denomination' => 'denomination',
        'dossier' => 'dossier',
        'n dossier' => 'dossier',
        'no dossier' => 'dossier',
        'numero dossier' => 'dossier',
        'region' => 'region',
        'region sanitaire' => 'region',
        'district' => 'district',
        'district sanitaire' => 'district',
        'localisation' => 'localisation',
        'forme' => 'forme',
        'forme juridique' => 'forme',
        'statut juridique' => 'forme',
        'qualite' => 'qualite',
        'promoteur' => 'promoteur',
        'promoteur proprietaire' => 'promoteur',
        'responsable' => 'responsable',
        'responsable technique de sante' => 'responsable',
        'sexe' => 'sexe',
        'resp med sexe' => 'sexe',
        'arrete' => 'arrete',
        'arrete n et date de signature' => 'arrete',
        'arrete no et date de signature' => 'arrete',
        'expiration' => 'expiration',
        'date d expiration' => 'expiration',
        'contacts' => 'contacts',
        'contact' => 'contacts',
        'code id' => 'code_id',
        'code identifiant' => 'code_id',
        'immat' => 'immat',
        'immatriculation' => 'immat',
    ];

    /**
     * Charge et parse toutes les lignes d'un fichier (.csv, .xlsx, .xls ou .json).
     *
     * @return list<array<string, mixed>>
     */
    public function parseFile(string $filePath): array
    {
        if (!is_file($filePath)) {
            throw new \InvalidArgumentException(sprintf('Fichier introuvable : %s', $filePath));
        }

        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $rawRows = match ($ext) {
            'csv', 'txt' => $this->readCsv($filePath),
            'xlsx', 'xls', 'ods' => $this->readSpreadsheet($filePath),
            'json' => $this->readJson($filePath),
            default => throw new \InvalidArgumentException(sprintf('Extension de fichier non supportée (.%s). Formats acceptés : .csv, .xlsx, .xls, .json', $ext)),
        };

        $parsed = [];
        foreach ($rawRows as $idx => $row) {
            $denomination = $this->cleanText($row['denomination'] ?? '');
            if ($denomination === '') {
                continue;
            }
            $parsed[] = $this->normalizeRow($row, $idx + 1);
        }

        return $parsed;
    }

    /**
     * Dédoublonne et fusionne les renouvellements d'un même établissement :
     * - Si un même établissement (même N° Dossier normalisé + dénomination proche/identique, ou même N° Arrêté + dénomination)
     *   apparaît plusieurs fois (ex. arrêté 2023 puis renouvellement 2025/2026), on conserve l'arrêté le plus récent
     *   tout en préservant les informations complémentaires non vides (contacts, immatriculation) de la ligne précédente.
     * - Deux établissements distincts partageant par erreur un même N° Dossier (ex. dénominations différentes) sont tous deux conservés.
     *
     * @param list<array<string, mixed>> $rows
     * @return array{records: list<array<string, mixed>>, stats: array{input_rows: int, exact_duplicates: int, renewals_merged: int, unique_establishments: int}}
     */
    public function deduplicateAndMergeRenewals(array $rows): array
    {
        $exactDuplicates = 0;
        $renewalsMerged = 0;
        $seenExact = [];
        /** @var array<string, int> $dossierBuckets */
        $dossierBuckets = [];
        /** @var list<array<string, mixed>> $records */
        $records = [];

        foreach ($rows as $row) {
            $denomKey = self::normalizeKey((string) ($row['denomination'] ?? ''));
            $arreteKey = self::normalizeKey((string) ($row['numeroArrete'] ?? $row['arreteAutorisation'] ?? ''));
            $exactKey = $denomKey . '|' . $arreteKey;

            if ($arreteKey !== '' && isset($seenExact[$exactKey])) {
                $exactDuplicates++;
                $existingIdx = $seenExact[$exactKey];
                $records[$existingIdx] = $this->mergeEstablishmentRow($records[$existingIdx], $row);
                continue;
            }

            $dossierKey = self::normalizeKey((string) ($row['numeroDossier'] ?? ''));
            $matchedIdx = null;

            if ($dossierKey !== '' && isset($dossierBuckets[$dossierKey])) {
                foreach ($dossierBuckets[$dossierKey] as $candidateIdx) {
                    if ($this->isSameEstablishmentRenewal($records[$candidateIdx], $row)) {
                        $matchedIdx = $candidateIdx;
                        break;
                    }
                }
            }

            if ($matchedIdx !== null) {
                $renewalsMerged++;
                $records[$matchedIdx] = $this->mergeEstablishmentRow($records[$matchedIdx], $row);
                if ($arreteKey !== '') {
                    $seenExact[$exactKey] = $matchedIdx;
                }
                continue;
            }

            $newIdx = \count($records);
            $records[] = $row;
            if ($arreteKey !== '') {
                $seenExact[$exactKey] = $newIdx;
            }
            if ($dossierKey !== '') {
                $dossierBuckets[$dossierKey][] = $newIdx;
            }
        }

        return [
            'records' => $records,
            'stats' => [
                'input_rows' => \count($rows),
                'exact_duplicates' => $exactDuplicates,
                'renewals_merged' => $renewalsMerged,
                'unique_establishments' => \count($records),
            ],
        ];
    }

    /**
     * Normalise une ligne brute vers une structure prête pour l'insertion Doctrine.
     *
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    public function normalizeRow(array $row, int $lineNumber = 0): array
    {
        $num = (int) ($row['num'] ?? $lineNumber);
        $denomination = $this->cleanText($row['denomination'] ?? '');

        // 1. Nature & Niveau d'intervention
        [$natureLibelle, $natureCode] = $this->normalizeNature($row['nature'] ?? '');
        $niveauLibelle = $this->normalizeNiveau($row['niveau'] ?? '');
        $niveauCode = $this->generateShortCode($niveauLibelle, 20);

        // 2. Dossier DEPPS
        [$numeroDossier, $anneeDossier] = $this->normalizeDossier($row['dossier'] ?? '');

        // 3. Région & District sanitaires
        $regionLibelle = $this->normalizeRegion($row['region'] ?? '');
        $districtLibelle = $this->normalizeDistrict($row['district'] ?? '');

        // 4. Localisation détaillée + estimation GPS
        $localisationRaw = $this->cleanText($row['localisation'] ?? '');
        $adresseDetails = $this->parseLocalisation($localisationRaw);

        // 5. Forme juridique -> TypePersonne + StatutJuridique + Genre éventuel
        $formeInfo = $this->parseFormeJuridique($row['forme'] ?? '');

        // 6. Qualité & Promoteur / Propriétaire
        $qualiteRaw = $this->cleanText($row['qualite'] ?? '');
        $promoteurRaw = $this->cleanText($row['promoteur'] ?? '');
        $promoteurInfo = $this->parsePromoteur($promoteurRaw, $qualiteRaw, $formeInfo['typePersonneCode']);

        // 7. Responsable technique de santé & Sexe
        $responsableRaw = $this->cleanText($row['responsable'] ?? '');
        $sexeResp = $this->normalizeSexe($row['sexe'] ?? '');
        $responsableInfo = $this->parseResponsable($responsableRaw, $sexeResp);

        // 8. Arrêté ministériel (numéro + date de signature) & Date d'expiration
        $arreteInfo = $this->parseArrete($row['arrete'] ?? '');
        $dateExpiration = $this->parseDateExpiration($row['expiration'] ?? '');

        // 9. Contacts téléphoniques
        $contactsInfo = $this->parseContacts($row['contacts'] ?? '');

        // 10. Code identifiant & Immatriculation
        $codeIdentifiant = $this->nullIfEmpty($this->cleanText($row['code_id'] ?? ''));
        $immatriculation = $this->nullIfEmpty($this->cleanText($row['immat'] ?? ''));

        // 11. Géolocalisation estimée (quartier / commune / ville / district / région) avec légère dispersion déterministe sur carte
        $dispersionKey = ($numeroDossier ?? '') . '|' . $denomination . '|' . $localisationRaw;
        $geo = PlaqueGeolocalisation::estimerAvecContexte($localisationRaw, $immatriculation, $districtLibelle, $regionLibelle, $dispersionKey);

        return [
            'num' => $num,
            'denomination' => $denomination,
            'natureLibelle' => $natureLibelle,
            'natureCode' => $natureCode,
            'niveauLibelle' => $niveauLibelle,
            'niveauCode' => $niveauCode,
            'numeroDossier' => $numeroDossier,
            'anneeCreation' => $anneeDossier,
            'regionLibelle' => $regionLibelle,
            'regionCode' => $this->generateShortCode($regionLibelle, 15),
            'districtLibelle' => $districtLibelle,
            'adresse' => $this->nullIfEmpty($localisationRaw),
            'villeVillage' => $adresseDetails['villeVillage'],
            'commune' => $adresseDetails['commune'],
            'quartier' => $adresseDetails['quartier'],
            'zoneSecteur' => $adresseDetails['zoneSecteur'],
            'ilotNumero' => $adresseDetails['ilotNumero'],
            'lotNumero' => $adresseDetails['lotNumero'],
            'rueAvenue' => $adresseDetails['rueAvenue'],
            'villaImmeubleEtagePorte' => $adresseDetails['villaImmeubleEtagePorte'],
            'pointDeRepere' => $adresseDetails['pointDeRepere'],
            'typePersonneCode' => $formeInfo['typePersonneCode'],
            'typePersonneLibelle' => $formeInfo['typePersonneLibelle'],
            'statutJuridiqueLibelle' => $formeInfo['statutJuridiqueLibelle'],
            'statutJuridiqueCode' => $formeInfo['statutJuridiqueCode'],
            'typeSociete' => $formeInfo['typeSociete'],
            'genrePromoteur' => $formeInfo['genrePromoteur'],
            'qualitePromoteur' => $this->nullIfEmpty($qualiteRaw),
            'promoteurProprietaire' => $this->nullIfEmpty($promoteurRaw),
            'civilitePromoteur' => $promoteurInfo['civilite'],
            'nom' => $promoteurInfo['nom'],
            'prenoms' => $promoteurInfo['prenoms'],
            'professionPromoteur' => $promoteurInfo['profession'],
            'representantCivilite' => $promoteurInfo['representantCivilite'],
            'nomRepresentant' => $promoteurInfo['nomRepresentant'],
            'representantQualite' => $promoteurInfo['representantQualite'],
            'responsableTechniqueSante' => $this->nullIfEmpty($responsableRaw),
            'responsableCivilite' => $responsableInfo['civilite'],
            'responsableNom' => $responsableInfo['nom'],
            'responsableProfession' => $responsableInfo['profession'],
            'responsableNumeroOrdre' => $responsableInfo['numeroOrdre'],
            'responsabiliteMedicolegale' => $responsableInfo['responsabiliteMedicolegale'],
            'responsableEstResident' => $responsableInfo['estResident'],
            'responsableSexe' => $sexeResp,
            'arreteAutorisation' => $arreteInfo['arreteComplet'],
            'numeroArrete' => $arreteInfo['numeroArrete'],
            'dateSignatureArrete' => $arreteInfo['dateSignature'],
            'anneeAutorisation' => $arreteInfo['anneeAutorisation'],
            'dateExpirationArrete' => $dateExpiration,
            'telephone' => $contactsInfo['telephone'],
            'telephoneFixe' => $contactsInfo['telephoneFixe'],
            'telephoneMobile' => $contactsInfo['telephoneMobile'],
            'telephoneAutre' => $contactsInfo['telephoneAutre'],
            'codeIdentifiant' => $codeIdentifiant,
            'immatriculation' => $immatriculation,
            'latitude' => $geo !== null ? (string) $geo['latitude'] : null,
            'longitude' => $geo !== null ? (string) $geo['longitude'] : null,
            'lieuGeolocalisation' => $geo['lieu'] ?? null,
            'precisionGeolocalisation' => $geo['precision'] ?? null,
        ];
    }

    public static function normalizeKey(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = strtr($value, [
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'à' => 'a', 'â' => 'a', 'ä' => 'a',
            'î' => 'i', 'ï' => 'i',
            'ô' => 'o', 'ö' => 'o',
            'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'ç' => 'c', 'œ' => 'oe', 'æ' => 'ae',
        ]);

        return trim((string) preg_replace('/[^a-z0-9]+/', ' ', $value));
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function readCsv(string $filePath): array
    {
        $handle = fopen($filePath, 'rb');
        if ($handle === false) {
            throw new \RuntimeException(sprintf('Impossible d’ouvrir le fichier CSV : %s', $filePath));
        }

        // Détecter et sauter le BOM UTF-8 éventuel
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fclose($handle);
            return [];
        }
        $delimiter = substr_count($firstLine, ';') >= substr_count($firstLine, ',') ? ';' : ',';
        rewind($handle);
        if ($bom === "\xEF\xBB\xBF") {
            fread($handle, 3);
        }

        $headers = null;
        $rows = [];
        while (($data = fgetcsv($handle, 0, $delimiter, '"', '')) !== false) {
            if ($data === [null] || $data === []) {
                continue;
            }
            if ($headers === null) {
                $headers = $this->mapHeaders($data);
                continue;
            }
            $mapped = [];
            foreach ($headers as $colIdx => $internalKey) {
                if ($internalKey !== null && isset($data[$colIdx])) {
                    $mapped[$internalKey] = $data[$colIdx];
                }
            }
            if ($mapped !== []) {
                $rows[] = $mapped;
            }
        }
        fclose($handle);

        return $rows;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function readSpreadsheet(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rawMatrix = $sheet->toArray(null, true, true, false);

        $headers = null;
        $rows = [];
        foreach ($rawMatrix as $line) {
            $nonEmpty = array_filter($line, static fn ($v) => $v !== null && trim((string) $v) !== '');
            if ($nonEmpty === []) {
                continue;
            }
            if ($headers === null) {
                $candidate = $this->mapHeaders(array_map(static fn ($v) => (string) ($v ?? ''), $line));
                if (\in_array('denomination', $candidate, true)) {
                    $headers = $candidate;
                }
                continue;
            }
            $mapped = [];
            foreach ($headers as $colIdx => $internalKey) {
                if ($internalKey !== null && isset($line[$colIdx])) {
                    $mapped[$internalKey] = (string) $line[$colIdx];
                }
            }
            if ($mapped !== []) {
                $rows[] = $mapped;
            }
        }

        return $rows;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function readJson(string $filePath): array
    {
        $decoded = json_decode((string) file_get_contents($filePath), true, 512, JSON_THROW_ON_ERROR);
        if (!\is_array($decoded)) {
            return [];
        }

        $rows = [];
        foreach ($decoded as $item) {
            if (!\is_array($item)) {
                continue;
            }
            $mapped = [];
            foreach ($item as $k => $v) {
                $keyNorm = self::normalizeKey(str_replace('_', ' ', (string) $k));
                $internalKey = self::HEADER_ALIASES[$keyNorm] ?? null;
                if ($internalKey !== null) {
                    $mapped[$internalKey] = $v;
                }
            }
            $rows[] = $mapped;
        }

        return $rows;
    }

    /**
     * @param list<string|null> $rawHeaders
     * @return array<int, ?string>
     */
    private function mapHeaders(array $rawHeaders): array
    {
        $mapped = [];
        foreach ($rawHeaders as $idx => $header) {
            $norm = self::normalizeKey(str_replace('_', ' ', (string) $header));
            $mapped[$idx] = self::HEADER_ALIASES[$norm] ?? null;
        }

        return $mapped;
    }

    /**
     * @return array{0: ?string, 1: ?string}
     */
    private function normalizeNature(string $nature): array
    {
        $clean = $this->cleanText($nature);
        if ($clean === '') {
            return [null, null];
        }
        $key = self::normalizeKey($clean);
        if (isset(self::NATURES_CANONIQUES[$key])) {
            return self::NATURES_CANONIQUES[$key];
        }

        return [$clean, $this->generateShortCode($clean, 10)];
    }

    private function normalizeNiveau(string $niveau): ?string
    {
        $clean = $this->cleanText($niveau);
        if ($clean === '') {
            return null;
        }
        $key = self::normalizeKey($clean);

        return self::NIVEAUX_CANONIQUES[$key] ?? $clean;
    }

    /**
     * @return array{0: ?string, 1: ?string}
     */
    private function normalizeDossier(string $dossier): array
    {
        $clean = $this->cleanText($dossier);
        if ($clean === '') {
            return [null, null];
        }
        $clean = (string) preg_replace('#/+#', '/', $clean);
        // Corrige la coquille « Dossier DEPPS n°271/025 » -> « 271/2025 »
        $clean = (string) preg_replace('#/0(\d{2})\b#', '/20$1', $clean);

        $annee = null;
        if (preg_match('#/(19\d{2}|20\d{2})\b#', $clean, $m)) {
            $annee = $m[1];
        }

        return [$clean, $annee];
    }

    private function normalizeRegion(string $region): ?string
    {
        $clean = $this->cleanText($region);

        return $clean !== '' ? $clean : null;
    }

    private function normalizeDistrict(string $district): ?string
    {
        $clean = $this->cleanText($district);
        if ($clean === '') {
            return null;
        }
        if (self::normalizeKey($clean) === 'yopougon ouest songon') {
            return 'Yopougon Ouest-Songon';
        }
        if (self::normalizeKey($clean) === 'odienne') {
            return 'Odienné';
        }

        return $clean;
    }

    /**
     * @return array{
     *   typePersonneCode: string,
     *   typePersonneLibelle: string,
     *   statutJuridiqueLibelle: ?string,
     *   statutJuridiqueCode: ?string,
     *   typeSociete: ?string,
     *   genrePromoteur: ?string
     * }
     */
    private function parseFormeJuridique(string $forme): array
    {
        $clean = $this->cleanText($forme);
        $key = self::normalizeKey($clean);

        if (str_contains($key, 'personne physique')) {
            $genre = null;
            if (preg_match('/\b(mme|madame)\b/i', $clean)) {
                $genre = 'Féminin';
            } elseif (preg_match('/\b(mr|m|monsieur)\b/i', $clean)) {
                $genre = 'Masculin';
            }

            return [
                'typePersonneCode' => 'PHYSIQUE',
                'typePersonneLibelle' => 'Personne Physique',
                'statutJuridiqueLibelle' => 'Personne Physique',
                'statutJuridiqueCode' => 'PP',
                'typeSociete' => null,
                'genrePromoteur' => $genre,
            ];
        }

        $codeMap = [
            'sarl' => ['SARL', 'SARL'],
            'sarlu' => ['SARLU', 'SARLU'],
            'sarlp' => ['SARLP', 'SARLP'],
            'sas' => ['SAS', 'SAS'],
            'sa' => ['SA', 'SA'],
            'association ong' => ['Association-ONG', 'ASSO_ONG'],
        ];

        [$statutLibelle, $statutCode] = $codeMap[$key] ?? [
            $clean !== '' ? $clean : null,
            $clean !== '' ? $this->generateShortCode($clean, 10) : null,
        ];

        return [
            'typePersonneCode' => 'MORALE',
            'typePersonneLibelle' => 'Personne Morale',
            'statutJuridiqueLibelle' => $statutLibelle,
            'statutJuridiqueCode' => $statutCode,
            'typeSociete' => $statutLibelle,
            'genrePromoteur' => null,
        ];
    }

    /**
     * @return array{
     *   civilite: ?string,
     *   nom: ?string,
     *   prenoms: ?string,
     *   profession: ?string,
     *   representantCivilite: ?string,
     *   nomRepresentant: ?string,
     *   representantQualite: ?string
     * }
     */
    private function parsePromoteur(string $promoteur, string $qualite, string $typePersonneCode): array
    {
        $result = [
            'civilite' => null,
            'nom' => null,
            'prenoms' => null,
            'profession' => null,
            'representantCivilite' => null,
            'nomRepresentant' => null,
            'representantQualite' => null,
        ];

        $text = rtrim($promoteur, " \t\n\r\0\x0B.;,");
        if ($text === '') {
            return $result;
        }

        $standardCiv = $this->extractStandardCivilite($qualite);

        if ($typePersonneCode === 'MORALE') {
            if (preg_match('/repr[ée]sent[ée]e?\s+par\s+(.+)$/iu', $text, $m)) {
                $repPart = trim($m[1]);
                // Ex: "Monsieur SORO Kolotioloman, le Gérant"
                $segments = array_map('trim', explode(',', $repPart, 2));
                $nameWithCiv = $segments[0];
                $qualiteRep = isset($segments[1]) ? (string) preg_replace('/^(le|la|les|l[’\'])\s+/iu', '', trim($segments[1])) : null;

                [$repCiv, $cleanRepName] = $this->stripLeadingCivilite($nameWithCiv);
                $result['representantCivilite'] = $repCiv ?? $standardCiv;
                $result['nomRepresentant'] = $this->truncate($cleanRepName, 255);
                $result['representantQualite'] = $this->truncate($this->nullIfEmpty((string) $qualiteRep), 255);
            } else {
                // Parfois pour une personne morale seul le nom du gérant est indiqué
                [$repCiv, $cleanRepName] = $this->stripLeadingCivilite($text);
                $segments = array_map('trim', explode(',', $cleanRepName, 2));
                $result['representantCivilite'] = $repCiv ?? $standardCiv;
                $result['nomRepresentant'] = $this->truncate($segments[0], 255);
                if (isset($segments[1])) {
                    $qualiteRep = (string) preg_replace('/^(le|la|les|l[’\'])\s+/iu', '', $segments[1]);
                    $result['representantQualite'] = $this->truncate($this->nullIfEmpty($qualiteRep), 255);
                }
            }

            return $result;
        }

        // Personne Physique : "Madame GNAMIEN Amoin Marie Pélagie, Opticienne-lunetière"
        $segments = array_map('trim', explode(',', $text, 2));
        [$civ, $fullName] = $this->stripLeadingCivilite($segments[0]);
        [$nom, $prenoms] = $this->splitNomPrenoms($fullName);

        $profession = null;
        if (isset($segments[1])) {
            $profCandidate = trim((string) preg_replace('/^(le|la|l[’\'])\s+/iu', '', $segments[1]));
            if ($profCandidate !== '' && !preg_match('/^(g[ée]rant|propri[ée]taire|promoteur)/iu', $profCandidate)) {
                $profession = $this->truncate($profCandidate, 255);
            }
        }

        $result['civilite'] = $civ ?? $standardCiv;
        $result['nom'] = $this->truncate($nom, 255);
        $result['prenoms'] = $this->truncate($prenoms, 255);
        $result['profession'] = $profession;

        return $result;
    }

    /**
     * @return array{
     *   civilite: ?string,
     *   nom: ?string,
     *   profession: ?string,
     *   numeroOrdre: ?string,
     *   responsabiliteMedicolegale: ?string,
     *   estResident: ?bool
     * }
     */
    private function parseResponsable(string $responsable, ?string $sexe): array
    {
        $result = [
            'civilite' => null,
            'nom' => null,
            'profession' => null,
            'numeroOrdre' => null,
            'responsabiliteMedicolegale' => null,
            'estResident' => null,
        ];

        $text = rtrim($responsable, " \t\n\r\0\x0B.;,");
        if ($text === '') {
            return $result;
        }

        // 1. Résidence : "Il est résident" / "Elle est résidente" / "non résident"
        if (preg_match('/\bnon[\s-]+r[ée]sidente?\b/iu', $text)) {
            $result['estResident'] = false;
        } elseif (preg_match('/\b(il|elle)\s+est\s+r[ée]sidente?\b/iu', $text)) {
            $result['estResident'] = true;
        }
        $work = (string) preg_replace('/[.,;]?\s*(?:il|elle)\s+(?:n[’\']est\s+pas|est)\s+(?:non[\s-]+)?r[ée]sidente?.*$/iu', '', $text);

        // 2. Numéro d'ordre / code DEPPS entre parenthèses : (OMCI n°4988), (N°MS2023OPTLO 1999.0559)...
        if (preg_match('/\(([^)]*(?:OMCI|ONMCI|ONCDCI|ONPCI|ONSFCI|MS\d{4}|n°|N°|\d{3,})[^)]*)\)/u', $work, $m)) {
            $ordre = trim((string) preg_replace('/\s+/', ' ', $m[1]));
            $result['numeroOrdre'] = $this->truncate($ordre, 255);
            $work = trim(str_replace($m[0], '', $work));
        }

        // 3. Découpage par virgules : [Civilité + Nom], [Profession], [Responsabilité médicolégale]
        $parts = array_values(array_filter(array_map('trim', explode(',', $work)), static fn ($s) => $s !== ''));
        if (isset($parts[0])) {
            [$civ, $cleanName] = $this->stripLeadingCivilite($parts[0]);
            if ($civ === null && $sexe !== null) {
                $civ = $sexe === 'Femme' ? 'Madame' : 'Monsieur';
            }
            $result['civilite'] = $civ;
            $result['nom'] = $this->truncate($cleanName, 255);
        }

        $rolePatterns = '/^(directeur\s+m[ée]dical|directrice\s+m[ée]dicale|responsable\s+technique(?:\s+param[ée]dicale?|\s+m[ée]dicale?)?|m[ée]decin[\s-]+chef)/iu';
        for ($i = 1, $count = \count($parts); $i < $count; $i++) {
            $part = rtrim($parts[$i], ' .;');
            if (preg_match($rolePatterns, $part)) {
                $result['responsabiliteMedicolegale'] = $this->canonicalizeResponsabilite($part);
            } elseif ($result['profession'] === null) {
                $result['profession'] = $this->truncate($part, 255);
            }
        }

        return $result;
    }

    private function canonicalizeResponsabilite(string $role): string
    {
        $key = self::normalizeKey($role);
        return match (true) {
            str_contains($key, 'directeur medical') || str_contains($key, 'directrice medicale') => 'Directeur Médical',
            str_contains($key, 'responsable technique paramedical') => 'Responsable Technique Paramédical',
            str_contains($key, 'responsable technique medical') => 'Responsable Technique Médical',
            str_contains($key, 'responsable technique') => 'Responsable Technique',
            default => $this->truncate($role, 255) ?? 'Responsable Technique',
        };
    }

    /**
     * @return array{
     *   arreteComplet: ?string,
     *   numeroArrete: ?string,
     *   dateSignature: ?\DateTimeImmutable,
     *   anneeAutorisation: ?string
     * }
     */
    private function parseArrete(string $arrete): array
    {
        $clean = $this->cleanText($arrete);
        if ($clean === '') {
            return [
                'arreteComplet' => null,
                'numeroArrete' => null,
                'dateSignature' => null,
                'anneeAutorisation' => null,
            ];
        }

        // Corrige les césures PDF dans "DEPP S", "DE PPS", "DGS/DEPS" et "DU16"
        $clean = (string) preg_replace('/\bDE\s*P\s*P\s*S\b/iu', 'DEPPS', $clean);
        $clean = (string) preg_replace('#/DGS/DEPS\b#iu', '/DGS/DEPPS', $clean);
        $clean = (string) preg_replace('/\bDU(\d)/iu', 'DU $1', $clean);
        $clean = (string) preg_replace('/\bN°\s*/iu', 'N° ', $clean);
        $clean = trim((string) preg_replace('/\s+/', ' ', $clean));

        $numeroArrete = null;
        if (preg_match('/N°\s*([^\s]+)\s+DU\b/iu', $clean, $m)) {
            $numeroArrete = trim($m[1]);
        }

        $dateSignature = null;
        // Format 1 : "DU 11 AOÛT 2023", "du 16 NOVEMBRE 2023" ou "DU 1ER MARS 2024"
        if (preg_match('/\bDU\s+(\d{1,2})(?:ER|er)?\s+([A-Za-zÀ-ÿ]+)\s+(20\d{2}|19\d{2})\b/iu', $clean, $m)) {
            $day = (int) $m[1];
            $monthKey = self::normalizeKey($m[2]);
            $year = (int) $m[3];
            if (isset(self::MOIS_FR[$monthKey]) && checkdate(self::MOIS_FR[$monthKey], $day, $year)) {
                $dateSignature = new \DateTimeImmutable(sprintf('%04d-%02d-%02d 00:00:00', $year, self::MOIS_FR[$monthKey], $day));
            }
        } elseif (preg_match('/\bDU\s+(\d{1,2})\/(\d{1,2})\/(20\d{2}|19\d{2})\b/iu', $clean, $m)) {
            // Format 2 : "DU 17/09/2024" (format français JJ/MM/AAAA dans le texte d'arrêté)
            $day = (int) $m[1];
            $month = (int) $m[2];
            $year = (int) $m[3];
            if (checkdate($month, $day, $year)) {
                $dateSignature = new \DateTimeImmutable(sprintf('%04d-%02d-%02d 00:00:00', $year, $month, $day));
            }
        }

        return [
            'arreteComplet' => $this->truncate($clean, 255),
            'numeroArrete' => $this->truncate($numeroArrete, 100),
            'dateSignature' => $dateSignature,
            'anneeAutorisation' => $dateSignature?->format('Y'),
        ];
    }

    private function parseDateExpiration(string $expiration): ?\DateTimeImmutable
    {
        $clean = $this->cleanText($expiration);
        if ($clean === '') {
            return null;
        }

        // Format ISO YYYY-MM-DD
        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})/', $clean, $m)) {
            $year = (int) $m[1];
            $month = (int) $m[2];
            $day = (int) $m[3];
            if (checkdate($month, $day, $year)) {
                return new \DateTimeImmutable(sprintf('%04d-%02d-%02d 00:00:00', $year, $month, $day));
            }
        }

        // Format Excel US M/D/YYYY (utilisé sur les 2 049 lignes exportées depuis Excel) ou fallback D/M/YYYY si p1 > 12
        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $clean, $m)) {
            $p1 = (int) $m[1];
            $p2 = (int) $m[2];
            $year = (int) $m[3];

            if ($p1 <= 12 && checkdate($p1, $p2, $year)) {
                return new \DateTimeImmutable(sprintf('%04d-%02d-%02d 00:00:00', $year, $p1, $p2));
            }
            if ($p1 > 12 && $p2 <= 12 && checkdate($p2, $p1, $year)) {
                return new \DateTimeImmutable(sprintf('%04d-%02d-%02d 00:00:00', $year, $p2, $p1));
            }
        }

        return null;
    }

    /**
     * @return array{telephone: ?string, telephoneFixe: ?string, telephoneMobile: ?string, telephoneAutre: ?string}
     */
    private function parseContacts(string $contacts): array
    {
        $clean = $this->cleanText($contacts);
        if ($clean === '') {
            return [
                'telephone' => null,
                'telephoneFixe' => null,
                'telephoneMobile' => null,
                'telephoneAutre' => null,
            ];
        }

        $parts = preg_split('#\s*[/;|]\s*#', $clean) ?: [];
        $phones = [];
        foreach ($parts as $part) {
            $p = trim($part);
            if ($p !== '') {
                $phones[] = $p;
            }
        }

        if ($phones === []) {
            return [
                'telephone' => null,
                'telephoneFixe' => null,
                'telephoneMobile' => null,
                'telephoneAutre' => null,
            ];
        }

        $primary = $phones[0];
        $fixe = null;
        $mobile = null;
        $others = [];

        foreach ($phones as $idx => $phone) {
            $digits = (string) preg_replace('/\D+/', '', $phone);
            if (str_starts_with($digits, '225') && \strlen($digits) === 13) {
                $digits = substr($digits, 3);
            }
            if ($fixe === null && (str_starts_with($digits, '25') || str_starts_with($digits, '27'))) {
                $fixe = $phone;
            } elseif ($mobile === null && (str_starts_with($digits, '01') || str_starts_with($digits, '05') || str_starts_with($digits, '07'))) {
                $mobile = $phone;
            } elseif ($idx > 0) {
                $others[] = $phone;
            }
        }

        if ($others === [] && \count($phones) > 1) {
            $others = \array_slice($phones, 1);
        }

        return [
            'telephone' => $this->truncate($primary, 255),
            'telephoneFixe' => $this->truncate($fixe, 255),
            'telephoneMobile' => $this->truncate($mobile, 255),
            'telephoneAutre' => $others !== [] ? $this->truncate(implode(' / ', $others), 255) : null,
        ];
    }

    /**
     * Extrait les sous-champs structurés d'une adresse ivoirienne tout en gardant le texte complet dans `adresse`.
     *
     * @return array{
     *   villeVillage: ?string,
     *   commune: ?string,
     *   quartier: ?string,
     *   zoneSecteur: ?string,
     *   ilotNumero: ?string,
     *   lotNumero: ?string,
     *   rueAvenue: ?string,
     *   villaImmeubleEtagePorte: ?string,
     *   pointDeRepere: ?string
     * }
     */
    private function parseLocalisation(string $localisation): array
    {
        $res = [
            'villeVillage' => null,
            'commune' => null,
            'quartier' => null,
            'zoneSecteur' => null,
            'ilotNumero' => null,
            'lotNumero' => null,
            'rueAvenue' => null,
            'villaImmeubleEtagePorte' => null,
            'pointDeRepere' => null,
        ];

        $text = rtrim($localisation, " \t\n\r\0\x0B.;,");
        if ($text === '') {
            return $res;
        }

        if (preg_match('/\bcommune\s+(?:de\s+|d[’\'])([^,;]+)/iu', $text, $m)) {
            $res['commune'] = $this->truncate(trim($m[1]), 255);
        }
        if (preg_match('/\bquartier\s+([^,;]+)/iu', $text, $m)) {
            $res['quartier'] = $this->truncate(trim($m[1]), 255);
        }
        if (preg_match('/\b(?:secteur|zone)\s+([^,;]+)/iu', $text, $m)) {
            $res['zoneSecteur'] = $this->truncate(trim($m[1]), 255);
        }
        if (preg_match('/\b[îi]lots?\s*(?:n[°o]\s*)?([A-Za-z0-9\-\/\s]+?)(?=[,.;]|$|\blot\b)/iu', $text, $m)) {
            $res['ilotNumero'] = $this->truncate(trim($m[1]), 255);
        }
        if (preg_match('/\blots?\s*(?:n[°o]\s*)?([A-Za-z0-9\-\/\s]+?)(?=[,.;]|$|\b[îi]lot\b)/iu', $text, $m)) {
            $res['lotNumero'] = $this->truncate(trim($m[1]), 255);
        }
        if (preg_match('/\b((?:rue|avenue|boulevard|bd)\s+[^,;]+)/iu', $text, $m)) {
            $res['rueAvenue'] = $this->truncate(trim($m[1]), 255);
        }
        if (preg_match('/\b((?:immeuble|villa|r[ée]sidence|appartement|porte|[\d]+(?:er|e|[èe]me)?\s*[ée]tage)[^,;]*)/iu', $text, $m)) {
            $res['villaImmeubleEtagePorte'] = $this->truncate(trim($m[1]), 255);
        }
        if (preg_match('/\b((?:non\s+loin|pr[èe]s\s+de|en\s+face\s+de|derri[èe]re|[àa]\s+c[ôo]t[ée]\s+de|apr[èe]s|avant)\s+[^,;]+)/iu', $text, $m)) {
            $res['pointDeRepere'] = $this->truncate(trim($m[1]), 255);
        }

        $firstChunk = trim(explode(',', $text, 2)[0]);
        if ($firstChunk !== '' && !preg_match('/^(commune|quartier|lot|[îi]lot|rue|avenue|boulevard)/iu', $firstChunk)) {
            $res['villeVillage'] = $this->truncate($firstChunk, 255);
        }

        return $res;
    }

    private function normalizeSexe(string $sexe): ?string
    {
        $clean = $this->cleanText($sexe);
        $key = self::normalizeKey($clean);

        return match ($key) {
            'homme', 'masculin', 'm', 'h' => 'Homme',
            'femme', 'feminin', 'f' => 'Femme',
            default => $clean !== '' ? $this->truncate($clean, 30) : null,
        };
    }

    private function extractStandardCivilite(string $qualite): ?string
    {
        $key = self::normalizeKey($qualite);

        return match ($key) {
            'monsieur', 'mr', 'm' => 'Monsieur',
            'madame', 'mme' => 'Madame',
            'mademoiselle', 'mlle' => 'Mademoiselle',
            'docteur', 'dr' => 'Docteur',
            'professeur', 'pr' => 'Professeur',
            default => null,
        };
    }

    /**
     * @return array{0: ?string, 1: string}
     */
    private function stripLeadingCivilite(string $name): array
    {
        $trimmed = trim($name);
        $patterns = [
            '/^(?:Monsieur|Mr\.?|M\.)\s+/iu' => 'Monsieur',
            '/^(?:Madame|Mme\.?)\s+/iu' => 'Madame',
            '/^(?:Mademoiselle|Mlle\.?)\s+/iu' => 'Mademoiselle',
            '/^(?:Docteur|Dr\.?)\s+/iu' => 'Docteur',
            '/^(?:Professeur|Pr\.?)\s+/iu' => 'Professeur',
            '/^(?:Sœur|Soeur|R[ée]v[ée]rende\s+Sœur|R[ée]v[ée]rende\s+Soeur)\s+/iu' => 'Madame',
            '/^(?:P[èe]re|Fr[èe]re|R[ée]v[ée]rend\s+P[èe]re)\s+/iu' => 'Monsieur',
        ];

        foreach ($patterns as $regex => $civ) {
            if (preg_match($regex, $trimmed)) {
                $rest = trim((string) preg_replace($regex, '', $trimmed, 1));
                // Gère "Docteur Monsieur ..." ou "Madame Docteur ..."
                foreach ($patterns as $regex2 => $civ2) {
                    if (preg_match($regex2, $rest)) {
                        $rest = trim((string) preg_replace($regex2, '', $rest, 1));
                        if ($civ === 'Monsieur' || $civ === 'Madame') {
                            $civ = $civ2;
                        }
                        break;
                    }
                }

                return [$civ, $rest];
            }
        }

        return [null, $trimmed];
    }

    /**
     * Sépare "GNAMIEN Amoin Marie Pélagie" en ["GNAMIEN", "Amoin Marie Pélagie"].
     *
     * @return array{0: ?string, 1: ?string}
     */
    private function splitNomPrenoms(string $fullName): array
    {
        $tokens = preg_split('/\s+/u', trim($fullName)) ?: [];
        if ($tokens === []) {
            return [null, null];
        }
        if (\count($tokens) === 1) {
            return [$tokens[0], null];
        }

        $upperTokens = [];
        $restTokens = [];
        $inSurname = true;

        foreach ($tokens as $idx => $token) {
            $lettersOnly = (string) preg_replace('/[^A-Za-zÀ-ÿ]/u', '', $token);
            $isUpper = $lettersOnly !== '' && mb_strtoupper($lettersOnly) === $lettersOnly;
            if ($inSurname && ($isUpper || ($idx > 0 && preg_match('/^(N[ée]e|[Éé]pouse|dit)$/iu', $token)))) {
                $upperTokens[] = $token;
            } else {
                $inSurname = false;
                $restTokens[] = $token;
            }
        }

        if ($upperTokens === []) {
            return [$tokens[0], implode(' ', \array_slice($tokens, 1))];
        }
        if ($restTokens === [] && \count($upperTokens) > 1) {
            return [$upperTokens[0], implode(' ', \array_slice($upperTokens, 1))];
        }

        return [
            implode(' ', $upperTokens),
            $restTokens !== [] ? implode(' ', $restTokens) : null,
        ];
    }

    /**
     * Vérifie si deux lignes partageant le même N° Dossier correspondent bien à un renouvellement
     * du même établissement (et non à deux établissements distincts ayant reçu le même numéro par erreur).
     *
     * @param array<string, mixed> $existing
     * @param array<string, mixed> $incoming
     */
    private function isSameEstablishmentRenewal(array $existing, array $incoming): bool
    {
        $d1 = self::normalizeKey((string) ($existing['denomination'] ?? ''));
        $d2 = self::normalizeKey((string) ($incoming['denomination'] ?? ''));

        if ($d1 === '' || $d2 === '') {
            return false;
        }
        if ($d1 === $d2 || str_contains($d1, $d2) || str_contains($d2, $d1)) {
            return true;
        }

        // Même acronyme entre parenthèses (ex: "IDÉALE VISION ... (IVSOL)" et "IVSOL")
        $compact1 = str_replace(' ', '', $d1);
        $compact2 = str_replace(' ', '', $d2);
        if (\strlen($compact1) >= 4 && \strlen($compact2) >= 4 && (str_contains($compact1, $compact2) || str_contains($compact2, $compact1))) {
            return true;
        }

        // Même promoteur ET même district sur le même numéro de dossier (ex: renommage OPTIC HEART -> CLARTE OPTIK ou CABINET -> CLINIQUE)
        $dist1 = self::normalizeKey((string) ($existing['districtLibelle'] ?? ''));
        $dist2 = self::normalizeKey((string) ($incoming['districtLibelle'] ?? ''));
        $prom1 = self::normalizeKey((string) ($existing['promoteurProprietaire'] ?? ''));
        $prom2 = self::normalizeKey((string) ($incoming['promoteurProprietaire'] ?? ''));

        if ($dist1 !== '' && $dist1 === $dist2 && $prom1 !== '' && $prom2 !== '') {
            $short1 = substr($prom1, 0, min(28, \strlen($prom1)));
            $short2 = substr($prom2, 0, min(28, \strlen($prom2)));
            if ($short1 === $short2) {
                return true;
            }
        }

        // Légère variante orthographique (ex: "CENTRE OPTIC VUD C" vs "CENTRE OPTIQUE VUD C")
        similar_text($d1, $d2, $percent);

        return $percent >= 80.0;
    }

    /**
     * Fusionne deux lignes du même établissement en privilégiant l'arrêté ayant la date d'expiration
     * (ou de signature) la plus récente, tout en conservant les champs non vides de l'ancienne ligne.
     *
     * @param array<string, mixed> $a
     * @param array<string, mixed> $b
     * @return array<string, mixed>
     */
    private function mergeEstablishmentRow(array $a, array $b): array
    {
        $expA = $a['dateExpirationArrete'] instanceof \DateTimeInterface ? $a['dateExpirationArrete']->getTimestamp() : 0;
        $expB = $b['dateExpirationArrete'] instanceof \DateTimeInterface ? $b['dateExpirationArrete']->getTimestamp() : 0;
        $sigA = $a['dateSignatureArrete'] instanceof \DateTimeInterface ? $a['dateSignatureArrete']->getTimestamp() : 0;
        $sigB = $b['dateSignatureArrete'] instanceof \DateTimeInterface ? $b['dateSignatureArrete']->getTimestamp() : 0;

        $bIsNewer = ($expB > $expA) || ($expB === $expA && $sigB >= $sigA);
        [$primary, $secondary] = $bIsNewer ? [$b, $a] : [$a, $b];

        foreach ($secondary as $key => $val) {
            if ((!isset($primary[$key]) || $primary[$key] === null || $primary[$key] === '') && $val !== null && $val !== '') {
                $primary[$key] = $val;
            }
        }

        return $primary;
    }

    private function generateShortCode(?string $libelle, int $maxLen = 10): ?string
    {
        if ($libelle === null || trim($libelle) === '') {
            return null;
        }
        $words = preg_split('/\s+/', self::normalizeKey($libelle)) ?: [];
        $stopWords = ['de', 'du', 'des', 'la', 'le', 'les', 'et', 'en', 'a', 'au', 'aux', 'd', 'l'];
        $filtered = array_values(array_filter($words, static fn ($w) => $w !== '' && !\in_array($w, $stopWords, true)));
        if ($filtered === []) {
            $filtered = $words;
        }

        if (\count($filtered) === 1) {
            return strtoupper(substr($filtered[0], 0, $maxLen));
        }

        $code = '';
        foreach ($filtered as $w) {
            $code .= strtoupper(substr($w, 0, $maxLen <= 10 ? 3 : 4));
            if (\strlen($code) >= $maxLen) {
                break;
            }
        }

        return substr($code, 0, $maxLen);
    }

    private function cleanText(mixed $value): string
    {
        if ($value === null) {
            return '';
        }
        $str = str_replace(["\r\n", "\r", "\n", "\u{00A0}"], ' ', (string) $value);

        return trim((string) preg_replace('/\s+/u', ' ', $str));
    }

    private function nullIfEmpty(?string $value): ?string
    {
        return ($value === null || trim($value) === '') ? null : trim($value);
    }

    private function truncate(?string $value, int $maxLength): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return mb_strlen($value) > $maxLength ? mb_substr($value, 0, $maxLength) : $value;
    }
}
