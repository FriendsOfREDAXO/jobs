<div class="row">
	<div class="col-xs-4">
		Anzuzeigende Stellenkategorie
	</div>
	<div class="col-xs-8">
		<?php
            // Job Categories
            $select = new rex_select();
            $select->setName('VALUE[1]');
            $select->setAttribute('class', 'form-control');
            $select->setSize(1);

            // Daten
            $select->addOption('Alle', 0);
            foreach (FriendsOfRedaxo\Jobs\Category::getAll(rex_clang::getCurrentId()) as $category) {
                $select->addOption($category->name, $category->category_id);
            }
            $select->setSelected('REX_VALUE[1]');
            $select->show();
        ?>
	</div>
</div>
<div class="row">
	<div class="col-xs-12">&nbsp;</div>
</div>
<div class="row">
	<div class="col-xs-4">
		<input type="checkbox" name="REX_INPUT_VALUE[2]" value="true" <?= 'REX_VALUE[2]' === 'true' ? ' checked="checked"' : '' /** @phpstan-ignore-line */ ?> class="form-control d2u_helper_toggle" />
	</div>
	<div class="col-xs-8">
		Allgemeiner Bewerbungshinweis unterhalb der Stellenanzeige verbergen.<br />
	</div>
</div>
<div class="row">
	<div class="col-xs-12">&nbsp;</div>
</div>
<div class="row">
	<div class="col-xs-4">
		<input type="checkbox" name="REX_INPUT_VALUE[3]" value="true" <?= 'REX_VALUE[3]' === 'true' ? ' checked="checked"' : '' /** @phpstan-ignore-line */ ?> class="form-control d2u_helper_toggle" />
	</div>
	<div class="col-xs-8">
		Stellen im JSON-LD Format ausgeben, damit Stellensuchmaschinen (z.B. Google Jobs) die Stelle anzeigen können.<br />
	</div>
</div>
<div class="row">
	<div class="col-xs-12">&nbsp;</div>
</div>
<div class="row">
	<div class="col-xs-4">
		<input type="checkbox" name="REX_INPUT_VALUE[4]" value="true" <?= 'REX_VALUE[4]' === 'true' ? ' checked="checked"' : '' /** @phpstan-ignore-line */ ?> class="form-control d2u_helper_toggle" />
	</div>
	<div class="col-xs-8">
		Bewerbungsformular statt E-Mail-Adresse anzeigen<?php if ((bool) rex_config::get('jobs', 'use_hr4you')) {
        echo ' (gilt nicht für Stellen die aus HR4You importiert werden)';
        } ?>.<br />
	</div>
</div>

<div class="row">
	<div class="col-xs-12">&nbsp;</div>
</div>
<div class="row">
	<div class="col-xs-12">
		Alle weiteren Änderungen bitte im <a href="index.php?page=jobs">D2U Stellenmarkt</a> Addon vornehmen.
	</div>
</div>