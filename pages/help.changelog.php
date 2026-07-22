<?php
?>
<h2>Changelog</h2>
<p>1.3.2-DEV:</p>
<ul>
	<li>Bugfix Module 23-1 (BS4) und 23-3 (BS5, Stellenanzeigen): Wenn keine Stellenangebote vorhanden sind, wird jetzt eine Überschrift (<code>jobs_vacancies</code>) sowie der Hinweis "<code>jobs_no_jobs_found</code>" angezeigt. Bisher blieb die Meldung durch das CSS <code>#jobs_no_vacancies { display: none; }</code> ausgeblendet, da sie nur vom Filter-JavaScript eingeblendet wurde (das erst ab mehr als einer Stelle greift); die Überschrift erscheint jetzt unabhängig von der Anzahl der Stellen.</li>
	<li>Neu Module 23-2 (BS4) und 23-4 (BS5, Kategorien): Die Kategorienübersicht erhält jetzt eine Überschrift (<code>jobs_catgories</code>). Wenn keine Kategorien vorhanden sind, wird zusätzlich der Hinweis "<code>jobs_no_categories_found</code>" angezeigt (neuer Sprachschluessel in de/en/nl).</li>
	<li>Bugfix: Der Online/Offline-Schalter in der Stellen-Liste funktioniert wieder. Beim Erzeugen des CSRF-geschützten Status-Links wurde der Platzhalter für die Datensatz-ID URL-kodiert und dadurch nicht mehr durch die echte ID ersetzt.</li>
	<li>Barrierefreiheit/Sicherheit: In neuen Tabs öffnende Links (HR4YOU-Bewerbungsformulare, WhatsApp-Video-Bewerbung) in den Modulen 23-1 und 23-3 erhalten jetzt <code>rel="noopener noreferrer"</code>.</li>
	<li>Intern: Das in den HR4YOU-Einstellungen hartcodierte Wort „Überschrift" in der Tag-Auswahl wurde in die Sprachdateien (<code>jobs_hr4you_settings_headline_option</code>) ausgelagert.</li>
</ul>
<p>1.3.1:</p>
<ul>
	<li>Backend: Abbrechen-Buttons in Stellen-, Kategorien- und Kontaktformularen fuehren jetzt wieder zur Liste.</li>
	<li>Backend: CSRF-Schutz fuer Speichern-, Loesch- und Statusaktionen der Stellenverwaltung sowie fuer den HR4YOU-Import ergaenzt.</li>
	<li>Backend: CSRF-Schutz fuer Modul-Installation, -Update und -Deinstallation auf der Setup-Seite ergaenzt.</li>
	<li>Sicherheit: Job-, Category- und Contact-Modelle speichern jetzt mit gebundenen Parametern. Die bisherigen addslashes()-Aufrufe und String-Konkatenationen in Save- und Suchqueries entfallen damit.</li>
	<li>Sicherheit: boot.php (Medien-In-Use-Check) bindet den Dateinamen jetzt als Parameter und escaped die ausgegebenen Job-, Kategorie- und Kontaktnamen.</li>
	<li>Sicherheit: Module 23-1 bis 23-4 sowie der FrontendHelper escapen Job-, Kategorie- und Kontaktdaten in HTML-Attributen, URLs und Ueberschriften, damit aus der Datenbank stammende Werte (z.B. aus dem HR4YOU-Import) nicht als HTML interpretiert werden.</li>
	<li>Sicherheit: Backend-Hinweise auf der Kategorie- und Kontakt-Seite escapen Jobnamen.</li>
	<li>Robustheit: HR4YOU-Import (<code>lib/Hr4youImport.php</code>) gegen fehlerhafte Eingaben gehärtet: <code>unpack()</code>-Rückgabe wird vor dem Byte-Zugriff auf <code>false</code> geprüft, die verschachtelte Salary-JSON-Auswertung (<code>baseSalary/value/unitText</code>) ist gegen Nicht-Array-Strukturen abgesichert und die Bild-Endungsprüfung nutzt jetzt den strikten <code>in_array()</code>-Vergleich.</li>
</ul>
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