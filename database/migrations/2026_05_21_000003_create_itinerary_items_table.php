<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('itinerary_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained('trips')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('datetime');
            $table->string('location')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->decimal('cost', 10, 2)->default(0);
            $table->string('category')->default('activity'); // transport, accommodation, activity, food, other
            $table->foreignId('added_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('itinerary_items');
    }
};
