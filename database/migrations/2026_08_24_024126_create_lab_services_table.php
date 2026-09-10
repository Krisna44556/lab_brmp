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
    Schema::create('lab_services', function (Blueprint $table) {
        $table->id();
        $table->string('lab_category');      // Biologi, Biologi Molekuler, Entomologi, Kimia Tanah, dll.[cite: 1, 2, 3, 7]
        $table->string('sub_category')->nullable(); // Rutin, Mikro, Khusus, Pupuk Organik, dll.[cite: 7, 8, 9, 10]
        $table->string('accreditation')->default('Non Akreditasi'); // Terakreditasi / Non Akreditasi[cite: 1, 2, 3]
        $table->string('service_name');     // Nama parameter[cite: 1, 2, 3]
        $table->string('method')->nullable(); // [cite: 1, 2, 3]
        $table->string('equipment')->nullable(); // [cite: 1, 2, 3]
        $table->string('unit');             // per sampel, per 10 umbi, per running[cite: 1, 2, 3]
        $table->decimal('price', 12, 2);    // Tarif[cite: 1, 2, 3]
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_services');
    }
};
