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
        Schema::create('maintenance_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained();
            $table->foreignId('tenant_id')->constrained();
            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->string('issue_type');
            $table->text('description');
            $table->enum('priority', ['low', 'medium', 'high']);
            $table->enum('status', ['pending', 'in_progress', 'resolved'])->default('pending');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
        php artisan make:model maintenance_requests -m        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_requests');
    }
};
