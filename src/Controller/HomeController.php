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
    public function index(ProfessionnelRepository $professionnelRepository,ProfessionRepository $professionneRepository): JsonResponse
    {
        $professionnel = $professionnelRepository->findAll();

        foreach ($professionnel as $key => $value) {
            $value->setProfession($professionneRepository->findOneBy(['code' => $value->getProfession()])->getId());
            $professionnelRepository->add($value,true);
        }

        return new JsonResponse([
            'message' => 'Hello World'
        ]);
    }
}