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
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();

            $table->json('meta')->nullable();


            $table->string('certificate_number')->unique()->nullable()->comment('رقم/كود الشهادة الفريد');
            $table->unsignedSmallInteger('score_percent')->nullable()->comment('النسبة/العلامة المئوية'); // 0-100
            $table->unsignedSmallInteger('passing_mark')->nullable()->comment('نقطة/نسبة النجاح المستخدمة عند الإصدار');




            $table->softDeletes();
            $table->timestamps();


            $table->index(['user_id']);
            $table->index(['course_id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
