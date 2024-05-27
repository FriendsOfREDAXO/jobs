<?php
namespace FriendsOfRedaxo\Jobs;

/**
 * Administrates background import cronjob for HR4You.
 */
class ImportCronjob extends \TobiasKrais\D2UHelper\ACronJob
{
    /**
     * Create a new instance of object.
     * @return self CronJob object
     */
    public static function factory(): self
    {
        $cronjob = new self();
        $cronjob->name = 'D2U Jobs Autoimport';
        return $cronjob;
    }

    /**
     * Install CronJob. Its also activated.
     */
    public function install(): void
    {
        $description = 'Imports jobs automatically from HR4You XML';
        $php_code = '<?php FriendsOfRedaxo\\\\\\\\Jobs\\\\\\\\Hr4youImport::autoimport(); ?>';
        $interval = '{\"minutes\":[0],\"hours\":[21],\"days\":\"all\",\"weekdays\":\"all\",\"months\":\"all\"}';
        self::save($description, $php_code, $interval);
    }
}
