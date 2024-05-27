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
            'Stellenmarkt - Stellenanzeigen',
            13);
        $modules[] = new \TobiasKrais\D2UHelper\Module('23-2',
            'Stellenmarkt - Kategorien',
            3);
        return $modules;
    }
}