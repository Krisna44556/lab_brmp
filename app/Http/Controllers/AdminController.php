<?php

namespace App\Http\Controllers;

use App\Models\SampleRequest;
use App\Models\Sample;
use App\Models\User;
use App\Models\LabService;
use App\Models\RequestServiceItem;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http; // 1. Tambahkan ini untuk panggil Fonnte API
use Illuminate\Support\Facades\Schema;
use SimpleSoftwareIO\QrCode\Facades\QrCode;


class AdminController extends Controller
{
    // 1. Halaman Utama Dashboard Admin
    public function index(Request $request)
    {
        // 1. Inisialisasi Query SampleRequest
        $query = SampleRequest::with(['user', 'samples']);

        // 2. Filter berdasarkan Kode Request / Kode Sampel / Nama Pemohon jika ada pencarian
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

        // 3. Paginate 10 data & pertahankan query string di URL
        $requests = $query->latest()->paginate(5)->appends($request->query());

        // 4. Hitung Stat Counter
        $pendingCount = SampleRequest::where('payment_status', 'pending')->count();
        $verifiedCount = SampleRequest::where('payment_status', 'verified')->count();

        return view('admin.dashboard', compact('pendingCount', 'verifiedCount', 'requests'));
    }

    // 2. Step 1: Form Pilih Laboratorium (Biologi, Kimia, Tanah)
    public function selectLab()
    {
        return view('admin.select-lab');
    }

    // 3. Step 2: Form Input Parameter & Data Pemohon berdasarkan Lab yang Dipilih
    public function create(Request $request)
    {
        $labType = $request->query('lab_type', 'tanah');
        
        if (!$labType) {
            return redirect()->route('admin.request.create.step1');
        }

        $services = LabService::whereRaw('LOWER(lab_category) LIKE ?', ["%{$labType}%"])->get();

        return view('admin.create', compact('labType', 'services'));
    }

    // 4. Simpan Data Permohonan (POST Step 2)
    public function storeRequest(Request $request)
    {
        // Validasi Input Form
        $request->validate([
            'lab_type'        => 'required|string',
            'applicant_name'  => 'required|string|max:255',
            'phone_number'    => 'required|string|max:20',
            'sample_type'     => 'required|string',
            'sample_quantity' => 'required|integer|min:1',
            'services'        => 'required|array|min:1',
        ], [
            'services.required' => 'Pilih minimal satu parameter pengujian!',
        ]);

        $createdRequestCode = null;

        DB::transaction(function () use ($request, &$createdRequestCode) {
            // Simpan / Cari User Pemohon
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
            
            // Hitung Total Biaya
            $selectedServices = LabService::whereIn('id', $request->services)->get();
            $totalPrice = $selectedServices->sum('price') * $request->sample_quantity;

            // Simpan Header Permohonan Pengujian
            $sampleRequest = SampleRequest::create([
                'user_id'         => $user->id,
                'request_code'    => 'REQ-' . date('Ymd') . '-' . rand(100, 999),
                'sample_type'     => $request->sample_type,
                'sample_quantity' => $request->sample_quantity,
                'village'         => $request->village,
                'district'        => $request->district,
                'regency'         => $request->regency,
                'province'        => $request->province,
                'testing_purpose' => $request->testing_purpose,
                'total_price'     => $totalPrice,
                'payment_status'  => 'verified',
            ]);

            $createdRequestCode = $sampleRequest->request_code;

            // Simpan Item Layanan
            foreach ($selectedServices as $service) {
                RequestServiceItem::create([
                    'sample_request_id' => $sampleRequest->id,
                    'lab_service_id'    => $service->id,
                    'quantity'          => $request->sample_quantity,
                    'price_at_time'     => $service->price,
                ]);
            }

            // Generate Sampel Fisik
            for ($i = 1; $i <= $request->sample_quantity; $i++) {
                Sample::create([
                    'sample_request_id' => $sampleRequest->id,
                    'sample_code'       => 'SMP-' . date('Ymd') . '-' . rand(1000, 9999),
                    'sample_name'       => 'Sampel ' . $request->sample_type . ' #' . $i,
                    'status'            => 'Diterima Distributor',
                ]);
            }
        });

        // 2. KIRIM WHATSAPP OTOMATIS VIA FONNTE
        try {
            $message = "Halo *" . $request->applicant_name . "*,\n\n";
            $message .= "Permohonan pengujian sampel Anda telah *BERHASIL TERDAFTAR* di Admin BRMP Laboratorium.\n\n";
            $message .= "📌 *Detail Permohonan:*\n";
            $message .= "• Kode Permohonan: *" . $createdRequestCode . "*\n";
            $message .= "• Jenis Sampel: " . $request->sample_type . "\n";
            $message .= "• Jumlah Sampel: " . $request->sample_quantity . "\n\n";
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
            \Log::error('Fonnte WA Error saat storeRequest: ' . $e->getMessage());
        }

        return redirect()->route('admin.dashboard')->with('success', 'Permohonan Pengujian Berhasil Disimpan & WA Notifikasi Terkirim!');
    }

