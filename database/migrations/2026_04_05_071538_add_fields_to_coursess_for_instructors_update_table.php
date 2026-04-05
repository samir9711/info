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
        Schema::table('courses', function (Blueprint $table) {
            $table->tinyInteger('approval_status')->default(0)->after('publish')->index();
            $table->decimal('profit_percentage', 5, 2)->default(0)->after('approval_status');
            $table->boolean('is_platform_owned')->default(false)->after('profit_percentage');
            $table->text('rejection_reason')->nullable()->after('is_platform_owned');

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('instructors')
                ->nullOnDelete()
                ->after('rejection_reason');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            //
        });
    }
};
