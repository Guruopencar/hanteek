<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TranslationsSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            // AUTH
            ['group'=>'auth','key'=>'login',                'uk'=>'Увійти',                    'en'=>'Login'],
            ['group'=>'auth','key'=>'register',             'uk'=>'Зареєструватись',           'en'=>'Register'],
            ['group'=>'auth','key'=>'logout',               'uk'=>'Вийти',                     'en'=>'Logout'],
            ['group'=>'auth','key'=>'email',                'uk'=>'Електронна пошта',          'en'=>'Email'],
            ['group'=>'auth','key'=>'password',             'uk'=>'Пароль',                    'en'=>'Password'],
            ['group'=>'auth','key'=>'forgot_password',      'uk'=>'Забули пароль?',            'en'=>'Forgot password?'],
            ['group'=>'auth','key'=>'reset_password',       'uk'=>'Відновити пароль',          'en'=>'Reset password'],
            ['group'=>'auth','key'=>'confirm_code',         'uk'=>'Введіть код підтвердження', 'en'=>'Enter confirmation code'],
            ['group'=>'auth','key'=>'select_role',          'uk'=>'Оберіть роль',              'en'=>'Select your role'],
            ['group'=>'auth','key'=>'role_programmer',      'uk'=>'Програміст',                'en'=>'Programmer'],
            ['group'=>'auth','key'=>'role_project_owner',   'uk'=>'Власник проекту',           'en'=>'Project Owner'],
            ['group'=>'auth','key'=>'already_have_account', 'uk'=>'Вже маєте акаунт?',        'en'=>'Already have an account?'],
            ['group'=>'auth','key'=>'no_account',           'uk'=>'Ще немає акаунту?',        'en'=>'No account yet?'],
            ['group'=>'auth','key'=>'phone',                'uk'=>'Номер телефону',            'en'=>'Phone number'],
            ['group'=>'auth','key'=>'verification_sent',    'uk'=>'Код надіслано на пошту',   'en'=>'Verification code sent'],

            // NAVIGATION
            ['group'=>'nav','key'=>'home',            'uk'=>'Головна',         'en'=>'Home'],
            ['group'=>'nav','key'=>'my_projects',     'uk'=>'Мої проекти',     'en'=>'My Projects'],
            ['group'=>'nav','key'=>'my_team',         'uk'=>'Моя команда',     'en'=>'My Team'],
            ['group'=>'nav','key'=>'messages',        'uk'=>'Повідомлення',    'en'=>'Messages'],
            ['group'=>'nav','key'=>'wallet',          'uk'=>'Гаманець',        'en'=>'Wallet'],
            ['group'=>'nav','key'=>'smart_contracts', 'uk'=>'Смарт-контракти','en'=>'Smart Contracts'],
            ['group'=>'nav','key'=>'my_account',      'uk'=>'Мій акаунт',     'en'=>'My Account'],
            ['group'=>'nav','key'=>'news',            'uk'=>'Новини',          'en'=>'News'],
            ['group'=>'nav','key'=>'support',         'uk'=>'Підтримка',       'en'=>'Support'],
            ['group'=>'nav','key'=>'rating',          'uk'=>'Рейтинг',         'en'=>'Rating'],
            ['group'=>'nav','key'=>'about_us',        'uk'=>'Про нас',         'en'=>'About Us'],

            // BUTTONS
            ['group'=>'buttons','key'=>'save',               'uk'=>'Зберегти',            'en'=>'Save'],
            ['group'=>'buttons','key'=>'cancel',             'uk'=>'Скасувати',           'en'=>'Cancel'],
            ['group'=>'buttons','key'=>'delete',             'uk'=>'Видалити',            'en'=>'Delete'],
            ['group'=>'buttons','key'=>'edit',               'uk'=>'Редагувати',          'en'=>'Edit'],
            ['group'=>'buttons','key'=>'create',             'uk'=>'Створити',            'en'=>'Create'],
            ['group'=>'buttons','key'=>'send',               'uk'=>'Надіслати',           'en'=>'Send'],
            ['group'=>'buttons','key'=>'submit_application', 'uk'=>'Подати заявку',       'en'=>'Submit an application'],
            ['group'=>'buttons','key'=>'hire_talent',        'uk'=>'Найняти спеціаліста','en'=>'Hire the talent'],
            ['group'=>'buttons','key'=>'sign_contract',      'uk'=>'Підписати контракт', 'en'=>'Sign contract'],
            ['group'=>'buttons','key'=>'view_all',           'uk'=>'Переглянути всі',     'en'=>'View all'],
            ['group'=>'buttons','key'=>'load_more',          'uk'=>'Завантажити ще',      'en'=>'Load more'],
            ['group'=>'buttons','key'=>'back',               'uk'=>'Назад',               'en'=>'Back'],
            ['group'=>'buttons','key'=>'next',               'uk'=>'Далі',                'en'=>'Next'],
            ['group'=>'buttons','key'=>'confirm',            'uk'=>'Підтвердити',         'en'=>'Confirm'],
            ['group'=>'buttons','key'=>'block_user',         'uk'=>'Заблокувати',         'en'=>'Block a user'],
            ['group'=>'buttons','key'=>'unban',              'uk'=>'Розблокувати',        'en'=>'Unban'],
            ['group'=>'buttons','key'=>'search',             'uk'=>'Пошук',               'en'=>'Search'],
            ['group'=>'buttons','key'=>'filter',             'uk'=>'Фільтр',              'en'=>'Filter'],
            ['group'=>'buttons','key'=>'apply',              'uk'=>'Застосувати',         'en'=>'Apply'],
            ['group'=>'buttons','key'=>'close',              'uk'=>'Закрити',             'en'=>'Close'],
            ['group'=>'buttons','key'=>'upload',             'uk'=>'Завантажити файл',    'en'=>'Upload file'],
            ['group'=>'buttons','key'=>'add_developer',      'uk'=>'Додати розробника',   'en'=>'Add Developer'],

            // HOME / FEED
            ['group'=>'home','key'=>'tab_project',    'uk'=>'Проект',          'en'=>'Project'],
            ['group'=>'home','key'=>'tab_developer',  'uk'=>'Розробник',       'en'=>'Developer'],
            ['group'=>'home','key'=>'remote',         'uk'=>'Дистанційно',     'en'=>'Remote'],
            ['group'=>'home','key'=>'office',         'uk'=>'Офіс',            'en'=>'Office'],
            ['group'=>'home','key'=>'hybrid',         'uk'=>'Гібрид',          'en'=>'Hybrid'],
            ['group'=>'home','key'=>'fixed_price',    'uk'=>'Фіксована ціна', 'en'=>'Fixed price'],
            ['group'=>'home','key'=>'time_material',  'uk'=>'Час і матеріал', 'en'=>'Time & Material'],
            ['group'=>'home','key'=>'per_hour',       'uk'=>'год',             'en'=>'h'],
            ['group'=>'home','key'=>'applications',   'uk'=>'Заявки',          'en'=>'Applications'],
            ['group'=>'home','key'=>'no_results',     'uk'=>'Нічого не знайдено','en'=>'No results found'],

            // CONTRACTS
            ['group'=>'contracts','key'=>'title',             'uk'=>'Смарт-контракти',     'en'=>'Smart Contracts'],
            ['group'=>'contracts','key'=>'new_contracts',     'uk'=>'Нові контракти',      'en'=>'New contracts'],
            ['group'=>'contracts','key'=>'active',            'uk'=>'Активні',             'en'=>'Active'],
            ['group'=>'contracts','key'=>'archived',          'uk'=>'Архів',               'en'=>'Archive'],
            ['group'=>'contracts','key'=>'type_vacancy',      'uk'=>'Вакансія',            'en'=>'Vacancy'],
            ['group'=>'contracts','key'=>'type_resume',       'uk'=>'Резюме',              'en'=>'Resume'],
            ['group'=>'contracts','key'=>'type_agreement',    'uk'=>'Угода',               'en'=>'Agreement'],
            ['group'=>'contracts','key'=>'add_task',          'uk'=>'Додати задачу',       'en'=>'Add task'],
            ['group'=>'contracts','key'=>'add_programmer',    'uk'=>'Додати програміста', 'en'=>'Add programmer'],
            ['group'=>'contracts','key'=>'priority_low',      'uk'=>'Низький',             'en'=>'Low'],
            ['group'=>'contracts','key'=>'priority_medium',   'uk'=>'Середній',            'en'=>'Medium'],
            ['group'=>'contracts','key'=>'priority_high',     'uk'=>'Високий',             'en'=>'High'],
            ['group'=>'contracts','key'=>'priority_critical', 'uk'=>'Критичний',           'en'=>'Critical'],
            ['group'=>'contracts','key'=>'status_todo',       'uk'=>'Очікує',              'en'=>'To do'],
            ['group'=>'contracts','key'=>'status_progress',   'uk'=>'В роботі',           'en'=>'In progress'],
            ['group'=>'contracts','key'=>'status_review',     'uk'=>'На перевірці',       'en'=>'Review'],
            ['group'=>'contracts','key'=>'status_done',       'uk'=>'Виконано',            'en'=>'Done'],
            ['group'=>'contracts','key'=>'status_cancelled',  'uk'=>'Скасовано',           'en'=>'Cancelled'],
            ['group'=>'contracts','key'=>'sign_both_required','uk'=>'Очікує підписання',  'en'=>'Awaiting signatures'],
            ['group'=>'contracts','key'=>'deadline',          'uk'=>'Дедлайн',             'en'=>'Deadline'],
            ['group'=>'contracts','key'=>'estimated_hours',   'uk'=>'Оціночні години',    'en'=>'Estimated hours'],
            ['group'=>'contracts','key'=>'view_documentation','uk'=>'Переглянути документ','en'=>'View documentation'],

            // PROJECTS
            ['group'=>'projects','key'=>'title',          'uk'=>'Проекти',              'en'=>'Projects'],
            ['group'=>'projects','key'=>'create_project', 'uk'=>'Створити проект',      'en'=>'Create a Project'],
            ['group'=>'projects','key'=>'create_vacancy', 'uk'=>'Створити вакансію',   'en'=>'Create a Vacancy'],
            ['group'=>'projects','key'=>'project_info',   'uk'=>'Інформація про проект','en'=>'Project info'],
            ['group'=>'projects','key'=>'archive',        'uk'=>'Архів проектів',       'en'=>'Archive'],
            ['group'=>'projects','key'=>'step',           'uk'=>'Крок',                 'en'=>'Step'],
            ['group'=>'projects','key'=>'cover_photo',    'uk'=>'Фото обкладинки',      'en'=>'Cover photo'],
            ['group'=>'projects','key'=>'choose_color',   'uk'=>'Оберіть колір',        'en'=>'Choose color'],
            ['group'=>'projects','key'=>'vacancy_title',  'uk'=>'Назва вакансії',       'en'=>'Vacancy title'],
            ['group'=>'projects','key'=>'budget',         'uk'=>'Бюджет',               'en'=>'Budget'],

            // WALLET
            ['group'=>'wallet','key'=>'title',           'uk'=>'Гаманець',          'en'=>'Wallet'],
            ['group'=>'wallet','key'=>'balance',         'uk'=>'Баланс',             'en'=>'Balance'],
            ['group'=>'wallet','key'=>'all_wallets',     'uk'=>'Всі рахунки',       'en'=>'All Wallets'],
            ['group'=>'wallet','key'=>'deposit',         'uk'=>'Поповнити',          'en'=>'Replenish'],
            ['group'=>'wallet','key'=>'withdraw',        'uk'=>'Вивести кошти',     'en'=>'Withdraw funds'],
            ['group'=>'wallet','key'=>'transfer',        'uk'=>'Переказ коштів',    'en'=>'Transfer of funds'],
            ['group'=>'wallet','key'=>'history',         'uk'=>'Каталог операцій',  'en'=>'Catalog of operations'],
            ['group'=>'wallet','key'=>'frozen',          'uk'=>'Заморожено',         'en'=>'Frozen'],
            ['group'=>'wallet','key'=>'amount',          'uk'=>'Сума',               'en'=>'Amount'],
            ['group'=>'wallet','key'=>'type_deposit',    'uk'=>'Поповнення',         'en'=>'Deposit'],
            ['group'=>'wallet','key'=>'type_withdrawal', 'uk'=>'Виведення',          'en'=>'Withdrawal'],
            ['group'=>'wallet','key'=>'type_payment',    'uk'=>'Оплата',             'en'=>'Payment'],
            ['group'=>'wallet','key'=>'type_commission', 'uk'=>'Комісія',            'en'=>'Commission'],
            ['group'=>'wallet','key'=>'type_refund',     'uk'=>'Повернення',         'en'=>'Refund'],
            ['group'=>'wallet','key'=>'type_bonus',      'uk'=>'Бонус',              'en'=>'Bonus'],
            ['group'=>'wallet','key'=>'period',          'uk'=>'Період',             'en'=>'Period'],

            // REVIEWS
            ['group'=>'reviews','key'=>'title',            'uk'=>'Відгуки',                   'en'=>'Reviews'],
            ['group'=>'reviews','key'=>'leave_review',     'uk'=>'Залишити відгук',           'en'=>'Leave a review'],
            ['group'=>'reviews','key'=>'rating',           'uk'=>'Оцінка',                    'en'=>'Rating'],
            ['group'=>'reviews','key'=>'comment',          'uk'=>'Коментар',                  'en'=>'Comment'],
            ['group'=>'reviews','key'=>'send_review',      'uk'=>'Надіслати відгук',         'en'=>'Send a review'],
            ['group'=>'reviews','key'=>'review_pending',   'uk'=>'Очікує публікації',        'en'=>'Pending publication'],
            ['group'=>'reviews','key'=>'review_published', 'uk'=>'Опубліковано',              'en'=>'Published'],
            ['group'=>'reviews','key'=>'days_left',        'uk'=>'Днів залишилось',          'en'=>'Days left'],
            ['group'=>'reviews','key'=>'no_reviews',       'uk'=>'Ще немає відгуків',        'en'=>'No reviews yet'],
            ['group'=>'reviews','key'=>'min_length',       'uk'=>'Мінімум 20 символів',      'en'=>'Minimum 20 characters'],
            ['group'=>'reviews','key'=>'blind_info',       'uk'=>'Відгук буде опубліковано після відповіді обох сторін','en'=>'Review will be published after both parties respond'],

            // PROFILE
            ['group'=>'profile','key'=>'my_profile',      'uk'=>'Мій профіль',         'en'=>'My Profile'],
            ['group'=>'profile','key'=>'my_resume',        'uk'=>'Моє резюме',          'en'=>'My Resume'],
            ['group'=>'profile','key'=>'edit_profile',     'uk'=>'Редагувати профіль',  'en'=>'Edit Profile'],
            ['group'=>'profile','key'=>'ban_list',         'uk'=>'Список блокувань',    'en'=>'Ban list'],
            ['group'=>'profile','key'=>'referral_system',  'uk'=>'Реферальна система', 'en'=>'My Referral System'],
            ['group'=>'profile','key'=>'recruiter',        'uk'=>'Рекрутер',            'en'=>'Recruiter'],
            ['group'=>'profile','key'=>'developer',        'uk'=>'Розробник',           'en'=>'Developer'],
            ['group'=>'profile','key'=>'name',             'uk'=>"Ім'я",               'en'=>'Name'],
            ['group'=>'profile','key'=>'surname',          'uk'=>'Прізвище',            'en'=>'Surname'],
            ['group'=>'profile','key'=>'location',         'uk'=>'Місцезнаходження',   'en'=>'Location'],
            ['group'=>'profile','key'=>'about',            'uk'=>'Про себе',            'en'=>'About'],
            ['group'=>'profile','key'=>'date_of_blocking', 'uk'=>'Дата блокування',    'en'=>'Date of blocking'],
            ['group'=>'profile','key'=>'add_dev_profile',  'uk'=>'Додати профіль розробника','en'=>'Add developer profile'],
            ['group'=>'profile','key'=>'select_profile',   'uk'=>'Оберіть профіль для роботи','en'=>'Select the profile from which you will work'],
            ['group'=>'profile','key'=>'log_out',          'uk'=>'Вийти з акаунту',    'en'=>'Log Out'],

            // RATING
            ['group'=>'rating','key'=>'title',        'uk'=>'Рейтинг',              'en'=>'Rating'],
            ['group'=>'rating','key'=>'top_users',    'uk'=>'Топ користувачів',     'en'=>'Top users'],
            ['group'=>'rating','key'=>'sort_rating',  'uk'=>'За рейтингом',         'en'=>'By rating'],
            ['group'=>'rating','key'=>'sort_reviews', 'uk'=>'За відгуками',         'en'=>'By reviews'],
            ['group'=>'rating','key'=>'sort_contracts','uk'=>'За контрактами',      'en'=>'By contracts'],

            // MESSAGES
            ['group'=>'messages','key'=>'title',        'uk'=>'Повідомлення',   'en'=>'Messages'],
            ['group'=>'messages','key'=>'type_message', 'uk'=>'Напишіть...',    'en'=>'Type a message...'],
            ['group'=>'messages','key'=>'no_messages',  'uk'=>'Немає повідомлень','en'=>'No messages yet'],
            ['group'=>'messages','key'=>'online',       'uk'=>'Онлайн',         'en'=>'Online'],
            ['group'=>'messages','key'=>'offline',      'uk'=>'Офлайн',         'en'=>'Offline'],

            // SUPPORT
            ['group'=>'support','key'=>'title',      'uk'=>'Підтримка',         'en'=>'Support'],
            ['group'=>'support','key'=>'contact_us', 'uk'=>'Зв\'язатися з нами','en'=>'Contact us'],
            ['group'=>'support','key'=>'subject',    'uk'=>'Тема',               'en'=>'Subject'],
            ['group'=>'support','key'=>'message',    'uk'=>'Повідомлення',       'en'=>'Message'],
            ['group'=>'support','key'=>'sent',       'uk'=>'Повідомлення надіслано','en'=>'Message sent'],

            // ERRORS
            ['group'=>'errors','key'=>'required',       'uk'=>'Це поле обов\'язкове',  'en'=>'This field is required'],
            ['group'=>'errors','key'=>'invalid_email',  'uk'=>'Невірний email',        'en'=>'Invalid email address'],
            ['group'=>'errors','key'=>'min_length',     'uk'=>'Занадто коротко',       'en'=>'Too short'],
            ['group'=>'errors','key'=>'server_error',   'uk'=>'Помилка сервера',       'en'=>'Server error'],
            ['group'=>'errors','key'=>'unauthorized',   'uk'=>'Не авторизовано',       'en'=>'Unauthorized'],
            ['group'=>'errors','key'=>'not_found',      'uk'=>'Не знайдено',           'en'=>'Not found'],
            ['group'=>'errors','key'=>'forbidden',      'uk'=>'Доступ заборонено',     'en'=>'Access forbidden'],
        ];

        foreach ($rows as $row) {
            foreach (['uk', 'en'] as $locale) {
                DB::table('translations')->updateOrInsert(
                    ['locale' => $locale, 'group' => $row['group'], 'key' => $row['key']],
                    [
                        'value'      => $row[$locale],
                        'status'     => 'translated',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        $this->command->info('✓ Translations створено (' . count($rows) * 2 . ' рядків)');
    }
}
