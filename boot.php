<?php

use FriendsOfRedaxo\Jobs\FrontendHelper;

if (rex::isBackend() && is_object(rex::getUser())) {
    rex_perm::register('jobs[]', rex_i18n::msg('jobs_rights_all'));
    rex_perm::register('jobs[edit_lang]', rex_i18n::msg('jobs_rights_edit_lang'), rex_perm::OPTIONS);
    rex_perm::register('jobs[edit_data]', rex_i18n::msg('jobs_rights_edit_data'), rex_perm::OPTIONS);
    rex_perm::register('jobs[hr4you]', rex_i18n::msg('jobs_rights_hr4you'));
    rex_perm::register('jobs[settings]', rex_i18n::msg('jobs_rights_settings'), rex_perm::OPTIONS);

    // hide hr4you import page if not activated in settings
    if ((bool) rex_config::get('jobs', 'use_hr4you') === FALSE) {
        $page = $this->getProperty('page');
        unset($page['subpages']['hr4you_import']);
        $this->setProperty('page', $page);
    }
}

// EPs
if (rex::isBackend()) {
    rex_extension::register('ART_PRE_DELETED', rex_jobs_article_is_in_use(...));
    rex_extension::register('CLANG_DELETED', rex_jobs_clang_deleted(...));
    rex_extension::register('D2U_HELPER_ALTERNATE_URLS', rex_jobs_alternate_urls(...));
    rex_extension::register('D2U_HELPER_BREADCRUMBS', rex_jobs_breadcrumbs(...));
    rex_extension::register('D2U_HELPER_TRANSLATION_LIST', rex_jobs_translation_list(...));
    rex_extension::register('MEDIA_IS_IN_USE', rex_jobs_media_is_in_use(...));
}
else {
    // Delete attachments after sending application e-mails
    rex_extension::register('YFORM_EMAIL_SENT', static function (rex_extension_point $ep_yform_sent) {
        if ('jobs_application' === $ep_yform_sent->getSubject()) {
            rex_extension::register('RESPONSE_SHUTDOWN', static function (rex_extension_point $ep_response_shutdown) {
                $folder = rex_path::pluginData('yform', 'manager') .'upload/frontend';
                if (file_exists($folder)) {
                    $objects = scandir($folder);
                    if (is_array($objects)) {
                        foreach ($objects as $object) {
                            if ('.' !== $object && '..' !== $object) {
                                unlink($folder .'/'. $object);
                            }
                        }
                    }
                }
            });
        }
    });
}

/**
 * Get alternate URLs for jobs.
 * @param rex_extension_point<array<string>> $ep Redaxo extension point
 * @return array<int,string> Addon url list
 */
function rex_jobs_alternate_urls(rex_extension_point $ep) {
    $params = $ep->getParams();
    $url_namespace = (string) $params['url_namespace'];
    $url_id = (int) $params['url_id'];

    $url_list = FrontendHelper::getAlternateURLs($url_namespace, $url_id);
    if (count($url_list) === 0) {
        $url_list = $ep->getSubject();
    }

    return $url_list;
}

/**
 * Checks if article is used by this addon.
 * @param rex_extension_point<string> $ep Redaxo extension point
 * @throws rex_api_exception If article is used
 * @return string empty string if article is not uses
 */
function rex_jobs_article_is_in_use(rex_extension_point $ep)
{
    $warning = [];
    $params = $ep->getParams();
    $article_id = $params['id'];

    // Settings
    $addon = rex_addon::get('jobs');
    if ($addon->hasConfig('article_id') && (int) $addon->getConfig('article_id') === $article_id) {
        $message = '<a href="index.php?page=jobs/settings">'. rex_i18n::msg('jobs_rights_all') .' - '. rex_i18n::msg('d2u_helper_settings') . '</a>';
        $warning[] = $message;
    }

    if (count($warning) > 0) {
        throw new rex_api_exception(rex_i18n::msg('d2u_helper_rex_article_cannot_delete') .'<ul><li>'. implode('</li><li>', $warning) .'</li></ul>');
    }

    return '';
}

