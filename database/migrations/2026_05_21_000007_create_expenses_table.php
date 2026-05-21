<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained('trips')->onDelete('cascade');
            $table->string('title');
            $table->decimal('amount', 10, 2);
            $table->foreignId('paid_by')->constrained('users')->onDelete('cascade');
            $table->string('split_type')->default('equal'); // equal, custom
            $table->string('category')->default('miscellaneous'); // transport, accommodation, food, activities, miscellaneous
            $table->date('date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
