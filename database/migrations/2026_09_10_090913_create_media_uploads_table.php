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
        Schema::create('media_uploads', function (Blueprint $table) {
            $table->id();

            $table->uuid('uuid')->unique();


            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();


            $table->string('type');


            $table->unsignedBigInteger('model_id')->nullable();


            $table->string('model_type')->nullable();


            $table->string('tus_id')->nullable()->unique();


            $table->string('original_name')->nullable();


            $table->string('mime_type')->nullable();


            $table->unsignedBigInteger('size')->nullable();


            $table->unsignedBigInteger('uploaded_size')
                ->default(0);


            $table->string('path')->nullable();


            $table->enum('status', [
                'pending',
                'uploading',
                'completed',
                'processing',
                'ready',
                'failed',
                'cancelled',
            ])->default('pending');


            $table->text('error')->nullable();

            $table->timestamp('started_at')->nullable();

            $table->timestamp('completed_at')->nullable();

            $table->timestamp('failed_at')->nullable();

            $table->timestamps();

            $table->softDeletes();

            $table->index([
                'model_type',
                'model_id',
            ]);

            $table->index([
                'user_id',
                'status',
            ]);

            $table->index([
                'type',
                'status',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_uploads');
    }
};
