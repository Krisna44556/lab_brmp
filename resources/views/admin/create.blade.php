<x-app-layout>
    <x-slot name="header"   >
        <div class="flex justify-between items-center max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="font-bold text-2xl text-gray-800 leading-tight flex items-center gap-2">
                <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Input Permohonan Pengujian
            </h2>
            <a href="{{ route('admin.select-lab') }}" class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2 px-4 rounded-xl text-sm transition-all shadow-sm border border-gray-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali Pilih Lab
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-gray-50/50 min-h-screen">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Banner Header Lab -->
            <div class="bg-gradient-to-r from-green-600 to-green-800 rounded-2xl p-6 text-white shadow-lg mb-8 flex justify-between items-center">
                <div>
                    <span class="text-white text-3xl font-bold uppercase tracking-wider px-1 py-1">Laboratorium Terpilih</span>
                    <h1 class="text-3xl font-extrabold mt-1 tracking-tight uppercase">{{ $labType }}</h1>
                </div>
                <div class="hidden sm:block text-right">
                    <p class="text-green-100 text-sm">Pastikan data sampel & parameter</p>
                    <p class="text-green-100 text-sm">diisi dengan benar sebelum disimpan.</p>
                </div>
            </div>

            <!-- Validation Errors -->
            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-xl shadow-sm">
                    <div class="flex items-center gap-2 text-red-800 font-bold mb-1">
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Terjadi Kesalahan Input!
                    </div>
                    <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.request.store') }}" method="POST" id="form-pendaftaran" class="space-y-8">
                @csrf
                <input type="hidden" name="lab_type" value="{{ $labType }}">

                <!-- 1. INFORMASI PEMOHON -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2 border-b pb-3">
                        <span class="w-7 h-7 text-xl text-green-600 flex items-center justify-center font-extrabold">1</span>
                        Informasi Pemohon
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Nama Pemohon <span class="text-red-500">*</span></label>
                            <input type="text" name="applicant_name" class="w-full rounded-xl border-gray-200 focus:border-green-500 focus:ring-green-500 text-sm shadow-sm py-2.5 px-3.5" placeholder="Contoh: Budi Santoso" value="{{ old('applicant_name') }}" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Nomor WhatsApp / HP <span class="text-red-500">*</span></label>
                            <input type="text" name="phone_number" class="w-full rounded-xl border-gray-200 focus:border-green-500 focus:ring-green-500 text-sm shadow-sm py-2.5 px-3.5" placeholder="08xxxxxxxxxx" value="{{ old('phone_number') }}" required>
                        </div>
                    </div>
                </div>

                <!-- 2. ALAMAT ASAL SAMPEL -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2 border-b pb-3">
                        <span class="w-7 h-7 text-xl text-green-600 flex items-center justify-center font-extrabold">2</span>
                        Alamat Asal Sampel <span class="text-xs font-normal text-gray-400">(Opsional)</span>
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <input type="text" name="village" class="w-full rounded-xl border-gray-200 focus:border-green-500 focus:ring-green-500 text-sm py-2.5" placeholder="Desa / Kelurahan">
                        </div>
                        <div>
                            <input type="text" name="district" class="w-full rounded-xl border-gray-200 focus:border-green-500 focus:ring-green-500 text-sm py-2.5" placeholder="Kecamatan">
                        </div>
                        <div>
                            <input type="text" name="regency" class="w-full rounded-xl border-gray-200 focus:border-green-500 focus:ring-green-500 text-sm py-2.5" placeholder="Kabupaten / Kota">
                        </div>
                        <div>
                            <input type="text" name="province" class="w-full rounded-xl border-gray-200 focus:border-green-500 focus:ring-green-500 text-sm py-2.5" placeholder="Provinsi">
                        </div>
                    </div>
                </div>

                <!-- 3. DETAIL WADAH & PARAMETER PENGUJIAN -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 border-b pb-4">
                        <div>
                            <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                               <span class="w-7 h-7 text-xl text-green-600 flex items-center justify-center font-extrabold">3</span>
                                Rincian Wadah & Parameter Pengujian
                            </h3>
                            <p class="text-xs text-gray-500 mt-1 ml-9">Tambahkan wadah jika pemohon membawa beberapa fisik sampel berbeda.</p>
                        </div>
                        <button type="button" id="btn-tambah-wadah" class="inline-flex items-center gap-2 bg-green-50 hover:bg-green-100 text-green-600 font-bold px-4 py-2 rounded-xl text-sm transition-all border border-green-200 shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Tambah Wadah Sampel
                        </button>
                    </div>

                    <!-- CONTAINER WADAH DINAMIS -->
                    <div id="samples-container" class="space-y-6">
                        
                        <!-- WADAH #1 (DEFAULT TEMPLATE) -->
                        <div class="sample-item bg-gray-50/70 rounded-2xl p-5 border border-gray-200 shadow-sm transition-all" data-index="0">
                            <div class="flex justify-between items-center mb-4">
                                <span class="sample-title font-extrabold text-green-700 px-1 py-1 text-xl uppercase tracking-wider">Wadah 1</span>
                                <button type="button" class="btn-hapus-wadah hidden inline-flex items-center gap-1 text-xs font-bold text-red-600 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg transition-all border border-red-200">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    Hapus
                                </button>
                            </div>

                            <!-- Input Nama Sampel Wadah -->
                            <div class="mb-5">
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Nama / Identitas Sampel Wadah Ini <span class="text-red-500">*</span></label>
                                <input type="text" name="samples[0][sample_name]" class="w-full rounded-xl border-gray-200 focus:border-green-500 focus:ring-green-500 text-sm bg-white py-2.5 px-3.5 shadow-sm" placeholder="Contoh: Tanah Regosol Blok A / Air Minum Kantin / Pupuk NPK" required>
                            </div>

                            <!-- Filter Search Parameter -->
                            <div class="mb-3">
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Pilih Parameter Pengujian Khusus Wadah Ini <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <input type="text" class="search-service-input w-full rounded-xl border-gray-200 focus:border-green-500 focus:ring-green-500 text-xs bg-white pl-9 py-2" placeholder="Ketik untuk memfilter nama parameter...">
                                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                </div>
                            </div>

                            <!-- List Parameter Grid Checkbox -->
                            <div class="services-wrapper grid grid-cols-1 md:grid-cols-2 gap-3 max-h-64 overflow-y-auto p-1 pr-2">
                                @foreach($services as $service)
                                    <div class="service-card bg-white p-3 rounded-xl border border-gray-200 hover:border-green-300 hover:shadow-sm transition-all">
                                        <label class="flex items-center justify-between cursor-pointer group">
                                            <div class="flex items-center gap-3">
                                                <input type="checkbox" 
                                                       name="samples[0][services][]" 
                                                       value="{{ $service->id }}" 
                                                       id="service_0_{{ $service->id }}"
                                                       data-price="{{ $service->price }}"
                                                       class="service-checkbox w-4 h-4 text-green-600 rounded border-gray-300 focus:ring-green-500">
                                                <span class="service-name text-xs font-semibold text-gray-700 group-hover:text-green-600 transition-colors">{{ $service->service_name }}</span>
                                            </div>
                                            <span class="text-xs font-extrabold text-green-600 bg-green-50 px-2.5 py-1 rounded-lg border border-green-100 whitespace-nowrap">
                                                Rp {{ number_format($service->price, 0, ',', '.') }}
                                            </span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                    </div>
                </div>

                <!-- BOTTOM BAR: TOTAL BIAYA & BUTTON SUBMIT -->
                <div class="text-white rounded-2xl p-6 shadow-xl flex flex-col sm:flex-row justify-between items-center gap-6">
                    <div>
                        
                        <span id="grand-total-text" class="text-3xl font-black text-emerald-400">Rp 0</span>
                    </div>
                    <button type="submit" class="w-32 sm:w-auto inline-flex items-center justify-center gap-3 bg-emerald-500 hover:bg-emerald-600 text-white font-extrabold px-8 py-4 rounded-xl   text-base transition-all shadow-lg hover:shadow-emerald-500/20 active:scale-95">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Simpan Permohonan Pengujian
                    </button>
                </div>

            </form>
        </div>
    </div>

    <!-- JAVASCRIPT MULTI-SAMPLE -->
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        let sampleIndex = 1;
        const container = document.getElementById('samples-container');
        const btnTambah = document.getElementById('btn-tambah-wadah');
        const grandTotalText = document.getElementById('grand-total-text');

        // 1. Kalkulasi Total Biaya
        function hitungTotalBiaya() {
            let grandTotal = 0;
            const checkedBoxes = container.querySelectorAll('.service-checkbox:checked');

            checkedBoxes.forEach(cb => {
                const price = parseFloat(cb.getAttribute('data-price')) || 0;
                grandTotal += price;
            });

            if (grandTotalText) {
                grandTotalText.innerText = 'Rp ' + grandTotal.toLocaleString('id-ID');
            }
        }

        // 2. Update Penomoran Wadah & Tombol Hapus
        function updateSampleState() {
            const items = container.querySelectorAll('.sample-item');
            items.forEach((item, index) => {
                const title = item.querySelector('.sample-title');
                if (title) title.innerText = `Wadah / Sampel #${index + 1}`;

                const btnHapus = item.querySelector('.btn-hapus-wadah');
                if (btnHapus) {
                    if (items.length === 1) {
                        btnHapus.classList.add('hidden');
                    } else {
                        btnHapus.classList.remove('hidden');
                    }
                }
            });
        }

        // 3. Tambah Wadah Baru
        btnTambah.addEventListener('click', function (e) {
            e.preventDefault();
            
            const firstSample = container.querySelector('.sample-item');
            if (!firstSample) return;

            const newSample = firstSample.cloneNode(true);
            newSample.setAttribute('data-index', sampleIndex);

            // Reset Nama Sampel
            const nameInput = newSample.querySelector('input[type="text"]');
            if (nameInput) {
                nameInput.name = `samples[${sampleIndex}][sample_name]`;
                nameInput.value = '';
            }

            // Reset Search Filter
            const searchInput = newSample.querySelector('.search-service-input');
            if (searchInput) searchInput.value = '';

            // Reset Checkbox & ID
            const checkboxes = newSample.querySelectorAll('.service-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = false;
                cb.name = `samples[${sampleIndex}][services][]`;
                
                const serviceId = cb.value;
                const newId = `service_${sampleIndex}_${serviceId}`;
                cb.id = newId;

                const label = cb.closest('label');
                if (label) label.setAttribute('for', newId);

                const card = cb.closest('.service-card');
                if (card) card.style.display = '';
            });

            container.appendChild(newSample);
            sampleIndex++;
            updateSampleState();
            hitungTotalBiaya();
        });

        // 4. Delegasi Event (Live Search, Hapus, & Checkbox)
        container.addEventListener('input', function (e) {
            if (e.target.classList.contains('search-service-input')) {
                const filter = e.target.value.toLowerCase();
                const sampleItem = e.target.closest('.sample-item');
                const serviceCards = sampleItem.querySelectorAll('.service-card');

                serviceCards.forEach(card => {
                    const nameText = card.querySelector('.service-name');
                    if (nameText) {
                        const name = nameText.innerText.toLowerCase();
                        card.style.display = name.includes(filter) ? '' : 'none';
                    }
                });
            }
        });

        container.addEventListener('change', function (e) {
            if (e.target.classList.contains('service-checkbox')) {
                hitungTotalBiaya();
            }
        });

        container.addEventListener('click', function (e) {
            const btnHapus = e.target.closest('.btn-hapus-wadah');
            if (btnHapus) {
                e.preventDefault();
                const sampleItem = btnHapus.closest('.sample-item');
                if (sampleItem) {
                    sampleItem.remove();
                    updateSampleState();
                    hitungTotalBiaya();
                }
            }
        });

        updateSampleState();
        hitungTotalBiaya();
    });
    </script>
</x-app-layout>