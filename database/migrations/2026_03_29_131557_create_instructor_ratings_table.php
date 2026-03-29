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
        Schema::create('instructor_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instructor_id')
                ->constrained('instructors')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('rating'); 
            $table->text('comment')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['instructor_id', 'rating']);
            $table->index(['user_id', 'rating']);

            $table->unique(['instructor_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instructor_ratings');
    }
};
