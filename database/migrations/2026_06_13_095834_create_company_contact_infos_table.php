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
        Schema::create('company_contact_infos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete()->unique();

            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('website')->nullable();

            $table->string('facebook')->nullable();
            $table->string('x')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('instagram')->nullable();
            $table->string('youtube')->nullable();


            $table->longText('contact_address')->nullable();
            $table->string('working_hours')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_contact_infos');
    }
};
