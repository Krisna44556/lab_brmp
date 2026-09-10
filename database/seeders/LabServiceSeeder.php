<?php

namespace Database\Seeders;

use App\Models\LabService;
use Illuminate\Database\Seeder;

class LabServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            // --- LABORATORIUM BIOLOGI ---
            [
                'lab_category'  => 'Biologi',
                'sub_category'  => 'Bakteriologi',
                'accreditation' => 'Terakreditasi',
                'service_name'  => 'E.coli pada pupuk organik',
                'method'        => 'MPN',
                'equipment'     => 'Tabung Durham',
                'unit'          => 'per sampel',
                'price'         => 300000,
            ],
            [
                'lab_category'  => 'Biologi',
                'sub_category'  => 'Bakteriologi',
                'accreditation' => 'Terakreditasi',
                'service_name'  => 'Salmonella pada pupuk organik',
                'method'        => 'MPN',
                'equipment'     => 'Cawan petri, Colony Counter',
                'unit'          => 'per sampel',
                'price'         => 300000,
            ],
            [
                'lab_category'  => 'Biologi',
                'sub_category'  => 'Bakteriologi',
                'accreditation' => 'Non Akreditasi',
                'service_name'  => 'Total mikroba aerob',
                'method'        => 'Media differensial',
                'equipment'     => 'Mikroskop',
                'unit'          => 'per sampel',
                'price'         => 150000,
            ],

            // --- BIOLOGI MOLEKULER ---
            [
                'lab_category'  => 'Biologi Molekuler',
                'sub_category'  => 'Biomol',
                'accreditation' => 'Terakreditasi',
                'service_name'  => 'Ekstraksi DNA skala kecil (Tanaman Cabai)',
                'method'        => 'CTAB',
                'equipment'     => 'Sentrifuge',
                'unit'          => 'per sampel',
                'price'         => 100000,
            ],
            [
                'lab_category'  => 'Biologi Molekuler',
                'sub_category'  => 'Biomol',
                'accreditation' => 'Non Akreditasi',
                'service_name'  => 'Amplifikasi DNA dengan PCR',
                'method'        => 'PCR',
                'equipment'     => 'PCR Thermal Cycler',
                'unit'          => 'per running',
                'price'         => 50000,
            ],

            // --- KIMIA TANAH (RUTIN/MAKRO) ---
            [
                'lab_category'  => 'Kimia Tanah',
                'sub_category'  => 'Makro',
                'accreditation' => 'Terakreditasi',
                'service_name'  => 'pH H2O dan KCl 1 M',
                'method'        => 'Peach dalam Black',
                'equipment'     => 'pH meter',
                'unit'          => 'per sampel',
                'price'         => 24000,
            ],
            [
                'lab_category'  => 'Kimia Tanah',
                'sub_category'  => 'Makro',
                'accreditation' => 'Terakreditasi',
                'service_name'  => 'N - Total',
                'method'        => 'Kjeldahl (%)',
                'equipment'     => 'Destilator',
                'unit'          => 'per sampel',
                'price'         => 30000,
            ],
        ];

        foreach ($services as $service) {
            LabService::create($service);
        }
    }
}