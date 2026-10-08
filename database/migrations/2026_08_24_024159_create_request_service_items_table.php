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
            // Ganti sample_request_id menjadi sample_id
            $table->foreignId('sample_id')->constrained('samples')->onDelete('cascade');
            $table->foreignId('lab_service_id')->constrained('lab_services')->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->decimal('price_at_time', 12, 2);
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
