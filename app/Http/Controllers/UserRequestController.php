<?php

namespace App\Http\Controllers;

use App\Models\SampleRequest;
use App\Models\Sample;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserRequestController extends Controller
{
    // 1. Tampilkan form pengajuan (View Sederhana untuk Pengetesan)
    public function create()
    {
        return view('users.create_user');
    }

    // 2. Eksekusi simpan pengajuan dari User (Status: Pending)
    public function store(Request $request)
    {
        // Validasi Input
        $request->validate([
            'sample_name'   => 'required|string|max:255',
            'payment_proof' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Upload Bukti Bayar ke Folder Storage
        $paymentPath = $request->file('payment_proof')->store('payment_proofs', 'public');

        // A. Simpan Permohonan (Status Bayar: pending)
        $sampleRequest = SampleRequest::create([
            'user_id'        => Auth::id(),
            'request_code'   => 'REQ-' . date('Ymd') . '-' . rand(100, 999),
            'payment_proof'  => $paymentPath,
            'payment_status' => 'paid', // Menunggu Verifikasi Admin
        ]);

        // B. Buat Draf Fisik Sampel
        Sample::create([
            'sample_request_id' => $sampleRequest->id,
            'sample_code'       => 'SMPL-' . date('Ymd') . '-' . rand(100, 999),
            'sample_name'       => $request->sample_name,
            'current_status'    => 'registered', // Menunggu Verifikasi Admin
        ]);

        return redirect()->route('dashboard')->with('success', 'Permohonan berhasil dikirim! Silakan tunggu verifikasi pembayaran dari Admin.');
    }
}