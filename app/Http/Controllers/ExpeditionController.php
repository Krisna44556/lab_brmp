<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\RajaOngkirService;
use Illuminate\Support\Facades\DB;

class ExpeditionController extends Controller
{
    // 1. Fetch Provinsi untuk Dropdown
    public function getProvinces()
    {
        $provinces = RajaOngkirService::getProvinces();
        return response()->json($provinces);
    }

    // 2. Fetch Kota berdasarkan ID Provinsi
    public function getCities($provinceId)
    {
        $cities = RajaOngkirService::getCities($provinceId);
        return response()->json($cities);
    }

    // 3. Hitung Estimasi Ongkir
    public function checkCost(Request $request)
    {
        $request->validate([
            'origin'      => 'required',
            'destination' => 'required',
            'weight'      => 'required|numeric',
            'courier'     => 'required'
        ]);

        $costs = RajaOngkirService::getCost(
            $request->origin,
            $request->destination,
            $request->weight,
            $request->courier
        );

        return response()->json($costs);
    }

    // 4. Simpan Nomor Resi Pengiriman Sampel (Diakses oleh Distributor/Admin)
    public function updateTracking(Request $request, $id)
    {
        $request->validate([
            'courier_code'    => 'required|string',
            'tracking_number' => 'required|string',
        ]);

        try {
            DB::table('sample_requests')
                ->where('id', $id)
                ->update([
                    'courier_code'    => strtolower($request->courier_code),
                    'tracking_number' => trim($request->tracking_number),
                    'updated_at'      => now(),
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Nomor resi & kurir berhasil diperbarui!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data resi: ' . $e->getMessage()
            ], 500);
        }
    }
}
