<?php
namespace FriendsOfRedaxo\Jobs;

use rex_clang;
use rex_config;

/**
 * @api
 * Offers helper functions for language issues.
 */
class LangHelper extends \TobiasKrais\D2UHelper\ALangHelper
{
    /** @var array<string,string> Array with chinese replacements. Key is the wildcard, value the replacement. */
    protected $replacements_chinese = [
        'jobs_all' => '显示所有',
        'jobs_announcement' => '招标',
        'jobs_application_link' => '在线申请表',
        'jobs_catgories' => '类别',
        'jobs_cities' => '城市',
        'jobs_faq' => '常问问题',
        'jobs_faq_link' => '前往常见问题解答',
        'jobs_faq_text' => '最常见的问题和有用的答案。',
        'jobs_footer' => '请将您的完整求职资料与可行的入职日期和薪水要求一并发送至电子邮箱：',
        'jobs_hr4you_offer_heading' => '我们的报价',
        'jobs_hr4you_profile_heading' => '您的个人资料',
        'jobs_hr4you_tasks_heading' => '你的任务',
        'jobs_module_attachment' => '申请文件：如履历、证书',
        'jobs_module_form_thanks' => '感谢您的申请。 我们的人力资源部门会尽快与您联系。',
        'jobs_module_form_your_data' => '申请资料概要',
        'jobs_no_jobs_found' => '没有找到职位空缺。',
        'jobs_phone' => '电话',
        'jobs_questions' => '如果您对该职位有任何疑问，请联系我们：',
        'jobs_reference_number' => '参考号',
        'jobs_region' => '市',
        'jobs_vacancies' => '招聘岗位',
        'jobs_video_application' => '视频应用',
    ];

    /** @var array<string,string> Array with netherlands replacements. Key is the wildcard, value the replacement. */
    protected array $replacements_dutch = [
        'jobs_all' => 'toon alles',
        'jobs_announcement' => 'Naar openstaande vacatures',
        'jobs_application_link' => 'Solliciteer direct',
        'jobs_catgories' => 'Categorieën',
        'jobs_cities' => 'Steden',
        'jobs_faq' => 'FAQ',
        'jobs_faq_link' => 'Weergave',
        'jobs_faq_text' => 'De meest gestelde vragen en nuttige antwoorden.',
        'jobs_footer' => 'U kunt uw motivatiebrief met CV onder vermelding van vroegst mogelijke opzegtermijn en salaris wens per E-Mail, met in het onderwerp „Sollicitatie <Vacature nummer>” sturen naar:',
        'jobs_hr4you_offer_heading' => 'Ons aanbod',
        'jobs_hr4you_profile_heading' => 'Uw profiel',
        'jobs_hr4you_tasks_heading' => 'Uw taken',
        'jobs_module_attachment' => 'Sollicitatiedocumenten: bijv. curriculum vitae, certificaten',
        'jobs_module_form_thanks' => 'Bedankt voor uw sollicitatie, we nemen op korte termijn contact met u op.',
        'jobs_module_form_your_data' => 'Uw gegevens',
        'jobs_no_jobs_found' => 'Er zijn geen vacatures gevonden.',
        'jobs_phone' => 'Telefoon:',
        'jobs_questions' => 'Indien u naar aanleiding van deze vacature nog vragen heeft kunt u contact opnemen met:',
        'jobs_reference_number' => 'Referentie',
        'jobs_region' => 'Stad',
        'jobs_vacancies' => 'Vacatures',
        'jobs_video_application' => 'Video-applicatie',
    ];

    /** @var array<string,string> Array with english replacements. Key is the wildcard, value the replacement. */
    public $replacements_english = [
        'jobs_all' => 'show all',
        'jobs_announcement' => 'Complete announcement',
        'jobs_application_link' => 'Online application form',
        'jobs_catgories' => 'Categories',
        'jobs_cities' => 'Cities',
        'jobs_faq' => 'FAQ',
        'jobs_faq_link' => 'View',
        'jobs_faq_text' => 'The most frequently asked questions and helpful answers.',
        'jobs_footer' => 'Please send us your complete application documents with the possible starting date and your salary expectations via email to:',
        'jobs_hr4you_offer_heading' => 'We offer',
        'jobs_hr4you_profile_heading' => 'Your Profile',
        'jobs_hr4you_tasks_heading' => 'Your Tasks',
        'jobs_module_attachment' => 'Application documents: e.g. CV, certificates',
        'jobs_module_form_thanks' => 'Thank you for your application. Our HR departement will answer you as soon as possible.',
        'jobs_module_form_your_data' => 'Application data summary',
        'jobs_no_jobs_found' => 'Sorry, we found no jobs.',
        'jobs_phone' => 'Phone',
        'jobs_questions' => 'If you have questions regarding this position, please contact:',
        'jobs_reference_number' => 'Reference number',
        'jobs_region' => 'City',
        'jobs_vacancies' => 'Vacancies',
        'jobs_video_application' => 'Video application',
    ];

