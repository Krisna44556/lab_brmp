<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SampleRequest;
use App\Models\Sample;
use App\Models\RequestServiceItem;
use App\Models\LabService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GuestRequestController extends Controller
{
    // 1. Tampilkan Halaman Form Pengajuan Publik/Guest
    public function create()
    {
        return view('frontend.pendaftaran');
    }

    // 2. Simpan Data, Generate Sampel, & Buat Akun Otomatis
    public function store(Request $request)
    {
        // Validasi Input (Menyesuaikan input multi-sample dari frontend)
        $request->validate([
            'pemohon_name'  => 'required|string|max:255',
            'phone_number'  => 'required|string|max:20',
            'lab_type'      => 'required|string',
            'samples'       => 'required|array|min:1',
            'samples.*.sample_name' => 'required|string|max:255',
            'samples.*.services'    => 'required|array|min:1',
            'payment_proof' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        DB::beginTransaction();

        try {
            // A. Cari atau Buat Akun User Publik Baru berdasarkan Nomor HP
            $user = User::firstOrCreate(
                ['phone_number' => $request->phone_number],
                [
                    'name'     => $request->pemohon_name,
                    'email'    => 'user_' . time() . rand(10, 99) . '@brmp.com',
                    'password' => Hash::make('password123'),
                    'role'     => 'user',
                ]
            );

            // B. Simpan Bukti Pembayaran
            $paymentPath = null;
            if ($request->hasFile('payment_proof')) {
                $paymentPath =$request->file('payment_proof')->store('payment_proofs', 'public');
            }

            // C. Generate 1 Nomor Request Utama
            $requestCode = 'REQ-' . date('Ymd') . '-' . rand(100, 999);

            // D. Simpan Header Permohonan
            $sampleRequest = SampleRequest::create([
                'user_id'         => $user->id,
                'pemohon_name'    => $request->pemohon_name,
                'phone_number'    => $request->phone_number,
                'lab_type'        => $request->lab_type,
                'sample_type'     => $request->samples[0]['sample_name'] ?? 'Umum',
                'sample_quantity' => count($request->samples), // Jumlah wadah
                'request_code'    => $requestCode,
                'payment_proof'   => $paymentPath,
                'payment_status'  => 'pending',
                'village'         => $request->village,
                'district'        => $request->district,
                'regency'         => $request->regency,
                'province'        => $request->province,
                'testing_purpose' => $request->testing_purpose,
                'total_price'     => 0,
                'status'          => 0,
            ]);

            $grandTotal = 0;

            // E. Loop Setiap Wadah / Sampel Fisik
            foreach ($request->samples as $index =>$sampleData) {
                
                // 1. Simpan Wadah ke tabel `samples` (Kode Unik: REQ-xxxx-S1, REQ-xxxx-S2)
                $sample = Sample::create([
                    'sample_request_id' => $sampleRequest->id,
                    'sample_code'       => $requestCode . '-S' . ($index + 1),                     'sample_name'       =>$sampleData['sample_name'],
                    'current_status'    => 'registered',
                ]);

                // 2. Loop Parameter Uji KHUSUS Wadah Ini (Simpan ke request_service_items)
                foreach ($sampleData['services'] as$serviceData) {
                    $labServiceId = is_array($serviceData) ? $serviceData['lab_service_id'] :$serviceData;
                    $quantity     = is_array($serviceData) ? ($serviceData['quantity'] ?? 1) : 1;

                    $labService = LabService::findOrFail($labServiceId);
                    $price =$labService->price;

                    RequestServiceItem::create([
                        'sample_id'      => $sample->id, // Terikat ke Wadah/Sampel
                        'lab_service_id' => $labService->id,
                        'quantity'       => $quantity,
                        'price_at_time'  => $price,
                    ]);

                    $grandTotal += ($price * $quantity);
                }
            }

            // F. Update Total Harga Keseluruhan Permohonan
            $sampleRequest->update(['total_price' =>$grandTotal]);

            DB::commit();

            // G. Login-kan User & Redirect
            Auth::login($user);

            return redirect()->route('dashboard')->with('success', 'Pengajuan berhasil dikirim! Kode permohonan Anda: ' . $requestCode);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error GuestRequestController store: ' . $e->getMessage());

            return redirect()->back()->withInput()->with('error', 'Gagal mengirim pengajuan: ' . $e->getMessage());
        }
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
        $code = trim($request->input('code'));$sample = null;
        $currentStatus = 'pending';

        if ($code) {$sample = \App\Models\Sample::with(['sampleRequest.user', 'serviceItems.labService'])
                ->where('sample_code', $code)
                ->first();

            if ($sample) {
                $rawStatus = strtolower(trim($sample->current_status ?? 'pending'));

                if (in_array($rawStatus, ['received', 'diterima', 'diterima_di_lab'])) {$currentStatus = 'received';
                } elseif (in_array($rawStatus, ['in_progress', 'sedang_diuji', 'proses'])) {$currentStatus = 'in_progress';
                } elseif (in_array($rawStatus, ['completed', 'selesai'])) {$currentStatus = 'completed';
                } elseif (in_array($rawStatus, ['issue', 'kendala', 'ditolak'])) {$currentStatus = 'issue';
                } else {
                    $currentStatus = 'pending';
                }
            }
        }

        return view('tracking', compact('sample', 'code', 'currentStatus'));
    }

    public function getParametersByLab($lab)
    {
        try {
            $cleanLab = strtolower(trim(urldecode($lab)));

            $parameters = LabService::where('lab_category', 'LIKE', '\%' .$cleanLab . '%')
                ->get();

            return response()->json([
                'status' => 'success',
                'data'   => $parameters
            ], 200);

        } catch (\Throwable $e) {
            Log::error('Error getParametersByLab: ' . $e->getMessage());

            return response()->json([
                'status'  => 'error',
                'message' => 'DB/Server Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getProvinces()
    {
        try {
            $response = Http::withHeaders([
                'key' => env('RAJAONGKIR_API_KEY')
            ])->get('https://api.rajaongkir.com/starter/province');

            if ($response->successful()) {
                $provinces =$response->json()['rajaongkir']['results'] ?? [];
                return response()->json($provinces);
            }

            return response()->json(['error' => 'Gagal mengambil data dari RajaOngkir'], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}