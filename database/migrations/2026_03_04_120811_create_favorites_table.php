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
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();


            $table->unsignedBigInteger('favoritable_id');
            $table->string('favoritable_type');


            $table->string('note')->nullable();


            $table->timestamps();
            $table->softDeletes();


            $table->index(['user_id']);
            $table->index(['favoritable_type','favoritable_id']);


            $table->unique(['user_id','favoritable_id','favoritable_type'], 'favorites_user_item_unique');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};
