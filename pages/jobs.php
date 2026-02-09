<?php

use FriendsOfRedaxo\Jobs\Category;
use FriendsOfRedaxo\Jobs\Contact;
use FriendsOfRedaxo\Jobs\Job;

$func = rex_request('func', 'string');
$entry_id = rex_request('entry_id', 'int');
$message = rex_get('message', 'string');
$message_type = rex_request('message_type', 'string');

// Print comments
if ('' !== $message) {
    if ('error' === $message_type) {
        echo rex_view::error(rex_i18n::msg($message));
    } else {
        echo rex_view::success(rex_i18n::msg($message));
    }
}

// save settings
if (1 === (int) filter_input(INPUT_POST, 'btn_save') || 1 === (int) filter_input(INPUT_POST, 'btn_apply')) {
    $form = rex_post('form', 'array', []);

    // Media fields and links need special treatment
    $input_media = rex_post('REX_INPUT_MEDIA', 'array', []);

    $success = true;
    $job = false;
    $job_id = (int) $form['job_id'];
    foreach (rex_clang::getAll() as $rex_clang) {
        if (!$job instanceof Job) {
            $job = new Job($job_id, $rex_clang->getId());
            $job->job_id = $job_id; // Ensure correct ID in case first language has no object
            $job->reference_number = (string) $form['reference_number'];
            $category_ids = $form['category_ids'] ?? [];
            $job->categories = [];
            foreach ($category_ids as $category_id) {
                $job->categories[$category_id] = new Category($category_id, $rex_clang->getId());
            }
            $job->date = (string) $form['date'];
            $job->zip_code = (string) $form['zip_code'];
            $job->city = (string) $form['city'];
            $job->country_code = (string) $form['country_code'];
            $job->picture = (string) $input_media[1];
            $job->online_status = array_key_exists('online_status', $form) ? 'online' : 'offline';
            $job->type = (string) $form['type'];
            $job->internal_name = (string) $form['internal_name'];
            $job->contact = new Contact($form['contact_id']);
            $job->hr4you_lead_in = (string) $form['hr4you_lead_in'];
            $job->hr4you_url_application_form = (string) $form['hr4you_url_application_form'];
            $job->salary_currency = (string) $form['salary_currency'];
            $job->salary_max = (int) $form['salary_max'];
        } else {
            $job->clang_id = $rex_clang->getId();
        }
        $job->name = (string) $form['lang'][$rex_clang->getId()]['name'];
        $job->prolog = (string) $form['lang'][$rex_clang->getId()]['prolog'];
        $job->tasks_heading = (string) $form['lang'][$rex_clang->getId()]['tasks_heading'];
        $job->tasks_text = (string) $form['lang'][$rex_clang->getId()]['tasks_text'];
        $job->profile_heading = (string) $form['lang'][$rex_clang->getId()]['profile_heading'];
        $job->profile_text = (string) $form['lang'][$rex_clang->getId()]['profile_text'];
        $job->offer_heading = (string) $form['lang'][$rex_clang->getId()]['offer_heading'];
        $job->offer_text = (string) $form['lang'][$rex_clang->getId()]['offer_text'];
        $job->translation_needs_update = (string) $form['lang'][$rex_clang->getId()]['translation_needs_update'];
        if ('delete' === $job->translation_needs_update) {
            $job->delete(false);
        } elseif ($job->save() > 0) {
            $success = false;
        } else {
            // remember id, for each database lang object needs same id
            $job_id = $job->job_id;
        }
    }
    // message output
    $message = 'form_save_error';
    $message_type = 'error';
    if ($success && $job instanceof Job && 0 === $job->job_id) {
        $message = 'jobs_not_saved_no_lang';
    } elseif ($success) {
        $message = 'form_saved';
        $message_type = 'success';
    }

    // Redirect to make reload and thus double save impossible
    if (1 === (int) filter_input(INPUT_POST, 'btn_apply', FILTER_VALIDATE_INT) && false !== $job) {
        header('Location: '. rex_url::currentBackendPage(['entry_id' => $job->job_id, 'func' => 'edit', 'message' => $message, 'message_type' => $message_type], false));
    } else {
        header('Location: '. rex_url::currentBackendPage(['message' => $message, 'message_type' => $message_type], false));
    }
    exit;
}
// Delete
if (1 === (int) filter_input(INPUT_POST, 'btn_delete', FILTER_VALIDATE_INT) || 'delete' === $func) {
    $job_id = $entry_id;
    if (0 === $job_id) {
        $form = rex_post('form', 'array', []);
        $job_id = $form['job_id'];
    }
    $job = new Job($job_id, (int) rex_config::get('d2u_helper', 'default_lang', rex_clang::getStartId()));
    $job->job_id = $job_id; // Ensure correct ID in case language has no object
    $job->delete();

    $func = '';
}
// Change online status of category
elseif ('changestatus' === $func) {
    $job_id = $entry_id;
    $job = new Job($job_id, (int) rex_config::get('d2u_helper', 'default_lang', rex_clang::getStartId()));
    if ($job->job_id === 0) {
        // try all languages
        foreach (rex_clang::getAll() as $rex_clang) {
            $job = new Job($job_id, $rex_clang->getId());
            if ($job->job_id > 0) {
                break;
            }
        }
    }
    $job->changeStatus();
    $func = '';

    header('Location: '. rex_url::currentBackendPage());
    exit;
}

