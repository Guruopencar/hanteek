<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\VerificationController;
use App\Http\Controllers\Api\Profile\ProfileController;
use App\Http\Controllers\Api\Profile\ResumeController;
use App\Http\Controllers\Api\Profile\RecruiterProfileController;
use App\Http\Controllers\Api\Feed\FeedController;
use App\Http\Controllers\Api\Project\ProjectController;
use App\Http\Controllers\Api\Project\VacancyController;
use App\Http\Controllers\Api\Project\ApplicationController;
use App\Http\Controllers\Api\Contract\ContractController;
use App\Http\Controllers\Api\Contract\TaskController;
use App\Http\Controllers\Api\Contract\TimeLogController;
use App\Http\Controllers\Api\Wallet\WalletController;
use App\Http\Controllers\Api\Wallet\TransactionController;
use App\Http\Controllers\Api\Review\ReviewController;
use App\Http\Controllers\Api\Message\ConversationController;
use App\Http\Controllers\Api\Message\MessageController;
use App\Http\Controllers\Api\Rating\RatingController;
use App\Http\Controllers\Api\News\NewsController;
use App\Http\Controllers\Api\Support\SupportController;
use App\Http\Controllers\Api\Referral\ReferralController;
use App\Http\Controllers\Api\Setting\SettingController;
use App\Http\Controllers\Api\Admin\AdminUserController;
use App\Http\Controllers\Api\Admin\AdminContentController;
use App\Http\Controllers\Api\Admin\AdminFinanceController;
use App\Http\Controllers\Api\Admin\AdminAnalyticsController;
use App\Http\Controllers\Api\Admin\AdminSettingController;
use App\Http\Controllers\Api\Admin\AdminTranslationController;

