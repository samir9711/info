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
        DB::statement("
            UPDATE instructors
            SET
            name = CASE WHEN name IS NULL OR name = '' THEN NULL ELSE JSON_OBJECT('ar', name) END,
            profession = CASE WHEN profession IS NULL OR profession = '' THEN NULL ELSE JSON_OBJECT('ar', profession) END,
            bio = CASE WHEN bio IS NULL OR bio = '' THEN NULL ELSE JSON_OBJECT('ar', bio) END,
            headline = CASE WHEN headline IS NULL OR headline = '' THEN NULL ELSE JSON_OBJECT('ar', headline) END,
            experience = CASE WHEN experience IS NULL OR experience = '' THEN NULL ELSE JSON_OBJECT('ar', experience) END
        ");

        Schema::table('instructors', function (Blueprint $table) {
            $table->json('name')->nullable()->change();
            $table->json('profession')->nullable()->change();
            $table->json('bio')->nullable()->change();
            $table->json('headline')->nullable()->change();
            $table->json('experience')->nullable()->change();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('instructors', function (Blueprint $table) {
            //
        });
    }
};
