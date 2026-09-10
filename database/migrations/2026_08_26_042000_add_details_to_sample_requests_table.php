<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sample_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('sample_requests', 'sample_type')) {
                $table->string('sample_type')->nullable()->after('request_code');
            }
            if (!Schema::hasColumn('sample_requests', 'sample_quantity')) {
                $table->integer('sample_quantity')->default(1)->after('sample_type');
            }
            if (!Schema::hasColumn('sample_requests', 'village')) {
                $table->string('village')->nullable()->after('sample_quantity');
            }
            if (!Schema::hasColumn('sample_requests', 'district')) {
                $table->string('district')->nullable()->after('village');
            }
            if (!Schema::hasColumn('sample_requests', 'regency')) {
                $table->string('regency')->nullable()->after('district');
            }
            if (!Schema::hasColumn('sample_requests', 'province')) {
                $table->string('province')->nullable()->after('regency');
            }
            if (!Schema::hasColumn('sample_requests', 'testing_purpose')) {
                $table->text('testing_purpose')->nullable()->after('province');
            }
            if (!Schema::hasColumn('sample_requests', 'total_price')) {
                $table->decimal('total_price', 12, 2)->default(0)->after('testing_purpose');
            }
            if (!Schema::hasColumn('sample_requests', 'payment_status')) {
                $table->string('payment_status')->default('pending')->after('total_price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sample_requests', function (Blueprint $table) {
            $table->dropColumn([
                'sample_type', 
                'sample_quantity', 
                'village', 
                'district', 
                'regency', 
                'province', 
                'testing_purpose', 
                'total_price', 
                'payment_status'
            ]);
        });
    }
};