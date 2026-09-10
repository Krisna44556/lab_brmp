<!-- Alert Sukses -->
@if (session('success'))
    <div class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded-r shadow-sm flex items-center justify-between">
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    </div>
@endif

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('') }}
        </h2>
    </x-slot>

    <div class="flex justify-between items-center mb-4 mt-8 ml-20">
        <h3 class="text-lg font-bold">Daftar Permohonan Pengujian</h3>
        <a href="{{ route('admin.select-lab') }}" class="bg-blue-600 text-white px-4 py-2 mr-20 rounded text-sm hover:bg-blue-700">+ Input Permohonan Baru</a>
    </div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Ringkasan Kartu Statistik -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div class="bg-yellow-100 border-l-4 border-yellow-500 p-4 rounded shadow">
                    <p class="text-gray-600">Menunggu Verifikasi</p>
                    <p class="text-3xl font-bold text-yellow-700">{{ $pendingCount }}</p>
                </div>
                <div class="bg-green-100 border-l-4 border-green-500 p-4 rounded shadow">
                    <p class="text-gray-600">Pembayaran Terverifikasi</p>
                    <p class="text-3xl font-bold text-green-700">{{ $verifiedCount }}</p>
                </div>
            </div>

            <!-- Tabel Verifikasi Permohonan -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-bold mb-4">Daftar Permohonan Pengujian</h3>
                <table class="min-w-full border-collapse border border-gray-200">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border p-2">Kode Req</th>
                            <th class="border p-2">Pemohon</th>
                            <th class="border p-2">Status Bayar</th>
                            <th class="p-3 text-center">Status Progres Lab</th>
                            <th class="border p-2">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $req)
                        <tr class="border-b">
                            <td class="border p-2 text-center">{{ $req->request_code }}</td>
                            <td class="border p-2">{{ $req->user->name }}</td>
                            <td class="border p-2 text-center">
                                <span class="px-2 py-1 rounded text-white text-xs {{ $req->payment_status == 'verified' ? 'bg-green-500' : ($req->payment_status == 'rejected' ? 'bg-red-500' : 'bg-yellow-500') }}">
                                    {{ ucfirst($req->payment_status) }}
                                </span>
                            </td>

                            <td class="p-3 text-center">
                                <div id="status-badge-{{ $req->id }}">
                                @php
                                    // Ambil sampel pertama dari relasi
                                    $s = $req->samples?->first();
                                    
                                    // Ambil status (cek di sampel dulu, lalu di req)
                                    $rawStatus = $s?->current_status ?? $s?->status ?? $req->current_status ?? $req->status ?? 'pending';
                                    
                                    // Normalisasi format status
                                    $status = strtolower(str_replace(' ', '_', trim($rawStatus)));
                                @endphp

                                @if($status == 'completed' || $status == 'selesai')
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-green-100 text-green-800 border border-green-300">
                                        ✔ Selesai
                                    </span>
                                @elseif($status == 'in_progress' || $status == 'in_progress')
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-blue-100 text-blue-800 border border-blue-300">
                                        🔬 In Progress
                                    </span>
                                @elseif($status == 'received' || $status == 'diterima_lab')
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-yellow-100 text-yellow-800 border border-yellow-300">
                                        📦 Diterima Lab
                                    </span>
                                @elseif($status == 'issue' || $status == 'kendala')
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-red-100 text-red-800 border border-red-300">
                                        ⚠️ Kendala / Issue
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-gray-100 text-gray-700 border border-gray-300">
                                        ⏳ Menunggu Distributor
                                    </span>
                                @endif
                                </div>
                            </td>


                            <td class="border p-2 text-center">
                                    <a href="{{ route('admin.verify', [$req->id, 'verified']) }}" class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700">Setujui</a>
                                    <a href="{{ route('admin.verify', [$req->id, 'rejected']) }}" class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700">Tolak</a>
                                    <a href="{{ route('admin.qr_code', $req->id) }}" target="_blank" class="bg-gray-800 text-white text-xs px-2 py-1 rounded hover:bg-gray-700">
                                        🖨️ QR Code
                                    </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="border p-4 text-center text-gray-500">Belum ada data permohonan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>

<script>
    setInterval(function() {
        fetch("{{ route('admin.status_realtime') }}")
            .then(response => response.json())
            .then(data => {
                data.forEach(item => {
                    const badgeContainer = document.getElementById(`status-badge-${item.id}`);
                    if (!badgeContainer) return;

                    // Normalisasi status dari JSON
                    let st = (item.status || 'pending').toLowerCase().replace(/\s+/g, '_');

                    let badgeHTML = '';
                    if (st === 'completed' || st === 'selesai') {
                        badgeHTML = `<span class="px-2.5 py-1 text-xs font-bold rounded-full bg-green-100 text-green-800 border border-green-300">✔ Selesai</span>`;
                    } else if (st === 'in_progress' || st === 'in progress') {
                        badgeHTML = `<span class="px-2.5 py-1 text-xs font-bold rounded-full bg-blue-100 text-blue-800 border border-blue-300">🔬 In Progress</span>`;
                    } else if (st === 'received' || st === 'diterima_lab') {
                        badgeHTML = `<span class="px-2.5 py-1 text-xs font-bold rounded-full bg-yellow-100 text-yellow-800 border border-yellow-300">📦 Diterima Lab</span>`;
                    } else if (st === 'issue' || st === 'kendala') {
                        badgeHTML = `<span class="px-2.5 py-1 text-xs font-bold rounded-full bg-red-100 text-red-800 border border-red-300">⚠️ Kendala / Issue</span>`;
                    } else {
                        badgeHTML = `<span class="px-2.5 py-1 text-xs font-bold rounded-full bg-gray-100 text-gray-700 border border-gray-300">⏳ Menunggu Distributor</span>`;
                    }

                    // Hanya update DOM jika isi HTML berbeda
                    if (badgeContainer.innerHTML.trim() !== badgeHTML.trim()) {
                        badgeContainer.innerHTML = badgeHTML;
                    }
                });
            })
            .catch(err => console.error("Error fetching status:", err));
    }, 3000);
</script>

</x-app-layout>