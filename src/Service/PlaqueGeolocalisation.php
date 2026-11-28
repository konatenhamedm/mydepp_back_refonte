<?php

namespace App\Service;

/**
 * Position estimée d'un établissement sanitaire à partir de sa localisation en texte,
 * de son code d'immatriculation, ou à défaut de son district / sa région sanitaire.
 *
 * Couvre les quartiers et communes du Grand Abidjan, l'ensemble des chefs-lieux de départements,
 * sous-préfectures, districts sanitaires et les 32 régions sanitaires de Côte d'Ivoire (WGS84).
 */
final class PlaqueGeolocalisation
{
    /** Quartiers d'Abidjan : testés en premier pour affiner la position à l'intérieur d'une commune. */
    private const QUARTIERS_ABIDJAN = [
        'riviera palmeraie' => ['Cocody (Riviera Palmeraie)', 5.3735, -3.9565],
        'riviera bonoumin' => ['Cocody (Riviera Bonoumin)', 5.3675, -3.9720],
        'riviera golf' => ['Cocody (Riviera Golf)', 5.3420, -3.9780],
        'riviera faya' => ['Cocody (Riviera Faya)', 5.3710, -3.9270],
        'riviera attoban' => ['Cocody (Attoban)', 5.3760, -3.9800],
        'attoban' => ['Cocody (Attoban)', 5.3760, -3.9800],
        'angre' => ['Cocody (Angré)', 5.4010, -3.9850],
        'deux plateaux' => ['Cocody (Deux-Plateaux)', 5.3750, -3.9980],
        '2 plateaux' => ['Cocody (Deux-Plateaux)', 5.3750, -3.9980],
        'ii plateaux' => ['Cocody (Deux-Plateaux)', 5.3750, -3.9980],
        'vallon' => ['Cocody (Vallon)', 5.3680, -3.9940],
        'danga' => ['Cocody (Danga)', 5.3440, -4.0050],
        'blockhauss' => ['Cocody (Blockhauss)', 5.3310, -3.9960],
        'm badon' => ['Cocody (M’Badon)', 5.3490, -3.9450],
        'mbadon' => ['Cocody (M’Badon)', 5.3490, -3.9450],
        'akouedo' => ['Cocody (Akouédo)', 5.3690, -3.9390],
        'dokui' => ['Abobo / Cocody (Dokui)', 5.4020, -4.0050],
        'pk 18' => ['Abobo (PK 18)', 5.4580, -4.0320],
        'n dotre' => ['Abobo (N’Dotré)', 5.4520, -4.0650],
        'ndotre' => ['Abobo (N’Dotré)', 5.4520, -4.0650],
        'avocatier' => ['Abobo (Avocatier)', 5.4310, -4.0070],
        'abobo baoule' => ['Abobo (Abobo-Baoulé)', 5.4260, -3.9950],
        'niangon' => ['Yopougon (Niangon)', 5.3290, -4.0980],
        'toits rouges' => ['Yopougon (Toits Rouges)', 5.3510, -4.0610],
        'gesco' => ['Yopougon (Gesco)', 5.3680, -4.1120],
        'ananeraie' => ['Yopougon (Ananeraie)', 5.3560, -4.0920],
        'williamsville' => ['Adjamé (Williamsville)', 5.3750, -4.0220],
        '220 logements' => ['Adjamé (220 Logements)', 5.3530, -4.0180],
        'zone 4' => ['Marcory (Zone 4)', 5.2880, -3.9780],
        'bietry' => ['Marcory (Biétry)', 5.2810, -3.9750],
        'anoumabo' => ['Marcory (Anoumabo)', 5.3090, -3.9820],
        'vridi' => ['Port-Bouët (Vridi)', 5.2580, -4.0020],
        'gonzagueville' => ['Port-Bouët (Gonzagueville)', 5.2430, -3.8920],
    ];

