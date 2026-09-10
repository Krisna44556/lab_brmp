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
        Schema::create('sample_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sample_id')->constrained()->onDelete('cascade');
            $table->foreignId('distributor_id')->constrained('users')->onDelete('cascade');
            $table->enum('action', ['terima_sampel', 'mulai_uji', 'selesai_uji']);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sample_logs');
    }
};
