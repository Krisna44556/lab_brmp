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

                 <!-- Form Filter Admin -->
                    <div class="mb-4 bg-white p-4 rounded-lg shadow-sm">
                        <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-3">
                            <div class="relative flex-1 max-w-md">
                                <input type="text" 
                                    name="code" 
                                    value="{{ request('code') }}" 
                                    placeholder="Cari Kode Req / Pemohon / Kode Sampel..." 
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
                        <tr class="border-b hover:bg-gray-50 transition-colors">
                            <!-- 1. KODE REQUEST -->
                            <td class="border p-2 text-center font-bold text-blue-600">
                                {{ $req->request_code }}
                            </td>

                            <!-- 2. NAMA PEMOHON & JUMLAH WADAH -->
                            <td class="border p-2">
                                <div class="font-semibold text-gray-800">
                                    {{ $req->pemohon_name ?? $req->user->name ?? '-' }}
                                </div>
                                <div class="mt-1">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                        {{ $req->samples->count() }} Wadah Sampel
                                    </span>
                                </div>
                            </td>

                            <!-- 3. STATUS PEMBAYARAN -->
                            <td class="border p-2 text-center">
                                <span class="px-2.5 py-1 rounded-full text-white text-xs font-bold {{ $req->payment_status == 'verified' ? 'bg-green-600' : ($req->payment_status == 'rejected' ? 'bg-red-600' : 'bg-yellow-500') }}">
                                    {{ ucfirst($req->payment_status) }}
                                </span>
                            </td>

                            <!-- 4. STATUS PROGRESS PENGUJIAN -->
                            <td class="border p-3 text-center">
                                <div id="status-badge-{{ $req->id }}">
                                    @php
                                        // Ambil semua status sampel
                                        $statuses = $req->samples->pluck('current_status')->map(fn($st) => strtolower(trim($st)))->toArray();
                                        
                                        if (in_array('issue', $statuses)) {
                                            $finalStatus = 'issue';
                                        } elseif (count($statuses) > 0 && array_key_exists('completed', array_count_values($statuses)) && array_count_values($statuses)['completed'] === count($statuses)) {
                                            $finalStatus = 'completed';
                                        } elseif (in_array('in_progress', $statuses)) {
                                            $finalStatus = 'in_progress';
                                        } elseif (in_array('received', $statuses)) {
                                            $finalStatus = 'received';
                                        } else {
                                            $finalStatus = 'registered';
                                        }
                                    @endphp

                                    @if($finalStatus == 'completed')
                                        <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-green-100 text-green-800 border border-green-300">
                                            ✔ Selesai
                                        </span>
                                    @elseif($finalStatus == 'in_progress')
                                        <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-blue-100 text-blue-800 border border-blue-300">
                                            🔬 In Progress
                                        </span>
                                    @elseif($finalStatus == 'received')
                                        <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-yellow-100 text-yellow-800 border border-yellow-300">
                                            📦 Diterima Lab
                                        </span>
                                    @elseif($finalStatus == 'issue')
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

                            <!-- 5. AKSI -->
                            <td class="border p-2 text-center">
                                <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                    <a href="{{ route('admin.verify', [$req->id, 'verified']) }}" class="bg-green-600 text-white px-2.5 py-1 rounded text-xs font-semibold hover:bg-green-700 transition-all">
                                        Setujui
                                    </a>
                                    <a href="{{ route('admin.verify', [$req->id, 'rejected']) }}" class="bg-red-600 text-white px-2.5 py-1 rounded text-xs font-semibold hover:bg-red-700 transition-all">
                                        Tolak
                                    </a>
                                    <a href="{{ route('admin.qr_code', $req->id) }}" target="_blank" class="bg-gray-800 text-white text-xs px-2.5 py-1 rounded font-semibold hover:bg-gray-700 transition-all flex items-center gap-1">
                                        🖨️ QR Code
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="border p-6 text-center text-gray-500 font-medium">
                                Belum ada data permohonan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                
                <div class="mt-4 px-2 flex justify-end">
                    {{ $requests->links() }}
                </div>
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