    /** Communes d'Abidjan : plus précises que « Abidjan », testées avant les villes. */
    private const COMMUNES_ABIDJAN = [
        'port bouet' => ['Port-Bouët', 5.2560, -3.9260],
        'treichville' => ['Treichville', 5.2950, -4.0080],
        'marcory' => ['Marcory', 5.3030, -3.9850],
        'koumassi' => ['Koumassi', 5.2950, -3.9550],
        'plateau' => ['Plateau', 5.3236, -4.0197],
        'adjame' => ['Adjamé', 5.3570, -4.0230],
        'attecoube' => ['Attécoubé', 5.3370, -4.0420],
        'yopougon' => ['Yopougon', 5.3450, -4.0800],
        'abobo' => ['Abobo', 5.4200, -4.0200],
        'cocody' => ['Cocody', 5.3600, -3.9900],
        'riviera' => ['Cocody (Riviera)', 5.3650, -3.9600],
        'bingerville' => ['Bingerville', 5.3550, -3.8850],
        'anyama' => ['Anyama', 5.4950, -4.0520],
        'songon' => ['Songon', 5.3100, -4.2600],
    ];

    /** Villes, départements et sous-préfectures de Côte d'Ivoire (incluant variantes orthographiques). */
    private const VILLES = [
        'yamoussoukro' => ['Yamoussoukro', 6.8200, -5.2770],
        'bouake' => ['Bouaké', 7.6900, -5.0300],
        'san pedro' => ['San-Pédro', 4.7480, -6.6360],
        'grand bereby' => ['Grand-Béréby', 4.6500, -6.9210],
        'daloa' => ['Daloa', 6.8770, -6.4500],
        'bonon' => ['Bonon', 6.9330, -6.0480],
        'zoukougbeu' => ['Zoukougbeu', 6.7630, -6.8650],
        'saioua' => ['Saïoua', 6.4540, -6.2830],
        'korhogo' => ['Korhogo', 9.4580, -5.6290],
        'komborodougou' => ['Komborodougou', 9.3850, -5.4890],
        'sinematiali' => ['Sinématiali', 9.5850, -5.3860],
        'dikodougou' => ['Dikodougou', 9.0680, -5.7720],
        'man' => ['Man', 7.4120, -7.5540],
        'logouale' => ['Logoualé', 7.0940, -7.6250],
        'biankouma' => ['Biankouma', 7.7390, -7.6140],
        'zouan hounien' => ['Zouan-Hounien', 6.9190, -8.2090],
        'gagnoa' => ['Gagnoa', 6.1320, -5.9500],
        'ouragahio' => ['Ouragahio', 6.3170, -5.9390],
        'abengourou' => ['Abengourou', 6.7290, -3.4960],
        'yakasse feyasse' => ['Yakassé-Féyassé', 6.9060, -3.4620],
        'divo' => ['Divo', 5.8390, -5.3570],
        'hire' => ['Hiré', 6.1910, -5.2860],
        'dabou' => ['Dabou', 5.3250, -4.3770],
        'bonoua' => ['Bonoua', 5.2720, -3.5960],
        'grand bassam' => ['Grand-Bassam', 5.2110, -3.7390],
        'bassam' => ['Grand-Bassam', 5.2110, -3.7390],
        'adiake' => ['Adiaké', 5.2860, -3.3040],
        'tiapoum' => ['Tiapoum', 5.1380, -3.0210],
        'noe' => ['Noé', 5.2820, -2.7960],
        'sinfra' => ['Sinfra', 6.6210, -5.9110],
        'bouafle' => ['Bouaflé', 6.9900, -5.7440],
        'gohitafla' => ['Gohitafla', 7.4880, -5.8820],
        'bocanda' => ['Bocanda', 7.0630, -4.4950],
        'agboville' => ['Agboville', 5.9280, -4.2130],
        'sikensi' => ['Sikensi', 5.6760, -4.5760],
        'n douci' => ['N’Douci', 5.8690, -4.7640],
        'ndouci' => ['N’Douci', 5.8690, -4.7640],
        'bodo' => ['Bodo (N’Douci)', 5.8920, -4.7250],
        'soubre' => ['Soubré', 5.7850, -6.6080],
        'grand zatry' => ['Grand-Zattry', 6.0850, -6.4840],
        'grand zattry' => ['Grand-Zattry', 6.0850, -6.4840],
        'meagui' => ['Méagui', 5.3980, -6.5560],
        'buyo' => ['Buyo', 6.2710, -7.0580],
        'gueyo' => ['Gueyo', 5.6880, -6.0710],
        'dabouyo' => ['Gueyo (Dabouyo)', 5.7520, -6.1850],
        'odienne' => ['Odienné', 9.5100, -7.5640],
        'kaniasso' => ['Kaniasso', 9.8190, -7.5140],
        'goulia' => ['Goulia', 10.0250, -7.2340],
        'minignan' => ['Minignan', 9.9970, -7.8360],
        'bondoukou' => ['Bondoukou', 8.0400, -2.8000],
        'koun fao' => ['Koun-Fao', 7.4880, -3.2520],
        'transua' => ['Transua', 7.5520, -2.9270],
        'kouassia niaguini' => ['Transua (Kouassia-Niaguini)', 7.6480, -3.0250],
        'assuefry' => ['Assuéfry', 7.7520, -2.9260],
        'seguela' => ['Séguéla', 7.9610, -6.6730],
        'worofla' => ['Worofla', 8.2100, -6.6620],
        'kani' => ['Kani', 8.4780, -6.6070],
        'katiola' => ['Katiola', 8.1370, -5.1010],
        'dabakala' => ['Dabakala', 8.3630, -4.4280],
        'niakaramandougou' => ['Niakaramandougou', 8.6590, -5.2910],
        'niakara' => ['Niakaramandougou', 8.6590, -5.2910],
        'ferkessedougou' => ['Ferkessédougou', 9.5930, -5.1940],
        'ouangolodougou' => ['Ouangolodougou', 9.9670, -5.1490],
        'pogo' => ['Pogo (Ouangolodougou)', 10.2280, -5.2390],
        'kong' => ['Kong', 9.1510, -4.6100],
        'toumodi' => ['Toumodi', 6.5560, -5.0190],
        'didievi' => ['Didiévi', 7.1310, -4.8960],
        'dimbokro' => ['Dimbokro', 6.6470, -4.7050],
        'dimbkro' => ['Dimbokro', 6.6470, -4.7050],
        'daoukro' => ['Daoukro', 7.0590, -3.9630],
        'ouelle' => ['Ouellé', 7.3020, -4.0090],
        'm bahiakro' => ['M’Bahiakro', 7.4570, -4.3400],
        'mbahiakro' => ['M’Bahiakro', 7.4570, -4.3400],
        'issia' => ['Issia', 6.4920, -6.5860],
        'sassandra' => ['Sassandra', 4.9500, -6.0830],
        'fresco' => ['Fresco', 5.0840, -5.5690],
        'gbagbam' => ['Fresco (Gbagbam)', 5.3620, -5.3680],
        'tiassale' => ['Tiassalé', 5.8980, -4.8230],
        'adzope' => ['Adzopé', 6.1070, -3.8600],
        'yakasse attobrou' => ['Yakassé-Attobrou', 6.1820, -3.6520],
        'aboisso' => ['Aboisso', 5.4680, -3.2070],
        'bouna' => ['Bouna', 9.2690, -3.0010],
        'doropo' => ['Doropo', 9.8110, -3.3440],
        'nassian' => ['Nassian', 8.4520, -3.4710],
        'teheni' => ['Téhini', 9.6050, -3.6580],
        'boundiali' => ['Boundiali', 9.5210, -6.4870],
        'tengrela' => ['Tengréla', 10.4810, -6.4070],
        'kouto' => ['Kouto', 9.8930, -6.4120],
        'duekoue' => ['Duékoué', 6.7420, -7.3490],
        'bangolo' => ['Bangolo', 7.0120, -7.4860],
        'kouibly' => ['Kouibly', 7.2550, -7.2350],
        'facobly' => ['Facobly', 7.3860, -7.3760],
        'guiglo' => ['Guiglo', 6.5430, -7.4930],
        'blolequin' => ['Bloléquin', 6.5710, -8.0050],
        'tai' => ['Taï', 5.8730, -7.4540],
        'zagne' => ['Zagné (Taï)', 6.1280, -7.4910],
        'toulepleu' => ['Toulépleu', 6.5830, -8.4170],
        'touba' => ['Touba', 8.2830, -7.6830],
        'mankono' => ['Mankono', 8.0590, -6.1890],
        'marandallah' => ['Marandallah', 8.4420, -5.8390],
        'dianra' => ['Dianra', 8.7650, -6.2560],
        'kounahiri' => ['Kounahiri', 7.8010, -5.8320],
        'oume' => ['Oumé', 6.3830, -5.4170],
        'zuenoula' => ['Zuénoula', 7.4300, -6.0500],
        'vavoua' => ['Vavoua', 7.3820, -6.4780],
        'danane' => ['Danané', 7.2600, -8.1500],
        'daname' => ['Danané', 7.2600, -8.1500],
        'daleu' => ['Daleu (Danané)', 7.0950, -8.1280],
        'agnibilekrou' => ['Agnibilékrou', 7.1300, -3.2000],
        'agnibilekro' => ['Agnibilékrou', 7.1300, -3.2000],
        'bettie' => ['Bettié', 6.0740, -3.4040],
        'tanda' => ['Tanda', 7.8030, -3.1680],
        'bongouanou' => ['Bongouanou', 6.6510, -4.2040],
        'm batto' => ['M’Batto', 6.4720, -4.3570],
        'mbatto' => ['M’Batto', 6.4720, -4.3570],
        'arrah' => ['Arrah', 6.6730, -3.9690],
        'kotobi' => ['Kotobi', 6.7320, -4.0630],
        'akoupe' => ['Akoupé', 6.3830, -3.8870],
        'jacqueville' => ['Jacqueville', 5.2050, -4.4150],
        'grand lahou' => ['Grand-Lahou', 5.1350, -5.0240],
        'alepe' => ['Alépé', 5.5000, -3.6630],
        'lakota' => ['Lakota', 5.8500, -5.6800],
        'tabou' => ['Tabou', 4.4230, -7.3530],
        'beoumi' => ['Béoumi', 7.6740, -5.5800],
        'botro' => ['Botro', 7.8520, -5.3110],
        'diabo' => ['Diabo', 7.7860, -5.1460],
        'djebonoua' => ['Djébonoua', 7.5230, -5.0670],
        'sakassou' => ['Sakassou', 7.4550, -5.2920],
        'tiebissou' => ['Tiébissou', 7.1580, -5.2250],
        'prikro' => ['Prikro', 7.6490, -3.9960],
        'guitry' => ['Guitry', 5.5220, -5.2430],
        'guiberoua' => ['Guibéroua', 6.2330, -6.1830],
        'abidjan' => ['Abidjan', 5.3450, -4.0240],
    ];

