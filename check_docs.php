<?php
require_once __DIR__.'/vendor/autoload.php';
$kernel = new App\Kernel('dev', true);
$kernel->boot();
$em = $kernel->getContainer()->get('doctrine')->getManager();
$docs = $em->getRepository(\App\Entity\AutreDocumentProfessionnel::class)->findAll();
foreach ($docs as $doc) {
    echo "ID: " . $doc->getId() . " | ProID: " . ($doc->getProfessionnel() ? $doc->getProfessionnel()->getId() : 'NULL') . "\n";
}
echo "Total docs: " . count($docs) . "\n";