// ── Публічні маршрути (без авторизації) ──────────────────────
Route::prefix('v1')->group(function () {

    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('register',         [AuthController::class, 'register']);
        Route::post('login',            [AuthController::class, 'login']);
        Route::post('forgot-password',  [AuthController::class, 'forgotPassword']);
        Route::post('reset-password',   [AuthController::class, 'resetPassword']);
        Route::post('verify-code',      [VerificationController::class, 'verify']);
        Route::post('resend-code',      [VerificationController::class, 'resend']);
    });

    // Публічні дані

    Route::get('settings/public',       [SettingController::class, 'public']);
    Route::get('locales',               [SettingController::class, 'locales']);
    Route::get('translations/{locale}', [SettingController::class, 'translations']);

    // Публічні профілі
	Route::get('users/search', [ProfileController::class, 'search']); 
    Route::get('users/{user}/profile',  [ProfileController::class, 'show']);
    Route::get('rating',                [RatingController::class, 'index']);
    Route::get('news',                  [NewsController::class, 'index']);
    Route::get('news/{slug}',           [NewsController::class, 'show']);

    // ── Захищені маршрути (потрібна авторизація) ──────────────
    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::prefix('auth')->group(function () {
            Route::post('logout',           [AuthController::class, 'logout']);
            Route::get('me',                [AuthController::class, 'me']);
            Route::post('refresh',          [AuthController::class, 'refresh']);
            Route::post('switch-profile',   [AuthController::class, 'switchProfile']);
            Route::post('push-token',       [AuthController::class, 'savePushToken']);
        });

        // Profile
        Route::prefix('profile')->group(function () {
            Route::get('/',                 [ProfileController::class, 'me']);
            Route::put('/',                 [ProfileController::class, 'update']);
            Route::post('avatar',           [ProfileController::class, 'updateAvatar']);
            Route::get('ban-list',          [ProfileController::class, 'banList']);
            Route::post('ban/{user}',       [ProfileController::class, 'ban']);
            Route::delete('ban/{user}',     [ProfileController::class, 'unban']);
        });

        // Resume (Developer)
        Route::prefix('resume')->group(function () {
            Route::get('/',                         [ResumeController::class, 'show']);
            Route::post('/',                        [ResumeController::class, 'store']);
            Route::put('/',                         [ResumeController::class, 'update']);
            Route::post('publish',                  [ResumeController::class, 'publish']);
            Route::post('unpublish',                [ResumeController::class, 'unpublish']);

            // Work experience
            Route::get('experience',                [ResumeController::class, 'experience']);
            Route::post('experience',               [ResumeController::class, 'addExperience']);
            Route::put('experience/{experience}',   [ResumeController::class, 'updateExperience']);
            Route::delete('experience/{experience}',[ResumeController::class, 'deleteExperience']);

            // Portfolio
            Route::get('portfolio',                 [ResumeController::class, 'portfolio']);
            Route::post('portfolio',                [ResumeController::class, 'addPortfolio']);
            Route::put('portfolio/{portfolio}',     [ResumeController::class, 'updatePortfolio']);
            Route::delete('portfolio/{portfolio}',  [ResumeController::class, 'deletePortfolio']);
        });

        // Recruiter Profile
        Route::prefix('recruiter-profile')->group(function () {
            Route::get('/',     [RecruiterProfileController::class, 'show']);
            Route::post('/',    [RecruiterProfileController::class, 'store']);
            Route::put('/',     [RecruiterProfileController::class, 'update']);
        });

        // Feed (Home стрічка)
        Route::prefix('feed')->group(function () {
            Route::get('vacancies',     [FeedController::class, 'vacancies']);
            Route::get('resumes',       [FeedController::class, 'resumes']);
        });

        // Projects
        Route::apiResource('projects', ProjectController::class);
        Route::post('projects/{project}/archive',   [ProjectController::class, 'archive']);
        Route::post('projects/{project}/restore',   [ProjectController::class, 'restore']);

        // Vacancies
        Route::prefix('projects/{project}')->group(function () {
            Route::apiResource('vacancies', VacancyController::class);
            Route::post('vacancies/{vacancy}/publish',  [VacancyController::class, 'publish']);
            Route::post('vacancies/{vacancy}/archive',  [VacancyController::class, 'archive']);
        });

        // Applications (заявки на вакансії)
        Route::prefix('vacancies/{vacancy}')->group(function () {
            Route::get('applications',              [ApplicationController::class, 'index']);
            Route::post('apply',                    [ApplicationController::class, 'apply']);
            Route::put('applications/{application}',[ApplicationController::class, 'respond']);
        });
        Route::get('my-applications',              [ApplicationController::class, 'myApplications']);
        Route::delete('applications/{application}',[ApplicationController::class, 'withdraw']);

        // Contracts
        Route::prefix('contracts')->group(function () {
            Route::get('/',                         [ContractController::class, 'index']);
            Route::post('/',                        [ContractController::class, 'store']);
            Route::get('/{contract}',               [ContractController::class, 'show']);
            Route::put('/{contract}',               [ContractController::class, 'update']);
            Route::post('/{contract}/sign',         [ContractController::class, 'sign']);
            Route::post('/{contract}/complete',     [ContractController::class, 'complete']);
            Route::post('/{contract}/archive',      [ContractController::class, 'archive']);
            Route::post('/{contract}/cancel',       [ContractController::class, 'cancel']);
        });

        // Contract Tasks
        Route::prefix('contracts/{contract}/tasks')->group(function () {
            Route::get('/',             [TaskController::class, 'index']);
            Route::post('/',            [TaskController::class, 'store']);
            Route::get('/{task}',       [TaskController::class, 'show']);
            Route::put('/{task}',       [TaskController::class, 'update']);
            Route::delete('/{task}',    [TaskController::class, 'destroy']);
            Route::post('/{task}/done', [TaskController::class, 'markDone']);
            Route::post('/reorder',     [TaskController::class, 'reorder']);
        });

        // Time Logs (T&M)
        Route::prefix('contracts/{contract}/time-logs')->group(function () {
            Route::get('/',          [TimeLogController::class, 'index']);
            Route::post('/',         [TimeLogController::class, 'store']);
            Route::put('/{log}',     [TimeLogController::class, 'update']);
            Route::delete('/{log}',  [TimeLogController::class, 'destroy']);
        });

        // Wallet
        Route::prefix('wallet')->group(function () {
            Route::get('/',                     [WalletController::class, 'show']);
            Route::get('accounts',              [WalletController::class, 'accounts']);
            Route::post('deposit',              [WalletController::class, 'deposit']);
            Route::post('withdraw',             [WalletController::class, 'withdraw']);
            Route::post('transfer',             [WalletController::class, 'transfer']);
            Route::get('transactions',          [TransactionController::class, 'index']);
            Route::get('transactions/{uuid}',   [TransactionController::class, 'show']);
        });

        // Reviews
        Route::prefix('reviews')->group(function () {
            Route::get('/',                         [ReviewController::class, 'index']);
            Route::post('/',                        [ReviewController::class, 'store']);
            Route::get('/contract/{contract}',      [ReviewController::class, 'byContract']);
            Route::get('/user/{user}',              [ReviewController::class, 'byUser']);
        });

        // Messages
        Route::prefix('conversations')->group(function () {
            Route::get('/',                             [ConversationController::class, 'index']);
            Route::post('/',                            [ConversationController::class, 'store']);
            Route::get('/{conversation}',               [ConversationController::class, 'show']);
            Route::get('/{conversation}/messages',      [MessageController::class, 'index']);
            Route::post('/{conversation}/messages',     [MessageController::class, 'store']);
            Route::put('/{conversation}/messages/{message}/read', [MessageController::class, 'markRead']);
        });

        // Referral
        Route::prefix('referral')->group(function () {
            Route::get('/',         [ReferralController::class, 'index']);
            Route::get('payments',  [ReferralController::class, 'payments']);
        });

        // Support
        Route::prefix('support')->group(function () {
            Route::get('tickets',               [SupportController::class, 'index']);
            Route::post('tickets',              [SupportController::class, 'store']);
            Route::get('tickets/{ticket}',      [SupportController::class, 'show']);
            Route::post('tickets/{ticket}/reply',[SupportController::class, 'reply']);
        });
		
	

        // ── Super Admin маршрути ───────────────────────────────
        Route::middleware('role:super_admin')->prefix('admin')->group(function () {

            // Users
            Route::get('users',                     [AdminUserController::class, 'index']);
            Route::get('users/{user}',              [AdminUserController::class, 'show']);
            Route::put('users/{user}',              [AdminUserController::class, 'update']);
            Route::post('users/{user}/ban',         [AdminUserController::class, 'ban']);
            Route::post('users/{user}/unban',       [AdminUserController::class, 'unban']);
            Route::post('users/{user}/verify',      [AdminUserController::class, 'verify']);
            Route::delete('users/{user}',           [AdminUserController::class, 'destroy']);
            Route::post('users/{user}/reset-rating',[AdminUserController::class, 'resetRating']);

            // Content
            Route::get('contracts',                 [AdminContentController::class, 'contracts']);
            Route::get('vacancies',                 [AdminContentController::class, 'vacancies']);
            Route::get('reviews',                   [AdminContentController::class, 'reviews']);
            Route::delete('reviews/{review}',       [AdminContentController::class, 'deleteReview']);
            Route::post('reviews/{review}/hide',    [AdminContentController::class, 'hideReview']);

            // News (CRUD)
            Route::apiResource('news',              AdminContentController::class)
                ->only(['index','store','update','destroy'])
                ->names('admin.news');
            Route::post('news/{news}/publish',      [AdminContentController::class, 'publishNews']);

            // Finance
            Route::get('transactions',                  [AdminFinanceController::class, 'index']);
            Route::get('wallets',                       [AdminFinanceController::class, 'wallets']);
            Route::post('wallets/{user}/adjust',        [AdminFinanceController::class, 'adjustBalance']);
            Route::get('commission',                    [AdminFinanceController::class, 'commission']);

            // Analytics
            Route::get('analytics/overview',            [AdminAnalyticsController::class, 'overview']);
            Route::get('analytics/users',               [AdminAnalyticsController::class, 'users']);
            Route::get('analytics/contracts',           [AdminAnalyticsController::class, 'contracts']);
            Route::get('analytics/finance',             [AdminAnalyticsController::class, 'finance']);

            // Settings
            Route::get('settings',                      [AdminSettingController::class, 'index']);
            Route::put('settings/{key}',                [AdminSettingController::class, 'update']);
            Route::get('feature-flags',                 [AdminSettingController::class, 'flags']);
            Route::put('feature-flags/{key}',           [AdminSettingController::class, 'updateFlag']);

            // Translations
            Route::get('translations',                  [AdminTranslationController::class, 'index']);
            Route::put('translations/{id}',             [AdminTranslationController::class, 'update']);
            Route::post('translations/import',          [AdminTranslationController::class, 'import']);
            Route::get('translations/export',           [AdminTranslationController::class, 'export']);
            Route::post('locales',                      [AdminTranslationController::class, 'addLocale']);
        });
    });
});
