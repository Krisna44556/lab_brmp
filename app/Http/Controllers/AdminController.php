<?php

namespace App\Http\Controllers;

use App\Models\SampleRequest;
use App\Models\Sample;
use App\Models\User;
use App\Models\LabService;
use App\Models\RequestServiceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AdminController extends Controller
{
    // 1. Halaman Utama Dashboard Admin
    public function index(Request $request)
    {
        $query = SampleRequest::with(['user', 'samples']);

        if ($request->filled('code') || $request->filled('search')) {
            $search = trim($request->input('code', $request->input('search')));

            $query->where(function ($q) use ($search) {
                $q->where('request_code', 'LIKE', "%{$search}%")
                  ->orWhereHas('user', function ($u) use ($search) {
                      $u->where('name', 'LIKE', "%{$search}%");
                  })
                  ->orWhereHas('samples', function ($s) use ($search) {
                      $s->where('sample_code', 'LIKE', "%{$search}%");
                  });
            });
        }

        $requests = SampleRequest::with('samples')->orderBy('id', 'desc')->paginate(5);

        $pendingCount = SampleRequest::where('payment_status', 'pending')->count();
        $verifiedCount = SampleRequest::where('payment_status', 'verified')->count();

        return view('admin.dashboard', compact('pendingCount', 'verifiedCount', 'requests'));
    }

    // 2. Step 1: Form Pilih Laboratorium
    public function selectLab()
    {
        return view('admin.select-lab');
    }

    // 3. Step 2: Form Input Parameter & Data Pemohon
    public function create(Request $request)
    {
        $labType = $request->query('lab_type', 'tanah');
        
        if (!$labType) {
            return redirect()->route('admin.request.create.step1');
        }

        $services = LabService::whereRaw('LOWER(lab_category) LIKE ?', ["%{$labType}%"])->get();

        return view('admin.create', compact('labType', 'services'));
    }

    // 4. Simpan Data Permohonan Admin (Multi-Sample Support)
    public function storeRequest(Request $request)
    {
        $request->validate([
            'lab_type'       => 'required|string',
            'applicant_name' => 'required|string|max:255',
            'phone_number'   => 'required|string|max:20',
            'samples'        => 'required|array|min:1',
            'samples.*.sample_name' => 'required|string|max:255',
            'samples.*.services'    => 'required|array|min:1',
        ], [
            'samples.required' => 'Minimal harus menambahkan satu wadah sampel!',
        ]);

        $createdRequestCode = null;

        DB::transaction(function () use ($request, &$createdRequestCode) {
            // A. Simpan / Cari User Pemohon
            $user = User::firstOrCreate(
                ['phone_number' => $request->phone_number],
                [
                    'name'     => $request->applicant_name,
                    'email'    => 'user_' . time() . rand(10, 99) . '@brmp.com',
                    'password' => Hash::make('password123'),
                    'role'     => 'user',
                ]
            );

            if ($user->name !== $request->applicant_name) {
                $user->update(['name' => $request->applicant_name]);
            }

            $requestCode = 'REQ-' . date('Ymd') . '-' . rand(100, 999);

            // B. Simpan Header Permohonan
            $sampleRequest = SampleRequest::create([
                'user_id'         => $user->id,
                'pemohon_name'    => $request->applicant_name,
                'lab_type'        => $request->lab_type,
                'phone_number'    => $request->phone_number,
                'request_code'    => $requestCode,
                'sample_type'     => $request->samples[0]['sample_name'] ?? 'Umum',
                'sample_quantity' => count($request->samples),
                'village'         => $request->village,
                'district'        => $request->district,
                'regency'         => $request->regency,
                'province'        => $request->province,
                'testing_purpose' => $request->testing_purpose,
                'total_price'     => 0,
                'payment_status'  => 'verified',
                'status'          => 0,
            ]);

            $createdRequestCode = $sampleRequest->request_code;
            $grandTotal = 0;

            // C. Loop Setiap Wadah/Sampel Fisik
            foreach ($request->samples as $index => $sampleData) {

                $sampleCode = 'SMP-' . date('Ymd') . '-' . rand(1000, 9999);

                $sample = Sample::create([
                    'sample_request_id' => $sampleRequest->id,
                    'sample_code'       => $sampleCode,
                    'sample_name'       => $sampleData['sample_name'],
                    'current_status'    => 'registered',
                ]);

                // Loop Parameter Uji Khusus Wadah Ini
                foreach ($sampleData['services'] as $serviceData) {
                    $serviceId = is_array($serviceData) ? $serviceData['lab_service_id'] : $serviceData;
                    $quantity  = is_array($serviceData) ? ($serviceData['quantity'] ?? 1) : 1;

                    $labService = LabService::findOrFail($serviceId);
                    $price = $labService->price;

                    RequestServiceItem::create([
                        'sample_id'      => $sampleRequest->id, // Menggunakan ID dari sample_requests (misal: 23)
                        'lab_service_id' => $labService->id,
                        'quantity'       => $quantity,
                        'price_at_time'  => $price,
                    ]);

                    $grandTotal += ($price * $quantity);
                }
            }

            // Update Total Biaya Keseluruhan
            $sampleRequest->update(['total_price' => $grandTotal]);
        });

        // Kirim WhatsApp Otomatis via Fonnte
        try {
            $message = "Halo *" . $request->applicant_name . "*,\n\n";
            $message .= "Permohonan pengujian sampel Anda telah *BERHASIL TERDAFTAR* di Admin BRMP Laboratorium.\n\n";
            $message .= "📌 *Detail Permohonan:*\n";
            $message .= "• Kode Permohonan: *" . $createdRequestCode . "*\n";
            $message .= "• Jumlah Sampel: " . count($request->samples) . " Wadah\n\n";
            $message .= "Anda dapat melacak status progres sampel Anda secara real-time melalui link berikut:\n";
            $message .= url('/tracking') . "?code=" . $createdRequestCode . "\n\n";
            $message .= "Terima kasih,\n*BRMP Laboratorium*";

            Http::withHeaders([
                'Authorization' => env('FONNTE_TOKEN', 'TOKEN_FONNTE_KAMU_DISINI'),
            ])->post('https://api.fonnte.com/send', [
                'target'  => $request->phone_number,
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            Log::error('Fonnte WA Error saat storeRequest: ' . $e->getMessage());
        }

        return redirect()->route('admin.dashboard')->with('success', 'Permohonan Pengujian Berhasil Disimpan & WA Notifikasi Terkirim!');
    }

    // 5. Detail Request
    public function showRequest($id)
    {
        $sampleRequest = SampleRequest::with(['user', 'samples.serviceItems.labService'])->findOrFail($id);
        return view('admin.request_show', compact('sampleRequest'));
    }
        
    // 6. Generate QR Code
    public function generateQrCode($id)
    {
        $sampleRequest = SampleRequest::with(['user', 'samples'])->findOrFail($id);
        $sample = $sampleRequest->samples->first();
        $qrCodeData = $sample?->sample_code ?? $sampleRequest->request_code;

        return view('admin.qr_code', compact('sampleRequest', 'sample', 'qrCodeData'));
    }

    // 7. Verifikasi Pembayaran
    public function verifyPayment($id, $status)
    {
        $requestData = SampleRequest::findOrFail($id);
        $requestData->update(['payment_status' => $status]);

        return redirect()->back()->with('success', 'Status pembayaran berhasil diperbarui!');
    }

    // 8. Update Status Progres Sampel
    public function updateStatus(Request $request, $id)
    {
        $statusInput = strtolower(str_replace(' ', '_', trim($request->status)));
        $sample = Sample::find($id);

        if (!$sample) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Data sampel tidak ditemukan.'], 404);
            }
            return redirect()->back()->with('error', 'Data sampel tidak ditemukan.');
        }

        $sample->update(['current_status' => $statusInput]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Status sampel berhasil diperbarui!'
            ]);
        }

        return redirect()->back()->with('success', 'Status sampel berhasil diperbarui!');
    }

    // 9. API Endpoint Status Realtime
    public function statusRealtime()
    {
        $requests = SampleRequest::with('samples')->get();

        $data = $requests->map(function ($req) {
            $sample = $req->samples->first();
            $rawStatus = $sample?->current_status ?? 'pending';

            return [
                'id'     => $req->id,
                'status' => strtolower(str_replace(' ', '_', trim($rawStatus)))
            ];
        });

        return response()->json($data);
    }

    // 10. Simpan Pendaftaran Offline
    public function storeOffline(Request $request)
    {
        $request->validate([
            'user_id'       => 'required_without:pemohon_name',
            'pemohon_name'  => 'required_without:user_id|string|max:255',
            'phone_number'  => 'required_without:user_id|string|max:20',
            'lab_type'      => 'required|string',
            'samples'       => 'required|array|min:1',
            'samples.*.sample_name' => 'required|string|max:255',
            'samples.*.services'    => 'required|array|min:1',
        ]);

        DB::beginTransaction();
        try {
            if ($request->user_id) {
                $user = User::findOrFail($request->user_id);
            } else {
                $user = User::firstOrCreate(
                    ['phone_number' => $request->phone_number],
                    [
                        'name'     => $request->pemohon_name,
                        'email'    => 'user_' . time() . rand(10, 99) . '@brmp.com',
                        'password' => Hash::make('password123'),
                        'role'     => 'user',
                    ]
                );
            }

            $requestCode = 'REQ-' . date('Ymd') . '-' . rand(100, 999);

            $sampleRequest = SampleRequest::create([
                'user_id'         => $user->id,
                'pemohon_name'    => $user->name,
                'phone_number'    => $user->phone_number,
                'lab_type'        => $request->lab_type,
                'sample_type'     => $request->samples[0]['sample_name'] ?? 'Umum',
                'sample_quantity' => count($request->samples),
                'request_code'    => $requestCode,
                'payment_proof'   => 'Bayar di Kasir (Offline)',
                'payment_status'  => 'verified',
                'village'         => $request->village,
                'district'        => $request->district,
                'regency'         => $request->regency,
                'province'        => $request->province,
                'testing_purpose' => $request->testing_purpose,
                'total_price'     => 0,
                'status'          => 1,
            ]);

            $grandTotal = 0;

            foreach ($request->samples as $index => $sampleData) {
                $sample = Sample::create([
                    'sample_request_id' => $sampleRequest->id,
                    'sample_code'       => $requestCode . '-S' . ($index + 1),
                    'sample_name'       => $sampleData['sample_name'],
                    'current_status'    => 'received',
                ]);

                foreach ($sampleData['services'] as $serviceData) {
                    $labServiceId = is_array($serviceData) ? $serviceData['lab_service_id'] : $serviceData;
                    $quantity     = is_array($serviceData) ? ($serviceData['quantity'] ?? 1) : 1;

                    $labService = LabService::findOrFail($labServiceId);
                    $price = $labService->price;

                    RequestServiceItem::create([
                        'sample_id'      => $sampleRequest->id,
                        'lab_service_id' => $labService->id,
                        'quantity'       => $quantity,
                        'price_at_time'  => $price,
                    ]);

                    $grandTotal += ($price * $quantity);
                }
            }

            $sampleRequest->update(['total_price' => $grandTotal]);

            DB::commit();

            return redirect()->back()->with('success', 'Pendaftaran offline berhasil disimpan! Kode Request: ' . $requestCode);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }
}