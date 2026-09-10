<?php

namespace App\Http\Controllers;

use App\Models\Sample;
use App\Models\SampleLog;
use App\Models\SampleIssue;
use App\Models\SampleRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\LabService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\WhatsappService;

class DistributorLabController extends Controller
{
    // 1. Tampilkan Halaman Dashboard Distributor
    public function index()
    {
        $scannedIds = session()->get('scanned_sample_ids', []);

        if (!empty($scannedIds)) {
            $scannedSamples = DB::table('samples')
                ->leftJoin('sample_requests', 'samples.sample_request_id', '=', 'sample_requests.id')
                ->leftJoin('users', 'sample_requests.user_id', '=', 'users.id')
                ->whereIn('samples.id', $scannedIds)
                ->select(
                    'samples.id',
                    'samples.sample_code',
                    'samples.sample_name',
                    'samples.current_status',
                    'users.name as applicant_name'
                )
                ->orderBy('samples.updated_at', 'desc')
                ->get();
        } else {
            $scannedSamples = collect();
        }

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
                        'applicant_name'  => $user?->name ?? $req?->pemohon_name ?? 'Pemohon Tidak Diketahui',
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
    public function getSampleDetail($id)
    {
        try {
            $sample = Sample::with(['sampleRequest.user', 'sampleRequest.items.labService'])->find($id);

            if (!$sample) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data sampel tidak ditemukan.'
                ], 404);
            }

            $req = $sample->sampleRequest;
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

            $userPhone = $user?->phone_number ?? $user?->phone ?? $user?->no_hp ?? $req?->phone_number ?? '-';
            
            // Format lokasi lengkap
            $locationParts = array_filter([
                $req?->village ?? $req?->desa,
                $req?->district ?? $req?->kecamatan,
                $req?->regency ?? $req?->kabupaten,
                $req?->province ?? $req?->provinsi
            ]);
            $locationString = !empty($locationParts) ? implode(', ', $locationParts) : '-';

