<?php

namespace App\Service;

use App\Attribute\Source;
use App\Controller\FileTrait;
use App\Entity\CodeGenerateur;
use App\Entity\Fichier;
use App\Entity\Pays;
use App\Entity\Profession;
use App\Entity\Professionnel;
use COM;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionClass;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints\Range;
use Twig\Environment;

class Utils
{
    private $em;
    public function __construct(
        private FileUploader $fileUploader,
        EntityManagerInterface $em
    ) {
        $this->em = $em;
    }

    use FileTrait;

    const MOIS = [
        1 => 'Janvier',
        'Février',
        'mars',
        'avril',
        'mai',
        'juin',
        'juillet',
        'août',
        'septembre',
        'octobre',
        'novembre',
        'décembre'
    ];

    const BASE_PATH = 'formation/certificat';





    public static function  localizeDate($value, $time = false)
    {
        $fmt = new \IntlDateFormatter(
            'fr',
            \IntlDateFormatter::FULL,
            $time ? \IntlDateFormatter::FULL : \IntlDateFormatter::NONE
        );
        return $fmt->format($value instanceof \DateTimeInterface ? $value : new \DateTime($value));
    }




    /**
     * @author Jean Mermoz Effi <mangoua.effi@uvci.edu.ci>
     * Cette fonction permet la création d'un nouveau fichier pour une entité liée
     *
     * @param mixed $filePath
     * @param mixed $entite
     * @param mixed $filePrefix
     * @param mixed $uploadedFile
     *
     * @return Fichier|null
     */
    public function sauvegardeFichier($filePath, $filePrefix, $uploadedFile, string $basePath = self::BASE_PATH, ?Fichier $oldFichier = null): ?Fichier
    {
        if (!$filePrefix || !$uploadedFile) {
            return null;
        }

        // Créer le répertoire s'il n'existe pas
        if (!is_dir($filePath)) {
            mkdir($filePath, 0777, true);
        }

        // Si un ancien fichier existe, on le supprime physiquement mais on réutilise l'entité
        // pour éviter les erreurs de contrainte de clé étrangère lors du flush()
        if ($oldFichier) {
            if ($oldFichier->getAlt()) {
                $oldPhysicalPath = rtrim($filePath, '/') . '/' . $oldFichier->getAlt();
                if (file_exists($oldPhysicalPath) && is_file($oldPhysicalPath)) {
                    @unlink($oldPhysicalPath);
                }
            }
        }

        // $path est passé par référence : après l'appel il contiendra le chemin complet du fichier
        $path = $filePath;
        $this->fileUploader->upload($uploadedFile, null, $path, $filePrefix, true);

        // $path est maintenant "$filePath/nomFichier.ext" grâce au passage par référence
        $fileExtension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        $fichier = $oldFichier ?: new Fichier();
        $fichier->setAlt(basename($path));
        $fichier->setPath($basePath);
        $fichier->setSize(file_exists($path) ? filesize($path) : 0);
        $fichier->setUrl($fileExtension);

        $this->em->persist($fichier);
        $this->em->flush();

        return $fichier;
    }

    public function sauvegardeFichierOld($filePath, $filePrefix, $uploadedFile, string $basePath = self::BASE_PATH, ?string $oldFilePath = null): ?Fichier
    {

        if (!$filePrefix || !$uploadedFile) {
            return false;
        }

        // Supprimer l'ancien fichier s'il existe
        if ($oldFilePath && file_exists($oldFilePath)) {
            @unlink($oldFilePath);

            // Optionnel : supprimer le répertoire parent si vide
            $dir = dirname($oldFilePath);
            if (is_dir($dir) && count(scandir($dir)) === 2) { // 2 pour . et ..
                @rmdir($dir);
            }
        }

        // Créer le répertoire s'il n'existe pas
        if (!is_dir($filePath)) {
            mkdir($filePath, 0777, true);
        }




        // Uploader le nouveau fichier
        $newFilePath = $this->fileUploader->upload($uploadedFile, null, $filePath, $filePrefix, true);
        /* dd($filePath, $filePrefix, $uploadedFile, $basePath, $oldFilePath,$newFilePath);
 */
        $fileExtension = strtolower(pathinfo($newFilePath, PATHINFO_EXTENSION));
        // Créer l'entité Fichier
        dd($fileExtension, $newFilePath, $basePath);
        $fichier = new Fichier();
        $fichier->setAlt(basename($newFilePath));
        $fichier->setPath($basePath);
        $fichier->setSize(filesize($newFilePath));
        $fichier->setUrl($fileExtension);


        /*    $fichier = new Fichier();
        $fichier->setAlt(basename($path));
        $fichier->setPath($basePath);
        $fichier->setSize(filesize($path));
        $fichier->setUrl($fileExtension); */

        return $fichier;
    }


    /**
     * @return mixed
     */
    public static function getUploadDir($path, $uploadDir, $create = false)
    {
        $path = $uploadDir . '/' . $path;

        if ($create && !is_dir($path)) {
            mkdir($path, 0777, true);
        }
        return $path;
    }

