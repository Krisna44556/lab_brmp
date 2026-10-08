<?php

namespace App\Http\Controllers;

use App\Models\Sample;
use App\Models\SampleLog;
use App\Models\SampleIssue;
use App\Models\SampleRequest;
use App\Models\LabService;
use App\Services\WhatsappService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\LengthAwarePaginator;

class DistributorLabController extends Controller
{

    public function index(Request $request)
{
    // 1. Kelola Keyword Pencarian di Session
    if ($request->isMethod('post')) {
        $searchCode = trim($request->input('code'));
        session(['search_sample_code' => $searchCode]);
    } else {
        if ($request->has('reset')) {
            session()->forget('search_sample_code');
            $searchCode = null;
        } else {
            $searchCode = session('search_sample_code');
        }
    }

    // 2. ID Sampel yang Pernah Di-scan di Session
    $scannedIds = session()->get('scanned_sample_ids', []);

    // 3. Query Utama Per Wadah Fisik (`samples.id`)
    $query = DB::table('samples')
        ->join('sample_requests', 'samples.sample_request_id', '=', 'sample_requests.id')
        ->leftJoin('users', 'sample_requests.user_id', '=', 'users.id')
        ->select(
            'samples.id',
            'samples.sample_code',
            'samples.sample_name',
            'samples.current_status',
            'samples.created_at',
            'sample_requests.id as request_id',
            'sample_requests.request_code',

            // Jenis Sampel (Tarik dari request jika sampel fisik kosong)
            DB::raw('COALESCE(NULLIF(sample_requests.sample_type, ""), NULLIF(samples.sample_name, ""), "Sampel Lab") as sample_type_name'),

            // Nama Pemohon
            DB::raw('COALESCE(
                NULLIF(sample_requests.pemohon_name, ""), 
                NULLIF(users.name, ""), 
                "Pemohon Online / Umum"
            ) as applicant_name'),

            // Alamat / Wilayah Lengkap (CONCAT_WS)
            DB::raw("COALESCE(
                NULLIF(
                    TRIM(
                        CONCAT_WS(', ', 
                            NULLIF(sample_requests.village, ''), 
                            NULLIF(sample_requests.district, ''), 
                            NULLIF(sample_requests.regency, ''), 
                            NULLIF(sample_requests.province, '')
                        )
                    ), ''
                ), 
                '-'
            ) as origin_location"),

            // Total wadah dalam 1 permohonan header (untuk badge "+X wadah lain")
            DB::raw('(SELECT COUNT(*) FROM samples s2 WHERE s2.sample_request_id = sample_requests.id) as total_samples_in_request')
        )
        // FILTER: Tampilkan status aktif ATAU hasil scan
        ->where(function($q) use ($scannedIds) {$q->whereIn('samples.current_status', ['received', 'diterima', 'in_progress', 'completed', 'issue'])
              ->orWhereIn('samples.id', $scannedIds);
        });

    // 4. Filter Pencarian Teks
    if ($request->filled('code') || $request->filled('search') || $searchCode) {
        $search = trim($request->input('code', $request->input('search',$searchCode)));

        $query->where(function ($q) use ($search) {
            $q->where('samples.sample_code', 'LIKE', "\%{$search}%")
              ->orWhere('samples.sample_name', 'LIKE', "%{$search}%")
              ->orWhere('sample_requests.request_code', 'LIKE', "%{$search}%")
              ->orWhere('sample_requests.sample_type', 'LIKE', "%{$search}%")
              ->orWhere('sample_requests.pemohon_name', 'LIKE', "%{$search}%")
              ->orWhere('users.name', 'LIKE', "%{$search}%");
        });
    }

    // 5. Pagination Per Wadah Sampel
    $scannedSamples =$query->orderBy('samples.id', 'desc')
                            ->paginate(10);

    // 6. Return View
    return view('distributor_lab.dashboard', compact('scannedSamples'));
    }
 

