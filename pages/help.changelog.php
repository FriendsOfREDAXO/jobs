<?php
?>
<h2>Changelog</h2>
<p>1.3.0:</p>
<ul>
	<li>Backend-Navigation für Einstellungen und Setup an die Struktur des d2u_references Addons angeglichen.</li>
	<li>Neue Module 23-3 und 23-4 als Bootstrap-5-Varianten der bestehenden Beispielmodule hinzugefügt.</li>
	<li>Module 23-1 und 23-2 als "(BS4, deprecated)" markiert. Die BS4-Varianten werden im nächsten Major Release entfernt.</li>
	<li>Benötigt d2u_helper &gt;= 2.1.3.</li>
	<li>Bugfix: Prioritäten werden bei Kategorien nach dem Speichern wieder stabil neu durchnummeriert, auch wenn in der Datenbank bereits doppelte Werte vorhanden sind.</li>
	<li>Die Priorität von Kategorien kann in der Backend-Liste jetzt direkt per Hoch-/Runter-Buttons geändert werden.</li>
</ul>
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