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
        Schema::create('samples', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sample_request_id')->constrained()->onDelete('cascade');
            $table->string('sample_code')->unique();
            $table->string('sample_name');
            $table->string('qr_code_path')->nullable();
            $table->enum('current_status', [
                'registered', 
                'received', 
                'in_progress', 
                'issue', 
                'completed'
            ])->default('registered');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('samples');
    }
};
