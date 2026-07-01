<?php
require_once __DIR__.'/vendor/autoload.php';
$kernel = new App\Kernel('dev', true);
$kernel->boot();
$container = $kernel->getContainer();
$request = Symfony\Component\HttpFoundation\Request::create('/api/autre-document-professionnel/professionnel/247', 'GET');
$response = $kernel->handle($request);
echo "Status: " . $response->getStatusCode() . "\n";
echo "Content: " . $response->getContent() . "\n";
