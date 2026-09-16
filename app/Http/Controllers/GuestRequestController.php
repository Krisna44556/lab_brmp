<?php

namespace App\Models; // Sesuaikan import jika diperlukan
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SampleRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

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

    public function showTrackingForm()
    {
        $sample = null;
        $code = null;
        $currentStatus = 'pending';

        return view('tracking', compact('sample', 'code', 'currentStatus'));
    }

    public function trackSample(Request $request)
    {
        $code = trim($request->input('code'));
        $sample = null;
        $currentStatus = 'pending';

           if ($code) {
                $sample = \App\Models\Sample::with(['sampleRequest.user'])
                    ->where('sample_code', $code)
                    ->first();

                if ($sample) {
                    // 2. BACA LANGSUNG DARI KOLOM 'current_status'
                    $rawStatus = strtolower(trim($sample->current_status ?? 'pending'));

                    // 3. Mapping status ke 4 tahap stepper
                    if (in_array($rawStatus, ['received', 'diterima', 'diterima_di_lab'])) {
                        $currentStatus = 'received';
                    } elseif (in_array($rawStatus, ['in_progress', 'sedang_diuji', 'proses'])) {
                        $currentStatus = 'in_progress';
                    } elseif (in_array($rawStatus, ['completed', 'selesai'])) {
                        $currentStatus = 'completed';
                    } elseif (in_array($rawStatus, ['issue', 'kendala', 'ditolak'])) {
                        $currentStatus = 'issue';
                    } else {
                        $currentStatus = 'pending';
                    }
                }
            }

            return view('tracking', compact('sample', 'code', 'currentStatus'));
        }


        public function getProvinces()
        {
            try {
                // Panggil API RajaOngkir menggunakan HTTP Client Laravel
                $response = Http::withHeaders([
                    'key' => env('RAJAONGKIR_API_KEY') // Pastikan API Key terisi di file .env
                ])->get('https://api.rajaongkir.com/starter/province');

                if ($response->successful()) {
                    // Ambil array results
                    $provinces = $response->json()['rajaongkir']['results'] ?? [];
                    return response()->json($provinces);
                }

                return response()->json(['error' => 'Gagal mengambil data dari RajaOngkir'], 500);
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }
}