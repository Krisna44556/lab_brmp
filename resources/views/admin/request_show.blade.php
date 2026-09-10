<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Detail Permohonan: {{ $sampleRequest->request_code }}
            </h2>
            <button onclick="window.print()" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded shadow print:hidden">
                🖨️ Cetak Tanda Terima
            </button>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-6 rounded-lg shadow space-y-6">
            
            <!-- Ringkasan Data Pemohon & Sampel -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 border-b pb-6">
                <div>
                    <h3 class="font-bold text-gray-700 text-lg mb-2">Informasi Pemohon</h3>
                    <p><span class="text-gray-500">Nama / Instansi:</span> <strong>{{ $sampleRequest->user->name ?? '-' }}</strong></p>
                    <p><span class="text-gray-500">No. WhatsApp:</span> {{ $sampleRequest->user->phone_number ?? '-' }}</p>
                    <p><span class="text-gray-500">Alamat:</span> {{ implode(', ', array_filter([$sampleRequest->village, $sampleRequest->district, $sampleRequest->regency, $sampleRequest->province])) }}</p>
                </div>
                <div>
                    <h3 class="font-bold text-gray-700 text-lg mb-2">Informasi Sampel</h3>
                    <p><span class="text-gray-500">Kode Req:</span> <strong>{{ $sampleRequest->request_code }}</strong></p>
                    <p><span class="text-gray-500">Jenis Sampel:</span> {{ $sampleRequest->sample_type }}</p>
                    <p><span class="text-gray-500">Jumlah Sampel:</span> {{ $sampleRequest->sample_quantity }} sampel</p>
                    <p><span class="text-gray-500">Status Pembayaran:</span> 
                        <span class="px-2 py-1 text-xs font-bold rounded bg-green-100 text-green-800">
                           {{ strtoupper($sampleRequest->payment_status) }}
                        </span>
                    </p>
                </div>
            </div>

            <!-- Tabel Detail Parameter Pengujian -->
            <div>
                <h3 class="font-bold text-gray-700 text-lg mb-3">Parameter Pengujian yang Dipilih</h3>
                <table class="w-full text-left border-collapse border border-gray-200">
                    <thead>
                        <tr class="bg-gray-100 border-b">
                            <th class="p-3">No</th>
                            <th class="p-3">Kategori Lab</th>
                            <th class="p-3">Nama Parameter</th>
                            <th class="p-3 text-right">Harga Satuan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sampleRequest->items as $index => $item)
                        <tr class="border-b">
                            <td class="p-3">{{ $index + 1 }}</td>
                            <td class="p-3 font-semibold text-blue-600">{{ $item->labService->lab_category ?? '-' }}</td>
                            <td class="p-3">{{ $item->labService->name ?? '-' }}</td>
                            <td class="p-3 text-right">Rp {{ number_format($item->price_at_time, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-blue-50 font-bold">
                            <td colspan="3" class="p-3 text-right">Total Est. Biaya Pengujian:</td>
                            <td class="p-3 text-right text-blue-700">Rp {{ number_format($sampleRequest->total_price, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="pt-4 print:hidden">
                <a href="{{ route('admin.dashboard') }}" class="text-gray-600 hover:underline">← Kembali ke Dashboard</a>
            </div>

        </div>
    </div>
</x-app-layout>