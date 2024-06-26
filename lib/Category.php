<?php

namespace FriendsOfRedaxo\Jobs;

use rex;
use rex_addon;
use rex_config;
use rex_sql;
use rex_yrewrite;

/**
 * @api
 * Category class.
 */
class Category implements \TobiasKrais\D2UHelper\ITranslationHelper
{
    /** @var int Database ID */
    public int $category_id = 0;

    /** @var int Redaxo language ID */
    public int $clang_id = 0;

    /** @var string Name */
    public string $name = '';

    /** @var string Picture */
    public string $picture = '';

    /** @var int Sort Priority */
    public int $priority = 0;

    /** @var int HR4YOU category ID */
    public int $hr4you_category_id = 0;

    /** @var string "yes" if translation needs update */
    public string $translation_needs_update = 'delete';

    /** @var string URL */
    private string $url = '';

    /**
     * Constructor.
     * @param int $category_id category ID
     * @param int $clang_id redaxo language ID
     */
    public function __construct($category_id, $clang_id)
    {
        $this->clang_id = $clang_id;
        $query = 'SELECT * FROM '. rex::getTablePrefix() .'jobs_categories_lang AS lang '
                .'LEFT JOIN '. rex::getTablePrefix() .'jobs_categories AS categories '
                    .'ON lang.category_id = categories.category_id '
                .'WHERE lang.category_id = '. $category_id .' '
                    .'AND clang_id = '. $clang_id;
        $result = rex_sql::factory();
        $result->setQuery($query);

        if ($result->getRows() > 0) {
            $this->category_id = (int) $result->getValue('category_id');
            $this->name = stripslashes((string) $result->getValue('name'));
            $this->picture = (string) $result->getValue('picture');
            $this->priority = (int) $result->getValue('priority');
            $this->translation_needs_update = (string) $result->getValue('translation_needs_update');
            $this->hr4you_category_id = (int) $result->getValue('hr4you_category_id');
        }
    }

    /**
     * Deletes the object in all languages.
     * @param bool $delete_all If true, all translations and main object are deleted. If
     * false, only this translation will be deleted.
     */
    public function delete($delete_all = true): void
    {
        $query_lang = 'DELETE FROM '. rex::getTablePrefix() .'jobs_categories_lang '
            .'WHERE category_id = '. $this->category_id
            . ($delete_all ? '' : ' AND clang_id = '. $this->clang_id);
        $result_lang = rex_sql::factory();
        $result_lang->setQuery($query_lang);

        // If no more lang objects are available, delete
        $query_main = 'SELECT * FROM '. rex::getTablePrefix() .'jobs_categories_lang '
            .'WHERE category_id = '. $this->category_id;
        $result_main = rex_sql::factory();
        $result_main->setQuery($query_main);
        if (0 === $result_main->getRows()) {
            $query = 'DELETE FROM '. rex::getTablePrefix() .'jobs_categories '
                .'WHERE category_id = '. $this->category_id;
            $result = rex_sql::factory();
            $result->setQuery($query);

            // reset priorities
            $this->setPriority(true);
        }

        \TobiasKrais\D2UHelper\BackendHelper::generateUrlCache('job_category_id');
        \TobiasKrais\D2UHelper\BackendHelper::generateUrlCache('job_id');
    }

    /**
     * Get all categories.
     * @param int $clang_id redaxo clang id
     * @param bool $ignoreOfflines Ignore offline categories
     * @return Category[] array with Category objects
     */
    public static function getAll($clang_id, $ignoreOfflines = true)
    {
        $query = 'SELECT lang.category_id FROM '. rex::getTablePrefix() .'jobs_categories_lang AS lang '
            .'LEFT JOIN '. rex::getTablePrefix() .'jobs_categories AS categories '
                .'ON lang.category_id = categories.category_id '
            .'WHERE clang_id = '. $clang_id .' '
            .'ORDER BY name';
        $result = rex_sql::factory();
        $result->setQuery($query);

        $categories = [];
        for ($i = 0; $i < $result->getRows(); ++$i) {
            if ($ignoreOfflines) {
                $query_check_offline = 'SELECT lang.job_id FROM '. rex::getTablePrefix() .'jobs_jobs_lang AS lang '
                    .'LEFT JOIN '. rex::getTablePrefix() .'jobs_jobs AS jobs '
                        .'ON lang.job_id = jobs.job_id AND lang.clang_id = '. $clang_id .' '
                    ."WHERE category_ids LIKE '%|". $result->getValue('category_id') ."|%' AND online_status = 'online'";

                $result_check_offline = rex_sql::factory();
                $result_check_offline->setQuery($query_check_offline);
                if ($result_check_offline->getRows() > 0) {
                    $categories[$result->getValue('category_id')] = new self((int) $result->getValue('category_id'), $clang_id);
                }
            } else {
                $categories[$result->getValue('category_id')] = new self((int) $result->getValue('category_id'), $clang_id);
            }
            $result->next();
        }
        return $categories;
    }

