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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->integer('floor');
            $table->integer('capacity');
            $table->text('amenities')->nullable();
            $table->enum('type', ['single', 'shared', 'suite']);
            $table->enum('status', ['vacant', 'occupied', 'maintenance'])->default('vacant');
            $table->decimal('rent_amount', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
