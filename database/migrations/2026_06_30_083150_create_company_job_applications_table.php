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
        Schema::create('company_job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_job_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('company_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->text('cover_letter')->nullable();

            $table->string('cv')->nullable();

            $table->enum('status',[
                'pending',
                'reviewing',
                'accepted',
                'rejected',
                'withdrawn'
            ])->default('pending');

            $table->timestamp('reviewed_at')->nullable();


            $table->text('company_note')->nullable();

            $table->timestamps();

            $table->softDeletes();

            $table->unique(['company_job_id','user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_job_applications');
    }
};
