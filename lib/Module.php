<?php

namespace FriendsOfRedaxo\Jobs;

/**
 * Class managing modules published by www.design-to-use.de.
 *
 * @author Tobias Krais
 */
class Module
{
    /**
     * Get modules offered by this addon.
     * @return array<int,\TobiasKrais\D2UHelper\Module> Modules offered by this addon
     */
    public static function getModules()
    {
        $modules = [];
        $modules[] = new \TobiasKrais\D2UHelper\Module('23-1',
            'Stellenmarkt - Stellenanzeigen (BS4, deprecated)',
            17);
        $modules[] = new \TobiasKrais\D2UHelper\Module('23-2',
            'Stellenmarkt - Kategorien (BS4, deprecated)',
            4);
        $modules[] = new \TobiasKrais\D2UHelper\Module('23-3',
            'Stellenmarkt - Stellenanzeigen (BS5)',
            2);
        $modules[] = new \TobiasKrais\D2UHelper\Module('23-4',
            'Stellenmarkt - Kategorien (BS5)',
            2);
        return $modules;
    }
}