/**
 * Get breadcrumb part for jobs.
 * @param rex_extension_point<array<string>> $ep Redaxo extension point
 * @return array<int,string> HTML formatted breadcrumb elements
 */
function rex_jobs_breadcrumbs(rex_extension_point $ep) {
    $params = $ep->getParams();
    $url_namespace = (string) $params['url_namespace'];
    $url_id = (int) $params['url_id'];

    $breadcrumbs = FrontendHelper::getBreadcrumbs($url_namespace, $url_id);
    if (count($breadcrumbs) === 0) {
        $breadcrumbs = $ep->getSubject();
    }

    return $breadcrumbs;
}

/**
 * Deletes language specific configurations and objects.
 * @param rex_extension_point<array<string>> $ep Redaxo extension point
 * @return array<string> Warning message as array
 */
function rex_jobs_clang_deleted(rex_extension_point $ep)
{
    $warning = $ep->getSubject();
    $params = $ep->getParams();
    $clang_id = (int) $params['id'];

    // Delete
    $categories = \FriendsOfRedaxo\Jobs\Category::getAll($clang_id);
    foreach ($categories as $category) {
        $category->delete(false);
    }
    $jobs = \FriendsOfRedaxo\Jobs\Job::getAll($clang_id, 0, false);
    foreach ($jobs as $job) {
        $job->delete(false);
    }

    // Delete language settings
    if (rex_config::has('jobs', 'lang_replacement_'. $clang_id)) {
        rex_config::remove('jobs', 'lang_replacement_'. $clang_id);
    }
    // Delete language replacements
    \FriendsOfRedaxo\Jobs\LangHelper::factory()->uninstall($clang_id);

    return $warning;
}

/**
 * Checks if media is used by this addon.
 * @param rex_extension_point<array<string>> $ep Redaxo extension point
 * @return array<string> Warning message as array
 */
function rex_jobs_media_is_in_use(rex_extension_point $ep)
{
    $warning = $ep->getSubject();
    $params = $ep->getParams();
    $filename = addslashes((string) $params['filename']);

    // Jobs
    $sql_jobs = rex_sql::factory();
    $sql_jobs->setQuery('SELECT lang.job_id, name FROM `' . rex::getTablePrefix() . 'jobs_jobs_lang` AS lang '
        .'LEFT JOIN `' . rex::getTablePrefix() . 'jobs_jobs` AS jobs ON lang.job_id = jobs.job_id '
        .'WHERE picture = "'. $filename .'" '
        .'GROUP BY job_id');

    // Categories
    $sql_categories = rex_sql::factory();
    $sql_categories->setQuery('SELECT lang.category_id, name FROM `' . rex::getTablePrefix() . 'jobs_categories_lang` AS lang '
        .'LEFT JOIN `' . rex::getTablePrefix() . 'jobs_categories` AS categories ON lang.category_id = categories.category_id '
        .'WHERE picture = "'. $filename .'"');

    // Contacts
    $sql_contacts = rex_sql::factory();
    $sql_contacts->setQuery('SELECT contact_id, name FROM `' . rex::getTablePrefix() . 'jobs_contacts` '
        .'WHERE picture = "'. $filename .'"');

    // Prepare warnings
    // Jobs
    for ($i = 0; $i < $sql_jobs->getRows(); ++$i) {
        $message = '<a href="javascript:openPage(\'index.php?page=jobs/jobs&func=edit&entry_id='.
            $sql_jobs->getValue('job_id') .'\')">'. rex_i18n::msg('jobs_rights_all') .' - '. rex_i18n::msg('jobs') .': '. $sql_jobs->getValue('name') .'</a>';
        if (!in_array($message, $warning, true)) {
            $warning[] = $message;
        }
        $sql_jobs->next();
    }

    // Categories
    for ($i = 0; $i < $sql_categories->getRows(); ++$i) {
        $message = '<a href="javascript:openPage(\'index.php?page=jobs/category&func=edit&entry_id='. $sql_categories->getValue('category_id') .'\')">'.
             rex_i18n::msg('jobs_rights_all') .' - '. rex_i18n::msg('d2u_helper_category') .': '. $sql_categories->getValue('name') . '</a>';
        if (!in_array($message, $warning, true)) {
            $warning[] = $message;
        }
        $sql_categories->next();
    }

    // Contacts
    for ($i = 0; $i < $sql_contacts->getRows(); ++$i) {
        $message = '<a href="javascript:openPage(\'index.php?page=jobs/contact&func=edit&entry_id='. $sql_contacts->getValue('contact_id') .'\')">'.
             rex_i18n::msg('jobs_rights_all') .' - '. rex_i18n::msg('jobs_contacts') .': '. $sql_contacts->getValue('name') . '</a>';
        if (!in_array($message, $warning, true)) {
            $warning[] = $message;
        }
        $sql_contacts->next();
    }

    // Settings
    $addon = rex_addon::get('jobs');
    if ($addon->hasConfig('logo') && $addon->getConfig('logo') === $filename) {
        $message = '<a href="javascript:openPage(\'index.php?page=jobs/settings\')">'.
             rex_i18n::msg('jobs') .' - '. rex_i18n::msg('d2u_helper_settings') . '</a>';
        if (!in_array($message, $warning, true)) {
            $warning[] = $message;
        }
    }
    return $warning;
}

