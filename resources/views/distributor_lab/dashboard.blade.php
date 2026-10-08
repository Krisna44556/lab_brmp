<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Distributor Lab - Scan QR Code & Log Sampel') }}
        </h2>
    </x-slot>

    <!-- Library HTML5 QR Scanner -->
    <script src="https://unpkg.com/html5-qrcode"></script>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Alert Floating Toast -->
            <div id="toast-notification" class="hidden fixed top-5 right-5 z-50 bg-green-600 text-white px-5 py-3 rounded-lg shadow-lg text-sm transition-all duration-300">
                <span id="toast-message">Berhasil</span>
            </div>

            <!-- Section 1: Camera Scanner QR Code & Manual Input -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <h3 class="text-lg font-bold text-gray-800 mb-4 text-center">Scan QR Code Pada Label Sampel</h3>
                
                <div id="reader" class="mx-auto max-w-md border rounded-lg overflow-hidden bg-gray-50 mb-4"></div>
                
                <div class="flex justify-center items-center gap-2 flex-wrap">
                    <input type="text" id="manual_code" placeholder="Atau ketik Kode Sampel manual..." class="text-sm rounded-md border-gray-300 w-full sm:w-64 focus:border-blue-500 focus:ring-blue-500">
                    <button type="button" onclick="triggerManualScan()" class="bg-gray-800 text-white px-4 py-2 rounded-md text-sm hover:bg-gray-700 transition">
                        Scan & Tambah
                    </button>
                </div>
            </div>

            <!-- Section 2: Tabel Log Sampel Masuk -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
                    <h3 class="text-lg font-bold text-gray-800">Daftar Sampel Masuk untuk Pengujian</h3>
                    
                    <!-- Form Filter Kode Sampel (Margin diperbaiki agar responsif) -->
                        <div class="mb-4 bg-white p-4 rounded-lg shadow-sm">
                            <form method="POST" action="{{ url()->current() }}" class="flex items-center gap-3">
                                <div class="relative flex-1 max-w-md">
                                 <input type="text" 
                                    name="code" 
                                    value="{{ request('code') }}" 
                                    placeholder="Cari Kode Sampel..." 
                                    class="w-full border border-slate-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>

                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition">
                                    Cari
                                </button>

                                @if(request('code'))
                                <a href="{{ url()->current() }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-2 rounded-md text-sm font-medium transition">
                                    Reset
                                </a>
                                 @endif
                            </form>
                        </div>   
                </div>
               
                
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-600 border-collapse">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-100 border-b">
                            <tr>
                                <th scope="col" class="px-4 py-3">Kode Sampel</th>
                                <th scope="col" class="px-4 py-3">Nama Sampel</th>
                                <th scope="col" class="px-4 py-3">Pemohon</th>
                                <th scope="col" class="px-4 py-3">Status Saat Ini</th>
                                <th scope="col" class="px-4 py-3 text-center">Aksi Progress</th>
                            </tr>
                        </thead>
