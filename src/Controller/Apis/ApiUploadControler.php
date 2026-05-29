<?php

namespace App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\Entity\Profession;
use App\Entity\Professionnel;
use App\Entity\User;
use App\Repository\CiviliteRepository;
use App\Repository\EntiteRepository;
use App\Repository\GenreRepository;
use App\Repository\PaysRepository;
use App\Repository\ProfessionnelRepository;
use App\Repository\ProfessionRepository;
use DateTimeImmutable;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/upload')]
class ApiUploadControler extends ApiInterface
{




    private const ALLOWED_EXTENSIONS = ['xlsx', 'xls'];
    private const MAX_FILE_SIZE = 10485760; // 10MB

    #[Route('/upload-excel/files', name: 'api_xlsx_ufr_examen', methods: ['POST'])]
    public function uploadExamen(
        Request $request,
        ProfessionnelRepository $professionnelRepository,
        EntiteRepository $personneRepository,
        GenreRepository $genreRepository,
        PaysRepository $nationaleRepository,
        CiviliteRepository $civiliteRepository,
        ProfessionRepository $professionRepository,
    ): JsonResponse {

        try {
            // Validation du fichier
            $file = $request->files->get('path');

            if ($file === null) {
                return $this->json([
                    'statut' => 0,
                    'message' => 'Aucun fichier reçu. Vérifiez que le champ s\'appelle bien "path".',
                    'data' => null
                ], Response::HTTP_BAD_REQUEST);
            }

            $professionSelected = $request->request->get('professionSelected');
            // Upload du fichier
            $fileFolder = $this->getParameter('kernel.project_dir') . '/public/uploads/excel_files/';
            $filePathName = md5(uniqid()) . '_' . $file->getClientOriginalName();

            try {
                $file->move($fileFolder, $filePathName);
            } catch (FileException $e) {
                return $this->json([
                    'statut' => 0,
                    'message' => 'Erreur lors de l\'upload du fichier',
                    'error' => $e->getMessage(),
                    'data' => null
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            // Traitement du fichier Excel

            $filePath = $fileFolder . $filePathName;

            try {
                $spreadsheet = IOFactory::load($filePath);
                $sheet = $spreadsheet->getActiveSheet();

                // Supprimer les 3 premières lignes
                $sheet->removeRow(1, 3);

                $sheetData = $sheet->toArray(null, true, true, true);

                /*  dd($sheetData); */
                $processedData = [];
                $errors = [];
                $successCount = 0;

                foreach ($sheetData as $index => $row) {
                    try {
                        // Closure robuste : supporte années 2 et 4 chiffres
                        // Exemples: "3/7/53", "19/6/19", "01/04/2021", "2021-04-01"
                        $parseDate = function (?string $value): \DateTimeImmutable {
                            if ($value === null || trim($value) === '') {
                                return new \DateTimeImmutable('1970-01-01');
                            }
                            // Ordre important : 4 chiffres d'abord, puis 2 chiffres
                            foreach (['d/m/Y', 'j/n/Y', 'd-m-Y', 'j-n-Y', 'Y-m-d', 'd/m/y', 'j/n/y'] as $fmt) {
                                $date = \DateTimeImmutable::createFromFormat($fmt, trim($value));
                                // PHP 8.1+ : getLastErrors() retourne false si aucune erreur
                                $errors = \DateTimeImmutable::getLastErrors();
                                $hasErrors = $errors !== false && ($errors['error_count'] > 0 || $errors['warning_count'] > 0);
                                if ($date !== false && !$hasErrors) {
                                    return $date->setTime(0, 0, 0);
                                }
                            }
                            return new \DateTimeImmutable('1970-01-01');
                        };

                        $dateAnniv      = $parseDate((string)($row['E'] ?? ''));
                        $dateEnreg      = $parseDate((string)($row['B'] ?? ''));
                        $dateCommission = $parseDate((string)($row['J'] ?? ''));

                        // dump($row);
                        $rowData = [
                            'num' => $row['A'] ?? null,
                            'dateEnregistre' => $dateEnreg,
                            'nom' => $row['C'] ?? null,
                            'numId' => $row['D'] ?? null,
                            'dateNaissance' => $dateAnniv,
                            'lieuNaissance' => $row['F'] ?? null,
                            'sexe' => $row['G'] ?? null,
                            'nationalite' => $row['H'] ?? null,
                            'specialite' => $professionSelected ?? $row['I'] ?? null,
                            'dateCommission' => $dateCommission,
                        ];
                        //Je compte ajouter une ligne pour ajouter les professions au cas ou on voit pas l'id dans la bd ou une correspondance avec le mot donné, Je commence par verifier que le type du champ (un int ou un str), apres quoi si c'est un int on continue sinon on fait un enregistrement et on continue avec l'id generer
                        if ($rowData['specialite'] != null) {
                            if (!is_numeric($rowData['specialite'])) {
                                //On verifie si la profession existe deja
                                $existingProfession = $professionRepository->findOneBy(['libelle' => $rowData['specialite']]);
                                if (!$existingProfession) {
                                    $newProfession = new Profession();
                                    $newProfession->setLibelle($rowData['specialite']);
                                    $professionRepository->add($newProfession, true);
                                    $rowData['specialite'] = $newProfession->getId();
                                } else {
                                    $rowData['specialite'] = $existingProfession->getId();
                                }
                            }
                        }


                        //Je verifie si le professionnel existe déjà pour eviter les doublons
                        $existingPerson = $rowData['numId'] != null
                            ? $professionnelRepository->findOneBy(['code' => $rowData['numId']])
                            : null;
                        if ($existingPerson) {
                            $successCount++;
                            // $errors[] = [
                            //     'Numero' => $rowData['num'],
                            //     // 'message' => 'Professionnel déjà existant'
                            // ];
                        } else {
                            if ($rowData['num'] != null && $rowData['dateEnregistre'] != null && $rowData['nom'] != null && $rowData['numId'] != null && $rowData['dateNaissance'] != null && $rowData['lieuNaissance'] != null && $rowData['sexe'] != null && $rowData['nationalite'] != null && $rowData['dateCommission'] != null) {
                                $personne = new Professionnel();
                                $personne->setStatus("a_jour");
                                $personne->setCivilite($civiliteRepository->findOneBy(['id' => $rowData['sexe']]));
                                $parts = explode(' ', $rowData['nom'], 2);
                                $nom = $parts[0];
                                $prenoms = isset($parts[1]) ? $parts[1] : '';
                                $personne->setNom($nom);
                                $personne->setPrenoms($prenoms);
                                $personne->setActived(true);
                                $personne->setCode($rowData['numId']);
                                $personne->setLieuNaissance($rowData['lieuNaissance']);
                                $personne->setEtatOld('inite');
                                
                                $personne->setDateNaissance($rowData['dateNaissance']);

                                $personne->setDateValidation($rowData['dateCommission']);

                                $personne->setCreatedAtValue($rowData['dateEnregistre']);

                                $personne->setNationate($nationaleRepository->findOneBy(['id' => $rowData['nationalite']]));
                                $personne->setProfession($professionRepository->findOneBy(['id' =>  $rowData['specialite']]));
                                // dd($personne);
                                $professionnelRepository->add($personne, true);
                                $successCount++;
                            } else {
                                $errors[] = [
                                    'ligne' => $index + 4,
                                    'message' => 'Données incomplètes'
                                ];
                            }
                        }
                    } catch (\Exception $e) {
                        $errors[] = [
                            'message' => $e->getMessage()
                        ];
                    }
                }

                // Suppression du fichier temporaire (optionnel)
                if (file_exists($filePath)) {
                    unlink($filePath);
                }

                return $this->json([
                    'statut' => 1,
                    'message' => "Import terminé avec succès",
                    'data' => [
                        'total_lignes' => count($sheetData),
                        'lignes_traitees' => $successCount,
                        'lignes_erreur' => count($errors),
                        'donnees' => $processedData,
                        'erreurs' => $errors
                    ]
                ], Response::HTTP_OK);
            } catch (\Exception $e) {
                // Suppression du fichier en cas d'erreur
                if (file_exists($filePath)) {
                    unlink($filePath);
                }

                return $this->json([
                    'statut' => 0,
                    'message' => 'Erreur lors du traitement du fichier Excel',
                    'error' => $e->getMessage(),
                    'data' => null
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $e) {
            return $this->json([
                'statut' => 0,
                'message' => 'Erreur serveur',
                'error' => $e->getMessage(),
                'data' => null
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