/**
 * Addon translation list.
 * @param rex_extension_point<array<string>> $ep Redaxo extension point
 * @return array<array<string,array<int,array<string,string>>|string>|string> Addon translation list
 */
function rex_jobs_translation_list(rex_extension_point $ep) {
    $params = $ep->getParams();
    $source_clang_id = (int) $params['source_clang_id'];
    $target_clang_id = (int) $params['target_clang_id'];
    $filter_type = (string) $params['filter_type'];

    $list = $ep->getSubject();
    $list_entry = [
        'addon_name' => rex_i18n::msg('jobs'),
        'pages' => []
    ];

    $categories = FriendsOfRedaxo\Jobs\Category::getTranslationHelperObjects($target_clang_id, $filter_type);
    if (count($categories) > 0) {
        $html_categories = '<ul>';
        foreach ($categories as $category) {
            if ('' === $category->name) {
                $category = new \FriendsOfRedaxo\Jobs\Category($category->category_id, $source_clang_id);
            }
            $html_categories .= '<li><a href="'. rex_url::backendPage('jobs/category', ['entry_id' => $category->category_id, 'func' => 'edit']) .'">'. $category->name .'</a></li>';
        }
        $html_categories .= '</ul>';
        
        $list_entry['pages'][] = [
            'title' => rex_i18n::msg('d2u_helper_category'),
            'icon' => 'rex-icon-open-category',
            'html' => $html_categories
        ];
    }

    $jobs = FriendsOfRedaxo\Jobs\Job::getTranslationHelperObjects($target_clang_id, $filter_type);
    if (count($jobs) > 0) {
        $html_jobs = '<ul>';
        foreach ($jobs as $job) {
            if ('' === $job->name) {
                $job = new \FriendsOfRedaxo\Jobs\Job($job->job_id, $source_clang_id);
            }
            $html_jobs .= '<li><a href="'. rex_url::backendPage('jobs/jobs', ['entry_id' => $job->job_id, 'func' => 'edit']) .'">'. $job->name .'</a></li>';
        }
        $html_jobs .= '</ul>';
        
        $list_entry['pages'][] = [
            'title' => rex_i18n::msg('jobs_jobs'),
            'icon' => 'fa-users',
            'html' => $html_jobs
        ];
    }

    $list[] = $list_entry;

    return $list;
}