    /** Coordonnées de repli des districts sanitaires composites (Abidjan / Bouaké / Gagnoa / Korhogo…). */
    private const DISTRICTS_SANITAIRES = [
        'abobo est' => ['Abobo Est', 5.4240, -4.0060],
        'abobo ouest' => ['Abobo Ouest', 5.4210, -4.0360],
        'yopougon est' => ['Yopougon Est', 5.3520, -4.0620],
        'yopougon ouest songon' => ['Yopougon Ouest-Songon', 5.3340, -4.1150],
        'adjame plateau attecoube' => ['Adjamé-Plateau-Attécoubé', 5.3410, -4.0250],
        'cocody bingerville' => ['Cocody-Bingerville', 5.3590, -3.9620],
        'port bouet vridi' => ['Port-Bouët-Vridi', 5.2560, -3.9520],
        'treichville marcory' => ['Treichville-Marcory', 5.2990, -3.9950],
        'bouake nord est' => ['Bouaké Nord-Est', 7.7120, -5.0120],
        'bouake ouest' => ['Bouaké Ouest', 7.6920, -5.0550],
        'bouake sud' => ['Bouaké Sud', 7.6680, -5.0280],
        'gagnoa 1' => ['Gagnoa 1', 6.1320, -5.9500],
        'gagnoa 2' => ['Gagnoa 2', 6.1450, -5.9350],
        'korhogo 1' => ['Korhogo 1', 9.4580, -5.6290],
        'korhogo 2' => ['Korhogo 2', 9.4420, -5.6150],
    ];

