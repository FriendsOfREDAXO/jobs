<?php

use FriendsOfRedaxo\Jobs\Category;
use FriendsOfRedaxo\Jobs\Contact;
use FriendsOfRedaxo\Jobs\Job;

if (!function_exists('prepareText')) {
    /**
     * Replaces common text changes.
     * @param string $text Text in need of replacements
     * @return string Replaced text
     */
    function prepareText($text)
    {
        return str_replace('</li>', '</span></li>', str_replace('<li>', '<li><span>', str_replace('<ul>', '<ul class="bullets">', TobiasKrais\D2UHelper\FrontendHelper::prepareEditorField($text))));
    }
}

$url_namespace = TobiasKrais\D2UHelper\FrontendHelper::getUrlNamespace();
$url_id = TobiasKrais\D2UHelper\FrontendHelper::getUrlId();

$category_id = (int) 'REX_VALUE[1]';
$category = false;
if ($category_id > 0) { /** @phpstan-ignore-line */
    $category = new Category($category_id, rex_clang::getCurrentId());
} else {
    if (filter_input(INPUT_GET, 'job_category_id', FILTER_VALIDATE_INT, ['options' => ['default' => 0]]) > 0 || 'job_category_id' === $url_namespace) {
        $category_id = (int) filter_input(INPUT_GET, 'job_category_id', FILTER_VALIDATE_INT);
        if (\rex_addon::get('url')->isAvailable() && $url_id > 0) {
            $category_id = $url_id;
        }
        $category = new Category($category_id, rex_clang::getCurrentId());
    }
}

$hide_application_hint = 'REX_VALUE[2]' === 'true' ? true : false; /** @phpstan-ignore-line */
$show_json_ld = 'REX_VALUE[3]' === 'true' ? true : false; /** @phpstan-ignore-line */
$show_application_form = 'REX_VALUE[4]' === 'true' ? true : false; /** @phpstan-ignore-line */

