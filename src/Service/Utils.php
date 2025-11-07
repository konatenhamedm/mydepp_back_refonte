<?php

namespace App\Service;

use App\Attribute\Source;
use App\Controller\FileTrait;
use App\Entity\CodeGenerateur;
use App\Entity\Fichier;
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
    public function sauvegardeFichier($filePath, $filePrefix, $uploadedFile, string $basePath = self::BASE_PATH): ?Fichier
    {

        if (!$filePrefix) {
            return false;
        }

        $path = $filePath;
        //dd($uploadedFile, $path, $filePrefix);
        $this->fileUploader->upload($uploadedFile, null, $path, $filePrefix, true);

        $fileExtension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        $fichier = new Fichier();
        $fichier->setAlt(basename($path));
        $fichier->setPath($basePath);
        $fichier->setSize(filesize($path));
        $fichier->setUrl($fileExtension);

        //$this->em->persist($fichier);
        //$this->em->flush();
        //dd('');


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

    public function numeroGeneration(
        ?string $codeCilite,
        ?\DateTime $dataNaissance,
        ?\DateTime $dataCreate,
        $dernierChronoAvantReset,
        ?string $type,
        ?string $professionCode,
        ?string $profession
    ): string {
        // Gestion des valeurs null avec valeurs par défaut
        $civilite = $codeCilite ?? '0';
        $anneeInscription = $dataCreate?->format('y') ?? '00';
        $jour = $dataNaissance?->format('d') ?? '01';
        $annee = $dataNaissance?->format('y') ?? '00';

        $dernierChrono = 0;

        // Requête seulement si profession est fournie
        if ($profession !== null) {
            $query = $this->em->createQueryBuilder();
            $query
                ->select("count(a.id)")
                ->from(CodeGenerateur::class, 'a')
                ->innerJoin('a.profession', 'r')
                ->andWhere('r.code = :valeur')
                ->setParameter('valeur', $profession);

            $dernierChrono = $query->getQuery()->getSingleScalarResult();
        }

        // Calcul du chrono en fonction du type
        if ($type === 'new' && $dernierChronoAvantReset !== null) {
            $maxChrono = intval($dernierChronoAvantReset);
        } else {
            $maxChrono = intval($dernierChrono);
        }

        // Incrémentation avec reset à 10000
        $maxChrono = ($maxChrono + 1) % 10000;
        if ($maxChrono === 0) {
            $maxChrono = 1;
        }

        // Génération du numéro final
        return sprintf(
            "%s%s0%s%s%s%s.%04d",
            $racine ?? '',
            $civilite,
            $anneeInscription,
            $professionCode ?? '00',
            $jour,
            $annee,
            $maxChrono
        );
    }
}
