<?php
use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;

require dirname(__DIR__).'/vendor/autoload.php';

(new Dotenv())->bootEnv(dirname(__DIR__).'/.env');

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();

$container = $kernel->getContainer();
$controller = $container->get('App\Controller\Apis\ApiStatistiqueController');
$request = Request::create('/api/statistique/generale', 'GET', ['annee' => 2024]);
$response = $controller->indexGeneral(
    $container->get('App\Repository\EtablissementRepository'),
    $container->get('App\Repository\ProfessionRepository'),
    $container->get('App\Repository\ProfessionnelRepository'),
    $container->get('App\Repository\CiviliteRepository'),
    $request
);
echo $response->getContent();