    /**
     * Génère un numéro d'identification unique pour un professionnel.
     *
     * Format CI  :  {racine}{civilite}{YY_inscription}{profCode}{JJ_naiss}{YY_naiss}.{XXXX}
     * Format NI  :  {racine}NI{civilite}{YY_inscription}{profCode}{JJ_naiss}{YY_naiss}.{XXXX}
     *
     * Le compteur XXXX repart de Profession::chronoMax et s'incrémente jusqu'à trouver
     * un code non encore attribué dans membre_professionnel.
     * Profession::chronoMax est mis à jour après génération (flush à la charge de l'appelant).
     *
     * @param string|null              $codeCilite
     * @param \DateTime|null           $dataNaissance
     * @param \DateTime|null           $dataCreate
     * @param string|null              $racine            ex: 'MS'
     * @param mixed                    $dernierChronoAvantReset  (legacy, ignoré)
     * @param string                   $type              'new' | 'old'
     * @param string|null              $professionCode    ex: 'OPTLO'
     * @param string|null              $profession        code interne profession (legacy count)
     * @param \App\Entity\Pays|null    $nationate         Nationalité du professionnel
     * @param \App\Entity\Profession|null $professionObj  Pour lire/écrire chronoMax
     */
    public function numeroGeneration(
        ?string $codeCilite,
        ?\DateTime $dataNaissance,
        ?\DateTime $dataCreate,
        ?string $racine,
        $dernierChronoAvantReset,
        string $type = 'new',
        ?string $professionCode = '00',
        ?string $profession = null,
        ?Pays $nationate = null,
        ?Profession $professionObj = null
    ): string {
        // ── Valeurs par défaut ────────────────────────────────────────
        $civilite       = $codeCilite ?? 'XX';
        $dataNaissance  = $dataNaissance ?? new \DateTime();
        $dataCreate     = $dataCreate   ?? new \DateTime();
        $racine         = $racine       ?? 'DEF';
        $professionCode = $professionCode ?? '00';
        $profession     = $profession   ?? 'DEFAULT';

        // ── Préfixe NI pour professionnel étranger ───────────────────
        $isEtranger = false;
        if ($nationate !== null) {
            $paysLibelle = mb_strtolower(trim($nationate->getLibelle() ?? ''), 'UTF-8');
            $ciVariants  = [
                "côte d'ivoire", "cote d'ivoire", "côte-d'ivoire",
                "cote-d'ivoire", "ci", "ivory coast",
            ];
            $isEtranger = !in_array($paysLibelle, $ciVariants, true);
        }
        $prefixe = $isEtranger ? ($racine . 'NI') : $racine;

        // ── Parties de date ──────────────────────────────────────────
        $anneeInscription = $dataCreate->format('Y');    // 4 chiffres : 2026
        $jour             = $dataNaissance->format('d'); // JJ
        $annee            = $dataNaissance->format('y'); // 2 chiffres : 79

        // ── Compteur de départ basé sur chronoMax de la profession ───
        $startChrono = 0;
        if ($professionObj !== null && $professionObj->getChronoMax() !== null) {
            $startChrono = intval($professionObj->getChronoMax());
        } elseif ($type !== 'new') {
            // Fallback legacy : CodeGenerateur count
            try {
                $qb = $this->em->createQueryBuilder()
                    ->select('COUNT(a.id)')
                    ->from(CodeGenerateur::class, 'a')
                    ->innerJoin('a.profession', 'r')
                    ->andWhere('r.code = :valeur')
                    ->setParameter('valeur', $profession);
                $startChrono = intval($qb->getQuery()->getSingleScalarResult());
            } catch (\Exception $e) {
                $startChrono = 0;
            }
        } else {
            // type = 'new' legacy : utiliser le paramètre passé
            $startChrono = intval($dernierChronoAvantReset ?? 0);
        }

        // ── Génération avec garantie d'unicité ───────────────────────
        $repo = $this->em->getRepository(Professionnel::class);
        $chrono = $startChrono;

        for ($attempt = 0; $attempt < 9999; $attempt++) {
            $chrono = ($chrono % 9999) + 1; // 1..9999, jamais 0

            $candidate = sprintf(
                "%s%s%s%s%s%s.%04d",
                $prefixe,
                //$civilite,
                $anneeInscription,
                $professionCode,
                $jour,
                $annee,
                $chrono
            );

            // Unicité garantie : aucun Professionnel ne doit déjà porter ce code
            if ($repo->findOneBy(['code' => $candidate]) === null) {
                // Mettre à jour chronoMax sur la profession
                if ($professionObj !== null) {
                    $professionObj->setChronoMax((string) $chrono);
                    $this->em->persist($professionObj);
                    // L'appelant est responsable du flush()
                }
                return $candidate;
            }
        }

        throw new \RuntimeException(
            sprintf('Impossible de générer un code unique pour la profession "%s" après 9999 tentatives.', $profession)
        );
    }

    /**
     * Génère un code court (3 à 4 lettres majuscules) à partir d'un libellé,
     * à utiliser quand l'utilisateur ne renseigne pas de code lui-même.
     * Le code est garanti unique dans le repository donné (un suffixe numérique
     * est ajouté en cas de collision).
     *
     * @param object $repository Repository disposant d'une méthode findOneBy(['code' => ...])
     */
    public function generateShortCodeFromLibelle(string $libelle, $repository, int $length = 4): string
    {
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $libelle) ?: $libelle;
        $letters = strtoupper(preg_replace('/[^A-Za-z]/', '', $ascii));

        if ($letters === '') {
            $letters = 'GEN';
        }

        $base = substr($letters, 0, $length);
        if (strlen($base) < 3) {
            $base = str_pad($base, 3, 'X');
        }

        $code = $base;
        for ($suffix = 1; $repository->findOneBy(['code' => $code]) !== null && $suffix < 1000; $suffix++) {
            $suffixStr = (string) $suffix;
            $code = substr($base, 0, max(1, $length - strlen($suffixStr))) . $suffixStr;
        }

        return $code;
    }
}