// Form
if ('edit' === $func || 'clone' === $func || 'add' === $func) {
?>
	<form action="<?= rex_url::currentBackendPage() ?>" method="post">
		<div class="panel panel-edit">
			<header class="panel-heading"><div class="panel-title"><?= rex_i18n::msg('jobs_jobs') ?></div></header>
			<div class="panel-body">
				<input type="hidden" name="form[job_id]" value="<?= 'edit' === $func ? $entry_id : 0 ?>">
				<fieldset>
					<legend><?= rex_i18n::msg('d2u_helper_data_all_lang') ?></legend>
					<div class="panel-body-wrapper slide">
						<?php
                            // Do not use last object from translations, because you don't know if it exists in DB
                            $job = new Job($entry_id, (int) rex_config::get('d2u_helper', 'default_lang', rex_clang::getStartId()));
                            foreach (rex_clang::getAllIds() as $clang_id) {
                                $temp_job = new Job($entry_id, $clang_id);
                                if ($temp_job->job_id > 0) {
                                    $job = $temp_job;
                                    break;
                                }
                            }

                            $readonly = true;
                            if (rex::getUser() instanceof rex_user && (rex::getUser()->isAdmin() || rex::getUser()->hasPerm('jobs[edit_data]'))) {
                                $readonly = false;
                            }

                            \TobiasKrais\D2UHelper\BackendHelper::form_input('jobs_internal_name', 'form[internal_name]', $job->internal_name, true, $readonly);
                            \TobiasKrais\D2UHelper\BackendHelper::form_input('jobs_reference_number', 'form[reference_number]', $job->reference_number, false, $readonly, 'text');
                            $options_categories = [];
                            foreach (Category::getAll((int) rex_config::get('d2u_helper', 'default_lang', rex_clang::getStartId()), false) as $category) {
                                $options_categories[$category->category_id] = $category->name;
                            }
                            \TobiasKrais\D2UHelper\BackendHelper::form_select('d2u_helper_category', 'form[category_ids][]', $options_categories, count($job->categories) > 0 ? array_keys($job->categories) : [], 5, true, $readonly);
                            \TobiasKrais\D2UHelper\BackendHelper::form_input('jobs_date', 'form[date]', $job->date, true, $readonly, 'date');
                            \TobiasKrais\D2UHelper\BackendHelper::form_input('jobs_zip_code', 'form[zip_code]', $job->zip_code, true, $readonly);
                            \TobiasKrais\D2UHelper\BackendHelper::form_input('jobs_city', 'form[city]', $job->city, true, $readonly);
                            $options_country_code = [
                                'AT' => 'AT',
                                'CH' => 'CH',
                                'DE' => 'DE',
                                'DN' => 'DN',
                                'ES' => 'ES',
                                'FR' => 'FR',
                                'GB' => 'GB',
                                'IT' => 'IT',
                                'NL' => 'NL',
                                'PL' => 'PL',
                                'US' => 'US',
                            ];
                            \TobiasKrais\D2UHelper\BackendHelper::form_select('jobs_country_code', 'form[country_code]', $options_country_code, [$job->country_code], 1, false, $readonly);
                            \TobiasKrais\D2UHelper\BackendHelper::form_checkbox('d2u_helper_online_status', 'form[online_status]', 'online', 'online' === $job->online_status, $readonly);
                            \TobiasKrais\D2UHelper\BackendHelper::form_mediafield('d2u_helper_picture', '1', $job->picture, $readonly);
                            $options_type = ['FULL_TIME' => rex_i18n::msg('jobs_type_FULL_TIME'),
                                'PART_TIME' => rex_i18n::msg('jobs_type_PART_TIME'),
                                'CONTRACTOR' => rex_i18n::msg('jobs_type_CONTRACTOR'),
                                'TEMPORARY' => rex_i18n::msg('jobs_type_TEMPORARY'),
                                'VOLUNTEER' => rex_i18n::msg('jobs_type_VOLUNTEER'),
                                'OTHER' => rex_i18n::msg('jobs_type_OTHER')];
                            \TobiasKrais\D2UHelper\BackendHelper::form_select('jobs_type', 'form[type]', $options_type, [$job->type], 1, false, $readonly);
                            \TobiasKrais\D2UHelper\BackendHelper::form_input('jobs_salary_currency', 'form[salary_currency]', $job->salary_currency, false, $readonly);
                            \TobiasKrais\D2UHelper\BackendHelper::form_input('jobs_salary_max', 'form[salary_max]', (string) $job->salary_max, false, $readonly, 'number');
                            $options_contacts = [];
                            foreach (Contact::getAll() as $contact) {
                                if ('' !== $contact->name) {
                                    $options_contacts[$contact->contact_id] = $contact->name;
                                }
                            }
                            \TobiasKrais\D2UHelper\BackendHelper::form_select('jobs_contact', 'form[contact_id]', $options_contacts, $job->contact instanceof Contact ? [$job->contact->contact_id] : [], 1, false, $readonly);
                        ?>
					</div>
				</fieldset>
                <fieldset>
                    <legend><small><i class="rex-icon fa-cloud-download"></i></small> <?= rex_i18n::msg('jobs_hr4you') ?></legend>
                    <div class="panel-body-wrapper slide">
                        <?php
                            \TobiasKrais\D2UHelper\BackendHelper::form_input('jobs_hr4you_import_job_id', 'form[hr4you_job_id]', $job->hr4you_job_id, false, true, 'number');
                            \TobiasKrais\D2UHelper\BackendHelper::form_input('jobs_hr4you_import_lead_in', 'form[hr4you_lead_in]', $job->hr4you_lead_in, false, $readonly);
                            \TobiasKrais\D2UHelper\BackendHelper::form_input('jobs_hr4you_url_application_form', 'form[hr4you_url_application_form]', $job->hr4you_url_application_form, false, $readonly);
                        ?>
                    </div>
                </fieldset>
				<?php
                    foreach (rex_clang::getAll() as $rex_clang) {
                        $job = new Job($entry_id, $rex_clang->getId());
                        $required = $rex_clang->getId() === (int) rex_config::get('d2u_helper', 'default_lang', rex_clang::getStartId()) ? true : false;

                        $readonly_lang = true;
                        if (rex::getUser() instanceof rex_user && (rex::getUser()->isAdmin() || (rex::getUser()->hasPerm('jobs[edit_lang]') && rex::getUser()->getComplexPerm('clang') instanceof rex_clang_perm && rex::getUser()->getComplexPerm('clang')->hasPerm($rex_clang->getId())))) {
                            $readonly_lang = false;
                        }
                ?>
					<fieldset>
						<legend><?= rex_i18n::msg('d2u_helper_text_lang') .' "'. $rex_clang->getName() .'"' ?></legend>
						<div class="panel-body-wrapper slide">
							<?php
                                $options_translations = [];
                                $options_translations['yes'] = rex_i18n::msg('d2u_helper_translation_needs_update');
                                $options_translations['no'] = rex_i18n::msg('d2u_helper_translation_is_uptodate');
                                $options_translations['delete'] = rex_i18n::msg('d2u_helper_translation_delete');
                                \TobiasKrais\D2UHelper\BackendHelper::form_select('d2u_helper_translation', 'form[lang]['. $rex_clang->getId() .'][translation_needs_update]', $options_translations, [$job->translation_needs_update], 1, false, $readonly_lang);
                            ?>
							<script>
								// Hide on document load
								$(document).ready(function() {
									toggleClangDetailsView(<?= $rex_clang->getId() ?>);
								});

								// Hide on selection change
								$("select[name='form[lang][<?= $rex_clang->getId() ?>][translation_needs_update]']").on('change', function(e) {
									toggleClangDetailsView(<?= $rex_clang->getId() ?>);
								});
							</script>
							<div id="details_clang_<?= $rex_clang->getId() ?>">
								<?php
                                    \TobiasKrais\D2UHelper\BackendHelper::form_textarea('jobs_prolog', 'form[lang]['. $rex_clang->getId() .'][prolog]', $job->prolog, 5, false, $readonly_lang, true);
                                    \TobiasKrais\D2UHelper\BackendHelper::form_input('d2u_helper_name', 'form[lang]['. $rex_clang->getId() .'][name]', $job->name, false, $readonly_lang);
                                    \TobiasKrais\D2UHelper\BackendHelper::form_input('jobs_tasks_heading', 'form[lang]['. $rex_clang->getId() .'][tasks_heading]', $job->tasks_heading, false, $readonly_lang);
                                    \TobiasKrais\D2UHelper\BackendHelper::form_textarea('jobs_tasks_text', 'form[lang]['. $rex_clang->getId() .'][tasks_text]', $job->tasks_text, 5, false, $readonly_lang, true);
                                    \TobiasKrais\D2UHelper\BackendHelper::form_input('jobs_profile_heading', 'form[lang]['. $rex_clang->getId() .'][profile_heading]', $job->profile_heading, false, $readonly_lang);
                                    \TobiasKrais\D2UHelper\BackendHelper::form_textarea('jobs_profile_text', 'form[lang]['. $rex_clang->getId() .'][profile_text]', $job->profile_text, 5, false, $readonly_lang, true);
                                    \TobiasKrais\D2UHelper\BackendHelper::form_input('jobs_offer_heading', 'form[lang]['. $rex_clang->getId() .'][offer_heading]', $job->offer_heading, false, $readonly_lang);
                                    \TobiasKrais\D2UHelper\BackendHelper::form_textarea('jobs_offer_text', 'form[lang]['. $rex_clang->getId() .'][offer_text]', $job->offer_text, 5, false, $readonly_lang, true);
                                ?>
							</div>
						</div>
					</fieldset>
				<?php
                    }
                ?>
			</div>
			<footer class="panel-footer">
				<div class="rex-form-panel-footer">
					<div class="btn-toolbar">
						<button class="btn btn-save rex-form-aligned" type="submit" name="btn_save" value="1" onclick="return check_langs()"><?= rex_i18n::msg('form_save') ?></button>
						<button class="btn btn-apply" type="submit" name="btn_apply" value="1" onclick="return check_langs()"><?= rex_i18n::msg('form_apply') ?></button>
						<button class="btn btn-abort" type="submit" name="btn_abort" formnovalidate="formnovalidate" value="1"><?= rex_i18n::msg('form_abort') ?></button>
						<?php
                            if (rex::getUser() instanceof rex_user && (rex::getUser()->isAdmin() || rex::getUser()->hasPerm('jobs[edit_data]'))) {
                                echo '<button class="btn btn-delete" type="submit" name="btn_delete" formnovalidate="formnovalidate" data-confirm="'. rex_i18n::msg('form_delete') .'?" value="1">'. rex_i18n::msg('form_delete') .'</button>';
                            }
                        ?>
					</div>
				</div>
			</footer>
		</div>
	</form>
	<br>
	<script>
		function check_langs() {
			let clangs = [<?= implode(',', rex_clang::getAllIds()) ?>];
			for (let i=0; i<clangs.length; i++) {
				if($("select[name='form[lang][" + clangs[i] + "][translation_needs_update]']").val() !== "delete") {
					return true;
				}
			}
			alert('<?= rex_i18n::msg('jobs_not_saved_no_lang') ?>');
			return false;
		}
	</script>
	<?php
        echo \TobiasKrais\D2UHelper\BackendHelper::getCSS();
        echo \TobiasKrais\D2UHelper\BackendHelper::getJS();
}