<tbody id="sample-table-body" class="divide-y divide-gray-200">
    @forelse($scannedSamples as $sample)
    <tr class="border-b hover:bg-gray-50" id="sample-row-{{ $sample->id }}">
        <!-- KODE SAMPEL WADAH -->
        <td class="px-4 py-3 font-semibold text-gray-900">
            <div>{{ $sample->sample_code }}</div>
            
            {{-- Badge jika ada wadah lain dalam 1 request --}}
            @if($sample->total_samples_in_request > 1)
                <div class="mt-0.5">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-blue-50 text-blue-700 border border-blue-200">
                        +{{ $sample->total_samples_in_request - 1 }} wadah lain
                    </span>
                </div>
            @endif
        </td>

        <!-- JENIS / NAMA SAMPEL (PERBAIKAN DISINI) -->
        <td class="px-4 py-3 text-gray-800 font-medium">
            {{ $sample->sample_type_name ?? $sample->sample_name ?? '-' }}
        </td>

        <!-- NAMA PEMOHON -->
        <td class="px-4 py-3 text-gray-600">
            {{ $sample->applicant_name }}
        </td>

        <!-- STATUS SAMPEL (WARNA DINAMIS PER WADAH) -->
        <td class="px-4 py-3">
            @php
                $statusClasses = [
                    'received'    => 'bg-blue-100 text-blue-800',
                    'diterima'    => 'bg-blue-100 text-blue-800',
                    'in_progress' => 'bg-amber-100 text-amber-800',
                    'completed'   => 'bg-emerald-100 text-emerald-800',
                    'issue'       => 'bg-rose-100 text-rose-800',
                ];
                $currentStatus = strtolower($sample->current_status ?? 'received');
                $badgeClass = $statusClasses[$currentStatus] ?? 'bg-gray-100 text-gray-800';
            @endphp

            <span id="status-badge-{{ $sample->id }}" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $badgeClass }}">
                {{ ucfirst(str_replace('_', ' ', $sample->current_status)) }}
            </span>
        </td>

        <!-- AKSI PROGRESS STATUS PER WADAH -->
        <td class="px-4 py-3 text-center">
            <div class="flex items-center justify-center gap-2">
                <select id="status-select-{{ $sample->id }}" class="text-xs rounded border-gray-300 py-1 px-2 focus:ring-blue-500 focus:border-blue-500" onchange="updateSampleStatus({{ $sample->id }}, this.value)">
                    <option value="received" {{ $sample->current_status == 'received' ? 'selected' : '' }}>Received (Diterima)</option>
                    <option value="in_progress" {{ $sample->current_status == 'in_progress' ? 'selected' : '' }}>In Progress (Pengujian)</option>
                    <option value="completed" {{ $sample->current_status == 'completed' ? 'selected' : '' }}>Completed (Selesai)</option>
                    <option value="issue" {{ $sample->current_status == 'issue' ? 'selected' : '' }}>Ada Kendala (Issue)</option>
                </select>
                <button type="button" onclick="showDetailModal({{ $sample->id }})" class="px-3 py-1 text-xs border border-gray-300 text-gray-700 rounded hover:bg-gray-100 font-medium transition-colors">
                    Detail
                </button>
            </div>
        </td>
    </tr>
    @empty
    <tr id="empty-row">
        <td colspan="5" class="px-4 py-6 text-center text-gray-500">
            Belum ada sampel yang discan atau diterima di lab.
        </td>
    </tr>
    @endforelse
