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
        Schema::create('course_quiz_attempt_answers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('course_quiz_attempt_id')
                ->constrained('course_quiz_attempts')
                ->cascadeOnDelete();

            $table->foreignId('question_id')
                ->constrained('questions')
                ->cascadeOnDelete();

            $table->foreignId('answer_id')
                ->nullable()
                ->constrained('answers')
                ->nullOnDelete();

            $table->boolean('is_correct')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(
                ['course_quiz_attempt_id', 'question_id'],
                'cqaa_attempt_question_unique'
            );
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_quiz_attempt_answers');
    }
};
