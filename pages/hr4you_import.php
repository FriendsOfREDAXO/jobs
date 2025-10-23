<h1>HR4YOU Import Plugin </h1>
<?php
$func = rex_request('func', 'string');

if ('import' === $func) {
    if (\FriendsOfRedaxo\Jobs\Hr4youImport::import()) {
        echo rex_view::success(rex_i18n::msg('jobs_hr4you_import_success'));
    }
}

if ('' !== rex_config::get('jobs', 'hr4you_xml_url')) {
    echo "<a href='". rex_url::currentBackendPage(['func' => 'import']) ."'>"
            . "<button class='btn btn-apply'>". rex_i18n::msg('jobs_hr4you_import') .'</button></a>';
}
?>

<h2>XML Format</h2>
<p>HR4YOU stellt ein für den Kunden angepasstes XML bereit, das durch dieses
	Plugin importiert werden kann. Da sich das XML bei jedem Kunden unterscheiden
	kann, nachfolgend die Vorlage für eine XML Datei, die dieses Plugin importieren
	kann.</p>
<textarea style="width: 100%" rows="20">
	<HR4YOU_JOBS>
		<job>
			<jobId>1</jobId>
			<jobTitle>Initiativbewerbung (m/w/d)</jobTitle>
			<jobCompanyProfile></jobCompanyProfile>
			<jobSummary></jobSummary>
			<jobResponsibilities>
				Ergreifen Sie die Initiative!
				&lt;br /&gt; Wir schätzen Mitarbeitende, die die Initiative ergreifen und schenken deshalb jeder Initiativbewerbung unsere volle Aufmerksamkeit.
			</jobResponsibilities>
			<jobRequirements>
				Spaß am Umgang mit Menschen, Aufgeschlossenheit und Neugierde, Kreativität, Teamgeist und Engagement
			</jobRequirements>
			<jobBenefits>
				Ein interessantes und vielfältiges Aufgabengebiet.
			</jobBenefits>
			<jobOutro></jobOutro>
			<jobFulltext></jobFulltext>
			<jobWorkplace>Meine Stadt</jobWorkplace>
			<jobWorkplaceZipcode>11111</jobWorkplaceZipcode>
			<jobLongitude>0.00000</jobLongitude>
			<jobLatitude>0.00000</jobLatitude>
			<jobRegion>Meine Region</jobRegion>
			<jobIndustry/>
			<jobCategory>Initiativbewerbung</jobCategory>
			<jobEmploymentType>Vollzeit</jobEmploymentType>
			<jobPublishingDateFrom>2019-01-01</jobPublishingDateFrom>
			<jobPublishingDateUntil>2999-09-23</jobPublishingDateUntil>
			<languageCode>DE</languageCode>
			<jobStreet></jobStreet>
			<salaryMin>0</salaryMin>
			<jobEducationRequirements/>
			<jobExperienceRequirements/>
			<workHours>0</workHours>
			<salaryCurrency/>
			<salaryMax>0</salaryMax>
			<contactCompany>Personal</contactCompany>
			<contactGender>Geschlecht HR Mitarbeiter</contactGender>
			<contactTitle></contactTitle>
			<contactFirstname>Vorname HR Mitarbeiter</contactFirstname>
			<contactLastname>Nachname HR Mitarbeiter</contactLastname>
			<contactDepartment>Human Resources</contactDepartment>
			<contactPhone>Telefonnummer HR Mitarbeiter</contactPhone>
			<contactEmail>E-Mail-Adresse HR Mitarbeiter</contactEmail>
			<contactWebsite>http://www.meinedomain.com</contactWebsite>
			<contactLocation>Meine Stadt</contactLocation>
			<contactZipcode>11111</contactZipcode>
			<jobOffer>
				https://mycompany.hr4you.org/job/view/1/...
			</jobOffer>
			<applicationForm>
				https://mycompany.hr4you.org/applicationForm.php?sid=1&amp;page_lang=de
			</applicationForm>
			<additionCompany></additionCompany>
			<contactStreet>Strasse und Hausnummer</contactStreet>
			<countryCompany>Land</countryCompany>
			<contactRole></contactRole>
			<faxCompany></faxCompany>
			<sprachcode>DE</sprachcode>
			<JobPostingSchema></JobPostingSchema>
			<contactPhoto/>
			<jobHeaderImage>
				https://mycompany.hr4you.org/system/file/Initiativbewerbung
			</jobHeaderImage>
		</job>
	</HR4YOU_JOBS>
</textarea>
<h2>Automatischer Import</h2>
<p>Um einen automatischen Import zu installieren muss im Addon Cronjobs ein neuer
	Cronjob hinzugefügt werden. Als Typ wird URL Aufruf ausgewählt. Die URL ist
	die Sync URL das HR4YOU Plugins.</p>