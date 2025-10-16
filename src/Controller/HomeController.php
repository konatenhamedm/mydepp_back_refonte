<?php 


namespace App\Controller;

use App\Repository\ProfessionnelRepository;
use App\Repository\ProfessionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/home/api')]
    public function index(ProfessionnelRepository $professionnelRepository,ProfessionRepository $professionRepository): JsonResponse
    {

        $membresProfessionnels = [
    ['id' => 71, 'profession' => 'TP00001_Médecins'],
    ['id' => 72, 'profession' => 'TP00001_Médecins'],
    ['id' => 73, 'profession' => 'TP00001_Médecins'],
    ['id' => 74, 'profession' => 'rd_infirmiers'],
    ['id' => 75, 'profession' => 'rd_dieteticien'],
    ['id' => 76, 'profession' => 'rd_kinesithérapie'],
    ['id' => 77, 'profession' => 'rd_opticiens_optometristes'],
    ['id' => 78, 'profession' => 'rd_dieteticien'],
    ['id' => 79, 'profession' => 'rd_physiciens_medicaux'],
    ['id' => 80, 'profession' => 'rd_technicien_hygiene_assainissement'],
    ['id' => 81, 'profession' => 'rd_sages_femmes_specialistes'],
    ['id' => 87, 'profession' => 'TP00001_Médecins'],
    ['id' => 91, 'profession' => 'rd_orthoprothesistes'],
    ['id' => 101, 'profession' => 'TP00001_Médecins'],
    ['id' => 102, 'profession' => 'TP00001_Médecins'],
    ['id' => 104, 'profession' => 'TP00001_Médecins'],
    ['id' => 105, 'profession' => 'TP00009_Physiciens_Médicaux'],
    ['id' => 106, 'profession' => 'rd_chirurgiens_dentistes'],
    ['id' => 107, 'profession' => 'TP00001_Médecins'],
    ['id' => 108, 'profession' => 'TP00009_Physiciens_Médicaux'],
    ['id' => 109, 'profession' => 'TP00001_Médecins'],
    ['id' => 110, 'profession' => 'TP00009_Physiciens_Médicaux'],
    ['id' => 113, 'profession' => 'TP00001_Médecins'],
    ['id' => 114, 'profession' => 'TP00001_Médecins'],
    ['id' => 115, 'profession' => 'rd_sages_femmes'],
    ['id' => 116, 'profession' => 'rd_technien_bio_medicale'],
    ['id' => 117, 'profession' => 'TP00001_Médecins'],
    ['id' => 118, 'profession' => 'TP00009_Physiciens_Médicaux'],
    ['id' => 119, 'profession' => 'TP00001_Médecins'],
    ['id' => 120, 'profession' => 'TP00001_Médecins'],
    ['id' => 124, 'profession' => 'TP00001_Médecins'],
    ['id' => 129, 'profession' => 'TP00001_Médecins'],
    ['id' => 130, 'profession' => 'rd_sages_femmes_specialistes'],
    ['id' => 158, 'profession' => 'rd_assistants_dentistes'],
    ['id' => 159, 'profession' => 'TP00011_Biomédicale']
];
       // $professionnel = $professionnelRepository->findAll();

       foreach ($membresProfessionnels as $key => $value) {

        $professionnel = $professionnelRepository->findOneBy(['id' => $value['id']]);
        if($professionRepository->findOneBy(['code' => $value['profession']])){
            $professionnel->setProfession($professionRepository->findOneBy(['code' => $value['profession']]));
        }
        $professionnelRepository->add($professionnel, true);
        
       }

        /* foreach ($professionnel as $key => $value) {
            $value->setProfession($professionneRepository->findOneBy(['code' => $value->getProfession()]) ? $professionneRepository->findOneBy(['code' => $value->getProfession()])->getId() : null);
            $professionnelRepository->add($value,true);
        } */

        return new JsonResponse([
            'message' => 'Hello World'
        ]);
    }
}