<?php

use D2U_Jobs\Job;
use TobiasKrais\D2UHelper\BackendHelper;

$func = rex_request('func', 'string');
$entry_id = rex_request('entry_id', 'int');
$message = rex_get('message', 'string');

$csrfToken = BackendHelper::getPageCsrfToken();
$invalidCsrf = false;
if ((
    1 === (int) filter_input(INPUT_POST, 'btn_save')
    || 1 === (int) filter_input(INPUT_POST, 'btn_apply')
    || 1 === (int) filter_input(INPUT_POST, 'btn_delete', FILTER_VALIDATE_INT)
    || 'save' === filter_input(INPUT_POST, 'btn_save')
    || 'Speichern' === rex_request::request('btn_save', 'string')
    || in_array($func, ['delete', 'changestatus', 'priority_up', 'priority_down'], true)
) && !$csrfToken->isValid()) {
    echo rex_view::error(rex_i18n::msg('csrf_token_invalid'));
    $invalidCsrf = true;
    if ('POST' !== rex_request::server('REQUEST_METHOD', 'string')) {
        $func = '';
    }
}

// Print comments
if ('' !== $message) {
    echo rex_view::success(rex_i18n::msg($message));
}

// save settings
if (!$invalidCsrf && (1 === (int) filter_input(INPUT_POST, 'btn_save') || 1 === (int) filter_input(INPUT_POST, 'btn_apply'))) {
    $form = rex_post('form', 'array', []);

    // Media fields and links need special treatment
    $input_media = rex_post('REX_INPUT_MEDIA', 'array', []);

    $success = true;
    $category = false;
    $category_id = (int) $form['category_id'];
    foreach (rex_clang::getAll() as $rex_clang) {
        if (!$category instanceof FriendsOfRedaxo\Jobs\Category) {
            $category = new FriendsOfRedaxo\Jobs\Category($category_id, $rex_clang->getId());
            $category->category_id = $category_id; // Ensure correct ID in case first language has no object
            $category->priority = (int) $form['priority'];
            $category->picture = $input_media[1];
            $category->hr4you_category_id = $form['hr4you_category_id'];
        } else {
            $category->clang_id = $rex_clang->getId();
        }
        $category->name = $form['lang'][$rex_clang->getId()]['name'];
        $category->translation_needs_update = $form['lang'][$rex_clang->getId()]['translation_needs_update'];

        if ('delete' === $category->translation_needs_update) {
            $category->delete(false);
        } elseif ($category->save() > 0) {
            $success = false;
        } else {
            // remember id, for each database lang object needs same id
            $category_id = $category->category_id;
        }
    }

    // message output
    $message = 'form_save_error';
    if ($success) {
        $message = 'form_saved';
    }

    // Redirect to make reload and thus double save impossible
    if (1 === (int) filter_input(INPUT_POST, 'btn_apply', FILTER_VALIDATE_INT) && false !== $category) {
        header('Location: '. rex_url::currentBackendPage(['entry_id' => $category->category_id, 'func' => 'edit', 'message' => $message], false));
    } else {
        header('Location: '. rex_url::currentBackendPage(['message' => $message], false));
    }
    exit;
}
// Delete
if ((!$invalidCsrf && 1 === (int) filter_input(INPUT_POST, 'btn_delete', FILTER_VALIDATE_INT)) || 'delete' === $func) {
    $category_id = $entry_id;
    if (0 === $category_id) {
        $form = rex_post('form', 'array', []);
        $category_id = $form['category_id'];
    }
    $category = new FriendsOfRedaxo\Jobs\Category($category_id, (int) rex_config::get('d2u_helper', 'default_lang'));
    $category->category_id = $category_id; // Ensure correct ID in case language has no object

    // Check if object is used
    $uses_jobs = $category->getJobs();

    if (0 === count($uses_jobs)) {
        $category->delete(true);
    } else {
        $message = '<ul>';
        foreach ($uses_jobs as $uses_job) {
            $message .= '<li><a href="index.php?page=jobs/jobs&func=edit&entry_id='. $uses_job->job_id .'">'. $uses_job->name.'</a></li>';
        }
        $message .= '</ul>';

        echo rex_view::error(rex_i18n::msg('d2u_helper_could_not_delete') . $message);
    }

    $func = '';
}
elseif ('priority_down' === $func || 'priority_up' === $func) {
    $category = new FriendsOfRedaxo\Jobs\Category($entry_id, (int) rex_config::get('d2u_helper', 'default_lang'));
    $category->category_id = $entry_id; // Ensure correct ID in case language has no object

    if ('priority_down' === $func) {
        ++$category->priority;
        $category->save();
    } elseif ($category->priority > 1) {
        --$category->priority;
        $category->save();
    }

    header('Location: '. BackendHelper::getCurrentBackendPage(['message' => 'd2u_helper_priority_changed'], ['func', 'entry_id']));
    exit;
}

