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
        Schema::create('sample_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sample_id')->constrained()->onDelete('cascade');
            $table->foreignId('distributor_id')->constrained('users')->onDelete('cascade');
            $table->string('issue_type');
            $table->text('description');
            $table->enum('status', ['reported', 'resolved'])->default('reported');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sample_issues');
    }
};