            return response()->json([
                'success' => true,
                'data' => [
                    'id'              => $sample->id,
                    'sample_code'     => $sample->sample_code ?? 'SMP-' . $sample->id,
                    'sample_name'     => $sample->sample_name ?? $req?->sample_type ?? 'Sampel Pengujian',
                    'current_status'  => $sample->current_status ?? 'registered',
                    'applicant_name'  => $user?->name ?? $req?->pemohon_name ?? 'Pemohon Tidak Diketahui',
                    'applicant_phone' => $userPhone,
                    'quantity'        => $sample->quantity ?? $req?->sample_quantity ?? 1,
                    'location_origin' => $locationString,
                    'services'        => $services
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data dari server: ' . $e->getMessage()
            ], 500);
        }
    }

    // 4. Update Status Sampel (Fix Masalah Status Tidak Berubah)
    public function updateStatus(Request $request, $id)
    {
        $statusInput = strtolower($request->input('status'));

        if (!$statusInput) {
            return response()->json(['success' => false, 'message' => 'Status tidak boleh kosong.'], 400);
        }

        $sample = Sample::find($id);
        if (!$sample) {
            return response()->json(['success' => false, 'message' => 'Sampel tidak ditemukan.'], 404);
        }

        // Update Database
        try {
            $sampleData = ['updated_at' => now()];
            if (Schema::hasColumn('samples', 'current_status')) $sampleData['current_status'] = $statusInput;
            if (Schema::hasColumn('samples', 'status')) $sampleData['status'] = $statusInput;

            if (!empty($sample->sample_request_id)) {
                DB::table('samples')->where('sample_request_id', $sample->sample_request_id)->update($sampleData);

                $requestData = ['updated_at' => now()];
                if (Schema::hasColumn('sample_requests', 'current_status')) $requestData['current_status'] = $statusInput;
                if (Schema::hasColumn('sample_requests', 'status')) $requestData['status'] = $statusInput;

                if (count($requestData) > 1) {
                    DB::table('sample_requests')->where('id', $sample->sample_request_id)->update($requestData);
                }
            } else {
                DB::table('samples')->where('id', $id)->update($sampleData);
            }
        } catch (\Exception $e) {
            \Log::error('Error update DB status: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal memperbarui status di database.'], 500);
        }

        // Kirim Notifikasi Whatsapp
        try {
            $dataWA = DB::table('samples')
                ->leftJoin('sample_requests', 'samples.sample_request_id', '=', 'sample_requests.id')
                ->where('samples.id', $id)
                ->select('samples.sample_code', 'sample_requests.request_code', 'sample_requests.user_id')
                ->first();

            if ($dataWA && !empty($dataWA->user_id)) {
                $user = DB::table('users')->where('id', $dataWA->user_id)->first();

                if ($user) {
                    $userPhone = $user->phone_number ?? $user->phone ?? $user->no_hp ?? $user->telepon ?? $user->whatsapp ?? null;
                    $applicantName = $user->name ?? 'Pelanggan';
                    $sampleCode = !empty($dataWA->sample_code) ? $dataWA->sample_code : ($dataWA->request_code ?? ('SMP-' . $id));

                    if (!empty($userPhone)) {
                        $statusLabels = [
                            'pending'     => 'Menunggu Verifikasi',
                            'registered'  => 'Terdaftar',
                            'received'    => 'Diterima di Lab',
                            'diterima'    => 'Diterima di Lab',
                            'in_progress' => 'Sedang Dalam Proses Pengujian',
                            'sedang_diuji'=> 'Sedang Dalam Proses Pengujian',
                            'completed'   => 'Selesai (LHU Siap)',
                            'selesai'     => 'Selesai (LHU Siap)',
                            'issue'       => 'Ada Kendala / Issue',
                            'ditolak'     => 'Ditolak',
                        ];

                        $readableStatus = $statusLabels[$statusInput] ?? ucfirst(str_replace('_', ' ', $statusInput));

                        $pesanWA  = "Halo, *{$applicantName}*!\n\n";
                        $pesanWA .= "Pemberitahuan pembaruan status sampel Anda:\n";
                        $pesanWA .= "*Kode Sampel:* {$sampleCode}\n";
                        $pesanWA .= "*Status Terbaru:* *{$readableStatus}*\n\n";

                        if (in_array($statusInput, ['completed', 'selesai'])) {
                            $pesanWA .= "Laporan Hasil Uji (LHU) sampel Anda telah selesai. Silakan cek dashboard aplikasi untuk mengunduh hasil.\n\n";
                        } elseif (in_array($statusInput, ['received', 'diterima', 'in_progress', 'sedang_diuji'])) {
                            $pesanWA .= "Sampel Anda saat ini sedang dalam proses pengujian di laboratorium kami.\n\n";
                        }

                        $pesanWA .= "Terima kasih telah menggunakan layanan laboratorium kami. 🙏\n_Pesan ini dikirim otomatis oleh sistem._";

                        WhatsappService::sendMessage($userPhone, $pesanWA);
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error('Gagal kirim WA di DistributorLabController: ' . $e->getMessage());
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
        $keyword = trim($request->input('keyword'));

        if (!$keyword) {
            return response()->json(['success' => false, 'message' => 'Kode sampel/request kosong.'], 400);
        }

        $requestColumns = Schema::getColumnListing('sample_requests');

        $sample = DB::table('samples')
            ->leftJoin('sample_requests', 'samples.sample_request_id', '=', 'sample_requests.id')
            ->leftJoin('users', 'sample_requests.user_id', '=', 'users.id')
            ->where(function($q) use ($keyword, $requestColumns) {
                $q->where('samples.sample_code', $keyword)
                ->orWhere('samples.sample_code', 'LIKE', '%' . $keyword . '%')
                ->orWhere('samples.sample_request_id', $keyword);

                foreach ($requestColumns as $col) {
                    $q->orWhere('sample_requests.' . $col, $keyword)
                    ->orWhere('sample_requests.' . $col, 'LIKE', '%' . $keyword . '%');
                }
            })
            ->orderByRaw("CASE WHEN LOWER(samples.current_status) IN ('registered', 'pending') THEN 1 ELSE 2 END")
            ->orderBy('samples.id', 'asc')
            ->select(
                'samples.id',
                'samples.sample_code',
                'samples.sample_name',
                'samples.current_status',
                'users.name as applicant_name'
            )
            ->first();

        if (!$sample) {
            return response()->json([
                'success' => false,
                'message' => 'Data untuk "' . $keyword . '" tidak terhubung ke sampel manapun di database!'
            ], 404);
        }

        if (in_array(strtolower($sample->current_status), ['registered', 'pending'])) {
            DB::table('samples')->where('id', $sample->id)->update([
                'current_status' => 'received',
                'updated_at' => now()
            ]);
            $sample->current_status = 'received';
        }

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
    }
}