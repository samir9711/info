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
        Schema::table('lesson_comments', function (Blueprint $table) {

            $table->dropForeign(['user_id']);
        });

        Schema::table('lesson_comments', function (Blueprint $table) {

            $table->unsignedBigInteger('user_id')->nullable()->change();


            $table->foreignId('admin_id')
                ->nullable()
                ->after('user_id')
                ->constrained('admins')
                ->nullOnDelete();

           
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lesson_comments', function (Blueprint $table) {
            //
        });
    }
};
