<?php

namespace App\Service;

/**
 * Position estimée d'un établissement à partir de sa localisation en texte
 * (« Abidjan, commune de Cocody, quartier Danga… »).
 *
 * Les fiches Word ne contiennent pas de coordonnées GPS : on reconnaît la commune
 * d'Abidjan ou la ville citée et on renvoie son centre (précision : la ville).
 * La position exacte peut ensuite être corrigée à la main sur la carte.
 * Coordonnées WGS84 approximatives (centre-ville), à quelques kilomètres près.
 */
final class PlaqueGeolocalisation
{
    /** Communes d'Abidjan : plus précises que « Abidjan », testées en premier. */
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

    private const VILLES = [
        'yamoussoukro' => ['Yamoussoukro', 6.8200, -5.2770],
        'bouake' => ['Bouaké', 7.6900, -5.0300],
        'san pedro' => ['San-Pédro', 4.7480, -6.6360],
        'daloa' => ['Daloa', 6.8770, -6.4500],
        'korhogo' => ['Korhogo', 9.4580, -5.6290],
        'man' => ['Man', 7.4120, -7.5540],
        'gagnoa' => ['Gagnoa', 6.1320, -5.9500],
        'abengourou' => ['Abengourou', 6.7290, -3.4960],
        'divo' => ['Divo', 5.8390, -5.3570],
        'dabou' => ['Dabou', 5.3250, -4.3770],
        'bonoua' => ['Bonoua', 5.2720, -3.5960],
        'grand bassam' => ['Grand-Bassam', 5.2110, -3.7390],
        'sinfra' => ['Sinfra', 6.6210, -5.9110],
        'bouafle' => ['Bouaflé', 6.9900, -5.7440],
        'bocanda' => ['Bocanda', 7.0630, -4.4950],
        'agboville' => ['Agboville', 5.9280, -4.2130],
        'soubre' => ['Soubré', 5.7850, -6.6080],
        'odienne' => ['Odienné', 9.5100, -7.5640],
        'bondoukou' => ['Bondoukou', 8.0400, -2.8000],
        'seguela' => ['Séguéla', 7.9610, -6.6730],
        'katiola' => ['Katiola', 8.1370, -5.1010],
        'ferkessedougou' => ['Ferkessédougou', 9.5930, -5.1940],
        'toumodi' => ['Toumodi', 6.5560, -5.0190],
        'dimbokro' => ['Dimbokro', 6.6470, -4.7050],
        'daoukro' => ['Daoukro', 7.0590, -3.9630],
        'issia' => ['Issia', 6.4920, -6.5860],
        'sassandra' => ['Sassandra', 4.9500, -6.0830],
        'tiassale' => ['Tiassalé', 5.8980, -4.8230],
        'adzope' => ['Adzopé', 6.1070, -3.8600],
        'aboisso' => ['Aboisso', 5.4680, -3.2070],
        'bouna' => ['Bouna', 9.2690, -3.0010],
        'boundiali' => ['Boundiali', 9.5210, -6.4870],
        'duekoue' => ['Duékoué', 6.7420, -7.3490],
        'guiglo' => ['Guiglo', 6.5430, -7.4930],
        'touba' => ['Touba', 8.2830, -7.6830],
        'mankono' => ['Mankono', 8.0590, -6.1890],
        'oume' => ['Oumé', 6.3830, -5.4170],
        'zuenoula' => ['Zuénoula', 7.4300, -6.0500],
        'vavoua' => ['Vavoua', 7.3820, -6.4780],
        'danane' => ['Danané', 7.2600, -8.1500],
        'agnibilekrou' => ['Agnibilékrou', 7.1300, -3.2000],
        'tanda' => ['Tanda', 7.8030, -3.1680],
        'bongouanou' => ['Bongouanou', 6.6510, -4.2040],
        'akoupe' => ['Akoupé', 6.3830, -3.8870],
        'jacqueville' => ['Jacqueville', 5.2050, -4.4150],
        'grand lahou' => ['Grand-Lahou', 5.1350, -5.0240],
        'alepe' => ['Alépé', 5.5000, -3.6630],
        'lakota' => ['Lakota', 5.8500, -5.6800],
        'tabou' => ['Tabou', 4.4230, -7.3530],
        'beoumi' => ['Béoumi', 7.6740, -5.5800],
        'sakassou' => ['Sakassou', 7.4550, -5.2920],
        'tiebissou' => ['Tiébissou', 7.1580, -5.2250],
        'arrah' => ['Arrah', 6.6730, -3.9690],
        'prikro' => ['Prikro', 7.6490, -3.9960],
        'guitry' => ['Guitry', 5.5220, -5.2430],
        'guiberoua' => ['Guibéroua', 6.2330, -6.1830],
        'abidjan' => ['Abidjan', 5.3450, -4.0240],
    ];

