<?php
// save settings
if ('save' === filter_input(INPUT_POST, 'btn_save')) {
    $settings = rex_post('settings', 'array', []);

    // Linkmap Link and media needs special treatment
    $link_ids = filter_input_array(INPUT_POST, ['REX_INPUT_LINK' => ['filter' => FILTER_VALIDATE_INT, 'flags' => FILTER_REQUIRE_ARRAY]]);
    $settings['article_id'] = is_array($link_ids['REX_INPUT_LINK']) ? $link_ids['REX_INPUT_LINK'][1] : rex_article::getSiteStartArticleId();
    $settings['faq_article_id'] = is_array($link_ids['REX_INPUT_LINK']) ? $link_ids['REX_INPUT_LINK'][2] : rex_article::getSiteStartArticleId();

    // Special treatment for media fields
    $input_media = rex_post('REX_INPUT_MEDIA', 'array', []);
    $settings['logo'] = $input_media['logo'];

    // Checkbox also needs special treatment if empty
    $settings['use_hr4you'] = array_key_exists('use_hr4you', $settings);
    $settings['hr4you_autoimport'] = array_key_exists('hr4you_autoimport', $settings) ? 'active' : 'inactive';
    $settings['lang_wildcard_overwrite'] = array_key_exists('lang_wildcard_overwrite', $settings) ? 'true' : 'false';

    // Save settings
    if (rex_config::set('jobs', $settings)) {
        echo rex_view::success(rex_i18n::msg('form_saved'));

        // Update url schemes
        if (\rex_addon::get('url')->isAvailable()) {
            \TobiasKrais\D2UHelper\BackendHelper::update_url_scheme(rex::getTablePrefix() .'jobs_url_jobs', $settings['article_id']);
            \TobiasKrais\D2UHelper\BackendHelper::update_url_scheme(rex::getTablePrefix() .'jobs_url_jobs_categories', $settings['article_id']);
        }

        // Install / update language replacements
        \FriendsOfRedaxo\Jobs\LangHelper::factory()->install();

        // Install / remove Cronjob
        if ((bool) rex_config::get('jobs', 'use_hr4you')) {
            $import_cronjob = FriendsOfRedaxo\Jobs\ImportCronjob::factory();
            if ('active' === rex_config::get('jobs', 'hr4you_autoimport')) {
                if (!$import_cronjob->isInstalled()) {
                    $import_cronjob->install();
                }
            } else {
                $import_cronjob->delete();
            }
        }
    } else {
        echo rex_view::error(rex_i18n::msg('form_save_error'));
    }

    // hide hr4you import page if not activated in settings
    if ((bool) rex_config::get('jobs', 'use_hr4you') === FALSE) {
        $page = $this->getProperty('page');
        unset($page['subpages']['hr4you_import']);
        $this->setProperty('page', $page);
    }
}
?>
<form action="<?= rex_url::currentBackendPage() ?>" method="post">
	<div class="panel panel-edit">
		<header class="panel-heading"><div class="panel-title"><?= rex_i18n::msg('d2u_helper_settings') ?></div></header>
		<div class="panel-body">
			<fieldset>
				<legend><small><i class="rex-icon rex-icon-database"></i></small> <?= rex_i18n::msg('d2u_helper_settings') ?></legend>
				<div class="panel-body-wrapper slide">
					<?php
                        \TobiasKrais\D2UHelper\BackendHelper::form_input('jobs_settings_email', 'settings[email]', (string) rex_config::get('jobs', 'email'), true, false, 'email');
                        \TobiasKrais\D2UHelper\BackendHelper::form_linkfield('d2u_helper_article_id', '1', (int) rex_config::get('jobs', 'article_id'), (int) rex_config::get('d2u_helper', 'default_lang'));
                        \TobiasKrais\D2UHelper\BackendHelper::form_linkfield('jobs_faq_article_id', '2', (int) rex_config::get('jobs', 'faq_article_id'), (int) rex_config::get('d2u_helper', 'default_lang'));
                    ?>
				</div>
			</fieldset>
			<fieldset>
				<legend><small><i class="rex-icon rex-icon-language"></i></small> <?= rex_i18n::msg('d2u_helper_lang_replacements') ?></legend>
				<div class="panel-body-wrapper slide">
					<?php
                        \TobiasKrais\D2UHelper\BackendHelper::form_checkbox('d2u_helper_lang_wildcard_overwrite', 'settings[lang_wildcard_overwrite]', 'true', 'true' === rex_config::get('jobs', 'lang_wildcard_overwrite'));
                        foreach (rex_clang::getAll() as $rex_clang) {
                            echo '<dl class="rex-form-group form-group">';
                            echo '<dt><label>'. $rex_clang->getName() .'</label></dt>';
                            echo '<dd>';
                            echo '<select class="form-control" name="settings[lang_replacement_'. $rex_clang->getId() .']">';
                            $replacement_options = [
                                'd2u_helper_lang_english' => 'english',
                                'd2u_helper_lang_german' => 'german',
                                'd2u_helper_lang_french' => 'french',
                                'd2u_helper_lang_dutch' => 'dutch',
                                'd2u_helper_lang_spanish' => 'spanish',
                                'd2u_helper_lang_russian' => 'russian',
                                'd2u_helper_lang_chinese' => 'chinese',
                            ];
                            foreach ($replacement_options as $key => $value) {
                                $selected = $value === rex_config::get('jobs', 'lang_replacement_'. $rex_clang->getId()) ? ' selected="selected"' : '';
                                echo '<option value="'. $value .'"'. $selected .'>'. rex_i18n::msg('d2u_helper_lang_replacements_install') .' '. rex_i18n::msg($key) .'</option>';
                            }
                            echo '</select>';
                            echo '</dl>';
                        }
                    ?>
				</div>
			</fieldset>
			<fieldset>
				<legend><small><i class="rex-icon fa-google"></i></small> <?= rex_i18n::msg('jobs_settings_google') ?></legend>
				<div class="panel-body-wrapper slide">
					<?php
                        \TobiasKrais\D2UHelper\BackendHelper::form_input('jobs_settings_company_name', 'settings[company_name]', (string) rex_config::get('jobs', 'company_name'), true, false, 'text');
                        \TobiasKrais\D2UHelper\BackendHelper::form_mediafield('jobs_settings_logo', 'logo', (string) rex_config::get('jobs', 'logo'));
                    ?>
				</div>
			</fieldset>
            <fieldset>
                <legend><small><i class="rex-icon fa-cloud-download"></i></small> <?= rex_i18n::msg('jobs_hr4you') ?></legend>
                <div class="panel-body-wrapper slide">
                    <?php
                        \TobiasKrais\D2UHelper\BackendHelper::form_checkbox('jobs_hr4you_activate', 'settings[use_hr4you]', 'true', (bool) rex_config::get('jobs', 'use_hr4you'));
                        echo '<div id="hr4you_settings"'. ((bool) rex_config::get('jobs', 'use_hr4you', false) ? '' : ' style="display: none;"') .'>';
                        // Default language for import
                        if (count(rex_clang::getAll()) > 1) {
                            $lang_options = [];
                            foreach (rex_clang::getAll() as $rex_clang) {
                                $lang_options[$rex_clang->getId()] = $rex_clang->getName();
                            }
                            \TobiasKrais\D2UHelper\BackendHelper::form_select('jobs_hr4you_settings_default_lang', 'settings[hr4you_default_lang]', $lang_options, [(int) rex_config::get('jobs', 'hr4you_default_lang')]);
                        }
                        \TobiasKrais\D2UHelper\BackendHelper::form_input('jobs_hr4you_settings_hr4you_xml_url', 'settings[hr4you_xml_url]', (string) rex_config::get('jobs', 'hr4you_xml_url'), false, false);
                    ?>
                    <dl class="rex-form-group form-group" id="settings[hr4you_media_category]">
                        <dt><label><?= rex_i18n::msg('jobs_hr4you_settings_hr4you_media_category') ?></label></dt>
                        <dd>
                            <?php
                                $media_category = new rex_media_category_select(false);
                                $media_category->addOption(rex_i18n::msg('pool_kats_no'), 0);
                                $media_category->get();
                                $media_category->setSelected((string) rex_config::get('jobs', 'hr4you_media_category'));
                                $media_category->setName('settings[hr4you_media_category]');
                                $media_category->setAttribute('class', 'form-control');
                                $media_category->show();
                            ?>
                        </dd>
                    </dl>
                    <?php
                        $job_category_options = [];
                        foreach (FriendsOfRedaxo\Jobs\Category::getAll((int) rex_config::get('d2u_helper', 'default_lang'), false) as $job_category) {
                            $job_category_options[$job_category->category_id] = $job_category->name;
                        }
                        \TobiasKrais\D2UHelper\BackendHelper::form_select('jobs_hr4you_settings_hr4you_default_category', 'settings[hr4you_default_category]', $job_category_options, [(string) rex_config::get('jobs', 'hr4you_default_category')]);
                        $job_headline_options = [];
                        for ($i = 1; $i <= 6; ++$i) {
                            $job_headline_options['h'. $i] = htmlspecialchars('Überschrift <h'. $i .'>');
                        }
                        \TobiasKrais\D2UHelper\BackendHelper::form_select('jobs_hr4you_settings_headline_tag', 'settings[hr4you_headline_tag]', $job_headline_options, [(string) rex_config::get('jobs', 'hr4you_headline_tag')]);
                        \TobiasKrais\D2UHelper\BackendHelper::form_checkbox('jobs_hr4you_settings_hr4you_autoimport', 'settings[hr4you_autoimport]', 'active', 'active' === rex_config::get('jobs', 'hr4you_autoimport'));
                        echo '</div>';
                    ?>
                    <script>
                        document.querySelector('input[name="settings[use_hr4you]"]').addEventListener('change', function() {
                            var hr4youSettingsDiv = document.getElementById('hr4you_settings');
                            if (this.checked) {
                                hr4youSettingsDiv.style.display = 'block';
                            } else {
                                hr4youSettingsDiv.style.display = 'none';
                            }
                        });
                    </script>
                </div>
            </fieldset>
		</div>
		<footer class="panel-footer">
			<div class="rex-form-panel-footer">
				<div class="btn-toolbar">
					<button class="btn btn-save rex-form-aligned" type="submit" name="btn_save" value="save"><?= rex_i18n::msg('form_save') ?></button>
				</div>
			</div>
		</footer>
	</div>
</form>
<?php
    echo \TobiasKrais\D2UHelper\BackendHelper::getCSS();
    echo \TobiasKrais\D2UHelper\BackendHelper::getJS();
    echo \TobiasKrais\D2UHelper\BackendHelper::getJSOpenAll();
