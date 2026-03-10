<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lesson_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('lesson_id')->constrained('lessons')->cascadeOnDelete();

            // بيانات المشاهدة
            $table->unsignedInteger('view_count')->default(1)->comment('عدد المرات التي شاهد فيها المستخدم الدرس');
            $table->unsignedInteger('total_watch_seconds')->default(0)->comment('مجموع ثواني المشاهدة');
            $table->unsignedInteger('last_watched_seconds')->nullable()->comment('نقطة التوقف الأخيرة داخل الفيديو بالثواني');
            $table->unsignedTinyInteger('progress_percent')->default(0)->comment('نسبة التقدّم بالدرس (0-100)');
            $table->boolean('is_completed')->default(false)->comment('هل أنهي المستخدم الدرس (حسب سياسة الإنهاء)');
            $table->timestamp('last_viewed_at')->nullable();

            // خيارات تتبع إضافية
            $table->string('device')->nullable()->comment('اختياري: وصف الجهاز/العميل');
            $table->string('ip')->nullable()->comment('IP عند آخر مشاهدة');

            $table->softDeletes();
            $table->timestamps();


            $table->unique(['user_id','lesson_id']);

            $table->index(['user_id']);
            $table->index(['lesson_id']);
            $table->index(['last_viewed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_views');
    }
};