// Form
if ('edit' === $func || 'add' === $func) {
?>
	<form action="<?= BackendHelper::getCurrentBackendPage([], ['message', 'message_type']) ?>" method="post">
		<?= $csrfToken->getHiddenField() ?>
		<div class="panel panel-edit">
			<header class="panel-heading"><div class="panel-title"><?= rex_i18n::msg('d2u_helper_category') ?></div></header>
			<div class="panel-body">
				<input type="hidden" name="form[category_id]" value="<?= $entry_id ?>">
				<fieldset>
					<legend><?= rex_i18n::msg('d2u_helper_data_all_lang') ?></legend>
					<div class="panel-body-wrapper slide">
						<?php
                            // Do not use last object from translations, because you don't know if it exists in DB
                            $category = new FriendsOfRedaxo\Jobs\Category($entry_id, (int) rex_config::get('d2u_helper', 'default_lang'));
                            $readonly = true;
                            if (rex::getUser() instanceof rex_user && (rex::getUser()->isAdmin() || rex::getUser()->hasPerm('jobs[edit_data]'))) {
                                $readonly = false;
                            }

                            BackendHelper::form_input('header_priority', 'form[priority]', $category->priority, true, $readonly, 'number');
                            BackendHelper::form_mediafield('d2u_helper_picture', '1', $category->picture, $readonly);
                            BackendHelper::form_input('jobs_hr4you_category_id', 'form[hr4you_category_id]', $category->hr4you_category_id, false, $readonly, 'number');
                        ?>
					</div>
				</fieldset>
				<?php
                    foreach (rex_clang::getAll() as $rex_clang) {
                        $category = new FriendsOfRedaxo\Jobs\Category($entry_id, $rex_clang->getId());
                        $required = $rex_clang->getId() === (int) (rex_config::get('d2u_helper', 'default_lang')) ? true : false;

                        $readonly_lang = true;
                        if (rex::getUser() instanceof rex_user && (rex::getUser()->isAdmin() || (rex::getUser()->hasPerm('jobs[edit_lang]') && rex::getUser()->getComplexPerm('clang') instanceof rex_clang_perm && rex::getUser()->getComplexPerm('clang')->hasPerm($rex_clang->getId())))) {
                            $readonly_lang = false;
                        }
                ?>
					<fieldset>
						<legend><?= rex_i18n::msg('d2u_helper_text_lang') .' "'. $rex_clang->getName() .'"' ?></legend>
						<div class="panel-body-wrapper slide">
							<?php
                                if ($rex_clang->getId() !== (int) rex_config::get('d2u_helper', 'default_lang')) {
                                    $options_translations = [];
                                    $options_translations['yes'] = rex_i18n::msg('d2u_helper_translation_needs_update');
                                    $options_translations['no'] = rex_i18n::msg('d2u_helper_translation_is_uptodate');
                                    $options_translations['delete'] = rex_i18n::msg('d2u_helper_translation_delete');
                                    BackendHelper::form_select('d2u_helper_translation', 'form[lang]['. $rex_clang->getId() .'][translation_needs_update]', $options_translations, [$category->translation_needs_update], 1, false, $readonly_lang);
                                } else {
                                    echo '<input type="hidden" name="form[lang]['. $rex_clang->getId() .'][translation_needs_update]" value="">';
                                }
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
                                    BackendHelper::form_input('d2u_helper_name', 'form[lang]['. $rex_clang->getId() .'][name]', $category->name, $required, $readonly_lang);
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
						<button class="btn btn-save rex-form-aligned" type="submit" name="btn_save" value="1"><?= rex_i18n::msg('form_save') ?></button>
						<button class="btn btn-apply" type="submit" name="btn_apply" value="1"><?= rex_i18n::msg('form_apply') ?></button>
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
	<?php
        echo BackendHelper::getCSS();
        echo BackendHelper::getJS();
}

if ('' === $func) {
    $query = 'SELECT category.category_id, name, priority, '
        . '(SELECT MAX(priority) FROM '. rex::getTablePrefix() .'jobs_categories) AS max_priority '
        . 'FROM '. rex::getTablePrefix() .'jobs_categories AS category '
        . 'LEFT JOIN '. rex::getTablePrefix() .'jobs_categories_lang AS lang '
            . 'ON category.category_id = lang.category_id AND lang.clang_id = '. (int) rex_config::get('d2u_helper', 'default_lang') ;
    $list = rex_list::factory(query:$query, rowsPerPage:1000, defaultSort:['priority' => 'ASC']);

    $list->addTableAttribute('class', 'table-striped table-hover');

    $tdIcon = '<i class="rex-icon rex-icon-open-category"></i>';
    $thIcon = '';
    if (rex::getUser() instanceof rex_user && (rex::getUser()->isAdmin() || rex::getUser()->hasPerm('jobs[edit_data]'))) {
        $thIcon = '<a href="' . $list->getUrl(['func' => 'add']) . '" title="' . rex_i18n::msg('add') . '"><i class="rex-icon rex-icon-add-module"></i></a>';
    }
    $list->addColumn($thIcon, $tdIcon, 0, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
    $list->setColumnParams($thIcon, ['func' => 'edit', 'entry_id' => '###category_id###']);

    $list->setColumnLabel('category_id', rex_i18n::msg('id'));
    $list->setColumnLayout('category_id', ['<th class="rex-table-id">###VALUE###</th>', '<td class="rex-table-id">###VALUE###</td>']);
    $list->setColumnSortable('category_id');

    $list->setColumnLabel('name', rex_i18n::msg('d2u_helper_name'));
    $list->setColumnParams('name', ['func' => 'edit', 'entry_id' => '###category_id###']);
    $list->setColumnSortable('name');

    $list->setColumnLabel('priority', rex_i18n::msg('header_priority'));
    $list->setColumnSortable('priority');
    $list->setColumnFormat('priority', 'custom', static function ($params) {
        $listParams = $params['list'];

        return BackendHelper::getPriorityButtons(
            (int) $listParams->getValue('category_id'),
            (int) $listParams->getValue('priority'),
            (int) $listParams->getValue('max_priority')
        );
    });

    $list->removeColumn('max_priority');

    $list->addColumn(rex_i18n::msg('module_functions'), '<i class="rex-icon rex-icon-edit"></i> ' . rex_i18n::msg('edit'));
    $list->setColumnLayout(rex_i18n::msg('module_functions'), ['<th class="rex-table-action" colspan="2">###VALUE###</th>', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams(rex_i18n::msg('module_functions'), ['func' => 'edit', 'entry_id' => '###category_id###']);

    if (rex::getUser() instanceof rex_user && (rex::getUser()->isAdmin() || rex::getUser()->hasPerm('jobs[edit_data]'))) {
        $list->addColumn(rex_i18n::msg('delete_module'), '<i class="rex-icon rex-icon-delete"></i> ' . rex_i18n::msg('delete'));
        $list->setColumnLayout(rex_i18n::msg('delete_module'), ['', '<td class="rex-table-action">###VALUE###</td>']);
        $list->setColumnParams(rex_i18n::msg('delete_module'), ['func' => 'delete', 'entry_id' => '###category_id###'] + $csrfToken->getUrlParams());
        $list->addLinkAttribute(rex_i18n::msg('delete_module'), 'data-confirm', rex_i18n::msg('d2u_helper_confirm_delete'));
    }

    $list->addColumn(rex_i18n::msg('d2u_helper_open_frontend'), '');
    $list->setColumnLayout(rex_i18n::msg('d2u_helper_open_frontend'), ['', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnFormat(rex_i18n::msg('d2u_helper_open_frontend'), 'custom', static function ($params) {
        $listParams = $params['list'];

        return BackendHelper::getFrontendLinkButton((new \FriendsOfRedaxo\Jobs\Category((int) $listParams->getValue('category_id'), (int) rex_config::get('d2u_helper', 'default_lang')))->getUrl());
    });

    $list->setNoRowsMessage(rex_i18n::msg('d2u_helper_no_categories_found'));

    $fragment = new rex_fragment();
    $fragment->setVar('title', rex_i18n::msg('d2u_helper_category'), false);
    $fragment->setVar('content', $list->get(), false);
    echo $fragment->parse('core/page/section.php');
}
