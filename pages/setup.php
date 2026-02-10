<?php
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
<h2>Changelog</h2>
<p>1.2.0:</p>
<ul>
	<li>Beispielcode für die Verwendung des HR4YOU Imports von Version 1 auf 3 angepasst.</li>
	<li>Gehaltsfelder (Min/Max/Währung/Zeitraum) und Wochenarbeitszeit wegen Entgelttransparenzgesetz ergänzt.</li>
	<li>JSON-LD Ausgabe um baseSalary und workHours erweitert.</li>
	<li>Modul 23-1 aktualisiert (Gehalt, Wildcards, Formatierung).</li>
	<li>Bugfix: beim reinstallieren des Addons wurde der Redaxo Artikel in den Einstellungen zurückgesetzt.</li>
</ul>
<p>1.1.0:</p>
<ul>
	<li>Bugfix: alternative URLs wurden in D2U Helper Templates nicht korrekt dargestellt, da EP im Backend statt Frontend eingebunden war.</li>
	<li>Hr4You Import von Version 1 auf Version 3 aktualisiert.</li>
</ul>
<p>1.0.1:</p>
<ul>
	<li>Bugfix Modul "23-1 Stellenmarkt - Stellenanzeigen": Beschriftung Button Bewerbungslink korrigiert.</li>
	<li>Bugfix: Fehlertexte für Uploadfeld im Bewerbungsformular hinzugefügt.</li>
	<li>Bugfix: beim Löschen eines Kontakts wurde der Name der Stellenanzeige nicht angezeigt wenn er nicht der Standardsprache verfügbar war.</li>
	<li>Bugfix: der HR4You Stellenimport hatte eine falsche Verzeichnisangabe und funktionierte nicht wenn er im Cronjob aufgerufen wurde.</li>
	<li>Bugfix: der HR4You Stellenimport stürzte bei einigen leeren Feldern ab.</li>
	<li>Bugfix: der HR4You Stellenimport überschreibt nicht mehr eine manuell auf offline gesetzte Stelle.</li>
	<li>Bugfix: auf mehrsprachigen Seiten konnte es passieren, dass die Änderung des Status nicht gespeichert wurden.</li>
</ul>
<p>1.0.0:</p>
<ul>
	<li>Initiale Version.</li>
</ul>