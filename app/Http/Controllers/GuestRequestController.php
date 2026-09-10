<?php

namespace App\Models; // Sesuaikan import jika diperlukan
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SampleRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class GuestRequestController extends Controller
{
    // 1. Tampilkan Halaman Form Pengajuan Publik/Guest
    public function create()
    {
        return view('guest_request');
    }

    // 2. Simpan Data & Buat Akun Otomatis
    public function store(Request $request)
    {
        // Validasi Input
        $request->validate([
            'applicant_name' => 'required|string|max:255',
            'phone_number'   => 'required|string|max:20',
            'sample_name'    => 'required|string|max:255',
            'payment_proof'  => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // 1. Cek Apakah No. HP Sudah Terdaftar. Jika Belum, Buat Akun Baru
        $user = User::firstOrCreate(
            ['phone_number' => $request->phone_number],
            [
                'name'     => $request->applicant_name,
                'email'    => 'user_' . time() . '@brmp.com', // Dummy email unik
                'password' => Hash::make('password123'),       // Password default
                'role'     => 'user',
            ]
        );

        // 2. Login-kan User Tersebut Secara Otomatis
        Auth::login($user);

        // 3. Simpan File Bukti Pembayaran
        $paymentPath = null;
        if ($request->hasFile('payment_proof')) {
            $paymentPath = $request->file('payment_proof')->store('payment_proofs', 'public');
        }

        // 4. Buat Record Permohonan Pengujian Baru
        SampleRequest::create([
            'user_id'        => $user->id,
            'request_code'   => 'REQ-' . date('Ymd') . '-' . rand(100, 999),
            'payment_proof'  => $paymentPath,
            'payment_status' => 'pending', // Perlu verifikasi admin
        ]);

        // 5. Redirect ke Dashboard User Dengan Pesan Sukses
        return redirect()->route('dashboard')->with('success', 'Pengajuan berhasil dikirim! Akun Anda telah dibuat secara otomatis.');
    }
}