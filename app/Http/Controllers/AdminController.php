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
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class AdminController extends Controller
{
    // 1. Halaman Utama Dashboard Admin
    public function index()
    {
        $requests = SampleRequest::with(['user', 'samples'])->latest()->get();
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
        
        // Jika parameter lab_type kosong, kembalikan ke Step 1
        if (!$labType) {
            return redirect()->route('admin.request.create.step1');
        }

        // Ambil daftar layanan yang terfilter sesuai kategori laboratorium yang dipilih
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

        DB::transaction(function () use ($request) {
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

            // Hitung Total Biaya Berdasarkan Layanan Terpilih & Jumlah Sampel
            $selectedServices = LabService::whereIn('id', $request->services)->get();
            $totalPrice = $selectedServices->sum('price') * $request->sample_quantity;

            // Simpan Header Permohonan Pengujian (SampleRequest)
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

            // Simpan Item Layanan/Parameter Pengujian
            foreach ($selectedServices as $service) {
                RequestServiceItem::create([
                    'sample_request_id' => $sampleRequest->id,
                    'lab_service_id'    => $service->id,
                    'quantity'          => $request->sample_quantity,
                    'price_at_time'     => $service->price,
                ]);
            }

            // Generate Sampel Fisik Sesuai Jumlah Sampel
            for ($i = 1; $i <= $request->sample_quantity; $i++) {
                Sample::create([
                    'sample_request_id' => $sampleRequest->id,
                    'sample_code'       => 'SMP-' . date('Ymd') . '-' . rand(1000, 9999),
                    'sample_name'       => 'Sampel ' . $request->sample_type . ' #' . $i,
                    'status'            => 'Diterima Distributor',
                ]);
            }
        });

        return redirect()->route('admin.dashboard')->with('success', 'Permohonan Pengujian Berhasil Disimpan!');
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
        // Load permohonan beserta relasi user, items, dan samples
        $sampleRequest = SampleRequest::with(['user', 'items.labService', 'samples'])->findOrFail($id);
        
        // Ambil sampel pertama (jika ada)
        $sample = $sampleRequest->samples->first();

        // Utamakan kode sampel unik (misal: SMP-XXXX), jika tidak ada pakai request_code
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
        // 1. Format input status
        $statusInput = strtolower(str_replace(' ', '_', trim($request->status)));

        // 2. Cari sampel menggunakan Model Eloquent
        $sample = Sample::with(['sampleRequest.user'])->find($id);

        if (!$sample) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Data sampel tidak ditemukan.'], 404);
            }
            return redirect()->back()->with('error', 'Data sampel tidak ditemukan.');
        }

        // 3. Update status sampel & request di database
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

        // 4. Proses Notifikasi WhatsApp via Service
        $dataWA = DB::table('samples')
        ->leftJoin('sample_requests', 'samples.sample_request_id', '=', 'sample_requests.id')
        ->leftJoin('users', 'sample_requests.user_id', '=', 'users.id')
        ->where('samples.id', $id)
        ->select(
            'samples.id as sample_id',
            'samples.sample_code',
            'sample_requests.id as req_id',
            'sample_requests.phone_number as req_phone',
            'users.id as user_id',
            'users.phone as user_phone'
        )
        ->first();

    // Hentikan proses & tampilkan isi variabel di layar browser
    dd([
        'sample_id_yang_diupdate' => $id,
        'data_di_database'        => $dataWA,
        'nomor_terdeteksi'        => $dataWA?->req_phone ?? $dataWA?->user_phone ?? 'TIDAK DITEMUKAN (NULL)'
    ]);
    
        // 5. Response
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
        // Ambil data request beserta relasi samples
        $requests = SampleRequest::with('samples')->get();

        $data = $requests->map(function ($req) {
            // Ambil sampel pertama
            $sample = $req->samples->first();

            // Utamakan current_status dari sampel, lalu dari request
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