<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LabService; // Sesuaikan dengan nama Model Parameter/Layanan Anda

class PublicSampleController extends Controller
{
    public function getParameters(Request $request)
    {
        $labType = $request->query('lab');
        
        // Ambil data parameter sesuai Kategori Lab
        $services = Service::where('lab_type', $labType)->get();

        return response()->json([
            'status' => 'success',
            'data'   => $services
        ]);
    }
}