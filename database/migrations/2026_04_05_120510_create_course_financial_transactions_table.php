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
        Schema::create('course_financial_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('course_application_id')
                ->constrained('course_applications')
                ->cascadeOnDelete();

            $table->foreignId('instructor_id')
                ->nullable()
                ->constrained('instructors')
                ->nullOnDelete();

            $table->foreignId('currency_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->tinyInteger('entry_type')->index();
            $table->decimal('amount', 12, 2);

            $table->boolean('is_settled')->default(false)->index();
            $table->timestamp('settled_at')->nullable();

            $table->foreignId('settled_by')
                ->nullable()
                ->constrained('admins')
                ->nullOnDelete();

            $table->text('description')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['course_application_id', 'entry_type'], 'cft_app_type_unique');
            $table->index(['course_id', 'entry_type']);
            $table->index(['instructor_id', 'is_settled']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_financial_transactions');
    }
};