    // 5. Detail Request
    public function showRequest($id)
    {
        $sampleRequest = SampleRequest::with(['user', 'items.labService'])->findOrFail($id);
        return view('admin.request_show', compact('sampleRequest'));
    }
    
    // 6. Generate QR Code
    public function generateQrCode($id)
    {
        $sampleRequest = SampleRequest::with(['user', 'items.labService', 'samples'])->findOrFail($id);
        $sample = $sampleRequest->samples->first();
        $qrCodeData = $sample?->sample_code ?? $sampleRequest->request_code;

        return view('admin.qr_code', compact('sampleRequest', 'sample', 'qrCodeData'));
    }

    // 7. Verifikasi Pembayaran
    public function verifyPayment($id, $status)
    {
        $requestData = SampleRequest::findOrFail($id);
        $requestData->update(['payment_status' => $status]);

        if ($status === 'verified') {
            Sample::create([
                'sample_request_id' => $requestData->id,
                'sample_code'       => 'SMPL-' . date('Ymd') . '-' . rand(100, 999),
                'sample_name'       => 'Sampel Pengujian ' . $requestData->request_code,
                'current_status'    => 'registered',
            ]);
        }

        return redirect()->back()->with('success', 'Status pembayaran berhasil diperbarui!');
    }

    // 8. Update Status Progres Sampel
    public function updateStatus(Request $request, $id)
    {
        $statusInput = strtolower(str_replace(' ', '_', trim($request->status)));
        $sample = Sample::with(['sampleRequest.user'])->find($id);

        if (!$sample) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Data sampel tidak ditemukan.'], 404);
            }
            return redirect()->back()->with('error', 'Data sampel tidak ditemukan.');
        }

        $sampleData = ['updated_at' => now()];
        if (Schema::hasColumn('samples', 'current_status')) {
            $sampleData['current_status'] = $statusInput;
        }
        if (Schema::hasColumn('samples', 'status')) {
            $sampleData['status'] = $statusInput;
        }

        if ($sample->sample_request_id) {
            DB::table('samples')
                ->where('sample_request_id', $sample->sample_request_id)
                ->update($sampleData);

            $requestData = ['updated_at' => now()];
            if (Schema::hasColumn('sample_requests', 'current_status')) {
                $requestData['current_status'] = $statusInput;
            }
            if (Schema::hasColumn('sample_requests', 'status')) {
                $requestData['status'] = $statusInput;
            }

            if (count($requestData) > 1) {
                DB::table('sample_requests')
                    ->where('id', $sample->sample_request_id)
                    ->update($requestData);
            }
        } else {
            DB::table('samples')->where('id', $id)->update($sampleData);
        }

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

            $rawStatus = $sample?->current_status 
                    ?? $sample?->status 
                    ?? $req->current_status 
                    ?? $req->status 
                    ?? 'pending';

            return [
                'id'     => $req->id,
                'status' => strtolower(str_replace(' ', '_', trim($rawStatus)))
            ];
        });

        return response()->json($data);
    }

    public function getStatusRealtime()
    {
        return $this->statusRealtime();
    }
}