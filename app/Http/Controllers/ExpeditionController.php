<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ExpeditionController extends Controller
{
    // 1. Fetch Provinsi untuk Dropdown
    public function getProvinces()
    {
        try {
            $response = Http::withHeaders([
                'key' => env('RAJAONGKIR_API_KEY')
            ])->get('https://rajaongkir.komerce.id/api/v1/destination/province');

            return response()->json($response->json()['data'] ?? $response->json());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // 2. Fetch Kota berdasarkan ID Provinsi
    public function getCities($provinceId)
    {
        try {
            $response = Http::withHeaders([
                'key' => env('RAJAONGKIR_API_KEY')
            ])->get("https://rajaongkir.komerce.id/api/v1/destination/city/{$provinceId}");

            return response()->json($response->json()['data'] ?? $response->json());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // 3. Hitung Estimasi Ongkir
    public function checkCost(Request $request)
    {
        try {
            $response = Http::withHeaders([
                'key' => env('RAJAONGKIR_API_KEY')
            ])->post('https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost', [
                'destination' => $request->destination,
                'weight' => $request->weight,
                'courier' => $request->courier
            ]);

            return response()->json($response->json()['data'] ?? $response->json());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}