    /** @var array<string,string> Array with french replacements. Key is the wildcard, value the replacement. */
    protected array $replacements_french = [
        'jobs_all' => 'afficher tout',
        'jobs_announcement' => 'annonce complète',
        'jobs_application_link' => 'Formulaire de demande en ligne',
        'jobs_catgories' => 'Catégories',
        'jobs_cities' => 'Villes',
        'jobs_faq' => 'FAQ',
        'jobs_faq_link' => 'Voir',
        'jobs_faq_text' => 'Les questions les plus fréquemment posées et les réponses utiles.',
        'jobs_footer' => 'Veuillez nous envoyer vos documents de demande complets avec la date de début possible et vos attentes de salaire par courrier électronique à:',
        'jobs_hr4you_offer_heading' => 'Notre offre',
        'jobs_hr4you_profile_heading' => 'Votre profil',
        'jobs_hr4you_tasks_heading' => 'Vos tâches',
        'jobs_module_attachment' => 'Documents de candidature : par exemple, curriculum vitae, certificats',
        'jobs_module_form_thanks' => 'Merci pour votre candidature. Notre service RH vous contactera dans les plus brefs délais.',
        'jobs_module_form_your_data' => "Résumé des données d'application",
        'jobs_no_jobs_found' => 'No jobs found.',
        'jobs_phone' => 'Tél.',
        'jobs_questions' => 'Si vous avez des questions concernant ce poste, veuillez contacter:',
        'jobs_reference_number' => 'Numéro de réference',
        'jobs_region' => 'Ville',
        'jobs_vacancies' => "Offres d'emploi",
        'jobs_video_application' => 'Application vidéo',
    ];

    /** @var array<string,string> Array with german replacements. Key is the wildcard, value the replacement. */
    protected array $replacements_german = [
        'jobs_all' => 'alles anzeigen',
        'jobs_announcement' => 'Zur Ausschreibung',
        'jobs_application_link' => 'Online Bewerbungsformular',
        'jobs_catgories' => 'Kategorien',
        'jobs_cities' => 'Orte',
        'jobs_faq' => 'FAQ',
        'jobs_faq_link' => 'Ansehen',
        'jobs_faq_text' => 'Die häufigsten Fragen und hilfreiche Antworten.',
        'jobs_footer' => 'Bitte senden Sie uns Ihre kompletten Bewerbungsunterlagen mit dem frühestmöglichen Eintrittstermin und Ihrer Gehaltvorstellung per E-Mail mit dem Stichwort „Karriere“ im Betreff an:',
        'jobs_hr4you_offer_heading' => 'Unser Angebot',
        'jobs_hr4you_profile_heading' => 'Ihr Profil',
        'jobs_hr4you_tasks_heading' => 'Ihre Aufgaben',
        'jobs_module_attachment' => 'Bewerbungsunterlagen: z.B. Lebenslauf, Zeugnisse',
        'jobs_module_form_thanks' => 'Vielen Dank für Ihre Bewerbung. Unsere Personalabteilung wird umgehend mit Ihnen Kontakt aufnehmen.',
        'jobs_module_form_your_data' => 'Zusammenfassung Bewerbungsdaten',
        'jobs_no_jobs_found' => 'Es tut uns leid, aber es wurden keine Stellenabgebote gefunden.',
        'jobs_phone' => 'Tel.',
        'jobs_questions' => 'Wenn Sie Fragen zur ausgeschriebenen Stelle haben, wenden Sie sich bitte an:',
        'jobs_reference_number' => 'Referenznummer',
        'jobs_region' => 'Ort',
        'jobs_vacancies' => 'Stellenangebote',
        'jobs_video_application' => 'Videobewerbung',
    ];

