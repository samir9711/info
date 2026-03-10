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
        Schema::create('otps', function (Blueprint $table) {
            $table->id();

            // polymorphic: verifiable_type, verifiable_id
            $table->string('verifiable_type');
            $table->unsignedBigInteger('verifiable_id');

            $table->string('otp', 6); // 6 digits
            $table->string('purpose', 50); // register | reset | phone_change | ...
            $table->enum('channel', ['sms','whatsapp','email'])->nullable();

            $table->boolean('is_used')->default(false)->index();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('verified_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['verifiable_type','verifiable_id','purpose'], 'otps_verifiable_purpose_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otps');
    }
};