    /**
     * Get all categories.
     * @param int $preferred_clang_id redaxo clang id
     * @param bool $ignoreOfflines Ignore offline categories
     * @return Category[] array with Category objects
     */
    public static function getAllIgnoreLanguage($preferred_clang_id, $ignoreOfflines = true)
    {
        $query = 'SELECT lang.category_id FROM '. rex::getTablePrefix() .'jobs_categories_lang AS lang '
            .'LEFT JOIN '. rex::getTablePrefix() .'jobs_categories AS categories '
                .'ON lang.category_id = categories.category_id '
            .'ORDER BY name';
        $result = rex_sql::factory();
        $result->setQuery($query);

        $categories = [];
        for ($i = 0; $i < $result->getRows(); ++$i) {
            if ($ignoreOfflines) {
                $query_check_offline = 'SELECT lang.job_id FROM '. rex::getTablePrefix() .'jobs_jobs_lang AS lang '
                    .'LEFT JOIN '. rex::getTablePrefix() .'jobs_jobs AS jobs '
                        .'ON lang.job_id = jobs.job_id '
                    ."WHERE category_ids LIKE '%|". $result->getValue('category_id') ."|%' AND online_status = 'online'";

                $result_check_offline = rex_sql::factory();
                $result_check_offline->setQuery($query_check_offline);
                if ($result_check_offline->getRows() > 0) {
                    $categories[$result->getValue('category_id')] = new self((int) $result->getValue('category_id'), $preferred_clang_id);
                }
            } else {
                $categories[$result->getValue('category_id')] = new self((int) $result->getValue('category_id'), $preferred_clang_id);
            }
            $result->next();
        }
        return $categories;
    }

    /**
     * Get object by HR4You ID.
     * @param int $hr4you_id HR4You ID
     * @return Category|bool Category object, if available, otherwise false
     */
    public static function getByHR4YouID($hr4you_id)
    {
        $query = 'SELECT category_id FROM '. rex::getTablePrefix() .'jobs_categories '
                .'WHERE hr4you_category_id = '. $hr4you_id;
        $result = rex_sql::factory();
        $result->setQuery($query);

        if ($result->getRows() > 0) {
            return new self((int) $result->getValue('category_id'), (int) rex_config::get('jobs', 'hr4you_default_lang'));
        }
        return false;
    }

    /**
     * Gets the jobs of the category.
     * @param bool $only_online Show only online jobs
     * @return array<Job> Jobs in this category
     */
    public function getJobs($only_online = false)
    {
        $query = 'SELECT lang.job_id FROM '. rex::getTablePrefix() .'jobs_jobs_lang AS lang '
            .'LEFT JOIN '. rex::getTablePrefix() .'jobs_jobs AS jobs '
                    .'ON lang.job_id = jobs.job_id '
            ."WHERE category_ids LIKE '%|". $this->category_id ."|%' AND clang_id = ". $this->clang_id .' ';
        if ($only_online) {
            $query .= "AND online_status = 'online' ";
        }
        $query .= 'ORDER BY name ASC';
        $result = rex_sql::factory();
        $result->setQuery($query);

        $jobs = [];
        for ($i = 0; $i < $result->getRows(); ++$i) {
            $jobs[] = new Job((int) $result->getValue('job_id'), $this->clang_id);
            $result->next();
        }
        return $jobs;
    }

    /**
     * Get objects concerning translation updates.
     * @param int $clang_id Redaxo language ID
     * @param string $type 'update' or 'missing'
     * @return Category[] array with Category objects
     */
    public static function getTranslationHelperObjects($clang_id, $type)
    {
        $query = 'SELECT category_id FROM '. rex::getTablePrefix() .'jobs_categories_lang '
                .'WHERE clang_id = '. $clang_id ." AND translation_needs_update = 'yes' "
                .'ORDER BY name';
        if ('missing' === $type) {
            $query = 'SELECT main.category_id FROM '. rex::getTablePrefix() .'jobs_categories AS main '
                    .'LEFT JOIN '. rex::getTablePrefix() .'jobs_categories_lang AS target_lang '
                        .'ON main.category_id = target_lang.category_id AND target_lang.clang_id = '. $clang_id .' '
                    .'LEFT JOIN '. rex::getTablePrefix() .'jobs_categories_lang AS default_lang '
                        .'ON main.category_id = default_lang.category_id AND default_lang.clang_id = '. rex_config::get('d2u_helper', 'default_lang') .' '
                    .'WHERE target_lang.category_id IS NULL '
                    .'ORDER BY default_lang.name';
            $clang_id = (int) rex_config::get('d2u_helper', 'default_lang');
        }
        $result = rex_sql::factory();
        $result->setQuery($query);

        $objects = [];
        for ($i = 0; $i < $result->getRows(); ++$i) {
            $objects[] = new self((int) $result->getValue('category_id'), $clang_id);
            $result->next();
        }

        return $objects;
    }

