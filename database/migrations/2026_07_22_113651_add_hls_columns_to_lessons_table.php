<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->string('video_source_disk')
                ->nullable()
                ->after('id');

            $table->string('video_source_path')
                ->nullable()
                ->after('video_source_disk');

            $table->string('hls_disk')
                ->nullable()
                ->after('video_source_path');

            $table->string('hls_path')
                ->nullable()
                ->after('hls_disk');

            $table->string('hls_status', 20)
                ->default('pending')
                ->index()
                ->after('hls_path');

            $table->text('hls_error')
                ->nullable()
                ->after('hls_status');

            $table->timestamp('hls_processed_at')
                ->nullable()
                ->after('hls_error');
        });
    }

    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropIndex(['hls_status']);

            $table->dropColumn([
                'video_source_disk',
                'video_source_path',
                'hls_disk',
                'hls_path',
                'hls_status',
                'hls_error',
                'hls_processed_at',
            ]);
        });
    }
};
