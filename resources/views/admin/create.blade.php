<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight ml-10">
            {{ __('Input Permohonan Pengujian ') }}
        </h2>
    </x-slot>
    <div class="flex justify-end ">
    <button class="bg-blue-600 mr-20 mt-6 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-md shadow">
        <a href="{{ route('admin.select-lab') }}" class="text-sm text-white font-medium">Kembali Pilih Lab</a>
    </button>
    </div>
    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <!-- Header & Navigasi Kembali -->
                <div class="mb-6 flex justify-between items-center border-b pb-4">
                    <div>
                        
                        <h3 class="text-xl font-bold text-gray-800 mt-1">
                            Laboratorium Terpilih
                            <span class="text-blue-600 uppercase">{{ $labType }}</span>
                        </h3>
                    </div>
                </div>

                <!-- Pesan Validation Error -->
                @if ($errors->any())
                    <div class="mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded">
                        <strong class="font-bold">Terjadi Kesalahan!</strong>
                        <ul class="mt-2 list-disc list-inside text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.request.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="lab_type" value="{{ $labType }}">

                    <!-- Informasi Pemohon -->
                    <h4 class="text-md font-semibold text-gray-700 mb-3 border-b pb-1">1. Informasi Pemohon & Sampel</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nama Pemohon *</label>
                            <input type="text" name="applicant_name" value="{{ old('applicant_name') }}" required class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nomor WhatsApp / HP *</label>
                            <input type="text" name="phone_number" value="{{ old('phone_number') }}" required placeholder="08xxxxxxxxxx" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Jenis Sampel *</label>
                            <input type="text" name="sample_type" value="{{ old('sample_type') }}" placeholder="Contoh: Tanah Regosol, Pupuk NPK, Air" required class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Jumlah Sampel (Jumlah Wadah) *</label>
                            <input type="number" id="sample_quantity" name="sample_quantity" value="{{ old('sample_quantity', 1) }}" min="1" required class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <!-- Informasi Lokasi -->
                    <h4 class="text-md font-semibold text-gray-700 mb-3 border-b pb-1">2. Alamat Asal Sampel (Opsional)</h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                        <div>
                            <label class="block text-xs text-gray-600">Desa/Kelurahan</label>
                            <input type="text" name="village" value="{{ old('village') }}" class="mt-1 w-full rounded-md border-gray-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600">Kecamatan</label>
                            <input type="text" name="district" value="{{ old('district') }}" class="mt-1 w-full rounded-md border-gray-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600">Kabupaten/Kota</label>
                            <input type="text" name="regency" value="{{ old('regency') }}" class="mt-1 w-full rounded-md border-gray-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600">Provinsi</label>
                            <input type="text" name="province" value="{{ old('province') }}" class="mt-1 w-full rounded-md border-gray-300 text-sm">
                        </div>
                    </div>

                    <!-- Parameter Pengujian -->
                    <h4 class="text-md font-semibold text-gray-700 mb-3 border-b pb-1">3. Pilih Parameter Pengujian ({{ ucfirst($labType) }})</h4>
                    <div class="mb-6">
                        @if(!isset($services) || $services->isEmpty())
                            <p class="text-sm text-yellow-600 italic">Belum ada layanan parameter yang terdaftar untuk laboratorium ini di database.</p>
                        @else
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-60 overflow-y-auto p-2 border rounded-md bg-gray-50">
                                @foreach($services as $service)
                                    <label class="flex items-center space-x-3 p-2 bg-white rounded border hover:bg-blue-50 cursor-pointer">
                                        <input type="checkbox" name="services[]" value="{{ $service->id }}" data-price="{{ $service->price }}" class="service-checkbox rounded text-blue-600 focus:ring-blue-500">
                                        <div class="flex-1">
                                            <span class="block text-sm font-medium text-gray-800">{{ $service->service_name }}</span>
                                            <span class="block text-xs text-gray-500">Rp {{ number_format($service->price, 0, ',', '.') }}</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Ringkasan Biaya & Tombol Submit -->
                    <div class="flex items-center justify-between border-t pt-4 bg-gray-50 p-4 rounded-md">
                        <div>
                            <span class="text-sm text-gray-600">Estimasi Total Biaya:</span>
                            <div id="total-price-display" class="text-2xl font-bold text-blue-600">Rp 0</div>
                        </div>

                        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 font-semibold shadow">
                            Simpan & Terbitkan Permohonan
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <!-- JavaScript Real-Time Cost Calculation -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const checkboxes = document.querySelectorAll('.service-checkbox');
            const quantityInput = document.getElementById('sample_quantity');
            const totalPriceDisplay = document.getElementById('total-price-display');

            function calculateTotal() {
                let unitTotal = 0;
                checkboxes.forEach(cb => {
                    if (cb.checked) {
                        unitTotal += parseFloat(cb.getAttribute('data-price')) || 0;
                    }
                });

                const quantity = parseInt(quantityInput.value) || 1;
                const grandTotal = unitTotal * quantity;

                totalPriceDisplay.textContent = 'Rp ' + grandTotal.toLocaleString('id-ID');
            }

            checkboxes.forEach(cb => cb.addEventListener('change', calculateTotal));
            if (quantityInput) {
                quantityInput.addEventListener('input', calculateTotal);
            }
        });
    </script>
</x-app-layout>