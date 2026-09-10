<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Akun Admin Lab (Untuk Verifikasi Pembayaran & Cetak QR)
        User::create([
            'name' => 'Admin Lab BRMP',
            'email' => 'admin@brmp.go.id',
            'phone_number' => '081234567890',
            'role' => 'admin',
            'password' => Hash::make('password123'),
        ]);

        // 2. Akun Distributor Lab (Untuk Scan QR & Input Status Pengerjaan)
        User::create([
            'name' => 'Petugas Distributor Lab',
            'email' => 'distributor@brmp.go.id',
            'phone_number' => '081234567891',
            'role' => 'distributor',
            'password' => Hash::make('password123'),
        ]);

        // 3. Akun User / Pemohon (Untuk Input Form Pemohon)
        User::create([
            'name' => 'Ahmad Pemohon',
            'email' => 'user@gmail.com',
            'phone_number' => '081234567892', // Nomor HP aktif untuk pengujian WA Gateway
            'role' => 'user',
            'password' => Hash::make('password123'),
        ]);
    }
}