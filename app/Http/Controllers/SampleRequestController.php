<?php

namespace App\Http\Controllers;

use App\Models\SampleRequest;
use App\Models\Sample;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SampleRequestController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validasi Input Form FE
        $request->validate([
            'sample_type'     => 'required|string',
            'sample_quantity' => 'required|numeric|min:1',
            'lab_type'        => 'required|string',
            'village'         => 'nullable|string',
            'district'        => 'nullable|string',
            'regency'         => 'nullable|string',
            'province'        => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // 2. Generate Kode Request Unik (Misal: REQ-20260915-123)
            $requestCode = 'REQ-' . date('Ymd') . '-' . rand(100, 999);

            // 3. Simpan Ke Tabel `sample_requests`
            $sampleRequest = SampleRequest::create([
                'request_code'    => $requestCode,
                'user_id'         => Auth::id() ?? null, // Jika butuh login
                'pemohon_name'    => $request->input('pemohon_name', Auth::user()?->name),
                'phone_number'    => $request->input('phone_number', Auth::user()?->phone_number),
                'sample_type'     => $request->sample_type,
                'sample_quantity' => $request->sample_quantity,
                'lab_type'        => $request->lab_type,
                'village'         => $request->village,
                'district'        => $request->district,
                'regency'         => $request->regency,
                'province'        => $request->province,
                'payment_status'  => 'pending',
                'current_status'  => 'pending',
            ]);

            // 4. Generate Rincian Item Sampel di Tabel `samples`
            for ($i = 1; $i <= $request->sample_quantity; $i++) {
                $sampleCode = 'SMP-' . date('Ymd') . '-' . rand(1000, 9999);

                Sample::create([
                    'sample_request_id' => $sampleRequest->id,
                    'sample_code'       => $sampleCode,
                    'sample_name'       => $request->sample_type . ' #' . $i,
                    'current_status'    => 'registered',
                ]);
            }

            DB::commit();

           if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pendaftaran berhasil disimpan!',
                    'request_code' => $requestCode
                ], 200);
            }

            return redirect()->back()->with('success', 'Pendaftaran berhasil!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }
}