    /** @var array<string,string> Array with spanish replacements. Key is the wildcard, value the replacement. */
    protected array $replacements_spanish = [
        'jobs_all' => 'mostrar todo',
        'jobs_announcement' => 'Anuncio completo',
        'jobs_application_link' => 'Formulario online',
        'jobs_catgories' => 'Categorías',
        'jobs_cities' => 'Cuidades',
        'jobs_faq' => 'Preguntas más frecuentes',
        'jobs_faq_link' => 'Vista',
        'jobs_faq_text' => 'Las preguntas más frecuentes y respuestas útiles.',
        'jobs_footer' => 'Por favor, envíenos sus documentos completos de solicitud con la posible fecha de inicio y sus expectativas salariales por correo electrónico a:',
        'jobs_hr4you_offer_heading' => 'Ofrecemos',
        'jobs_hr4you_profile_heading' => 'Su Perfil',
        'jobs_hr4you_tasks_heading' => 'Sus tareas',
        'jobs_module_attachment' => 'Documentos de solicitud: p. Ej., Curriculum vitae, certificados',
        'jobs_module_form_thanks' => 'Gracias por tu aplicación. Nuestro departamento de RRHH se pondrá en contacto contigo lo antes posible.',
        'jobs_module_form_your_data' => 'Resumen de los datos de la aplicación ',
        'jobs_no_jobs_found' => 'No se encontraron vacantes.',
        'jobs_phone' => 'Teléfono',
        'jobs_questions' => 'Si tiene preguntas relativas a esto, por favor contáctenos',
        'jobs_reference_number' => 'Número de referencia',
        'jobs_region' => 'Ciudad',
        'jobs_vacancies' => 'Vacantes',
        'jobs_video_application' => 'Applicación de video',
    ];

    /** @var array<string,string> Array with russian replacements. Key is the wildcard, value the replacement. */
    protected array $replacements_russian = [
        'jobs_all' => 'показать все',
        'jobs_announcement' => 'Отправить заявку',
        'jobs_application_link' => 'Онлайн-заявка',
        'jobs_catgories' => 'Категории',
        'jobs_cities' => 'Города',
        'jobs_faq' => 'Часто задаваемые вопросы',
        'jobs_faq_link' => 'Вид',
        'jobs_faq_text' => 'Наиболее часто задаваемые вопросы и полезные ответы.',
        'jobs_footer' => 'Присылайте Ваше резюме и документы с возможной датой начала работы и ваши ожидания по зарплате по электронной почте:',
        'jobs_hr4you_offer_heading' => 'Наше предложение',
        'jobs_hr4you_profile_heading' => 'Ваш профиль',
        'jobs_hr4you_tasks_heading' => 'Желаемая должность',
        'jobs_module_attachment' => 'Документы для подачи заявки: например, биографические данные, сертификаты.',
        'jobs_module_form_thanks' => 'Спасибо за вашу заявку . Наш отдел кадров свяжется с вами в ближайшее время.',
        'jobs_module_form_your_data' => 'Сводка данных приложения',
        'jobs_no_jobs_found' => 'Вакансий не обнаружено.',
        'jobs_phone' => 'тел.',
        'jobs_questions' => 'Если у вас есть вопросы относительно этой вакансии, мы всегда к Вашим услугам:',
        'jobs_reference_number' => 'ссылка',
        'jobs_region' => 'город',
        'jobs_vacancies' => 'Вакансии',
        'jobs_video_application' => 'Видео приложение',
    ];

    /**
     * Factory method.
     * @return self Object
     */
    public static function factory(): self
    {
        return new self();
    }

    /**
     * Installs the replacement table for this addon.
     */
    public function install(): void
    {
        foreach ($this->replacements_english as $key => $value) {
            foreach (rex_clang::getAllIds() as $clang_id) {
                $lang_replacement = rex_config::get('jobs', 'lang_replacement_'. $clang_id, '');

                // Load values for input
                if ('chinese' === $lang_replacement && isset($this->replacements_chinese[$key])) {
                    $value = $this->replacements_chinese[$key];
                } elseif ('french' === $lang_replacement && isset($this->replacements_french[$key])) {
                    $value = $this->replacements_french[$key];
                } elseif ('german' === $lang_replacement && isset($this->replacements_german[$key])) {
                    $value = $this->replacements_german[$key];
                } elseif ('dutch' === $lang_replacement && isset($this->replacements_dutch[$key])) {
                    $value = $this->replacements_dutch[$key];
                } elseif ('russian' === $lang_replacement && isset($this->replacements_russian[$key])) {
                    $value = $this->replacements_russian[$key];
                } elseif ('spanish' === $lang_replacement && isset($this->replacements_spanish[$key])) {
                    $value = $this->replacements_spanish[$key];
                } else {
                    $value = $this->replacements_english[$key];
                }

                $overwrite = 'true' === rex_config::get('jobs', 'lang_wildcard_overwrite', false) ? true : false;
                parent::saveValue($key, $value, $clang_id, $overwrite);
            }
        }
    }
}
