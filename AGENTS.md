# Stellenmarkt Addon Notes

## Purpose

- Provide job listings with multilingual content, categories, and contact persons.
- Optional HR4YOU XML import for job data.
- Frontend modules for job lists and job detail pages.

## Core Tables

- `jobs_jobs`: language-independent data (status, type, HR4YOU IDs, salary fields, etc.)
- `jobs_jobs_lang`: language-specific texts (titles, tasks, profile, offer, etc.)
- `jobs_categories` and `jobs_categories_lang`: job categories
- `jobs_contacts`: contact persons

## Key Classes

- `FriendsOfRedaxo\Jobs\Job`: main model, handles save and URL generation.
- `FriendsOfRedaxo\Jobs\Category`: category model.
- `FriendsOfRedaxo\Jobs\Contact`: contact model.
- `FriendsOfRedaxo\Jobs\Hr4youImport`: HR4YOU XML import (jobs, contacts, images).
- `FriendsOfRedaxo\Jobs\Module`: module definitions and revisions.

## HR4YOU Import

- XML source configured in addon settings.
- Maps XML fields to `Job` and `Contact`.
- Imports job header images into mediapool.
- Salary fields: `salaryCurrency` and `salaryMax` stored on `jobs_jobs`.

## Backend

- Main page: `pages/jobs.php` (create/edit jobs, HR4YOU fields, translations).
- HR4YOU import page: `pages/hr4you_import.php`.
- Settings via config and helper permissions.

## Frontend

- Module `23/1`: job list and job detail output.
- Module `23/2`: job categories output.
- JSON-LD job output available in `Job::getJsonLdCode()` when enabled.

## Media

- Media manager types: `jobs_joblist`, `jobs_jobheader`, `jobs_contact`.
- Placeholder image: `assets/noavatar.jpg` if no picture is set.

## Notes

- Translation handling uses `translation_needs_update` in `jobs_jobs_lang`.
- URL handling integrates with URL addon when available.
- Frontend labels should use `Sprog\Wildcard::get()` backed by `LangHelper`, not `rex_i18n::msg()`.
- When adding new database fields that should be exposed to Google Jobs, update `Job::getJsonLdCode()` accordingly.