    /** Coordonnées de repli des 32 régions sanitaires (chef-lieu régional). */
    private const REGIONS_SANITAIRES = [
        'abidjan 1' => ['Abidjan 1', 5.3850, -4.0650],
        'abidjan 2' => ['Abidjan 2', 5.3250, -3.9850],
        'agneby tiassa' => ['Agnéby-Tiassa (Agboville)', 5.9280, -4.2130],
        'bagoue' => ['Bagoué (Boundiali)', 9.5210, -6.4870],
        'bounkani' => ['Bounkani (Bouna)', 9.2690, -3.0010],
        'belier' => ['Bélier (Yamoussoukro / Toumodi)', 6.8200, -5.2770],
        'bere' => ['Béré (Mankono)', 8.0590, -6.1890],
        'cavally' => ['Cavally (Guiglo)', 6.5430, -7.4930],
        'folon' => ['Folon (Minignan)', 9.9970, -7.8360],
        'gbokle' => ['Gbôklé (Sassandra)', 4.9500, -6.0830],
        'gbeke' => ['Gbêkê (Bouaké)', 7.6900, -5.0300],
        'gontougo' => ['Gontougo (Bondoukou)', 8.0400, -2.8000],
        'grand ponts' => ['Grands-Ponts (Dabou)', 5.3250, -4.3770],
        'grands ponts' => ['Grands-Ponts (Dabou)', 5.3250, -4.3770],
        'guemon' => ['Guémon (Duékoué)', 6.7420, -7.3490],
        'goh' => ['Gôh (Gagnoa)', 6.1320, -5.9500],
        'hambol' => ['Hambol (Katiola)', 8.1370, -5.1010],
        'haut sassandra' => ['Haut-Sassandra (Daloa)', 6.8770, -6.4500],
        'iffou' => ['Iffou (Daoukro)', 7.0590, -3.9630],
        'indenie djuablin' => ['Indénié-Djuablin (Abengourou)', 6.7290, -3.4960],
        'kabadougou' => ['Kabadougou (Odienné)', 9.5100, -7.5640],
        'la me' => ['La Mé (Adzopé)', 6.1070, -3.8600],
        'loh djiboua' => ['Lôh-Djiboua (Divo)', 5.8390, -5.3570],
        'marahoue' => ['Marahoué (Bouaflé)', 6.9900, -5.7440],
        'moronou' => ['Moronou (Bongouanou)', 6.6510, -4.2040],
        'n zi' => ['N’Zi (Dimbokro)', 6.6470, -4.7050],
        'nzi' => ['N’Zi (Dimbokro)', 6.6470, -4.7050],
        'nawa' => ['Nawa (Soubré)', 5.7850, -6.6080],
        'poro' => ['Poro (Korhogo)', 9.4580, -5.6290],
        'san pedro' => ['San-Pédro', 4.7480, -6.6360],
        'sud comoe' => ['Sud-Comoé (Aboisso)', 5.4680, -3.2070],
        'tchologo' => ['Tchologo (Ferkessédougou)', 9.5930, -5.1940],
        'tonkpi' => ['Tonkpi (Man)', 7.4120, -7.5540],
        'worodougou' => ['Worodougou (Séguéla)', 7.9610, -6.6730],
        'bafing' => ['Bafing (Touba)', 8.2830, -7.6830],
    ];

