<?php
$showChangelogOnly = defined('JOBS_SHOW_CHANGELOG');

if (!$showChangelogOnly) {
/*
 * Modules
 */
$d2u_module_manager = new \TobiasKrais\D2UHelper\ModuleManager(\FriendsOfRedaxo\Jobs\Module::getModules(), 'modules/', 'jobs');

// ModuleManager actions
$d2u_module_id = rex_request('d2u_module_id', 'string');
$paired_module = rex_request('pair_'. $d2u_module_id, 'int');
$function = rex_request('function', 'string');
if ('' !== $d2u_module_id) {
    $d2u_module_manager->doActions($d2u_module_id, $function, $paired_module);
}

// ModuleManager show list
$d2u_module_manager->showManagerList();

?>
<h2>Beispielseiten</h2>
<ul>
	<li>Stellenmarkt Addon: <a href="https://test.design-to-use.de/de/addontests/stellenmarkt/" target="_blank">
		Demoseite</a>.</li>
	<li>Stellenmarkt Addon: <a href="https://www.inotec-gmbh.com/de/" target="_blank">
		www.inotec-gmbh.com</a>.</li>
	<li>Stellenmarkt Addon: <a href="https://www.kaltenbach.com/de/" target="_blank">
		www.kaltenbach.com</a>.</li>
</ul>
<h2>Support</h2>
<p>Fehlermeldungen bitte im Git Projekt unter
	<a href="https://github.com/FriendsOfREDAXO/jobs/issues" target="_blank">https://github.com/FriendsOfREDAXO/jobs/issues</a> melden.</p>
<?php } ?>

<?php if ($showChangelogOnly) { ?>
<?php include rex_path::addon('jobs', 'pages/help.changelog.php'); ?>
<?php } ?>