if (rex::isBackend()) {
    // Ausgabe im BACKEND
?>
	<h1 style="font-size: 1.5em;">Stellenmarkt Ausgabe</h1>
<?php
    if ($category instanceof Category) {
        echo '<p>Anzuzeigende Kategorie: '. $category->name .'</p>';
    } else {
        echo '<p>Anzuzeigende Kategorien: Alle</p>';
    }
    if ($show_json_ld) { /** @phpstan-ignore-line */
        echo '<p>Die Anzeigen werden im JSON-LD Format für z.B. Google Jobs veröffentlicht.</p>';
    } else {
        echo '<p>Die Anzeigen werden NICHT im JSON-LD Format für z.B. Google Jobs veröffentlicht.</p>';
    }
} else {
    // FRONTEND Output
    $sprog = rex_addon::get('sprog');
    $tag_open = $sprog->getConfig('wildcard_open_tag');
    $tag_close = $sprog->getConfig('wildcard_close_tag');

    // Output job details
    if (filter_input(INPUT_GET, 'job_id', FILTER_VALIDATE_INT, ['options' => ['default' => 0]]) > 0 || 'job_id' === $url_namespace) {
        $job_id = (int) filter_input(INPUT_GET, 'job_id', FILTER_VALIDATE_INT);
        if (\rex_addon::get('url')->isAvailable() && $url_id > 0) {
            $job_id = $url_id;
        }

        $job = new Job($job_id, rex_clang::getCurrentId());
        // Redirect if object is not online
        if ('online' !== $job->online_status) {
            rex_redirect(rex_article::getNotfoundArticleId(), rex_clang::getCurrentId());
        }
        echo '<div class="col-12 col-lg-8">';
        echo '<article class="job-box">';
        echo '<img src="'. ('' !== $job->picture ? rex_media_manager::getUrl('jobs_jobheader', $job->picture) : \rex_url::addonAssets('jobs', 'noavatar.jpg'))  .'" alt="'. strip_tags($job->name) .'">';
        if ('' !== $job->prolog) { /** @phpstan-ignore-line */
            echo '<div class="prolog">'. $job->prolog .'</div>';
        }
        echo '<div class="heading">';
        echo '<h2>'. $job->name .'</h2>';
        if ('' !== $job->city || '' !== $job->reference_number) {
            echo '<p><b>';
            if ('' !== $job->city) {
                echo $tag_open .'jobs_region'. $tag_close .': '. $job->city . ('' !== $job->reference_number ? ' / ' : '');
            }
            if ('' !== $job->reference_number) {
                echo $tag_open .'jobs_reference_number'. $tag_close .': '. $job->reference_number;
            }
            echo '</b></p>';
        }
        echo '</div>';

        $application_form = rex_request('apply', 'int', 0) > 0 ? true : false;
        $job_application_link = $job->clang_id === rex_clang::getCurrentId() ? $job->getUrl() . (false !== strstr($job->getUrl(), '?') ? '&' : '?') .'apply=1' : rex_getUrl('', '', ['job_id' => $job->job_id, 'target_clang' => $job->clang_id, 'apply' => 1]);
        if ($application_form) {
            echo '<a name="application-form" class="anchor"></a>';
            echo '<h3>'. \Sprog\Wildcard::get('jobs_application_link', $job->clang_id) .'</h3>';
            $yform = new rex_yform();
            $form_data = 'hidden|job_name|'. $job->name . ('' !== $job->reference_number ? ' (Referenznummer: '. $job->reference_number .')' : '') .'|REQUEST
					hidden|job_clang_id|'. $job->clang_id .'|REQUEST
					text|name|'. \Sprog\Wildcard::get('d2u_helper_module_form_name', $job->clang_id) .' *|||{"required":"required"}
					text|address|'. \Sprog\Wildcard::get('d2u_helper_module_form_street', $job->clang_id) .'|||
					text|zip|'. \Sprog\Wildcard::get('d2u_helper_module_form_zip', $job->clang_id) .'|||
					text|city|'. \Sprog\Wildcard::get('d2u_helper_module_form_city', $job->clang_id) .'|||
					text|phone|'. \Sprog\Wildcard::get('d2u_helper_module_form_phone', $job->clang_id) .' *|||{"required":"required"}
					text|email|'. \Sprog\Wildcard::get('d2u_helper_module_form_email', $job->clang_id) .' *|||{"required":"required"}
					textarea|message|'. \Sprog\Wildcard::get('d2u_helper_module_form_message', $job->clang_id);
            if (rex_addon::get('yform_spam_protection')->isAvailable()) {
                $form_data .= '
					spam_protection|honeypot|Leave empty|'. \Sprog\Wildcard::get('d2u_helper_module_form_validate_spam_detected', $job->clang_id) .'|0';
            } else {
                $form_data .= '
					php|validate_timer|Spamprotection|<input name="validate_timer" type="hidden" value="'. microtime(true) .'" />|
					validate|customfunction|validate_timer|TobiasKrais\D2UHelper\FrontendHelper::yform_validate_timer|10|'. \Sprog\Wildcard::get('d2u_helper_module_form_validate_spambots', $job->clang_id) .'|

					html|honeypot||<div class="mail-validate hide">
					text|mailvalidate|'. \Sprog\Wildcard::get('d2u_helper_module_form_email', $job->clang_id) .'||no_db
					validate|compare_value|mailvalidate||!=|'. \Sprog\Wildcard::get('d2u_helper_module_form_validate_spam_detected', $job->clang_id) .'|
					html|honeypot||</div>';
            }
            $form_data .= '
					validate|empty|name|'. \Sprog\Wildcard::get('d2u_helper_module_form_validate_name', $job->clang_id) .'
					validate|empty|phone|'. \Sprog\Wildcard::get('d2u_helper_module_form_validate_phone', $job->clang_id) .'
					validate|empty|email|'. \Sprog\Wildcard::get('d2u_helper_module_form_validate_email', $job->clang_id) .'
					validate|type|email|email|'. \Sprog\Wildcard::get('d2u_helper_module_form_validate_email', $job->clang_id) .'
					validate|empty|privacy_policy_accepted|'. \Sprog\Wildcard::get('d2u_helper_module_form_validate_privacy_policy', $job->clang_id) .'

					action|tpl2email|jobs_thanks_application|email
					action|tpl2email|jobs_application|'. rex_config::get('jobs', 'email');

            $yform->setFormData(trim($form_data));
			$upload_config = [
				'sizes' => ['min' => 100, 'max' => 100000],
				'allowed_extensions' => ['pdf', 'odt', 'doc', 'docx', 'zip'],
				'messages' => [
					'min_error' => \Sprog\Wildcard::get('jobs_module_attachment_min_error_msg', $job->clang_id),
					'max_error' => \Sprog\Wildcard::get('jobs_module_attachment_max_error_msg', $job->clang_id),
					'type_error' => \Sprog\Wildcard::get('jobs_module_attachment_type_error_msg', $job->clang_id),
					'empty_error' => \Sprog\Wildcard::get('jobs_module_attachment_empty_error_msg', $job->clang_id),
					'delete_file' => \Sprog\Wildcard::get('jobs_module_attachment_delete_file_msg', $job->clang_id),
					'system_error' => \Sprog\Wildcard::get('jobs_module_attachment_system_error_msg', $job->clang_id)
				]
			];
			$yform->setValueField('upload', ['upload', \Sprog\Wildcard::get('jobs_module_attachment', $job->clang_id), 'config' => json_encode($upload_config)]);
			$yform->setValueField('php', ['php_attach', \Sprog\Wildcard::get('jobs_module_attachment', $job->clang_id), '<?php if (isset($this->params["value_pool"]["files"])) { $this->params["value_pool"]["email_attachments"] = $this->params["value_pool"]["files"]; } ?>']);

			$yform->setValueField('checkbox', ['privacy_policy_accepted', \Sprog\Wildcard::get('d2u_helper_module_form_privacy_policy', $job->clang_id), '0']);
			$yform->setValidateField('empty', ['privacy_policy_accepted', \Sprog\Wildcard::get('d2u_helper_module_form_validate_privacy_policy', $job->clang_id)]);

            $yform->setObjectparams('Error-occured', \Sprog\Wildcard::get('d2u_helper_module_form_validate_title', $job->clang_id));
            $yform->setObjectparams('form_action', $job_application_link);
            $yform->setObjectparams('form_anchor', 'application-form');
            $yform->setObjectparams('form_name', 'jobs_module_23_1_'. $this->getCurrentSlice()->getId()); /** @phpstan-ignore-line */
            $yform->setObjectparams('real_field_names', true);

            // action - showtext
            $yform->setActionField('showtext', [\Sprog\Wildcard::get('jobs_module_form_thanks', $job->clang_id),
                '<div class="rex-message"><div class="rex-info"><p>',
                '</p></div></div>',
                1]);

            echo $yform->getForm();
        } else {
            if ('' !== $job->hr4you_lead_in) {
                echo '<br>';
                echo $job->hr4you_lead_in;
            }
            if ('' !== $job->tasks_heading) {
                echo '<h3>'. $job->tasks_heading .'</h3>';
                echo prepareText($job->tasks_text);
            }
            if ('' !== $job->profile_heading) {
                echo '<h3>'. $job->profile_heading .'</h3>';
                echo prepareText($job->profile_text);
            }
            if ('' !== $job->offer_heading) {
                echo '<h3>'. $job->offer_heading .'</h3>';
                echo prepareText($job->offer_text);
            }
            if ('' !== $job->hr4you_url_application_form) {
                echo '<p><a target="_blank" href="'. $job->hr4you_url_application_form .'">'
                    .'<button class="d2u_application_form_button">'. $tag_open .'jobs_application_link'. $tag_close .'<span class="jobs_arrow_right"></span></button></a></p>';
            } elseif ($show_application_form) { /** @phpstan-ignore-line */
                echo '<p><a href="'. $job_application_link .'" title="'. \Sprog\Wildcard::get('jobs_application_link', $job->clang_id) .'">'
                    .'<button class="d2u_application_form_button">'. \Sprog\Wildcard::get('jobs_application_link', $job->clang_id)
                    .'<span class="jobs_arrow_right"></span></button></a></p>';
            } elseif (false === $hide_application_hint) {
                echo '<p class="appendix">'. $tag_open .'jobs_footer'. $tag_close
                    .'<br><br><a href="mailto:'. rex_config::get('jobs', 'email') .'" title="'. rex_config::get('jobs', 'email') .'">'. rex_config::get('jobs', 'email') .'</a>'
                    .'</p>';
            }
        }
        echo '</article>';
        echo '</div>';
        echo '<div class="sp sections-less hide-for-medium-up"></div>';

        echo '<div class="col-12 col-lg-4">';
        if ($job->contact instanceof Contact) {
            echo '<div class="job-box contact">';
            echo '<div class="row">';
            echo '<div class="col-12">'. \Sprog\Wildcard::get('jobs_questions') .'</div>';

            echo '<div class="col-12 col-sm-4">';
            echo '<img src="'. ('' !== $job->contact->picture ? rex_media_manager::getUrl('jobs_contact', $job->contact->picture) : \rex_url::addonAssets('jobs', 'noavatar.jpg'))  .'" alt="'. $job->contact->name .'">';
            echo '</div>';

            echo '<div class="col-12 col-sm-8">';
            echo '<h3 class="contact-heading">'. $job->contact->name .'</h3>';
            if ('' !== $job->contact->phone) {
                echo $tag_open .'jobs_phone'. $tag_close .': '. $job->contact->phone .'<br>';
            }
            if ('' !== $job->contact->email) {
                echo '<a href="mailto:'. $job->contact->email .'" title="'. rex_config::get('jobs', 'email') .'">'.$job->contact->email .'</a>';
            }
            echo '</div>';

            if ('' !== $job->contact->phone_video) {
                echo '<div class="col-12">';
                echo '<a target="_blank" href="https://api.whatsapp.com/send?phone='. preg_replace('/[^0-9]/', '', $job->contact->phone_video) .'">'
                    .'<button class="d2u_application_form_button"><span class="jobs_video"></span>'. $tag_open .'jobs_video_application'. $tag_close .'<span class="jobs_arrow_right"></span></button></a>';
                echo '</div>';
            }
            if ('' !== $job->hr4you_url_application_form) {
                echo '<div class="col-12">';
                echo '<a target="_blank" href="'. $job->hr4you_url_application_form .'">'
                    .'<button class="d2u_application_form_button">'. $tag_open .'jobs_application_link'. $tag_close .'<span class="jobs_arrow_right"></span></button></a>';
                echo '</div>';
            } elseif ($show_application_form) { /** @phpstan-ignore-line */
                echo '<div class="col-12">';
                echo '<a href="'. $job_application_link .'" title="'. \Sprog\Wildcard::get('jobs_application_link', $job->clang_id) .'">'
                    .'<button class="d2u_application_form_button">'. \Sprog\Wildcard::get('jobs_application_link', $job->clang_id)
                    .'<span class="jobs_arrow_right"></span></button></a>';
                echo '</div>';
            }
            echo '</div>';
            echo '</div>';
        }
        $faq_article = rex_article::get((int) rex_config::get('jobs', 'faq_article_id'));
        if ($faq_article instanceof rex_article) {
            echo '<div class="job-box faq">';
            echo '<div class="row">';

            echo '<div class="col-4 jobs_questionmark_center"><span class="jobs_big_questionmark">?</span></div>';

            echo '<div class="col-8">';
            echo '<h3 class="contact-heading">'. \Sprog\Wildcard::get('jobs_faq', $job->clang_id) .'</h3>';
            echo '<p>'. \Sprog\Wildcard::get('jobs_faq_text', $job->clang_id) .'</p>';
            echo '<a href="'. $faq_article->getUrl() .'" title="'. $faq_article->getName() .'">'
            .'<button class="d2u_application_form_button">'. \Sprog\Wildcard::get('jobs_faq_link', $job->clang_id)
            .'<span class="jobs_arrow_right"></span></button></a>';
            echo '</div>';

            echo '</div>';
            echo '</div>';
        }
        echo '</div>';

        echo '</div>';

        // Show job as JSON-LD
        if ($show_json_ld) { /** @phpstan-ignore-line */
            echo $job->getJsonLdCode();
        }
    } else {
        // Output Job list
        $jobs = Job::getAll(rex_clang::getCurrentId(), $category_id, true);
        echo '<div class="col-12">';
        echo '<div class="row" data-match-height>';
        if (count($jobs) > 0) {
            echo '<div class="col-12">';
            echo '<h1>'. $tag_open .'jobs_vacancies'. $tag_close .' ';
            if (false !== $category) {
                echo $category->name;
            }
            echo '</h1>';
            echo '</div>';

            // Job filter
            $filter_categories = Category::getAll(rex_clang::getCurrentId(), true);
            $filter_cities = Job::getAllCities(rex_clang::getCurrentId(), true);
            if (0 === $category_id && count($jobs) > 1 && (count($filter_categories) > 1 || count($filter_cities) > 1)) { /** @phpstan-ignore-line */
                echo '<div class="col-12 jobs_filters">';
                if (count($filter_categories) > 1) {
                    echo '<label for="filter">'. \Sprog\Wildcard::get('jobs_catgories') .':</label>';
                    echo '<select id="filter_categories">';
                    echo '<option value="all">'. \Sprog\Wildcard::get('jobs_all') .'</option>';
                    foreach ($filter_categories as $filter_category) {
                        echo '<option value="'. rex_string::normalize($filter_category->name) .'">'. $filter_category->name .'</option>';
                    }
                    echo '</select>';
                } else {
                    echo '<input id="filter_categories" type="hidden" value="all">';
                }
                if (count($filter_cities) > 1) {
                    echo '<label for="filter">'. \Sprog\Wildcard::get('jobs_cities') .':</label>';
                    echo '<select id="filter_cities">';
                    echo '<option value="all">'. \Sprog\Wildcard::get('jobs_all') .'</option>';
                    foreach ($filter_cities as $filter_city) {
                        echo '<option value="'. rex_string::normalize($filter_city) .'">'. $filter_city .'</option>';
                    }
                    echo '</select>';
                } else {
                    echo '<input id="filter_cities" type="hidden" value="all">';
                }
                echo '</div>';
                // Job filter JS follows below
            }

            foreach ($jobs as $job) {
                $category_classes = [];
                foreach ($job->categories as $job_category) {
                    $category_classes[] = rex_string::normalize($job_category->name);
                }
                echo '<div class="col-12 col-md-6 col-lg-4 d2u_job'. (count($category_classes) > 0 ? ' '. implode(' ', $category_classes) : '')  . ('' !== $job->city ? ' '. rex_string::normalize($job->city) : '') .'">';
                echo '<a href="'. $job->getUrl() .'" class="job-box-list-link" title="'. strip_tags($job->name).'">';
                echo '<div class="job-box job-box-list" data-height-watch>';
                echo '<img src="'. ('' !== $job->picture ? rex_media_manager::getUrl('jobs_joblist', $job->picture) : \rex_url::addonAssets('jobs', 'noavatar.jpg'))  .'" alt="'. strip_tags($job->name) .'">';
                echo '<h2>'. $job->name .'</h2>';
                if ('' !== $job->city || '' !== $job->reference_number) {
                    echo '<p>';
                    if ('' !== $job->city) {
                        echo $tag_open .'jobs_region'. $tag_close .': '. $job->city . ('' !== $job->reference_number ? ' / ' : '');
                    }
                    if ('' !== $job->reference_number) {
                        echo $tag_open .'jobs_reference_number'. $tag_close .': '. $job->reference_number;
                    }
                    echo '</p>';
                }
                echo '</div>';
                echo '</a>';
                echo '</div>';
            }
        }
        echo '<div id="jobs_no_vacancies" class="col-12">';
        echo '<p>'. $tag_open .'jobs_no_jobs_found'. $tag_close .'</p>';
        echo '</div>';

        echo '</div>';
        echo '</div>';

        // Job filter JS
        if (0 === $category_id && count($jobs) > 1 && (count($filter_categories) > 1 || count($filter_cities) > 1)) { /** @phpstan-ignore-line */
    ?>
        <script>
            const jobs_filter_categories = document.getElementById('filter_categories');
            const jobs_filter_cities = document.getElementById('filter_cities');
            const jobs_elementsToShow = [];

            /** Make jobs visible or hide them according to filter selection */
            function jobs_toggleElements() {
                const jobs_filter_categories_all = jobs_filter_categories.value === 'all';
                const jobs_filter_cities_all = jobs_filter_cities.value === 'all';
                let jobs_one_element_visible = false;

                jobs_elementsToShow.forEach(el => {
                    let style = '';
                    if ((jobs_filter_categories_all && jobs_filter_cities_all)
                        || (jobs_filter_categories_all && el.classList.contains(jobs_filter_cities.value))
                        || (el.classList.contains(jobs_filter_categories.value) && jobs_filter_cities_all)
                        || (el.classList.contains(jobs_filter_categories.value) && el.classList.contains(jobs_filter_cities.value))
                            ) {
                        style = 'block';
                        jobs_one_element_visible = true;
                    }
                    else {
                        style = 'none';
                    }
                    el.style.display = style;
                });

                if(jobs_one_element_visible) {
                    document.getElementById('jobs_no_vacancies').style.display = 'none';
                }
                else {
                    document.getElementById('jobs_no_vacancies').style.display = 'block';
                }

                // Change style for non-default values
                if(jobs_filter_categories.value === 'all') {
                    jobs_filter_categories.classList.remove('jobs_filter_select_not_all');
                }
                else {
                    jobs_filter_categories.classList.add('jobs_filter_select_not_all');
                }
                if(jobs_filter_cities.value === 'all') {
                    jobs_filter_cities.classList.remove('jobs_filter_select_not_all');
                }
                else {
                    jobs_filter_cities.classList.add('jobs_filter_select_not_all');
                }
            }

            // find jobs and put them in an array
            document.querySelectorAll('.d2u_job').forEach(el => {
                if (el.classList.length > 0) {
                    jobs_elementsToShow.push(el);
                }
            });

            jobs_toggleElements();

            jobs_filter_categories.addEventListener('change', jobs_toggleElements);
            jobs_filter_cities.addEventListener('change', jobs_toggleElements);
        </script>
    <?php
        }
    }
}
