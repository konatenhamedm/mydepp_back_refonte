<?php

namespace App\Tests\Service;

use App\Entity\Pays;
use App\Entity\Profession;
use App\Entity\Professionnel;
use App\Service\FileUploader;
use App\Service\Utils;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

/**
 * Couvre la génération du numéro d'inscription initial (Utils::numeroGeneration),
 * point de départ du format "MSAAAAPROFJJAA.CCCC" (ex: MS2024OPTLO2987.0002).
 */
class UtilsTest extends TestCase
{
    private function makeUtils(EntityManagerInterface $em): Utils
    {
        return new Utils($this->createMock(FileUploader::class), $em);
    }

    private function mockEmWithProfessionnelRepo(?string $existingCode = null): EntityManagerInterface
    {
        $repo = $this->createMock(\App\Repository\ProfessionnelRepository::class);
        $repo->method('findOneBy')->willReturn(null);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())
            ->method('getRepository')
            ->with(Professionnel::class)
            ->willReturn($repo);

        return $em;
    }

    public function testFormatInitialPourProfessionnelIvoirien(): void
    {
        $em = $this->mockEmWithProfessionnelRepo();
        $utils = $this->makeUtils($em);

        $pays = new Pays();
        $pays->setLibelle("Côte d'Ivoire");

        $profession = new Profession();
        // chronoMax déjà à 2986 => le prochain candidat testé doit être 2987
        $profession->setChronoMax('2986');

        $code = $utils->numeroGeneration(
            codeCilite: 'M',
            dataNaissance: new \DateTime('1979-02-15'),
            dataCreate: new \DateTime('2024-06-01'),
            racine: 'MS',
            dernierChronoAvantReset: 0,
            type: 'new',
            professionCode: 'OPTLO',
            profession: 'OPTLO',
            nationate: $pays,
            professionObj: $profession
        );

        // MS (racine, pas de suffixe NI) + 2024 (année d'inscription) + OPTLO
        // + 15 (jour naissance) + 79 (année naissance 2 chiffres) + .2987 (chrono)
        $this->assertSame('MS2024OPTLO1579.2987', $code);
    }

    public function testPrefixeNiPourProfessionnelEtranger(): void
    {
        $em = $this->mockEmWithProfessionnelRepo();
        $utils = $this->makeUtils($em);

        $pays = new Pays();
        $pays->setLibelle('France');

        $profession = new Profession();
        $profession->setChronoMax('0');

        $code = $utils->numeroGeneration(
            codeCilite: 'M',
            dataNaissance: new \DateTime('1990-01-10'),
            dataCreate: new \DateTime('2024-06-01'),
            racine: 'MS',
            dernierChronoAvantReset: 0,
            type: 'new',
            professionCode: 'OPTLO',
            profession: 'OPTLO',
            nationate: $pays,
            professionObj: $profession
        );

        $this->assertStringStartsWith('MSNI2024OPTLO', $code);
    }

    public function testChronoMaxDeLaProfessionEstMisAJourApresGeneration(): void
    {
        $em = $this->mockEmWithProfessionnelRepo();
        $utils = $this->makeUtils($em);

        $profession = new Profession();
        $profession->setChronoMax('41');

        $utils->numeroGeneration(
            codeCilite: 'M',
            dataNaissance: new \DateTime('1985-03-20'),
            dataCreate: new \DateTime('2026-01-01'),
            racine: 'MS',
            dernierChronoAvantReset: 0,
            type: 'new',
            professionCode: 'OPTLO',
            profession: 'OPTLO',
            nationate: null,
            professionObj: $profession
        );

        $this->assertSame('42', $profession->getChronoMax());
    }

    public function testCandidatDejaPrisEstIgnoreEtLeSuivantEstUtilise(): void
    {
        $repo = $this->createMock(\App\Repository\ProfessionnelRepository::class);
        // Le premier candidat (.2987) est déjà pris, le second (.2988) est libre
        $repo->method('findOneBy')->willReturnOnConsecutiveCalls(
            new Professionnel(),
            null
        );

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->with(Professionnel::class)->willReturn($repo);

        $utils = $this->makeUtils($em);

        $profession = new Profession();
        $profession->setChronoMax('2986');

        $code = $utils->numeroGeneration(
            codeCilite: 'M',
            dataNaissance: new \DateTime('1979-02-15'),
            dataCreate: new \DateTime('2024-06-01'),
            racine: 'MS',
            dernierChronoAvantReset: 0,
            type: 'new',
            professionCode: 'OPTLO',
            profession: 'OPTLO',
            nationate: null,
            professionObj: $profession
        );

        $this->assertSame('MS2024OPTLO1579.2988', $code);
    }
}
