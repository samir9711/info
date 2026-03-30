<?php

namespace App\Providers;

use App\Services\Functional\AdminAuthService;
use App\Services\Functional\UserAuthService;
use Illuminate\Support\ServiceProvider;
use App\Exceptions\Handler;

class AppServiceProvider extends ServiceProvider
{

     protected $facades = [
    'InstructorRatingService' => \App\Services\Model\InstructorRating\InstructorRatingService::class,

    'CourseInstructorService' => \App\Services\Model\CourseInstructor\CourseInstructorService::class,

    'InstructorService' => \App\Services\Model\Instructor\InstructorService::class,

    'LessonCommentService' => \App\Services\Model\LessonComment\LessonCommentService::class,

    'QuizAttemptAnswerService' => \App\Services\Model\QuizAttemptAnswer\QuizAttemptAnswerService::class,

    'QuizAttemptService' => \App\Services\Model\QuizAttempt\QuizAttemptService::class,

    'VocabularyService' => \App\Services\Model\Vocabulary\VocabularyService::class,

    'UserService' => \App\Services\Model\User\UserService::class,

    'TermsConditionService' => \App\Services\Model\TermsCondition\TermsConditionService::class,

    'TaggableService' => \App\Services\Model\Taggable\TaggableService::class,

    'TagService' => \App\Services\Model\Tag\TagService::class,

    'SettingService' => \App\Services\Model\Setting\SettingService::class,

    'RoleService' => \App\Services\Model\Role\RoleService::class,

    'QuizService' => \App\Services\Model\Quiz\QuizService::class,

    'QuestionService' => \App\Services\Model\Question\QuestionService::class,

    'PrivacyUsageService' => \App\Services\Model\PrivacyUsage\PrivacyUsageService::class,

    'PrivacyPolicyService' => \App\Services\Model\PrivacyPolicy\PrivacyPolicyService::class,

    'OtpService' => \App\Services\Model\Otp\OtpService::class,

    'LessonViewService' => \App\Services\Model\LessonView\LessonViewService::class,

    'LessonQuizService' => \App\Services\Model\LessonQuiz\LessonQuizService::class,

    'LessonService' => \App\Services\Model\Lesson\LessonService::class,

    'FavoriteService' => \App\Services\Model\Favorite\FavoriteService::class,

    'FaqService' => \App\Services\Model\Faq\FaqService::class,

    'CurrencyService' => \App\Services\Model\Currency\CurrencyService::class,

    'CourseQuizService' => \App\Services\Model\CourseQuiz\CourseQuizService::class,

    'CourseApplicationService' => \App\Services\Model\CourseApplication\CourseApplicationService::class,

    'CourseService' => \App\Services\Model\Course\CourseService::class,

    'ContactUsService' => \App\Services\Model\ContactUs\ContactUsService::class,

    'CertificateService' => \App\Services\Model\Certificate\CertificateService::class,

    'CategoryService' => \App\Services\Model\Category\CategoryService::class,

    'ArticleSectionService' => \App\Services\Model\ArticleSection\ArticleSectionService::class,

    'ArticleService' => \App\Services\Model\Article\ArticleService::class,

    'AnswerService' => \App\Services\Model\Answer\AnswerService::class,

    'AdminService' => \App\Services\Model\Admin\AdminService::class,

    'AdminAuthService' => AdminAuthService::class,

    'UserAuthService' => UserAuthService::class,


     ];
    /**
     * Register any application services.
     */
    public function register(): void
    {
        foreach ($this->facades as $facade => $service) {
            $this->app->singleton($facade, function ($app) use ($service) {
                return $app->make($service);
            });
        }

        $this->app->singleton(\Illuminate\Contracts\Debug\ExceptionHandler::class, Handler::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