    /** Codes de lieu de l'immatriculation qui ne sont pas les 5 premières lettres du nom. */
    private const CODES_PARTICULIERS = [
        'YAKRO' => 'yamoussoukro',
        'PORTB' => 'port bouet',
        'GRDBA' => 'grand bassam',
        'SANPE' => 'san pedro',
    ];

    /**
     * Position estimée depuis la localisation ; à défaut, depuis le code de lieu de
     * l'immatriculation (« PV-SINFR-SOSAN/… » → Sinfra).
     * Conserve exactement la signature attendue par ApiPlaqueImmatriculationController.
     *
     * @return array{latitude: float, longitude: float, lieu: string}|null
     */
    public static function estimer(?string $localisation, ?string $immatriculation = null): ?array
    {
        $res = self::depuisLocalisation($localisation) ?? self::depuisImmatriculation($immatriculation);
        if ($res === null) {
            return null;
        }

        return [
            'latitude' => $res['latitude'],
            'longitude' => $res['longitude'],
            'lieu' => $res['lieu'],
        ];
    }

    /**
     * Estimation enrichie utilisant dans l'ordre :
     *  1. Quartier / Commune / Ville dans la localisation textuelle
     *  2. Code de lieu dans le numéro d'immatriculation
     *  3. District sanitaire
     *  4. Région sanitaire
     *
     * Si $cleDispersion est fournie (ex. numéro de dossier + dénomination), applique un léger décalage
     * déterministe (~100 à 600 m) afin d'éviter la superposition exacte de plusieurs marqueurs d'une même commune sur la carte.
     *
     * @return array{latitude: float, longitude: float, lieu: string, precision: string}|null
     */
    public static function estimerAvecContexte(
        ?string $localisation,
        ?string $immatriculation = null,
        ?string $district = null,
        ?string $region = null,
        ?string $cleDispersion = null
    ): ?array {
        $found = self::depuisLocalisation($localisation)
            ?? self::depuisImmatriculation($immatriculation)
            ?? self::depuisDistrict($district)
            ?? self::depuisRegion($region);

        if ($found === null) {
            return null;
        }

        if ($cleDispersion !== null && trim($cleDispersion) !== '') {
            [$dLat, $dLng] = self::dispersionDeterministe($cleDispersion, $found['precision']);
            $found['latitude'] = round($found['latitude'] + $dLat, 6);
            $found['longitude'] = round($found['longitude'] + $dLng, 6);
        }

        return $found;
    }

