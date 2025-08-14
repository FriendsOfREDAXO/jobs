<?php
namespace FriendsOfRedaxo\Jobs;

use DOMDocument;
use Exception;
use rex;
use rex_api_exception;
use rex_config;
use rex_dir;
use rex_i18n;
use rex_media_service;
use rex_path;
use rex_view;
use SimpleXMLElement;

/**
 * @api
 * Class managing all HR4You v3 stuff.
 */
class Hr4youImport
{
    /**
     * Perform HR4You XML import, calls import().
     */
    public static function autoimport(): void
    {
        // Include mediapool functions when call is frontend call
        if (!rex::isBackend()) {
            require_once rex_path::addon('mediapool', 'functions/function_rex_mediapool.php');
        }

        if (self::import()) {
            echo rex_view::success(rex_i18n::msg('jobs_hr4you_import_success'));
        }
    }

    /**
     * Perform HR4You XML import.
     * @return bool true if successfull
     */
    public static function import()
    {
        $hr4you_xml_url = rex_config::get('jobs', 'hr4you_xml_url', false);
        if (false === $hr4you_xml_url) {
            echo \rex_view::error(\rex_i18n::msg('jobs_hr4you_settings_failure_xml_url'));
            return false;
        }

        $xml_stream = stream_context_create(['http' => ['header' => 'Accept: application/xml']]);
        $xml_contents = file_get_contents((string) $hr4you_xml_url, false, $xml_stream);
        if (false === $xml_contents) {
            echo \rex_view::error(\rex_i18n::msg('jobs_hr4you_import_failure_xml_url'));
            return false;
        }
        $xml_jobs = new SimpleXMLElement($xml_contents);

        self::log('***** Starting Import *****');
        // Get old stuff to be able to delete it later
        $old_jobs = Job::getAllHR4YouJobs();
        $old_contacts = []; // Get them later from Jobs
        $old_pictures = [];
        foreach ($old_jobs as $old_job) {
            // Pictures
            if (!in_array($old_job->picture, $old_pictures, true)) {
                $old_pictures[$old_job->picture] = $old_job->picture;
            }
            // Contacts
            if ($old_job->contact instanceof Contact && !array_key_exists($old_job->contact->contact_id, $old_contacts)) {
                $old_contacts[$old_job->contact->contact_id] = $old_job->contact;
                if (!in_array($old_job->contact->picture, $old_pictures, true)) {
                    $old_pictures[$old_job->contact->picture] = $old_job->contact->picture;
                }
            }
        }

        // Get new jobs
        foreach ($xml_jobs->job as $xml_job) {
            // Import pictures
            $job_picture_filename = '';
            if ('' !== (string) $xml_job->jobHeaderImage) {
                // Clean URL first (remove query parameters)
                $clean_url = strtok($xml_job->jobHeaderImage, '?');
                $job_picture_pathInfo = pathinfo($clean_url);
                
                // If no extension or suspicious extension, detect from actual image data
                if (!isset($job_picture_pathInfo['extension']) || empty($job_picture_pathInfo['extension']) || 
                    !in_array(strtolower($job_picture_pathInfo['extension']), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    
                    // Download a small part of the image to detect the real format
                    $context = stream_context_create([
                        'http' => [
                            'method' => 'GET',
                            'header' => 'Range: bytes=0-50' // Just get first 50 bytes to detect format
                        ]
                    ]);
                    
                    $image_data = file_get_contents($xml_job->jobHeaderImage, false, $context);
                    if ($image_data !== false) {
                        // Detect image type from binary data
                        $detected_extension = self::detectImageExtensionFromData($image_data);
                        if ($detected_extension) {
                            $job_picture_pathInfo['extension'] = $detected_extension;
                            self::log('Detected image format: ' . $detected_extension . ' for ' . $xml_job->jobHeaderImage);
                        } else {
                            $job_picture_pathInfo['extension'] = 'jpg'; // fallback
                            self::log('Could not detect image format, using JPG fallback for ' . $xml_job->jobHeaderImage);
                        }
                    } else {
                        $job_picture_pathInfo['extension'] = 'jpg'; // fallback
                        self::log('Could not download image for format detection, using JPG fallback for ' . $xml_job->jobHeaderImage);
                    }
                    
                    // Reconstruct basename with correct extension
                    if (isset($job_picture_pathInfo['filename'])) {
                        $job_picture_pathInfo['basename'] = $job_picture_pathInfo['filename'] . '.' . $job_picture_pathInfo['extension'];
                    } else {
                        $filename = basename($clean_url);
                        // Remove any existing extension first
                        $filename = pathinfo($filename, PATHINFO_FILENAME);
                        $job_picture_pathInfo['basename'] = $filename . '.' . $job_picture_pathInfo['extension'];
                    }
                }
                
                $job_picture_filename = \TobiasKrais\D2UHelper\BackendHelper::getMediapoolFilename($job_picture_pathInfo['basename']);
                $job_picture = \rex_media::get($job_picture_filename);
                if ($job_picture instanceof \rex_media && $job_picture->fileExists()) {
                    // File already imported, unset in $old_pictures, because remaining ones will be deleted
                    if (in_array($job_picture->getFileName(), $old_pictures, true)) {
                        unset($old_pictures[$job_picture->getFileName()]);
                    }
                    self::log('Job picture '. $job_picture_filename .' already available in mediapool.');
                } else {
                    // File exists only in database, but no more physically: remove it before import
                    if ($job_picture instanceof \rex_media) {
                        try {
                            rex_media_service::deleteMedia($job_picture->getFileName());
                        } catch (Exception $e) {
                            self::log('Picture physically not found. Error deleting media from mediapool database.');
                        }
                    }

                    // Import
                    $target_picture = \rex_path::media($job_picture_pathInfo['basename']);
                    // Copy first
                    if (copy($xml_job->jobHeaderImage, $target_picture)) {
                        chmod($target_picture, rex::getFilePerm());

                        $data = [];
                        $data['category_id'] = (int) \rex_config::get('jobs', 'hr4you_media_category');
                        $data['title'] = (string) $xml_jobs->jobTitle;
                        $data['file'] = [
                            'name' => $job_picture_pathInfo['basename'],
                            'path' => rex_path::media($job_picture_pathInfo['basename']),
                        ];

                        try {
                            $media_info = rex_media_service::addMedia($data, false);
                            $job_picture_filename = $media_info['filename'];
                            self::log('Job picture '. $media_info['filename'] .' importet into database.');
                        } catch (rex_api_exception $e) {
                            self::log('Job picture '. $job_picture_pathInfo['basename'] .' not importet into database: '. $e->getMessage());
                        }

                    }
                }
            }

            // Import contact
            $contact = Contact::getByMail($xml_job->contactEmail);
            if ($contact instanceof Contact) {
                self::log('Contact '. $contact->name .' already exists.');
            } else {
                $contact = Contact::factory();
                self::log('New Contact added.');
            }
            $contact->name = $xml_job->contactFirstname . ' ' . $xml_job->contactLastname;
            if ('' !== $xml_job->contactPhone->__toString()) {
                $contact->phone = $xml_job->contactPhone->__toString();
            }
            if ('' !== $xml_job->contactEmail->__toString()) {
                $contact->email = $xml_job->contactEmail->__toString();
            }
            $contact->save();
            if (array_key_exists($contact->contact_id, $old_contacts)) {
                unset($old_contacts[$contact->contact_id]);
            }
            if ('' !== $contact->picture && in_array($contact->picture, $old_pictures, true)) {
                unset($old_pictures[$contact->picture]);
            }

            // Category
            $category = Category::getByHR4YouName($xml_job->jobCategory->__toString());
            if (false === $category) {
                self::log('Category with HR4You Name '. $xml_job->jobCategory->__toString() .' does not exist. Falback to default category.');
                $category = new Category((int) \rex_config::get('jobs', 'hr4you_default_category'), (int) \rex_config::get('jobs', 'hr4you_default_lang'));
            }

            // Import job
            $job = Job::getByHR4YouID((int) $xml_job->jobId->__toString());
            if (!$job instanceof Job) {
                $job = Job::factory();
                $job->clang_id = (int) \rex_config::get('jobs', 'hr4you_default_lang');
                $job->hr4you_job_id = (int) $xml_job->jobId->__toString();
            }

            foreach (\rex_clang::getAll() as $clang) {
                if ($clang->getCode() === $xml_job->sprachcode->__toString()) {
                    $job->clang_id = $clang->getId();
                    break;
                }
            }

            $job->contact = $contact;
            if ($category instanceof Category && !in_array($category, $job->categories, true)) {
                $job->categories[$category->category_id] = $category;
            }

            $job->city = $xml_job->jobWorkplace->__toString();
            $job->date = $xml_job->jobPublishingDateFrom->__toString();
            $job->hr4you_lead_in = $xml_job->jobSummary->__toString();
            $job->hr4you_url_application_form = $xml_job->applicationForm->__toString();
            $job->internal_name = $xml_job->jobTitle->__toString();
            $job->name = $xml_job->jobTitle->__toString();
            $job->offer_heading = html_entity_decode('' !== self::getHeadline($xml_job->jobBenefits) ? self::getHeadline($xml_job->jobBenefits) : \Sprog\Wildcard::get('jobs_hr4you_offer_heading', (int) \rex_config::get('jobs', 'hr4you_default_lang')));
            $job->offer_text = html_entity_decode(self::trimString(self::stripHeadline($xml_job->jobBenefits)));
            if ($job->job_id === 0) {
                // avoid overwriting status of existing jobs
                $job->online_status = 'online';
            }
            if ('' !== $job_picture_filename) {
                $job->picture = $job_picture_filename;
            }
            $job->profile_heading = html_entity_decode('' !== self::getHeadline($xml_job->jobRequirements) ? self::getHeadline($xml_job->jobRequirements) : \Sprog\Wildcard::get('jobs_hr4you_profile_heading', (int) \rex_config::get('jobs', 'hr4you_default_lang')));
            $job->profile_text = html_entity_decode(self::trimString(self::stripHeadline($xml_job->jobRequirements)));
            $job->reference_number = $xml_job->referenznummer->__toString();
            $job->tasks_heading = html_entity_decode('' !== self::getHeadline($xml_job->jobResponsibilities) ? self::getHeadline($xml_job->jobResponsibilities) : \Sprog\Wildcard::get('jobs_hr4you_tasks_heading', (int) \rex_config::get('jobs', 'hr4you_default_lang')));
            $job->tasks_text = html_entity_decode(self::trimString(self::stripHeadline($xml_job->jobResponsibilities)));
            if ('Freiwillig' === $xml_job->jobEmploymentType->__toString()) {
                $job->type = 'VOLUNTEER';
            } elseif ('Freelancer' === $xml_job->jobEmploymentType->__toString()) {
                $job->type = 'CONTRACTOR';
            } elseif (in_array($xml_job->jobEmploymentType->__toString(), ['Vollzeit'], true)) {
                $job->type = 'FULL_TIME';
            } elseif (in_array($xml_job->jobEmploymentType->__toString(), ['Teilzeit'], true)) {
                $job->type = 'PART_TIME';
            } else {
                $job->type = 'OTHER';
            }
            $job->translation_needs_update = 'no';
            $job->save();

            if (array_key_exists($job->hr4you_job_id, $old_jobs)) {
                unset($old_jobs[$job->hr4you_job_id]);
                self::log('Job '. $job->name .' already exists. Updated.');
            } else {
                self::log('Job '. $job->name .' added.');
            }
        }

        // Delete unused old jobs
        foreach ($old_jobs as $old_job) {
            $old_job->delete(true);
            self::log('Job '. $old_job->name .' deleted.');
        }

        // Delete unused old contacts
        foreach ($old_contacts as $old_contact) {
            $old_contact->delete();
            self::log('Contact '. $old_contact->name .' deleted.');
        }

        // Delete unused old pictures
        foreach ($old_pictures as $old_picture) {
            try {
                rex_media_service::deleteMedia($old_picture);
                self::log('Picture '. $old_picture .' deleted.');
            } catch (rex_api_exception $exception) {
                self::log('Picture '. $old_picture .' deletion requested, but is in use.');
            }
        }

        return true;
    }

    /**
     * Isolates headline from text.
     * @param SimpleXMLElement $string String potenially containing headline
     * @return string headline text without tags
     */
    private static function getHeadline(SimpleXMLElement $string)
    {
        if ('' === (string) $string) {
            return '';
        }

        $doc = new DOMDocument();
        $doc->loadHTML($string);

        foreach ($doc->getElementsByTagName((string) \rex_config::get('jobs', 'hr4you_headline_tag')) as $item) {
            return $item->textContent;
        }

        return '';
    }

    /**
     * Removes headline from text.
     * @param SimpleXMLElement $string String with text potentially containing headline
     * @return string text without headline
     */
    private static function stripHeadline(SimpleXMLElement$string)
    {
        $headline = self::getHeadline($string);

        $h_tag = \rex_config::get('jobs', 'hr4you_headline_tag');
        return str_replace('<' . $h_tag . '>' . $headline . '</' . $h_tag . '>', '', $string);
    }

    /**
     * Removes not allowed tags and other stuff from string.
     * @param string $string String to be prepared
     * @return string Prepared string
     */
    private static function trimString($string)
    {
        $string = strip_tags($string, '<ul></ul><li></li><b></b><i></i><strong></strong><br><br /><p></p><small></small>');
        $string = trim((string) preg_replace('/\t+/', '', $string));
        $string = str_replace(['&nbsp;', '&crarr;'], ' ', $string);
        $string = (string) preg_replace('/\\s+/', ' ', $string);
        return str_replace(["\r", "\n"], '', $string);
    }

    /**
     * Detect image extension from binary data by checking magic bytes.
     * @param string $data First bytes of image data
     * @return string|false Extension or false if not detected
     */
    private static function detectImageExtensionFromData($data)
    {
        if (strlen($data) < 4) {
            return false;
        }
        
        // Check for common image file signatures (magic bytes)
        $bytes = unpack('C4', substr($data, 0, 4));
        
        // JPEG: FF D8 FF
        if ($bytes[1] === 0xFF && $bytes[2] === 0xD8 && $bytes[3] === 0xFF) {
            return 'jpg';
        }
        
        // PNG: 89 50 4E 47
        if ($bytes[1] === 0x89 && $bytes[2] === 0x50 && $bytes[3] === 0x4E && $bytes[4] === 0x47) {
            return 'png';
        }
        
        // GIF: 47 49 46 38 or 47 49 46 39
        if ($bytes[1] === 0x47 && $bytes[2] === 0x49 && $bytes[3] === 0x46 && 
            ($bytes[4] === 0x38 || $bytes[4] === 0x39)) {
            return 'gif';
        }
        
        // WebP: Check for "RIFF" and "WEBP" (need more bytes for this)
        if (strlen($data) >= 12) {
            $riff = substr($data, 0, 4);
            $webp = substr($data, 8, 4);
            if ($riff === 'RIFF' && $webp === 'WEBP') {
                return 'webp';
            }
        }
        
        // BMP: 42 4D
        if ($bytes[1] === 0x42 && $bytes[2] === 0x4D) {
            return 'bmp';
        }
        
        return false;
    }

    /**
     * Logs message.
     * @param string $message Message to be logged
     */
    private static function log($message): void
    {
        $log = file_exists(rex_path::addonCache('jobs', 'hr4you_import_log.txt')) ? file_get_contents(rex_path::addonCache('jobs', 'hr4you_import_log.txt')) : '';

        $log .= PHP_EOL. date('d.m.Y H:i:s', time()) .': '. $message;

        // Write to log
        if (!is_dir(rex_path::addonCache('jobs'))) {
            rex_dir::create(rex_path::addonCache('jobs'));
        }
        file_put_contents(rex_path::addonCache('jobs', 'hr4you_import_log.txt'), $log);
    }
}