    /**
     * Returns the URL of this object.
     * @param bool $including_domain true if Domain name should be included
     * @return string URL
     */
    public function getUrl($including_domain = false)
    {
        if ('' === $this->url) {

            $parameterArray = [];
            $parameterArray['job_category_id'] = $this->category_id;

            $this->url = rex_getUrl((int) rex_config::get('jobs', 'article_id'), $this->clang_id, $parameterArray, '&');
        }

        if ($including_domain) {
            if (rex_addon::get('yrewrite')->isAvailable()) {
                return str_replace(rex_yrewrite::getCurrentDomain()->getUrl() .'/', rex_yrewrite::getCurrentDomain()->getUrl(), rex_yrewrite::getCurrentDomain()->getUrl() . $this->url);
            }

            return str_replace(rex::getServer(). '/', rex::getServer(), rex::getServer() . $this->url);

        }

        return $this->url;

    }

    /**
     * Updates or inserts the object into database.
     * @return bool true if successful
     */
    public function save()
    {
        $error = false;

        // Save the not language specific part
        $pre_save_object = new self($this->category_id, $this->clang_id);

        // save priority, but only if new or changed
        if ($this->priority !== $pre_save_object->priority || 0 === $this->category_id) {
            $this->setPriority();
        }

        if (0 === $this->category_id || $pre_save_object !== $this) {
            $query = rex::getTablePrefix() .'jobs_categories SET '
                .'priority = '. $this->priority .', '
                ."picture = '". $this->picture ."', "
                .'hr4you_category_id = '. $this->hr4you_category_id;

            if (0 === $this->category_id) {
                $query = 'INSERT INTO '. $query;
            } else {
                $query = 'UPDATE '. $query .' WHERE category_id = '. $this->category_id;
            }
            $result = rex_sql::factory();
            $result->setQuery($query);
            if (0 === $this->category_id) {
                $this->category_id = (int) $result->getLastId();
                $error = $result->hasError();
            }
        }

        $regenerate_urls = false;
        if (!$error) {
            // Save the language specific part
            $pre_save_object = new self($this->category_id, $this->clang_id);
            if ($pre_save_object !== $this) {
                $query = 'REPLACE INTO '. rex::getTablePrefix() .'jobs_categories_lang SET '
                        ."category_id = '". $this->category_id ."', "
                        ."clang_id = '". $this->clang_id ."', "
                        ."name = '". addslashes($this->name) ."', "
                        ."translation_needs_update = '". $this->translation_needs_update ."' ";

                $result = rex_sql::factory();
                $result->setQuery($query);
                $error = $result->hasError();

                if (!$error && $pre_save_object->name !== $this->name) {
                    $regenerate_urls = true;
                }
            }
        }

        // Update URLs
        if ($regenerate_urls) {
            \TobiasKrais\D2UHelper\BackendHelper::generateUrlCache('job_category_id');
            \TobiasKrais\D2UHelper\BackendHelper::generateUrlCache('job_id');
        }

        return $error;
    }

    /**
     * Reassigns priorities in database.
     * @param bool $delete Reorder priority after deletion
     */
    private function setPriority($delete = false): void
    {
        // Pull prios from database
        $query = 'SELECT category_id, priority FROM '. rex::getTablePrefix() .'jobs_categories '
            .'WHERE category_id <> '. $this->category_id .' ORDER BY priority';
        $result = rex_sql::factory();
        $result->setQuery($query);

        // When priority is too small, set at beginning
        if ($this->priority <= 0) {
            $this->priority = 1;
        }

        // When prio is too high or was deleted, simply add at end
        if ($this->priority > $result->getRows() || $delete) {
            $this->priority = $result->getRows() + 1;
        }

        $categories = [];
        for ($i = 0; $i < $result->getRows(); ++$i) {
            $categories[$result->getValue('priority')] = $result->getValue('category_id');
            $result->next();
        }
        array_splice($categories, $this->priority - 1, 0, [$this->category_id]);

        // Save all prios
        foreach ($categories as $prio => $category_id) {
            $query = 'UPDATE '. rex::getTablePrefix() .'jobs_categories '
                    .'SET priority = '. ((int) $prio + 1) .' ' // +1 because array_splice recounts at zero
                    .'WHERE category_id = '. $category_id;
            $result = rex_sql::factory();
            $result->setQuery($query);
        }
    }
}
