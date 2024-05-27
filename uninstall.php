<?php

$sql = rex_sql::factory();

// Delete views
$sql->setQuery('DROP VIEW IF EXISTS ' . rex::getTablePrefix() . 'jobs_url_jobs');
$sql->setQuery('DROP VIEW IF EXISTS ' . rex::getTablePrefix() . 'jobs_url_jobs_categories');

// Delete url schemes
if (\rex_addon::get('url')->isAvailable()) {
    $sql->setQuery('DELETE FROM '. \rex::getTablePrefix() ."url_generator_profile WHERE `namespace` = 'job_id';");
    $sql->setQuery('DELETE FROM '. \rex::getTablePrefix() ."url_generator_profile WHERE `namespace` = 'job_category_id';");
}

// Delete language replacements
if (!class_exists(\FriendsOfRedaxo\Jobs\LangHelper::class)) {
    // Load class in case addon is deactivated
    require_once 'lib/LangHelper.php';
}
\FriendsOfRedaxo\Jobs\LangHelper::factory()->uninstall();

// Delete CronJob if installed
if (!class_exists(FriendsOfRedaxo\Jobs\ImportCronjob::class)) {
    // Load class in case addon is deactivated
    require_once 'lib/ImportCronjob.php';
}
$import_cronjob = FriendsOfRedaxo\Jobs\ImportCronjob::factory();
if ($import_cronjob->isInstalled()) {
    $import_cronjob->delete();
}

// Delete tables
$sql->setQuery('DROP TABLE IF EXISTS ' . rex::getTablePrefix() . 'jobs_jobs');
$sql->setQuery('DROP TABLE IF EXISTS ' . rex::getTablePrefix() . 'jobs_jobs_lang');
$sql->setQuery('DROP TABLE IF EXISTS ' . rex::getTablePrefix() . 'jobs_categories');
$sql->setQuery('DROP TABLE IF EXISTS ' . rex::getTablePrefix() . 'jobs_categories_lang');
$sql->setQuery('DROP TABLE IF EXISTS ' . rex::getTablePrefix() . 'jobs_contacts');
