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
        Schema::create('request_service_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sample_request_id')->constrained()->onDelete('cascade');
            $table->foreignId('lab_service_id')->constrained()->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->decimal('price_at_time', 12, 2); // Menyimpan harga saat transaksi
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_service_items');
    }
};