</tbody>
                    </table>
                </div>

                <!-- Pagination Bagian Bawah Tabel -->
                <div class="mt-4 flex flex-col md:flex-row items-center justify-between px-4 py-3 bg-white border-t border-gray-200 rounded-b-lg">
                    <!-- Informasi Data -->
                    <div class="mb-2 md:mb-0 text-sm text-gray-600">
                        Menampilkan 
                        <span class="font-semibold">{{ $scannedSamples->firstItem() ?? 0 }}</span> 
                        sampai 
                        <span class="font-semibold">{{ $scannedSamples->lastItem() ?? 0 }}</span> 
                        dari 
                        <span class="font-semibold">{{ $scannedSamples->total() }}</span> 
                        data sampel
                    </div>

                    <!-- Tombol Halaman / Link Paging -->
                    <div>
                        {{ $scannedSamples->appends(request()->query())->links() }}
                    </div>
                </div>  
            </div>

        </div>
    </div>

    <!-- Modal Pop-Up Detail -->
    <div id="detailSampleModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900 bg-opacity-50 flex items-center justify-center p-4">
        <div class="relative bg-white rounded-lg shadow-xl max-w-2xl w-full border-2 border-blue-500 p-6">
            
            <div class="flex justify-between items-center mb-4 pb-2 border-b">
                <span class="px-2.5 py-1 bg-blue-600 text-white font-bold text-xs rounded">DISTRIBUTOR LAB</span>
                <span class="text-sm text-gray-500">Status: <strong id="modal-status-text" class="text-gray-900 font-semibold">-</strong></span>
            </div>
            
            <h3 class="text-xl font-bold text-gray-900 mb-4" id="modal-sample-code">-</h3>

            <div class="bg-gray-50 rounded-lg p-4 mb-4 border border-gray-200">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-xs text-gray-500 block">Nama Pemohon</span>
                        <strong id="modal-applicant-name" class="text-gray-800">-</strong>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 block">Nomor WhatsApp / HP</span>
                        <strong id="modal-applicant-phone" class="text-gray-800">-</strong>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 block">Jenis Sampel</span>
                        <strong id="modal-sample-name" class="text-gray-800">-</strong>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 block">Jumlah Sampel</span>
                        <strong id="modal-quantity" class="text-gray-800">- Wadah</strong>
                    </div>
                    <div class="md:col-span-2">
                        <span class="text-xs text-gray-500 block">Asal Lokasi Sampel</span>
                        <strong id="modal-location" class="text-gray-800">-</strong>
                    </div>
                </div>
            </div>

            <div class="mb-6">
                <label class="block font-bold text-sm text-gray-700 mb-2">Parameter Pengujian:</label>
                <ul id="modal-parameter-list" class="bg-blue-50 border border-blue-200 text-blue-900 text-sm rounded-lg p-3 space-y-1">
                    <li>• Parameter Pengujian</li>
                </ul>
            </div>

            <div class="pt-4 border-t flex flex-col sm:flex-row gap-3 items-center justify-between">
                <div class="w-full sm:w-auto flex-1 flex gap-2 items-center">
                    <select id="modal-select-status" class="text-sm rounded-md border-gray-300 focus:ring-blue-500 focus:border-blue-500 w-full">
                        <option value="received">Received (Diterima di Lab)</option>
                        <option value="in_progress">In Progress (Sedang Pengujian)</option>
                        <option value="completed">Completed (Selesai - LHU Siap)</option>
                        <option value="issue">Ada Kendala (Issue)</option>
                    </select>
                    <button type="button" id="btn-save-modal-status" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-md font-medium whitespace-nowrap transition">
                        Simpan Status
                    </button>
                </div>
                <button type="button" onclick="closeModal()" class="w-full sm:w-auto bg-gray-200 hover:bg-gray-300 text-gray-800 text-sm px-4 py-2 rounded-md transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- Script JavaScript -->
    <script>
        let currentActiveSampleId = null;
        let isProcessingScan = false;

        function showToast(message, isError = false) {
            const toast = document.getElementById('toast-notification');
            const toastMsg = document.getElementById('toast-message');
            
            toastMsg.innerText = message;
            toast.className = `fixed top-5 right-5 z-50 text-white px-5 py-3 rounded-lg shadow-lg text-sm transition-all duration-300 ${isError ? 'bg-red-600' : 'bg-green-600'}`;
            toast.classList.remove('hidden');

            setTimeout(() => {
                toast.classList.add('hidden');
            }, 3000);
        }

        function extractCodeFromInput(input) {
            if (!input) return '';
            let text = input.trim();
            if (text.startsWith('http://') || text.startsWith('https://')) {
                const parts = text.split('/').filter(Boolean);
                return parts[parts.length - 1];
            }
            return text;
        }

        function triggerManualScan() {
            const rawCode = document.getElementById('manual_code').value;
            const cleanCode = extractCodeFromInput(rawCode);
            if (cleanCode) {
                processScanCode(cleanCode);
            } else {
                showToast('Kode sampel tidak boleh kosong!', true);
            }
        }

        function processScanCode(code) {
            if (isProcessingScan) return;
            isProcessingScan = true;

            fetch("{{ url('/distributor/process-scan') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json', // Paksa Laravel merespon JSON
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ keyword: code })
            })
            .then(async response => {
                // Cek apakah respon yang kembali benar-benar berformat JSON
                const isJson = response.headers.get('content-type')?.includes('application/json');
                const data = isJson ? await response.json() : null;

                // Jika HTTP Status Error (misal 404 / 500)
                if (!response.ok) {
                    const errorMsg = data?.message || `Terjadi kesalahan server (Status: ${response.status})`;
                    return Promise.reject(errorMsg);
                }

                return data;
            })
            .then(res => {
                isProcessingScan = false;

                if (res && res.success) {
                    const sample = res.sample;

                    const emptyRow = document.getElementById('empty-row');
                    if (emptyRow) emptyRow.remove();

                    const existingRow = document.getElementById(`sample-row-${sample.id}`);
                    
                    if (!existingRow) {
                        const tbody = document.getElementById('sample-table-body');
                        const newRow = document.createElement('tr');
                        newRow.id = `sample-row-${sample.id}`;
                        newRow.className = "hover:bg-gray-50";
                        
                        newRow.innerHTML = `
                            <td class="px-4 py-3 font-semibold text-gray-900">${sample.sample_code}</td>
                            <td class="px-4 py-3">${sample.sample_name}</td>
                            <td class="px-4 py-3">${sample.applicant_name || '-'}</td>
                            <td class="px-4 py-3">
                                <span id="status-badge-${sample.id}" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                    Diterima di Lab
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <select id="status-select-${sample.id}" class="text-xs rounded border-gray-300 py-1 px-2 focus:ring-blue-500 focus:border-blue-500" onchange="updateSampleStatus(${sample.id}, this.value)">
                                        <option value="" disabled>-- Ubah Status --</option>
                                        <option value="received" selected>Received (Diterima)</option>
                                        <option value="in_progress">In Progress (Pengujian)</option>
                                        <option value="completed">Completed (Selesai)</option>
                                        <option value="issue">Ada Kendala (Issue)</option>
                                    </select>
                                    <button type="button" onclick="showDetailModal(${sample.id})" class="px-3 py-1 text-xs border border-gray-300 text-gray-700 rounded hover:bg-gray-100">
                                        Detail
                                    </button>
                                </div>
                            </td>
                        `;
                        tbody.insertBefore(newRow, tbody.firstChild);
                    }

                    showToast(`Sampel ${sample.sample_code} berhasil ditambahkan!`);
                    document.getElementById('manual_code').value = '';
                } else {
                    showToast(res?.message || 'Sampel tidak ditemukan.', true);
                }
            })
            .catch(err => {
                isProcessingScan = false;
                console.error('Scan Process Error:', err);
                showToast(typeof err === 'string' ? err : 'Terjadi kesalahan server saat memproses scan.', true);
            });
        }

        function updateSampleStatus(sampleId, newStatus) {
            fetch(`{{ url('/distributor/samples') }}/${sampleId}/update-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json', // TAMBAHKAN INI agar Laravel mengembalikan response JSON
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ status: newStatus })
            })
            .then(async res => {
                const data = await res.json();
                if (!res.ok) {
                    throw new Error(data.message || "Route tidak ditemukan atau server error");
                }
                return data;
            })
            .then(res => {
                if (res.success || res.status === 'success') {
                    const badge = document.getElementById(`status-badge-${sampleId}`);
                    if (badge) {
                        let label = newStatus;
                        let bgClass = 'bg-gray-100 text-gray-800';

                        if (newStatus === 'received') {
                            label = 'Diterima di Lab';
                            bgClass = 'bg-blue-100 text-blue-800';
                        } else if (newStatus === 'in_progress') {
                            label = 'In Progress';
                            bgClass = 'bg-yellow-100 text-yellow-800';
                        } else if (newStatus === 'completed') {
                            label = 'Completed';
                            bgClass = 'bg-green-100 text-green-800';
                        } else if (newStatus === 'issue') {
                            label = 'Ada Kendala';
                            bgClass = 'bg-red-100 text-red-800';
                        }

                        badge.innerText = label;
                        badge.className = `inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold ${bgClass}`;
                    }
                    
                    const tableSelect = document.getElementById(`status-select-${sampleId}`);
                    if (tableSelect) tableSelect.value = newStatus;

                    const modalStatusText = document.getElementById('modal-status-text');
                    if (modalStatusText) modalStatusText.innerText = newStatus;

                    showToast('Status berhasil diperbarui!');
                } else {
                    showToast(res.message || 'Gagal memperbarui status.', true);
                }
            })
            .catch(err => {
                console.error(err);
                showToast(err.message || 'Gagal memperbarui status. Periksa koneksi/route.', true);
            });
        }
        
        function showDetailModal(sampleId) {
            const url = `/distributor/samples/${sampleId}/detail`;

            fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(async res => {
                const data = await res.json().catch(() => null);
                if (!res.ok) throw new Error(data?.message || `HTTP ${res.status}`);
                return data;
            })
            .then(data => {
                if (data && data.success) {
                    const sample = data.sample;

                    // 1. Informasi Utama
                    document.getElementById('modal-sample-code').innerText = sample.sample_code || '-';
                    document.getElementById('modal-status-text').innerText = sample.current_status ? sample.current_status.toUpperCase() : '-';
                    document.getElementById('modal-applicant-name').innerText = sample.applicant_name || '-';
                    document.getElementById('modal-applicant-phone').innerText = sample.applicant_phone || '-';
                    
                    // FIX JENIS SAMPEL: Baca sample_type_name (atau sample_type / sample_name)
                    document.getElementById('modal-sample-name').innerText = sample.sample_type_name || sample.sample_type || sample.sample_name || '-';
                    
                    document.getElementById('modal-quantity').innerText = (sample.total_quantity || 1) + ' Wadah';
                    document.getElementById('modal-location').innerText = sample.origin_location || '-';

                    // Set Pilihan Status Select
                    const selectStatus = document.getElementById('modal-select-status');
                    if (selectStatus) selectStatus.value = sample.current_status || 'received';

                    const btnSave = document.getElementById('btn-save-modal-status');
                    if (btnSave) {
                        btnSave.onclick = function() {
                            updateSampleStatus(sample.id, selectStatus.value);
                        };
                    }

                    // FIX PARAMETER PENGUJIAN: Render list dari data.services
                    // Render Parameter Pengujian
                    const paramList = document.getElementById('modal-parameter-list');
                    if (paramList) {
                        paramList.innerHTML = '';
                        
                        if (data.services && data.services.length > 0) {
                            data.services.forEach(item => {
                                const li = document.createElement('li');
                                li.className = 'flex justify-between items-center py-1 font-medium';
                                li.innerHTML = `
                                    <span>• ${item.service_name}</span>
                                    <span class="text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded font-semibold">
                                        Rp ${Number(item.price_at_time || 0).toLocaleString('id-ID')}
                                    </span>
                                `;
                                paramList.appendChild(li);
                            });
                        } else {
                            paramList.innerHTML = '<li class="text-amber-600 text-xs py-1 italic">Belum ada parameter pengujian yang dipilih untuk sampel ini.</li>';
                        }
                    }

                    // Tampilkan Modal
                    const modal = document.getElementById('detailSampleModal');
                    if (modal) {
                        modal.classList.remove('hidden');
                        modal.classList.add('flex');
                    }
                } else {
                    showToast(data?.message || 'Gagal memuat detail sampel.', true);
                }
            })
            .catch(err => {
                console.error('Detail Fetch Error:', err);
                showToast(err.message, true);
            });
        }



        function closeModal() {
            document.getElementById('detailSampleModal').classList.add('hidden');
        }

        document.getElementById('btn-save-modal-status').addEventListener('click', function() {
            const selectedStatus = document.getElementById('modal-select-status').value;
            if (currentActiveSampleId) {
                updateSampleStatus(currentActiveSampleId, selectedStatus);
                closeModal();
            }
        });

        function onScanSuccess(decodedText) {
            const cleanCode = extractCodeFromInput(decodedText);
            if (cleanCode && !isProcessingScan) {
                processScanCode(cleanCode);
            }
        }

        let html5QrcodeScanner = new Html5QrcodeScanner("reader", { fps: 10, qrbox: 250 });
        html5QrcodeScanner.render(onScanSuccess);
    </script>
</x-app-layout>