    /**
     * @return array{latitude: float, longitude: float, lieu: string, precision: string}|null
     */
    private static function depuisImmatriculation(?string $immatriculation): ?array
    {
        if (!preg_match('/^\s*[A-Z]{1,4}-([A-Z]{3,6})-/u', mb_strtoupper(trim((string) $immatriculation)), $m)) {
            return null;
        }
        $code = $m[1];
        $listes = self::COMMUNES_ABIDJAN + self::VILLES;
        if (isset(self::CODES_PARTICULIERS[$code])) {
            $targetKey = self::CODES_PARTICULIERS[$code];
            [$lieu, $lat, $lng] = $listes[$targetKey];

            return [
                'latitude' => $lat,
                'longitude' => $lng,
                'lieu' => $lieu,
                'precision' => isset(self::COMMUNES_ABIDJAN[$targetKey]) ? 'commune' : 'ville',
            ];
        }
        foreach ($listes as $cle => [$lieu, $lat, $lng]) {
            if (strtoupper(substr(str_replace(' ', '', $cle), 0, \strlen($code))) === $code) {
                return [
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'lieu' => $lieu,
                    'precision' => isset(self::COMMUNES_ABIDJAN[$cle]) ? 'commune' : 'ville',
                ];
            }
        }

        return null;
    }

    /**
     * @return array{latitude: float, longitude: float, lieu: string, precision: string}|null
     */
    private static function depuisLocalisation(?string $localisation): ?array
    {
        if ($localisation === null || trim($localisation) === '') {
            return null;
        }
        $texte = ' ' . self::normaliser($localisation) . ' ';

        $sources = [
            ['quartier', self::QUARTIERS_ABIDJAN],
            ['commune', self::COMMUNES_ABIDJAN],
            ['ville', self::VILLES],
        ];

        foreach ($sources as [$precision, $liste]) {
            $cles = array_keys($liste);
            usort($cles, static fn ($a, $b) => \strlen($b) <=> \strlen($a));
            foreach ($cles as $cle) {
                if (str_contains($texte, ' ' . $cle . ' ')) {
                    [$lieu, $lat, $lng] = $liste[$cle];

                    return ['latitude' => $lat, 'longitude' => $lng, 'lieu' => $lieu, 'precision' => $precision];
                }
            }
        }

        return null;
    }

