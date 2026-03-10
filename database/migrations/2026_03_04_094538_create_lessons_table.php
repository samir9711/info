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
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();

            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->json('title');
            $table->json('content')->nullable();
            $table->json('conclusion')->nullable();

            $table->string('video_url')->nullable();


            $table->boolean('is_published')->default(false);
            $table->boolean('free_preview')->default(false);

            $table->softDeletes();
            $table->timestamps();

            $table->index(['course_id']);
            $table->index(['is_published']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
