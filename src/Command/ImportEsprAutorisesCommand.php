<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Civilite;
use App\Entity\District;
use App\Entity\Etablissement;
use App\Entity\Genre;
use App\Entity\NatureEtablissement;
use App\Entity\NiveauIntervention;
use App\Entity\OrganismeEnregistrement;
use App\Entity\Profession;
use App\Entity\Region;
use App\Entity\ResponsabiliteMedicolegale;
use App\Entity\StatutJuridique;
use App\Entity\TypePersonne;
use App\Entity\User;
use App\Service\EsprListParser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
    name: 'app:import-espr-autorises',
    description: 'Importe et synchronise la liste officielle des ESPR autorisés par la DEPPS (création automatique des référentiels manquants, géolocalisation et dates d’expiration).'
)]
final class ImportEsprAutorisesCommand extends Command
{
    private const DEFAULT_REL_FILE = 'data/import/liste_espr_autorises_aout2023_juin2026.csv';
    private const BATCH_SIZE = 200;

    /** @var array<string, TypePersonne> */
    private array $typePersonnes = [];

    /** @var array<string, NatureEtablissement> */
    private array $natures = [];

    /** @var array<string, NiveauIntervention> */
    private array $niveaux = [];

    /** @var array<string, StatutJuridique> */
    private array $statutsJuridiques = [];

    /** @var array<string, Region> */
    private array $regions = [];

    /** @var array<string, District> */
    private array $districts = [];

    /** @var array<string, Civilite> */
    private array $civilites = [];

    /** @var array<string, Genre> */
    private array $genres = [];

    /** @var array<string, Profession> */
    private array $professions = [];

    /** @var array<string, ResponsabiliteMedicolegale> */
    private array $responsabilites = [];

    private ?OrganismeEnregistrement $organismeDepps = null;

