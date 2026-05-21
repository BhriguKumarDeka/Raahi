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
        Schema::table('trips', function (Blueprint $table) {
            $table->string('cover_image_url', 2048)->nullable();
            $table->string('photographer_name')->nullable();
            $table->string('photographer_url', 2048)->nullable();
            $table->string('photo_url', 2048)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropColumn(['cover_image_url', 'photographer_name', 'photographer_url', 'photo_url']);
        });
    }
};