    /**
     * @return array{latitude: float, longitude: float, lieu: string, precision: string}|null
     */
    private static function depuisDistrict(?string $district): ?array
    {
        if ($district === null || trim($district) === '') {
            return null;
        }
        $norm = self::normaliser($district);
        if (isset(self::DISTRICTS_SANITAIRES[$norm])) {
            [$lieu, $lat, $lng] = self::DISTRICTS_SANITAIRES[$norm];

            return ['latitude' => $lat, 'longitude' => $lng, 'lieu' => $lieu, 'precision' => 'district'];
        }

        $locMatch = self::depuisLocalisation($district);
        if ($locMatch !== null) {
            $locMatch['precision'] = 'district';
            return $locMatch;
        }

        return null;
    }

    /**
     * @return array{latitude: float, longitude: float, lieu: string, precision: string}|null
     */
    private static function depuisRegion(?string $region): ?array
    {
        if ($region === null || trim($region) === '') {
            return null;
        }
        $norm = self::normaliser($region);
        if (isset(self::REGIONS_SANITAIRES[$norm])) {
            [$lieu, $lat, $lng] = self::REGIONS_SANITAIRES[$norm];

            return ['latitude' => $lat, 'longitude' => $lng, 'lieu' => $lieu, 'precision' => 'region'];
        }

        return null;
    }

    /**
     * Décalage polaire déterministe léger en degrés (~0.001° ≈ 111 m) pour éviter que plusieurs
     * établissements d'un même quartier/commune aient exactement le même point sur la carte.
     *
     * @return array{0: float, 1: float}
     */
    private static function dispersionDeterministe(string $cle, string $precision): array
    {
        $hash = md5($cle);
        $angle = (hexdec(substr($hash, 0, 6)) / 0xFFFFFF) * 2.0 * \M_PI;
        $factor = sqrt(hexdec(substr($hash, 6, 6)) / 0xFFFFFF);

        $maxRadiusDeg = match ($precision) {
            'quartier' => 0.0035, // ~380 m
            'commune' => 0.0065,  // ~720 m
            'ville' => 0.0060,    // ~660 m
            'district' => 0.0080, // ~880 m
            default => 0.0100,    // ~1.1 km
        };

        $r = $factor * $maxRadiusDeg;

        return [$r * sin($angle), $r * cos($angle)];
    }

    /** Minuscules sans accents, ponctuation et tirets remplacés par des espaces. */
    private static function normaliser(string $texte): string
    {
        $texte = mb_strtolower($texte);
        $texte = strtr($texte, [
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e', 'à' => 'a', 'â' => 'a', 'î' => 'i', 'ï' => 'i',
            'ô' => 'o', 'ö' => 'o', 'û' => 'u', 'ù' => 'u', 'ü' => 'u', 'ç' => 'c', 'œ' => 'oe',
        ]);

        return trim((string) preg_replace('/[^a-z0-9]+/', ' ', $texte));
    }

    /**
     * « MÉDICALE - POLYCLINIQUE MÉDICALE » → ['MÉDICALE', 'POLYCLINIQUE MÉDICALE'].
     *
     * @return array{0: string, 1: string}
     */
    public static function decouperNature(?string $nature): array
    {
        $nature = trim((string) $nature);
        if ($nature === '') {
            return ['Non renseignée', 'Non renseigné'];
        }
        $parts = preg_split('/\s*[-–—]\s+|\s+[-–—]\s*/u', $nature, 2);
        $categorie = mb_strtoupper(trim($parts[0] ?? ''));
        $niveau = mb_strtoupper(trim($parts[1] ?? ''));

        return [$categorie !== '' ? $categorie : 'Non renseignée', $niveau !== '' ? $niveau : 'Non précisé'];
    }
}