if ('' === $func) {
    $query = 'SELECT job.job_id, internal_name, `date`, city, online_status '. ((bool) rex_config::get('jobs', 'use_hr4you') ? ', hr4you_job_id ' : '')
        . 'FROM '. rex::getTablePrefix() .'jobs_jobs AS job';
    $list = rex_list::factory(query:$query, rowsPerPage:1000, defaultSort:['online_status' => 'DESC', 'internal_name' => 'ASC']);

    $list->addTableAttribute('class', 'table-striped table-hover');

    $tdIcon = '<i class="rex-icon fa-users"></i>';
    $thIcon = '';
    if (rex::getUser() instanceof rex_user && (rex::getUser()->isAdmin() || rex::getUser()->hasPerm('jobs[edit_data]'))) {
        $thIcon = '<a href="' . $list->getUrl(['func' => 'add']) . '" title="' . rex_i18n::msg('add') . '"><i class="rex-icon rex-icon-add-module"></i></a>';
    }
    $list->addColumn($thIcon, $tdIcon, 0, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
    $list->setColumnParams($thIcon, ['func' => 'edit', 'entry_id' => '###job_id###']);

    $list->setColumnLabel('job_id', rex_i18n::msg('id'));
    $list->setColumnLayout('job_id', ['<th class="rex-table-id">###VALUE###</th>', '<td class="rex-table-id">###VALUE###</td>']);
    $list->setColumnSortable('job_id');

    $list->setColumnLabel('internal_name', rex_i18n::msg('jobs_internal_name'));
    $list->setColumnParams('internal_name', ['func' => 'edit', 'entry_id' => '###job_id###']);
    $list->setColumnSortable('internal_name');

    $list->setColumnLabel('date', rex_i18n::msg('jobs_date'));
    $list->setColumnParams('date', ['func' => 'edit', 'entry_id' => '###job_id###']);
    $list->setColumnSortable('date');

    $list->setColumnLabel('city', rex_i18n::msg('jobs_city'));
    $list->setColumnParams('city', ['func' => 'edit', 'entry_id' => '###job_id###']);
    $list->setColumnSortable('city');

    if ((bool) rex_config::get('jobs', 'use_hr4you')) {
        $list->setColumnLabel('hr4you_job_id', rex_i18n::msg('jobs_hr4you_import_job_id'));
        $list->setColumnSortable('hr4you_job_id');
    }

    $list->addColumn(rex_i18n::msg('module_functions'), '<i class="rex-icon rex-icon-edit"></i> ' . rex_i18n::msg('edit'));
    $list->setColumnLayout(rex_i18n::msg('module_functions'), ['<th class="rex-table-action" colspan="2">###VALUE###</th>', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams(rex_i18n::msg('module_functions'), ['func' => 'edit', 'entry_id' => '###job_id###']);

    $list->removeColumn('online_status');
    if (rex::getUser() instanceof rex_user && (rex::getUser()->isAdmin() || rex::getUser()->hasPerm('jobs[edit_data]'))) {
        $list->addColumn(rex_i18n::msg('status_online'), '<a class="rex-###online_status###" href="' . rex_url::currentBackendPage(['func' => 'changestatus']) . '&entry_id=###job_id###"><i class="rex-icon rex-icon-###online_status###"></i> ###online_status###</a>');
        $list->setColumnLayout(rex_i18n::msg('status_online'), ['', '<td class="rex-table-action">###VALUE###</td>']);

        $list->addColumn(rex_i18n::msg('d2u_helper_clone'), '<i class="rex-icon fa-copy"></i> ' . rex_i18n::msg('d2u_helper_clone'));
        $list->setColumnLayout(rex_i18n::msg('d2u_helper_clone'), ['', '<td class="rex-table-action">###VALUE###</td>']);
        $list->setColumnParams(rex_i18n::msg('d2u_helper_clone'), ['func' => 'clone', 'entry_id' => '###job_id###']);

        $list->addColumn(rex_i18n::msg('delete_module'), '<i class="rex-icon rex-icon-delete"></i> ' . rex_i18n::msg('delete'));
        $list->setColumnLayout(rex_i18n::msg('delete_module'), ['', '<td class="rex-table-action">###VALUE###</td>']);
        $list->setColumnParams(rex_i18n::msg('delete_module'), ['func' => 'delete', 'entry_id' => '###job_id###']);
        $list->addLinkAttribute(rex_i18n::msg('delete_module'), 'data-confirm', rex_i18n::msg('d2u_helper_confirm_delete'));
    }

    $list->setNoRowsMessage(rex_i18n::msg('jobs_no_jobs_found'));

    $fragment = new rex_fragment();
    $fragment->setVar('title', rex_i18n::msg('jobs_jobs'), false);
    $fragment->setVar('content', $list->get(), false);
    echo $fragment->parse('core/page/section.php');
}
