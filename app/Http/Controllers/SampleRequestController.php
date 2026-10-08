<?php

namespace App\Http\Controllers;

use App\Models\User; // <-- DITAMBAHKAN: Import Model User
use App\Models\SampleRequest;
use App\Models\LabService;
use App\Models\Sample;
use App\Services\WhatsappService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class SampleRequestController extends Controller
{
public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'pemohon_name'    => 'required|string',
        'sample_type'     => 'required|string',
        'sample_quantity' => 'required|numeric|min:1',
        'lab_type'        => 'required|string',
        'phone_number'    => 'nullable|string',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status'  => 'error',
            'message' => 'Validasi Gagal: ' . $validator->errors()->first()
        ], 422);
    }

    try {
        DB::beginTransaction();

        // 1. CARI ATAU BUAT AKUN USER PEMOHON BARU
        $phone = $request->phone_number ?? '08' . rand(100000000, 999999999);
        
        $user = User::firstOrCreate(
            ['phone_number' => $phone],
            [
                'name'     => $request->pemohon_name,
                'email'    => 'user_' . time() . rand(10, 99) . '@brmp.com',
                'password' => Hash::make('password123'),
                'role'     => 'user',
            ]
        );

        $quantity = (int) $request->input('sample_quantity', 1);
        $selectedServices = $request->input('services', []); // Array ID Layanan/Parameter

        // Hitung total harga parameter per 1 sampel
        $pricePerSample = LabService::whereIn('id', $selectedServices)->sum('price');

        // Total Biaya Keseluruhan = Harga Parameter x Jumlah Sampel
        $totalAmount = $pricePerSample * $quantity;

        $requestCode = 'REQ-' . date('Ymd') . '-' . rand(100, 999);

        // 2. SIMPAN DENGAN ID USER YANG BARU DIBUAT
        $sampleRequest = SampleRequest::create([
            'request_code'    => $requestCode,
            'user_id'         => $user->id,
            'pemohon_name'    => $request->pemohon_name,
            'phone_number'    => $phone,
            'province'        => $request->province,
            'regency'         => $request->regency,
            'district'        => $request->district,
            'village'         => $request->village,
            'sample_type'     => $request->sample_type,
            'sample_quantity' => $request->sample_quantity,
            'lab_type'        => $request->lab_type,
            'total_amount'    => $totalAmount,
            'payment_status'  => 'pending',
        ]);

        // 3. GENERATE ITEM SAMPEL KE TABEL SAMPLES
        for ($i = 1; $i <= $request->sample_quantity; $i++) {
            Sample::create([
                'sample_request_id' => $sampleRequest->id,
                'sample_code'       => 'SMP-' . date('Ymd') . '-' . rand(1000, 9999),
                'sample_name'       => $request->sample_type . ' #' . $i,
                'current_status'    => 'registered', // Disesuaikan dengan kolom DB
            ]);
        }

        DB::commit();

        // Kirim Notifikasi WA Otomatis ke User
        try {
            if ($phone && class_exists(\App\Services\WhatsappService::class)) {
                $trackingUrl = url('/tracking?code=' . $requestCode);

                $pesanWA  = "Halo, *{$request->pemohon_name}*!\n\n";
                $pesanWA .= "Terima kasih telah melakukan pendaftaran sampel.\n";
                $pesanWA .= "*Kode Pendaftaran:* `{$requestCode}`\n";
                $pesanWA .= "*Jenis Sampel:* {$request->sample_type}\n";
                $pesanWA .= "*Jumlah Sampel:* {$quantity}\n";
                $pesanWA .= "*Total Biaya:* Rp " . number_format($totalAmount, 0, ',', '.') . "\n\n";
                $pesanWA .= "Silakan simpan Kode Pendaftaran Anda untuk melakukan pengecekan status secara berkala melalui link berikut:\n";
                $pesanWA .= "🔍 *Lacak Sampel:* {$trackingUrl}\n\n";
                $pesanWA .= "_Pesan ini dikirim otomatis oleh sistem laboratorium._";

                \App\Services\WhatsappService::sendMessage($phone,$pesanWA);
            }
        } catch (\Exception $e) {             \Illuminate\Support\Facades\Log::error('Gagal kirim WA Pendaftaran Publik: ' .$e->getMessage());
        }

        return response()->json([
            'status'       => 'success',
            'message'      => 'Pendaftaran berhasil disimpan!',
            'request_code' => $requestCode
        ], 200);

        return response()->json([
            'status'       => 'success',
            'message'      => 'Pendaftaran berhasil disimpan!',
            'request_code' => $requestCode
        ], 200);

    } catch (\Exception $e) {
        
        DB::rollBack();

        return response()->json([
            'status'  => 'error',
            'message' => 'Error DB: ' . $e->getMessage() . ' di baris ' . $e->getLine()
        ], 500);
    }
}
}