            // 2. Detail Sampel berdasarkan Kode Hasil Scan / Search
            public function showByCode($code)
            {
                try {
                    $code = trim($code);

                    $sample = Sample::with(['sampleRequest.user', 'sampleRequest.items.labService'])
                        ->where('sample_code', $code)
                        ->orWhere('id', $code)
                        ->orWhereHas('sampleRequest', function ($q) use ($code) {
                            $q->where('request_code', $code);
                        })
                        ->first();

                    if (!$sample) {
                        $sampleRequest = SampleRequest::with(['user', 'samples', 'items.labService'])
                            ->where('request_code', $code)
                            ->orWhere('id', $code)
                            ->first();

                        if ($sampleRequest && $sampleRequest->samples && $sampleRequest->samples->count() > 0) {
                            $sample = $sampleRequest->samples->first();
                            $sample->setRelation('sampleRequest', $sampleRequest);
                        }
                    }

                    if (!$sample) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Data sampel dengan kode "' . $code . '" tidak ditemukan!'
                        ], 404);
                    }

                    $req  = $sample->sampleRequest;
                    $user = $req?->user;

                    $services = [];
                    if ($req && $req->items) {
                        foreach ($req->items as $item) {
                            if ($item->labService) {
                                $services[] = [
                                    'service_name' => $item->labService->name ?? $item->labService->service_name ?? 'Parameter Pengujian'
                                ];
                            }
                        }
                    }

                    return response()->json([
                        'success' => true,
                        'data' => [
                            'sample' => [
                                'id'             => $sample->id,
                                'sample_code'    => $sample->sample_code ?? $req?->request_code ?? ('SMP-' . $sample->id),
                                'sample_name'    => $sample->sample_name ?? $sample->jenis_sampel ?? $req?->sample_type ?? 'Sampel Pengujian',
                                'current_status' => $sample->current_status ?? $sample->status ?? 'registered',
                            ],
                            'request' => [
                                'pemohon_name'  => $user?->name ?? $req?->pemohon_name ?? 'Pemohon Tidak Diketahui',
                                'phone_number'    => $user?->phone_number ?? $user?->no_hp ?? $user?->phone ?? $req?->phone_number ?? '-',
                                'lab_type'        => strtoupper($req?->lab_type ?? $req?->lab_category ?? 'LAB'),
                                'sample_quantity' => $sample->quantity ?? $req?->sample_quantity ?? 1,
                                'village'         => $req?->village ?? $req?->desa ?? '',
                                'district'        => $req?->district ?? $req?->kecamatan ?? '',
                                'regency'         => $req?->regency ?? $req?->kabupaten ?? '',
                                'province'        => $req?->province ?? $req?->provinsi ?? '',
                            ],
                            'services' => $services
                        ]
                    ]);

                } catch (\Exception $e) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Terjadi kesalahan pada server: ' . $e->getMessage()
                    ], 500);
                }
            }

            // 3. Detail Sampel berdasarkan Primary Key ID (Fix Error "Gagal Memuat Server")
            public function getDetail($id)
            {
                try {
                    // 1. Deteksi otomatis kolom telepon
                    $phoneColumn = "' - '";
                    if (Schema::hasColumn('sample_requests', 'pemohon_phone')) {
                        $phoneColumn = "NULLIF(sample_requests.pemohon_phone, '')";
                    } elseif (Schema::hasColumn('sample_requests', 'phone_number')) {
                        $phoneColumn = "NULLIF(sample_requests.phone_number, '')";
                    } elseif (Schema::hasColumn('sample_requests', 'phone')) {
                        $phoneColumn = "NULLIF(sample_requests.phone, '')";
                    } elseif (Schema::hasColumn('users', 'phone')) {
                        $phoneColumn = "NULLIF(users.phone, '')";
                    } elseif (Schema::hasColumn('users', 'telepon')) {
                        $phoneColumn = "NULLIF(users.telepon, '')";
                    }

                    // 2. Query Detail Sampel
                    $sample = DB::table('samples')
                        ->join('sample_requests', 'samples.sample_request_id', '=', 'sample_requests.id')
                        ->leftJoin('users', 'sample_requests.user_id', '=', 'users.id')
                        ->where('samples.id', $id)
                        ->select(
                            'samples.id',
                            'samples.sample_code',
                            'samples.sample_name',
                            'samples.current_status',
                            'sample_requests.id as sample_request_id',
                            'sample_requests.request_code',
                            'sample_requests.sample_type',
                            'sample_requests.created_at as request_date',
                            
                            // Nama Pemohon
                            DB::raw("COALESCE(NULLIF(sample_requests.pemohon_name, ''), NULLIF(users.name, ''), 'Pemohon') as applicant_name"),
                            
                            // Telepon / WA
                            DB::raw("COALESCE({$phoneColumn}, '-') as applicant_phone"),

                            // Jenis Sampel (Mengambil dari sample_requests.sample_type atau samples.sample_name)
                            DB::raw("COALESCE(NULLIF(sample_requests.sample_type, ''), NULLIF(samples.sample_name, ''), 'Sampel Lab') as sample_type_name"),

                            // Alamat Lengkap
                            DB::raw("COALESCE(
                                NULLIF(
                                    TRIM(
                                        CONCAT_WS(', ', 
                                            NULLIF(sample_requests.village, ''), 
                                            NULLIF(sample_requests.district, ''), 
                                            NULLIF(sample_requests.regency, ''), 
                                            NULLIF(sample_requests.province, '')
                                        )
                                    ), ''
                                ), 
                                '-'
                            ) as origin_location"),

                            // Total Wadah
                            DB::raw("(SELECT COUNT(*) FROM samples s2 WHERE s2.sample_request_id = sample_requests.id) as total_quantity")
                        )
                        ->first();

                    if (!$sample) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Data sampel tidak ditemukan.'
                        ], 404);
                    }

                    // 3. Ambil Parameter Pengujian (Cek via sample_id ATAU join via samples)
            // 3. Ambil Parameter Pengujian via Relasi Permohonan Induk (sample_request_id)
            // Ambil data asli dari database
            // 3. Ambil Parameter Pengujian berdasarkan sample_request_id
            $services = DB::table('request_service_items')
                ->join('lab_services', 'request_service_items.lab_service_id', '=', 'lab_services.id')
                ->where('request_service_items.sample_id', $sample->sample_request_id)
                ->select(
                    'lab_services.service_name',
                    'request_service_items.price_at_time'
                )
                ->get();
                
            // Fallback: Jika ternyata sample_id terdaftar langsung
            if ($services->isEmpty()) {
                $services = DB::table('request_service_items')
                    ->join('lab_services', 'request_service_items.lab_service_id', '=', 'lab_services.id')
                    ->where('request_service_items.sample_id', $id)
                    ->select(
                        'lab_services.service_name',
                        'request_service_items.price_at_time'
                    )
                    ->get();
            }

        return response()->json([
            'success'  => true,
            'sample'   => $sample,
            'services' => $services
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Database Error: ' . $e->getMessage()
        ], 500);
    }
}   




            
            // 4. Update Status Sampel (Fix Masalah Status Tidak Berubah)
            public function updateStatus(Request $request,$id)
            {
                $statusInput = strtolower($request->input('status'));

                if (!$statusInput) {
                    return response()->json(['success' => false, 'message' => 'Status tidak boleh kosong.'], 400);
                }

                // 1. Cari sampel (Cek berdasarkan ID di tabel samples)
                $sample = \DB::table('samples')->where('id',$id)->first();
                if (!$sample) {
                    $sample = \DB::table('samples')->where('sample_request_id',$id)->first();
                }

                if (!$sample) {
                    return response()->json(['success' => false, 'message' => 'Sampel tidak ditemukan.'], 404);
                }

                // 2. Update Database
            // 2. Update Database (Aman untuk Samples & Sample Requests)
            try {
                // 1. Update tabel samples (selalu pakai ENUM current_status)
                \DB::table('samples')->where('id', $sample->id)->update([
                    'current_status' => $statusInput,
                    'updated_at'     => now(),
                ]);

                // 2. Update tabel sample_requests (Sinkronisasi ke Dashboard Admin)
                if (!empty($sample->sample_request_id)) {
                    
                    // Cek apakah tabel sample_requests punya kolom current_status atau status
                    if (\Illuminate\Support\Facades\Schema::hasColumn('sample_requests', 'current_status')) {
                        // Jika kolomnya current_status (ENUM Teks)
                        \DB::table('sample_requests')->where('id', $sample->sample_request_id)->update([
                            'current_status' => $statusInput,
                            'updated_at'     => now(),
                        ]);
                    } elseif (\Illuminate\Support\Facades\Schema::hasColumn('sample_requests', 'status')) {
                        // Jika kolomnya status (Integer / ID Status)
                        $statusToIdMap = [
                            'pending'     => 1,
                            'registered'  => 2,
                            'received'    => 3,
                            'diterima'    => 3,
                            'in_progress' => 4,
                            'sedang_diuji'=> 4,
                            'completed'   => 5,
                            'selesai'     => 5,
                            'issue'       => 6,
                            'ditolak'     => 7,
                        ];

                        $statusId = $statusToIdMap[$statusInput] ?? 1;

                        \DB::table('sample_requests')->where('id', $sample->sample_request_id)->update([
                            'status'     => $statusId,
                            'updated_at' => now(),
                        ]);
                    }
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Error update DB status: ' . $e->getMessage());
                return response()->json([
                    'success' => false, 
                    'message' => 'Gagal DB: ' . $e->getMessage()
                ], 500);
            }

                // 3. Kirim Notifikasi Whatsapp
                try {
                    $dataWA = \DB::table('samples')
                        ->leftJoin('sample_requests', 'samples.sample_request_id', '=', 'sample_requests.id')
                        ->where('samples.id', $sample->id)
                        ->select('samples.sample_code', 'sample_requests.request_code', 'sample_requests.user_id')
                        ->first();

                    if ($dataWA && !empty($dataWA->user_id)) {
                        $user = \DB::table('users')->where('id',$dataWA->user_id)->first();

                        if ($user) {
                            $userPhone =$user->phone_number ?? $user->phone ?? $user->no_hp ?? $user->telepon ?? $user->whatsapp ?? null;
                            $applicantName =$user->name ?? 'Pelanggan';
                            $sampleCode = !empty($dataWA->sample_code) ?$dataWA->sample_code : ($dataWA->request_code ?? ('SMP-' . $sample->id));

                            if (!empty($userPhone)) {$statusLabels = [
                                    'pending'      => 'Menunggu Verifikasi',
                                    'registered'   => 'Terdaftar',
                                    'received'     => 'Diterima di Lab',
                                    'diterima'     => 'Diterima di Lab',
                                    'in_progress'  => 'Sedang Dalam Proses Pengujian',
                                    'sedang_diuji' => 'Sedang Dalam Proses Pengujian',
                                    'completed'    => 'Selesai (LHU Siap)',
                                    'selesai'      => 'Selesai (LHU Siap)',
                                    'issue'        => 'Ada Kendala / Issue',
                                    'ditolak'      => 'Ditolak',
                                ];

                                $readableStatus =$statusLabels[$statusInput] ?? ucfirst(str_replace('_', ' ', $statusInput));
                                $trackingUrl = url('/tracking?code=' . $sampleCode);

                                $pesanWA  = "Halo, *{$applicantName}*!\n\n";
                                $pesanWA .= "Pemberitahuan pembaruan status sampel Anda:\n";
                                $pesanWA .= "*Kode Sampel:* `{$sampleCode}`\n";
                                $pesanWA .= "*Status Terbaru:* *{$readableStatus}*\n\n";

                                if (in_array($statusInput, ['completed', 'selesai'])) {$pesanWA .= "Laporan Hasil Uji (LHU) sampel Anda telah selesai. Silakan cek/unduh hasil melalui link di bawah ini:\n";
                                } elseif (in_array($statusInput, ['received', 'diterima', 'in_progress', 'sedang_diuji'])) {$pesanWA .= "Sampel Anda saat ini sedang dalam proses pengujian di laboratorium kami.\n";
                                }

                                $pesanWA .= "\n🔍 *Lacak/Cek Detail Sampel Anda:* \n{$trackingUrl}\n\n";
                                $pesanWA .= "Terima kasih telah menggunakan layanan laboratorium kami. 🙏\n_Pesan ini dikirim otomatis oleh sistem._";

                                if (class_exists(\App\Services\WhatsappService::class)) {
                                    \App\Services\WhatsappService::sendMessage($userPhone,$pesanWA);
                                }
                            }
                        }
                    }
                } catch (\Exception $e) {         \Illuminate\Support\Facades\Log::error('Gagal kirim WA di DistributorLabController: ' .$e->getMessage());
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Status sampel berhasil diperbarui!'
                ]);
            }

            // 5. Laporkan Kendala Sampel
            public function reportIssue(Request $request, $id)
            {
                $request->validate([
                    'issue_type'  => 'required|string',
                    'description' => 'required|string',
                ]);

                $sample = Sample::findOrFail($id);
                
                $sample->update([
                    'current_status' => 'issue'
                ]);

                SampleIssue::create([
                    'sample_id'      => $sample->id,
                    'distributor_id' => Auth::id(),
                    'issue_type'     => $request->issue_type,
                    'description'    => $request->description,
                ]);

                return redirect()->back()->with('success', 'Laporan kendala sampel berhasil dicatat!');
            }

            // 6. Pencarian Sampel
            public function searchSample(Request $request)
            {
                $code = trim($request->code);

                $sample = Sample::with(['sampleRequest.user'])
                    ->where('sample_code', $code)
                    ->orWhere('id', $code)
                    ->orWhere('sample_request_id', $code)
                    ->first();

                if (!$sample) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Kode sampel "' . $code . '" tidak ditemukan di database!'
                    ], 404);
                }

                return response()->json([
                    'success' => true,
                    'data'    => $sample
                ]);
            }

            // 7. Proses Scan
            public function processScan(Request $request)
            {
                try {
                    $keyword = trim($request->input('keyword'));

                    if (!$keyword) {
                        return response()->json(['success' => false, 'message' => 'Kode sampel/request kosong.'], 400);
                    }

                    // 1. UTAMAKAN Pencarian Tepat (Exact Match) Berdasarkan Kode Sampel / Kode Request
                    $sample = DB::table('samples')
                        ->leftJoin('sample_requests', 'samples.sample_request_id', '=', 'sample_requests.id')
                        ->leftJoin('users', 'sample_requests.user_id', '=', 'users.id')
                        ->where('samples.sample_code', $keyword)
                        ->orWhere('sample_requests.request_code', $keyword)
                        ->select(
                            'samples.id',   
                            'samples.sample_code',
                            'samples.sample_name',
                            'samples.current_status',
                            // Ambil pemohon_name dari sample_requests, jika null gunakan users.name atau fallback 'Pemohon'
                            DB::raw("COALESCE(sample_requests.pemohon_name, users.name, 'Pemohon') as applicant_name")
                        )
                        ->first();

                    // 2. JIKA TIDAK KETEMU, Lakukan Pencarian LIKE Khusus Kode Sampel / Request
                    if (!$sample) {
                        $sample = DB::table('samples')
                            ->leftJoin('sample_requests', 'samples.sample_request_id', '=', 'sample_requests.id')
                            ->leftJoin('users', 'sample_requests.user_id', '=', 'users.id')
                            ->where('samples.sample_code', 'LIKE', '%' . $keyword . '%')
                            ->orWhere('sample_requests.request_code', 'LIKE', '%' . $keyword . '%')
                            ->select(
                                'samples.id',   
                                'samples.sample_code',
                                'samples.sample_name',
                                'samples.current_status',
                                DB::raw("COALESCE(sample_requests.pemohon_name, users.name, 'Pemohon') as applicant_name")
                            )
                            ->first();
                    }

                    if (!$sample) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Kode sampel/request "' . $keyword . '" tidak ditemukan!'
                        ], 404);
                    }

                    // Update status sampel menjadi 'received' jika masih registered/pending
                    if (in_array(strtolower($sample->current_status), ['registered', 'pending'])) {
                        DB::table('samples')->where('id', $sample->id)->update([
                            'current_status' => 'received',
                            'updated_at'     => now()
                        ]);
                        $sample->current_status = 'received';
                    }

                    // Simpan ID ke Session
                    $scannedIds = session()->get('scanned_sample_ids', []);
                    if (!in_array($sample->id, $scannedIds)) {
                        $scannedIds[] = $sample->id;
                        session()->put('scanned_sample_ids', $scannedIds);
                    }

                    return response()->json([
                        'success' => true,
                        'message' => 'Sampel ' . $sample->sample_code . ' berhasil diproses!',
                        'sample'  => $sample
                    ]);

                } catch (\Exception $e) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Server Error: ' . $e->getMessage()
                    ], 500);
                }
            }
        }