    /** Codes de lieu de l'immatriculation qui ne sont pas les 5 premières lettres du nom. */
    private const CODES_PARTICULIERS = [
        'YAKRO' => 'yamoussoukro',
    ];

    /**
     * Position estimée depuis la localisation ; à défaut, depuis le code de lieu de
     * l'immatriculation (« PV-SINFR-SOSAN/… » → Sinfra).
     *
     * @return array{latitude: float, longitude: float, lieu: string}|null
     */
    public static function estimer(?string $localisation, ?string $immatriculation = null): ?array
    {
        return self::depuisLocalisation($localisation) ?? self::depuisImmatriculation($immatriculation);
    }

    /**
     * « PV-COCOD-MEDIC/CLNQ-04552-0028/2022 » : le 2e segment est le code du lieu,
     * soit les 5 premières lettres de la ville / commune (PORTB = Port-Bouët, KOUMA = Koumassi…).
     * ABIDJ (Abidjan sans commune) donne le centre d'Abidjan.
     *
     * @return array{latitude: float, longitude: float, lieu: string}|null
     */
    private static function depuisImmatriculation(?string $immatriculation): ?array
    {
        if (!preg_match('/^\s*[A-Z]{1,4}-([A-Z]{3,6})-/u', mb_strtoupper(trim((string) $immatriculation)), $m)) {
            return null;
        }
        $code = $m[1];
        $listes = self::COMMUNES_ABIDJAN + self::VILLES;
        if (isset(self::CODES_PARTICULIERS[$code])) {
            [$lieu, $lat, $lng] = $listes[self::CODES_PARTICULIERS[$code]];

            return ['latitude' => $lat, 'longitude' => $lng, 'lieu' => $lieu];
        }
        foreach ($listes as $cle => [$lieu, $lat, $lng]) {
            if (strtoupper(substr(str_replace(' ', '', $cle), 0, \strlen($code))) === $code) {
                return ['latitude' => $lat, 'longitude' => $lng, 'lieu' => $lieu];
            }
        }

        return null;
    }

    /**
     * @return array{latitude: float, longitude: float, lieu: string}|null
     */
    private static function depuisLocalisation(?string $localisation): ?array
    {
        if ($localisation === null || trim($localisation) === '') {
            return null;
        }
        $texte = ' ' . self::normaliser($localisation) . ' ';

        // Les plus longs d'abord (« grand bassam » avant « bassam », « port bouet » avant « bouet »).
        foreach ([self::COMMUNES_ABIDJAN, self::VILLES] as $liste) {
            $cles = array_keys($liste);
            usort($cles, static fn ($a, $b) => \strlen($b) <=> \strlen($a));
            foreach ($cles as $cle) {
                if (str_contains($texte, ' ' . $cle . ' ')) {
                    [$lieu, $lat, $lng] = $liste[$cle];

                    return ['latitude' => $lat, 'longitude' => $lng, 'lieu' => $lieu];
                }
            }
        }

        return null;
    }

    /** Minuscules sans accents, ponctuation et tirets remplacés par des espaces. */
    private static function normaliser(string $texte): string
    {
        $texte = mb_strtolower($texte);
        $texte = strtr($texte, [
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e', 'à' => 'a', 'â' => 'a', 'î' => 'i', 'ï' => 'i',
            'ô' => 'o', 'ö' => 'o', 'û' => 'u', 'ù' => 'u', 'ü' => 'u', 'ç' => 'c',
        ]);

        return trim(preg_replace('/[^a-z0-9]+/', ' ', $texte));
    }

    /**
     * « MÉDICALE - POLYCLINIQUE MÉDICALE » → ['MÉDICALE', 'POLYCLINIQUE MÉDICALE'].
     * Le premier terme est la nature (médicale / paramédicale…), le second le niveau d'intervention.
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
