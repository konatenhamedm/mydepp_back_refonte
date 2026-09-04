<?php

namespace App\Tests\Service;

use App\Entity\Professionnel;
use App\Entity\Transaction;
use App\Entity\User;
use App\Repository\ProfessionnelRepository;
use App\Repository\TempEtablissementRepository;
use App\Repository\TempProfessionnelRepository;
use App\Repository\TransactionRepository;
use App\Repository\UserRepository;
use App\Service\PaiementProService;
use App\Service\PaiementService;
use App\Service\Utils;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Confirme le comportement réel du renouvellement (circuit Pro / Mobile Money,
 * seul chemin appelé par le frontend) sur le numéro d'inscription :
 * PaiementProService::finaliserRenouvellement ne fait que remplacer le bloc
 * "année" du code existant par (année du code + nombre d'années payées) ;
 * tout le reste du numéro (préfixe, profession, jour/année de naissance, séquence)
 * est conservé à l'identique.
 *
 * Couvre aussi le correctif anti-double-traitement : avant, un même paiement
 * confirmé pouvait déclencher deux fois la mise à jour du code (webhook MTN +
 * polling frontend), faisant sauter l'année deux fois de suite (ex: 2024→2026
 * puis 2026→2028 au lieu de s'arrêter à 2026).
 */
class PaiementProServiceTest extends TestCase
{
    private TransactionRepository $transactionRepository;
    private EntityManagerInterface $em;
    private PaiementProService $service;

    protected function setUp(): void
    {
        $this->transactionRepository = $this->createMock(TransactionRepository::class);
        $this->em = $this->createMock(EntityManagerInterface::class);

        $this->service = new PaiementProService(
            $this->transactionRepository,
            $this->createMock(UserRepository::class),
            $this->createMock(HttpClientInterface::class),
            $this->createMock(Utils::class),
            $this->createMock(ProfessionnelRepository::class),
            $this->em,
            $this->createMock(TempProfessionnelRepository::class),
            $this->createMock(TempEtablissementRepository::class),
            $this->createMock(PaiementService::class),
            $this->createMock(LoggerInterface::class)
        );
    }

    private function makeTransaction(User $user, ?int $nbreAnnee, ?int $yearDue, array $extraData = []): Transaction
    {
        $transaction = new Transaction();
        $transaction->setUser($user);
        $transaction->setNbreAnnee($nbreAnnee);
        $transaction->setData(json_encode(array_merge(
            ['yearsToPay' => $nbreAnnee, 'yearDue' => $yearDue],
            $extraData
        )));

        return $transaction;
    }

    public function testRenouvellementCompletRemplaceUniquementLAnneeDuCodeEtPasseAJour(): void
    {
        $personne = new Professionnel();
        $personne->setCode('MS2024OPTLO1579.2987'); // équivalent de "MS2024OPTLOxxx"
        $personne->setStatus('en_retard');

        $user = new User();
        $user->setPersonne($personne);

        // 2 années dues (2026 - 2024), 2 années payées => régularisation complète
        $transaction = $this->makeTransaction($user, 2, 2);

        $this->transactionRepository->expects($this->once())
            ->method('add')
            ->with($transaction, true);
        $this->em->expects($this->once())->method('flush');

        $result = $this->service->finaliserRenouvellement($transaction);

        $this->assertSame(['code' => 200, 'message' => 'Success'], $result);
        $this->assertSame('MS2026OPTLO1579.2987', $personne->getCode());
        $this->assertSame('a_jour', $personne->getStatus());
        $this->assertSame(1, $transaction->getState());

        $expected = (new \DateTime())->modify('+1 year');
        $this->assertEqualsWithDelta(
            $expected->getTimestamp(),
            $personne->getDateValidation()->getTimestamp(),
            5,
            'La nouvelle expiration doit être ~aujourd\'hui + 1 an'
        );
    }

    public function testRenouvellementPartielAvanceLAnneeDuCodeSansChangerLeStatus(): void
    {
        $personne = new Professionnel();
        $personne->setCode('MS2024OPTLO1579.2987');
        $personne->setStatus('en_retard');
        $currentExpiration = new \DateTime('2025-08-31');
        $personne->setDateValidation($currentExpiration);

        $user = new User();
        $user->setPersonne($personne);

        // 2 années dues, mais seulement 1 payée => paiement partiel
        $transaction = $this->makeTransaction($user, 1, 2);

        $result = $this->service->finaliserRenouvellement($transaction);

        $this->assertSame(['code' => 200, 'message' => 'Success'], $result);
        // Seule l'année avance (2024 + 1 année payée = 2025), le reste du numéro est inchangé
        $this->assertSame('MS2025OPTLO1579.2987', $personne->getCode());
        // Statut NON régularisé : inchangé
        $this->assertSame('en_retard', $personne->getStatus());
        // Expiration = expiration actuelle + années payées (pas +1 an fixe)
        $expected = (clone $currentExpiration)->modify('+1 year');
        $this->assertEquals($expected, $personne->getDateValidation());
    }

    public function testCodeSansAnneeReconnaissableResteInchange(): void
    {
        $personne = new Professionnel();
        $personne->setCode('CODE-SANS-ANNEE');

        $user = new User();
        $user->setPersonne($personne);

        $transaction = $this->makeTransaction($user, 1, 1);

        $this->service->finaliserRenouvellement($transaction);

        $this->assertSame('CODE-SANS-ANNEE', $personne->getCode());
    }

    public function testSansUtilisateurRetourne400(): void
    {
        $transaction = new Transaction();
        // pas de setUser()

        $result = $this->service->finaliserRenouvellement($transaction);

        $this->assertSame(['code' => 400, 'message' => 'User not found'], $result);
    }

    public function testSansPersonneRetourne400(): void
    {
        $user = new User();
        // pas de setPersonne()
        $transaction = new Transaction();
        $transaction->setUser($user);

        $result = $this->service->finaliserRenouvellement($transaction);

        $this->assertSame(['code' => 400, 'message' => 'Personne not found'], $result);
    }

    /**
     * Régression du bug rapporté : un même paiement confirmé pouvait être
     * finalisé deux fois (webhook MTN + endpoint de polling du frontend, ou
     * webhook rejoué), faisant sauter le code deux fois (2024→2026→2028).
     * Le garde-fou sur `getState() === 1` doit rendre le second appel neutre.
     */
    public function testDeuxiemeAppelSurTransactionDejaFinaliseeNeReincrementePasLeCode(): void
    {
        $personne = new Professionnel();
        $personne->setCode('MS2024OPTLO1579.2987');

        $user = new User();
        $user->setPersonne($personne);

        $transaction = $this->makeTransaction($user, 2, 2);

        // Un seul add()/flush() attendu sur les DEUX appels : le second doit
        // s'arrêter avant d'écrire quoi que ce soit.
        $this->transactionRepository->expects($this->once())->method('add');
        $this->em->expects($this->once())->method('flush');

        $first = $this->service->finaliserRenouvellement($transaction);
        $this->assertSame('MS2026OPTLO1579.2987', $personne->getCode());
        $this->assertSame(200, $first['code']);

        $second = $this->service->finaliserRenouvellement($transaction);

        $this->assertSame('MS2026OPTLO1579.2987', $personne->getCode(), 'Le code ne doit pas avancer une deuxième fois');
        $this->assertSame(['code' => 200, 'message' => 'Renouvellement déjà finalisé'], $second);
    }

    /**
     * Même scénario de double traitement, mais en simulant le cas où le
     * garde-fou d'état serait contourné (ex: vraie course webhook/polling où
     * les deux lectures de `state` ont lieu avant qu'aucune écriture ne soit
     * commitée). Le calcul doit rester ABSOLU (année d'origine + années
     * payées, capturée dans Transaction::data à l'initiation du paiement),
     * pas relatif à l'année déjà présente dans le code — donc même rejoué
     * "sans protection", le résultat ne doit pas composer.
     */
    public function testCalculResteIdempotentMemeSiLaGardeDEtatEstContournee(): void
    {
        $personne = new Professionnel();
        $personne->setCode('MS2024OPTLO1579.2987');

        $user = new User();
        $user->setPersonne($personne);

        // yearInCodeAtInit capturé à l'initiation (avant toute mise à jour) : 2024
        $transaction = $this->makeTransaction($user, 2, 2, ['yearInCodeAtInit' => 2024]);

        $this->transactionRepository->expects($this->exactly(2))->method('add');
        $this->em->expects($this->exactly(2))->method('flush');

        $this->service->finaliserRenouvellement($transaction);
        $this->assertSame('MS2026OPTLO1579.2987', $personne->getCode());

        // Simule une course : un deuxième traitement démarre alors que l'état
        // n'est pas encore passé à 1 côté base (on force state=0 pour
        // contourner volontairement la garde testée ci-dessus).
        $transaction->setState(0);
        $this->service->finaliserRenouvellement($transaction);

        // Toujours 2026, pas 2028 : la valeur cible est recalculée depuis
        // yearInCodeAtInit (2024) + yearsPaid (2), pas depuis le code déjà modifié.
        $this->assertSame('MS2026OPTLO1579.2987', $personne->getCode());
    }
}