    /** @var array<string, int> */
    private array $createdRefCounts = [
        'type_personne' => 0,
        'nature_etablissement' => 0,
        'niveau_intervention' => 0,
        'statut_juridique' => 0,
        'region' => 0,
        'district' => 0,
        'civilite' => 0,
        'genre' => 0,
        'profession' => 0,
        'responsabilite_medicolegale' => 0,
        'organisme_enregistrement' => 0,
    ];

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly EsprListParser $parser,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'file',
                'f',
                InputOption::VALUE_REQUIRED,
                'Chemin du fichier à importer (.csv, .xlsx, .xls, .json)',
                $this->projectDir . '/' . self::DEFAULT_REL_FILE
            )
            ->addOption(
                'dir',
                null,
                InputOption::VALUE_REQUIRED,
                'Dossier de dépôt automatique (mode cron) : importe tous les fichiers .csv/.xlsx présents puis les archive dans processed/'
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Analyse et valide le fichier sans écrire en base de données'
            )
            ->addOption(
                'no-dedup',
                null,
                InputOption::VALUE_NONE,
                'Désactive la fusion automatique des renouvellements successifs d’un même établissement'
            )
            ->addOption(
                'sync-expired-status',
                null,
                InputOption::VALUE_NONE,
                'Passe le statut à "renouvellement" si la date d’expiration de l’arrêté est dépassée (par défaut tous sont insérés à "oep_dossier_conforme")'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = (bool) $input->getOption('dry-run');
        $noDedup = (bool) $input->getOption('no-dedup');
        $syncExpired = (bool) $input->getOption('sync-expired-status');
        $watchDir = $input->getOption('dir');

        $filesToProcess = [];
        $archiveAfter = false;

        if (\is_string($watchDir) && trim($watchDir) !== '') {
            if (!is_dir($watchDir)) {
                $io->warning(sprintf('Dossier de dépôt inexistant : %s (aucun fichier à traiter).', $watchDir));
                return Command::SUCCESS;
            }
            $found = glob(rtrim($watchDir, '/') . '/*.{csv,xlsx,xls,json}', \GLOB_BRACE) ?: [];
            sort($found);
            if ($found === []) {
                $io->info(sprintf('Aucun nouveau fichier à importer dans %s.', $watchDir));
                return Command::SUCCESS;
            }
            $filesToProcess = $found;
            $archiveAfter = !$dryRun;
        } else {
            $filePath = (string) $input->getOption('file');
            if (!str_starts_with($filePath, '/')) {
                $filePath = $this->projectDir . '/' . ltrim($filePath, '/');
            }
            $filesToProcess = [$filePath];
        }

        foreach ($filesToProcess as $file) {
            $io->section(sprintf('Traitement du fichier : %s', $file));

            try {
                $parsedRows = $this->parser->parseFile($file);
            } catch (\Throwable $e) {
                $io->error('Erreur de lecture du fichier : ' . $e->getMessage());
                return Command::FAILURE;
            }

            if ($noDedup) {
                $records = $parsedRows;
                $dedupStats = [
                    'input_rows' => \count($parsedRows),
                    'exact_duplicates' => 0,
                    'renewals_merged' => 0,
                    'unique_establishments' => \count($parsedRows),
                ];
            } else {
                $dedup = $this->parser->deduplicateAndMergeRenewals($parsedRows);
                $records = $dedup['records'];
                $dedupStats = $dedup['stats'];
            }

            $withGeo = \count(array_filter($records, static fn ($r) => $r['latitude'] !== null && $r['longitude'] !== null));
            $withSigDate = \count(array_filter($records, static fn ($r) => $r['dateSignatureArrete'] instanceof \DateTimeInterface));
            $withExpDate = \count(array_filter($records, static fn ($r) => $r['dateExpirationArrete'] instanceof \DateTimeInterface));

            $io->table(
                ['Indicateur d’analyse', 'Valeur'],
                [
                    ['Lignes lues dans le document', (string) $dedupStats['input_rows']],
                    ['Doublons exacts fusionnés', (string) $dedupStats['exact_duplicates']],
                    ['Renouvellements fusionnés (arrêté le plus récent conservé)', (string) $dedupStats['renewals_merged']],
                    ['Établissements uniques à importer / mettre à jour', (string) $dedupStats['unique_establishments']],
                    ['Dates de signature d’arrêté extraites', sprintf('%d / %d', $withSigDate, \count($records))],
                    ['Dates d’expiration extraites', sprintf('%d / %d', $withExpDate, \count($records))],
                    ['Établissements géolocalisés (WGS84)', sprintf('%d / %d', $withGeo, \count($records))],
                ]
            );

            if ($dryRun) {
                $io->success('Mode --dry-run terminé avec succès : aucune écriture effectuée en base de données.');
                continue;
            }

            try {
                $importResult = $this->persistRecords($records, $syncExpired, $io);
            } catch (\Throwable $e) {
                $io->error('Erreur lors de l’insertion en base de données : ' . $e->getMessage());
                return Command::FAILURE;
            }

            $refRows = [];
            foreach ($this->createdRefCounts as $refName => $cnt) {
                if ($cnt > 0) {
                    $refRows[] = [$refName, (string) $cnt];
                }
            }

            $io->table(
                ['Résultat de synchronisation en base', 'Nombre'],
                [
                    ['Nouveaux établissements créés (validés DEPPS)', (string) $importResult['created']],
                    ['Établissements existants mis à jour', (string) $importResult['updated']],
                    ['Comptes utilisateurs ETABLISSEMENT associés créés', (string) $importResult['users_created']],
                ]
            );

            if ($refRows !== []) {
                $io->table(['Référentiel auto-créé en base', 'Nouvelles entrées'], $refRows);
            }

            if ($archiveAfter) {
                $processedDir = rtrim((string) $watchDir, '/') . '/processed';
                if (!is_dir($processedDir)) {
                    mkdir($processedDir, 0775, true);
                }
                $dest = $processedDir . '/' . date('Ymd_His') . '_' . basename($file);
                rename($file, $dest);
                $io->info(sprintf('Fichier archivé vers : %s', $dest));
            }
        }

        $io->success('Importation des ESPR autorisés terminée avec succès.');

        return Command::SUCCESS;
    }

    /**
     * @param list<array<string, mixed>> $records
     * @return array{created: int, updated: int, users_created: int}
     */
    private function persistRecords(array $records, bool $syncExpired, SymfonyStyle $io): array
    {
        $this->preloadReferences();
        [$existingByImmat, $existingByArrete, $existingByDossierDenom, $existingByDenomDistrict, $linkedPersonneIds] = $this->preloadExistingEstablishments();

        // Mot de passe par défaut haché une seule fois pour les comptes établissements importés sans accès initial
        $defaultPasswordHash = password_hash(bin2hex(random_bytes(16)), \PASSWORD_BCRYPT);
        $now = new \DateTimeImmutable();

        $created = 0;
        $updated = 0;
        $usersCreated = 0;
        $seqCounter = $this->countCurrentEstablishments();

        $io->progressStart(\count($records));

        foreach ($records as $idx => $row) {
            $etab = $this->findMatchingEntity(
                $row,
                $existingByImmat,
                $existingByArrete,
                $existingByDossierDenom,
                $existingByDenomDistrict
            );

            $isNew = $etab === null;
            if ($isNew) {
                $seqCounter++;
                $etab = new Etablissement();
                $etab->setCreatedAtValue($now);
                $created++;
            } else {
                $updated++;
            }

            $this->hydrateEtablissement($etab, $row, $seqCounter, $syncExpired, $now);
            $this->em->persist($etab);

            // Indexer immédiatement l'entité dans nos tables de correspondance mémoire
            $this->indexEntityInMaps($etab, $existingByImmat, $existingByArrete, $existingByDossierDenom, $existingByDenomDistrict);

            if ($isNew || ($etab->getId() !== null && !isset($linkedPersonneIds[$etab->getId()]))) {
                $user = new User();
                $user->setEmail(null);
                $user->setPassword($defaultPasswordHash);
                $user->setRoles(['ROLE_MEMBRE']);
                $user->setTypeUser(User::TYPE['ETABLISSEMENT']);
                $user->setPayement(User::PAYEMENT['payed']);
                $user->setIsActive(true);
                $user->setPersonne($etab);
                $user->setCreatedAtValue($now);
                $user->setUpdatedAt($now);
                $this->em->persist($user);
                $usersCreated++;
            }

            if ((($idx + 1) % self::BATCH_SIZE) === 0) {
                $this->em->flush();
            }

            $io->progressAdvance();
        }

        $this->em->flush();
        $io->progressFinish();

        return [
            'created' => $created,
            'updated' => $updated,
            'users_created' => $usersCreated,
        ];
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrateEtablissement(
        Etablissement $etab,
        array $row,
        int $seqCounter,
        bool $syncExpired,
        \DateTimeImmutable $now
    ): void {
        // Statut déjà validé par la DEPPS
        $dateExp = $row['dateExpirationArrete'] instanceof \DateTimeInterface ? $row['dateExpirationArrete'] : null;
        $dateSig = $row['dateSignatureArrete'] instanceof \DateTimeInterface ? $row['dateSignatureArrete'] : null;

        if ($syncExpired && $dateExp !== null && $dateExp < $now) {
            $etab->setStatus('renouvellement');
        } else {
            $etab->setStatus('oep_dossier_conforme');
        }

        $etab->setActived(true);
        $etab->setEnregistreeDepps(true);
        $etab->setAccordMinistere(true);
        $etab->setACertificatConformite(true);
        $etab->setOrganismeEnregistrement($this->getOrCreateOrganismeDepps());

        if ($etab->getCode() === null || $etab->getCode() === '') {
            $code = $row['codeIdentifiant'] ?: sprintf('DEPPS-ESPR-%s-%04d', date('Y'), $seqCounter);
            $etab->setCode($code);
        }

        if (!empty($row['denomination'])) {
            $etab->setDenomination($row['denomination']);
        }

        // Référentiels ManyToOne avec création automatique si absents en base
        $typePersonne = $this->getOrCreateTypePersonne(
            (string) $row['typePersonneCode'],
            (string) $row['typePersonneLibelle']
        );
        $etab->setTypePersonne($typePersonne);

        if (!empty($row['natureLibelle'])) {
            $etab->setNatureEtablissement($this->getOrCreateNature((string) $row['natureLibelle'], $row['natureCode']));
        }
        if (!empty($row['niveauLibelle'])) {
            $etab->setNiveauIntervention($this->getOrCreateNiveau((string) $row['niveauLibelle'], $row['niveauCode']));
        }
        if (!empty($row['statutJuridiqueLibelle'])) {
            $etab->setStatutJuridique($this->getOrCreateStatutJuridique((string) $row['statutJuridiqueLibelle'], $row['statutJuridiqueCode']));
        }
        if (!empty($row['typeSociete'])) {
            $etab->setTypeSociete($row['typeSociete']);
        }
        if (!empty($row['genrePromoteur'])) {
            $etab->setGenre($this->getOrCreateGenre((string) $row['genrePromoteur']));
        }

        $region = null;
        if (!empty($row['regionLibelle'])) {
            $region = $this->getOrCreateRegion((string) $row['regionLibelle'], $row['regionCode']);
            $etab->setRegion($region);
        }
        if (!empty($row['districtLibelle'])) {
            $etab->setDistrict($this->getOrCreateDistrict((string) $row['districtLibelle'], $region));
        }

        // Dossier, arrêté et dates officielles
        if (!empty($row['numeroDossier'])) {
            $etab->setNumeroDossier($row['numeroDossier']);
            $etab->setNumeroEnregistrement($row['numeroDossier']);
        }
        if (!empty($row['anneeCreation']) && !$etab->getAnneeCreation()) {
            $etab->setAnneeCreation($row['anneeCreation']);
        }
        if (!empty($row['arreteAutorisation'])) {
            $etab->setArreteAutorisation($row['arreteAutorisation']);
        }
        if (!empty($row['numeroArrete'])) {
            $etab->setNumeroArrete($row['numeroArrete']);
        }
        if ($dateSig !== null) {
            $mutableSig = \DateTime::createFromInterface($dateSig);
            $etab->setDateSignatureArrete($mutableSig);
            $etab->setDateValidation($mutableSig);
        }
        if (!empty($row['anneeAutorisation'])) {
            $etab->setAnneeAutorisation($row['anneeAutorisation']);
        }
        if ($dateExp !== null) {
            $mutableExp = \DateTime::createFromInterface($dateExp);
            $etab->setDateExpirationArrete($mutableExp);
            $etab->setDateValiditeAccord($mutableExp);
            $etab->setDateValiditeCertificat($mutableExp);
        }

        // Localisation & sous-champs d'adresse (ne remplace pas une valeur déjà saisie si la nouvelle est vide)
        $this->setIfNotEmpty($etab, 'setAdresse', $row['adresse']);
        $this->setIfNotEmpty($etab, 'setVilleVillage', $row['villeVillage']);
        $this->setIfNotEmpty($etab, 'setCommune', $row['commune']);
        $this->setIfNotEmpty($etab, 'setQuartier', $row['quartier']);
        $this->setIfNotEmpty($etab, 'setZoneSecteur', $row['zoneSecteur']);
        $this->setIfNotEmpty($etab, 'setIlotNumero', $row['ilotNumero']);
        $this->setIfNotEmpty($etab, 'setLotNumero', $row['lotNumero']);
        $this->setIfNotEmpty($etab, 'setRueAvenue', $row['rueAvenue']);
        $this->setIfNotEmpty($etab, 'setVillaImmeubleEtagePorte', $row['villaImmeubleEtagePorte']);
        $this->setIfNotEmpty($etab, 'setPointDeRepere', $row['pointDeRepere']);

        // Géolocalisation : préserve une position déjà corrigée manuellement sur la carte
        if ($etab->getPrecisionGeolocalisation() !== 'manuelle') {
            if ($row['latitude'] !== null && $row['longitude'] !== null) {
                $etab->setLatitude((string) $row['latitude']);
                $etab->setLongitude((string) $row['longitude']);
                $etab->setLieuGeolocalisation($row['lieuGeolocalisation']);
                $etab->setPrecisionGeolocalisation($row['precisionGeolocalisation']);
            }
        }

        // Contacts, Code Identifiant, Immatriculation
        $this->setIfNotEmpty($etab, 'setTelephone', $row['telephone']);
        $this->setIfNotEmpty($etab, 'setTelephoneFixe', $row['telephoneFixe']);
        $this->setIfNotEmpty($etab, 'setTelephoneMobile', $row['telephoneMobile']);
        $this->setIfNotEmpty($etab, 'setTelephoneAutre', $row['telephoneAutre']);
        $this->setIfNotEmpty($etab, 'setCodeIdentifiant', $row['codeIdentifiant']);
        $this->setIfNotEmpty($etab, 'setImmatriculation', $row['immatriculation']);

        // Promoteur / Propriétaire
        $this->setIfNotEmpty($etab, 'setQualitePromoteur', $row['qualitePromoteur']);
        $this->setIfNotEmpty($etab, 'setPromoteurProprietaire', $row['promoteurProprietaire']);
        if (!empty($row['civilitePromoteur'])) {
            $etab->setCivilite($this->getOrCreateCivilite((string) $row['civilitePromoteur']));
        }
        $this->setIfNotEmpty($etab, 'setNom', $row['nom']);
        $this->setIfNotEmpty($etab, 'setPrenoms', $row['prenoms']);
        if (!empty($row['professionPromoteur'])) {
            $etab->setProfession($this->getOrCreateProfession((string) $row['professionPromoteur']));
        }
        if (!empty($row['representantCivilite'])) {
            $etab->setRepresentantCivilite($this->getOrCreateCivilite((string) $row['representantCivilite']));
        }
        $this->setIfNotEmpty($etab, 'setNomRepresentant', $row['nomRepresentant']);
        $this->setIfNotEmpty($etab, 'setRepresentantQualite', $row['representantQualite']);

        // Responsable technique de santé
        $this->setIfNotEmpty($etab, 'setResponsableTechniqueSante', $row['responsableTechniqueSante']);
        if (!empty($row['responsableCivilite'])) {
            $etab->setResponsableCivilite($this->getOrCreateCivilite((string) $row['responsableCivilite']));
        }
        $this->setIfNotEmpty($etab, 'setResponsableNom', $row['responsableNom']);
        $this->setIfNotEmpty($etab, 'setResponsableProfession', $row['responsableProfession']);
        $this->setIfNotEmpty($etab, 'setResponsableNumeroOrdre', $row['responsableNumeroOrdre']);
        $this->setIfNotEmpty($etab, 'setResponsableSexe', $row['responsableSexe']);
        if ($row['responsableEstResident'] !== null) {
            $etab->setResponsableEstResident((bool) $row['responsableEstResident']);
        }
        if (!empty($row['responsabiliteMedicolegale'])) {
            $etab->setResponsabiliteMedicolegale($this->getOrCreateResponsabilite((string) $row['responsabiliteMedicolegale']));
        }

        $etab->setUpdatedAt($now);
    }

    private function setIfNotEmpty(Etablissement $etab, string $setter, mixed $value): void
    {
        if ($value !== null && $value !== '') {
            $etab->{$setter}($value);
        }
    }

    private function preloadReferences(): void
    {
        foreach ($this->em->getRepository(TypePersonne::class)->findAll() as $tp) {
            if ($tp->getCode()) {
                $this->typePersonnes[EsprListParser::normalizeKey($tp->getCode())] = $tp;
            }
            if ($tp->getLibelle()) {
                $this->typePersonnes[EsprListParser::normalizeKey($tp->getLibelle())] = $tp;
            }
        }

        foreach ($this->em->getRepository(NatureEtablissement::class)->findAll() as $nat) {
            if ($nat->getLibelle()) {
                $this->natures[EsprListParser::normalizeKey($nat->getLibelle())] = $nat;
            }
        }

        foreach ($this->em->getRepository(NiveauIntervention::class)->findAll() as $niv) {
            if ($niv->getLibelle()) {
                $this->niveaux[EsprListParser::normalizeKey($niv->getLibelle())] = $niv;
            }
        }

        foreach ($this->em->getRepository(StatutJuridique::class)->findAll() as $sj) {
            if ($sj->getLibelle()) {
                $this->statutsJuridiques[EsprListParser::normalizeKey($sj->getLibelle())] = $sj;
            }
            if ($sj->getCode()) {
                $this->statutsJuridiques[EsprListParser::normalizeKey($sj->getCode())] = $sj;
            }
        }

        foreach ($this->em->getRepository(Region::class)->findAll() as $reg) {
            if ($reg->getLibelle()) {
                $this->regions[EsprListParser::normalizeKey($reg->getLibelle())] = $reg;
            }
        }

        foreach ($this->em->getRepository(District::class)->findAll() as $dist) {
            if ($dist->getLibelle()) {
                $regKey = $dist->getRegion()?->getLibelle() ? EsprListParser::normalizeKey($dist->getRegion()->getLibelle()) : '';
                $distKey = EsprListParser::normalizeKey($dist->getLibelle());
                $this->districts[$regKey . '|' . $distKey] = $dist;
                $this->districts['*|' . $distKey] ??= $dist;
            }
        }

        foreach ($this->em->getRepository(Civilite::class)->findAll() as $civ) {
            if ($civ->getLibelle()) {
                $this->civilites[EsprListParser::normalizeKey($civ->getLibelle())] = $civ;
            }
            if ($civ->getCode()) {
                $this->civilites[EsprListParser::normalizeKey($civ->getCode())] = $civ;
            }
        }

        foreach ($this->em->getRepository(Genre::class)->findAll() as $g) {
            if ($g->getLibelle()) {
                $this->genres[EsprListParser::normalizeKey($g->getLibelle())] = $g;
            }
        }

        foreach ($this->em->getRepository(Profession::class)->findAll() as $prof) {
            if ($prof->getLibelle()) {
                $this->professions[EsprListParser::normalizeKey($prof->getLibelle())] = $prof;
            }
        }

        foreach ($this->em->getRepository(ResponsabiliteMedicolegale::class)->findAll() as $rm) {
            if ($rm->getLibelle()) {
                $this->responsabilites[EsprListParser::normalizeKey($rm->getLibelle())] = $rm;
            }
        }

        foreach ($this->em->getRepository(OrganismeEnregistrement::class)->findAll() as $org) {
            if ($org->getCode() === 'DEPPS' || EsprListParser::normalizeKey((string) $org->getLibelle()) === 'depps') {
                $this->organismeDepps = $org;
                break;
            }
        }
    }

    /**
     * @return array{
     *   0: array<string, Etablissement>,
     *   1: array<string, Etablissement>,
     *   2: array<string, Etablissement>,
     *   3: array<string, Etablissement>,
     *   4: array<int, true>
     * }
     */
    private function preloadExistingEstablishments(): array
    {
        $byImmat = [];
        $byArrete = [];
        $byDossierDenom = [];
        $byDenomDistrict = [];

        foreach ($this->em->getRepository(Etablissement::class)->findAll() as $etab) {
            $this->indexEntityInMaps($etab, $byImmat, $byArrete, $byDossierDenom, $byDenomDistrict);
        }

        $linkedIds = [];
        $rows = $this->em->createQuery('SELECT IDENTITY(u.personne) AS pid FROM App\Entity\User u WHERE u.personne IS NOT NULL')
            ->getScalarResult();
        foreach ($rows as $r) {
            if (!empty($r['pid'])) {
                $linkedIds[(int) $r['pid']] = true;
            }
        }

        return [$byImmat, $byArrete, $byDossierDenom, $byDenomDistrict, $linkedIds];
    }

    /**
     * @param array<string, Etablissement> $byImmat
     * @param array<string, Etablissement> $byArrete
     * @param array<string, Etablissement> $byDossierDenom
     * @param array<string, Etablissement> $byDenomDistrict
     */
    private function indexEntityInMaps(
        Etablissement $etab,
        array &$byImmat,
        array &$byArrete,
        array &$byDossierDenom,
        array &$byDenomDistrict
    ): void {
        $denom = EsprListParser::normalizeKey((string) $etab->getDenomination());
        $immat = EsprListParser::normalizeKey((string) $etab->getImmatriculation());
        $arrete = EsprListParser::normalizeKey((string) ($etab->getNumeroArrete() ?? $etab->getArreteAutorisation()));
        $dossier = EsprListParser::normalizeKey((string) ($etab->getNumeroDossier() ?? $etab->getNumeroEnregistrement()));
        $dist = EsprListParser::normalizeKey((string) $etab->getDistrict()?->getLibelle());

        if ($immat !== '') {
            $byImmat[$immat] = $etab;
        }
        if ($arrete !== '' && $denom !== '') {
            $byArrete[$arrete . '|' . $denom] = $etab;
        }
        if ($dossier !== '' && $denom !== '') {
            $byDossierDenom[$dossier . '|' . $denom] = $etab;
        }
        if ($denom !== '' && $dist !== '') {
            $byDenomDistrict[$denom . '|' . $dist] = $etab;
        }
    }

    /**
     * @param array<string, mixed> $row
     * @param array<string, Etablissement> $byImmat
     * @param array<string, Etablissement> $byArrete
     * @param array<string, Etablissement> $byDossierDenom
     * @param array<string, Etablissement> $byDenomDistrict
     */
    private function findMatchingEntity(
        array $row,
        array $byImmat,
        array $byArrete,
        array $byDossierDenom,
        array $byDenomDistrict
    ): ?Etablissement {
        $immat = EsprListParser::normalizeKey((string) ($row['immatriculation'] ?? ''));
        if ($immat !== '' && isset($byImmat[$immat])) {
            return $byImmat[$immat];
        }

        $denom = EsprListParser::normalizeKey((string) ($row['denomination'] ?? ''));
        $arrete = EsprListParser::normalizeKey((string) ($row['numeroArrete'] ?? $row['arreteAutorisation'] ?? ''));
        if ($arrete !== '' && $denom !== '' && isset($byArrete[$arrete . '|' . $denom])) {
            return $byArrete[$arrete . '|' . $denom];
        }

        $dossier = EsprListParser::normalizeKey((string) ($row['numeroDossier'] ?? ''));
        if ($dossier !== '' && $denom !== '' && isset($byDossierDenom[$dossier . '|' . $denom])) {
            return $byDossierDenom[$dossier . '|' . $denom];
        }

        $dist = EsprListParser::normalizeKey((string) ($row['districtLibelle'] ?? ''));
        if ($denom !== '' && $dist !== '' && isset($byDenomDistrict[$denom . '|' . $dist])) {
            $candidate = $byDenomDistrict[$denom . '|' . $dist];
            $candDossier = EsprListParser::normalizeKey((string) ($candidate->getNumeroDossier() ?? $candidate->getNumeroEnregistrement()));
            if ($candDossier === '' || $candDossier === $dossier) {
                return $candidate;
            }
        }

        return null;
    }

    private function countCurrentEstablishments(): int
    {
        return (int) $this->em->createQuery('SELECT COUNT(e.id) FROM App\Entity\Etablissement e')->getSingleScalarResult();
    }

    private function getOrCreateTypePersonne(string $code, string $libelle): TypePersonne
    {
        $kCode = EsprListParser::normalizeKey($code);
        $kLib = EsprListParser::normalizeKey($libelle);
        if (isset($this->typePersonnes[$kCode])) {
            return $this->typePersonnes[$kCode];
        }
        if (isset($this->typePersonnes[$kLib])) {
            return $this->typePersonnes[$kLib];
        }

        $entity = new TypePersonne();
        $entity->setCode(substr(strtoupper($code), 0, 10));
        $entity->setLibelle($libelle);
        $this->em->persist($entity);
        $this->createdRefCounts['type_personne']++;

        $this->typePersonnes[$kCode] = $entity;
        $this->typePersonnes[$kLib] = $entity;

        return $entity;
    }

    private function getOrCreateNature(string $libelle, ?string $code): NatureEtablissement
    {
        $key = EsprListParser::normalizeKey($libelle);
        if (isset($this->natures[$key])) {
            return $this->natures[$key];
        }

        $entity = new NatureEtablissement();
        $entity->setLibelle($libelle);
        $entity->setCode($code !== null ? substr($code, 0, 10) : null);
        $this->em->persist($entity);
        $this->createdRefCounts['nature_etablissement']++;

        return $this->natures[$key] = $entity;
    }

    private function getOrCreateNiveau(string $libelle, ?string $code): NiveauIntervention
    {
        $key = EsprListParser::normalizeKey($libelle);
        if (isset($this->niveaux[$key])) {
            return $this->niveaux[$key];
        }

        $entity = new NiveauIntervention();
        $entity->setLibelle($libelle);
        $entity->setCode($code ?: strtoupper(substr(str_replace(' ', '_', $key), 0, 30)));
        $entity->setMontant('0');
        $entity->setMontantRenouvellement('0');
        $this->em->persist($entity);
        $this->createdRefCounts['niveau_intervention']++;

        return $this->niveaux[$key] = $entity;
    }

    private function getOrCreateStatutJuridique(string $libelle, ?string $code): StatutJuridique
    {
        $key = EsprListParser::normalizeKey($libelle);
        if (isset($this->statutsJuridiques[$key])) {
            return $this->statutsJuridiques[$key];
        }
        if ($code !== null && isset($this->statutsJuridiques[EsprListParser::normalizeKey($code)])) {
            return $this->statutsJuridiques[EsprListParser::normalizeKey($code)];
        }

        // Si la base a "Entreprise Individuelle" pour Personne Physique, on crée explicitement "Personne Physique" tout en évitant les doublons
        $entity = new StatutJuridique();
        $entity->setLibelle($libelle);
        $entity->setCode($code !== null ? substr($code, 0, 10) : null);
        $this->em->persist($entity);
        $this->createdRefCounts['statut_juridique']++;

        $this->statutsJuridiques[$key] = $entity;
        if ($code !== null) {
            $this->statutsJuridiques[EsprListParser::normalizeKey($code)] = $entity;
        }

        return $entity;
    }

    private function getOrCreateRegion(string $libelle, ?string $code): Region
    {
        $key = EsprListParser::normalizeKey($libelle);
        if (isset($this->regions[$key])) {
            return $this->regions[$key];
        }

        $entity = new Region();
        $entity->setLibelle($libelle);
        $entity->setCode($code ?: strtoupper(substr(str_replace(' ', '_', $key), 0, 20)));
        $this->em->persist($entity);
        $this->createdRefCounts['region']++;

        return $this->regions[$key] = $entity;
    }

    private function getOrCreateDistrict(string $libelle, ?Region $region): District
    {
        $regKey = $region?->getLibelle() ? EsprListParser::normalizeKey($region->getLibelle()) : '';
        $distKey = EsprListParser::normalizeKey($libelle);
        $composite = $regKey . '|' . $distKey;

        if (isset($this->districts[$composite])) {
            return $this->districts[$composite];
        }
        if (isset($this->districts['*|' . $distKey])) {
            $existing = $this->districts['*|' . $distKey];
            if ($existing->getRegion() === null && $region !== null) {
                $existing->setRegion($region);
            }
            if ($existing->getRegion() === null || $existing->getRegion() === $region) {
                return $this->districts[$composite] = $existing;
            }
        }

        $entity = new District();
        $entity->setLibelle($libelle);
        $entity->setRegion($region);
        $this->em->persist($entity);
        $this->createdRefCounts['district']++;

        $this->districts[$composite] = $entity;
        $this->districts['*|' . $distKey] ??= $entity;

        return $entity;
    }

    private function getOrCreateCivilite(string $libelle): Civilite
    {
        $key = EsprListParser::normalizeKey($libelle);
        if (isset($this->civilites[$key])) {
            return $this->civilites[$key];
        }

        $shortCodes = [
            'monsieur' => 'M.',
            'madame' => 'Mme',
            'mademoiselle' => 'Mlle',
            'docteur' => 'Dr',
            'professeur' => 'Pr',
        ];
        $code = $shortCodes[$key] ?? strtoupper(substr($key, 0, 6));

        $entity = new Civilite();
        $entity->setLibelle($libelle);
        $entity->setCode($code);
        $this->em->persist($entity);
        $this->createdRefCounts['civilite']++;

        return $this->civilites[$key] = $entity;
    }

    private function getOrCreateGenre(string $libelle): Genre
    {
        $key = EsprListParser::normalizeKey($libelle);
        // Gère l'équivalence Masculin/Homme et Féminin/Femme si l'un des deux existe déjà en base
        $aliases = match ($key) {
            'masculin', 'homme' => ['masculin', 'homme'],
            'feminin', 'femme' => ['feminin', 'femme'],
            default => [$key],
        };
        foreach ($aliases as $alias) {
            if (isset($this->genres[$alias])) {
                return $this->genres[$alias];
            }
        }

        $entity = new Genre();
        $entity->setLibelle($libelle);
        $this->em->persist($entity);
        $this->createdRefCounts['genre']++;

        foreach ($aliases as $alias) {
            $this->genres[$alias] = $entity;
        }

        return $entity;
    }

    private function getOrCreateProfession(string $libelle): Profession
    {
        $key = EsprListParser::normalizeKey($libelle);
        if (isset($this->professions[$key])) {
            return $this->professions[$key];
        }

        $entity = new Profession();
        $entity->setLibelle($libelle);
        $entity->setCode(strtoupper(substr(str_replace(' ', '_', $key), 0, 20)));
        $entity->setMontantNouvelleDemande('0');
        $entity->setMontantRenouvellement('0');
        $this->em->persist($entity);
        $this->createdRefCounts['profession']++;

        return $this->professions[$key] = $entity;
    }

    private function getOrCreateResponsabilite(string $libelle): ResponsabiliteMedicolegale
    {
        $key = EsprListParser::normalizeKey($libelle);
        if (isset($this->responsabilites[$key])) {
            return $this->responsabilites[$key];
        }

        $code = match ($key) {
            'directeur medical' => 'DIR_MED',
            'responsable technique paramedical' => 'RT_PARAM',
            'responsable technique medical' => 'RT_MED',
            default => strtoupper(substr(str_replace(' ', '_', $key), 0, 10)),
        };

        $entity = new ResponsabiliteMedicolegale();
        $entity->setLibelle($libelle);
        $entity->setCode($code);
        $this->em->persist($entity);
        $this->createdRefCounts['responsabilite_medicolegale']++;

        return $this->responsabilites[$key] = $entity;
    }

    private function getOrCreateOrganismeDepps(): OrganismeEnregistrement
    {
        if ($this->organismeDepps !== null) {
            return $this->organismeDepps;
        }

        $org = new OrganismeEnregistrement();
        $org->setLibelle('DEPPS');
        $org->setCode('DEPPS');
        $this->em->persist($org);
        $this->createdRefCounts['organisme_enregistrement']++;

        return $this->organismeDepps = $org;
    }
}
