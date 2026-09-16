<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lacak Status Sampel - BRMP Lab</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col justify-between">

    <!-- Header Navigation -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-4xl mx-auto px-4 py-4 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div>
                    <h1 class="font-bold text-slate-900 text-lg leading-tight">BRMP LABORATORIUM</h1>
                    <p class="text-xs text-slate-500">Layanan Tracking Sampel Real-time</p>
                </div>
            </div>
            <a href="/" class="text-sm text-slate-800 hover:text-blue-700 font-medium flex items-center gap-1">
                <i class="fa-solid fa-house text-xs"></i> Beranda
            </a>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 py-8 w-full flex-grow">
        
        <!-- Form Cari Sampel -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-8">
            <form action="{{ route('tracking.search') }}" method="POST" class="space-y-3">
                @csrf
                <label for="code" class="block text-sm font-semibold text-slate-700">
                    Masukkan Kode Sampel atau Kode Pengajuan:
                </label>
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="relative flex-grow">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3.5 text-slate-400"></i>
                        <input 
                            type="text" 
                            name="code" 
                            id="code" 
                            value="{{ request('code', $code ?? '') }}" 
                            placeholder="Contoh: SMP-20260910-2936" 
                            class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-slate-800 font-mono text-sm"
                            required
                        >
                    </div>
                    <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-all shadow-sm flex items-center justify-center gap-2">
                        <i class="fa-solid fa-search"></i> Lacak Sampel
                    </button>
                </div>
            </form>
        </div>

        <!-- STATE HASIL PENCARIAN -->
        @if($code && !$sample)
            <!-- 1. State jika Kode Dicari tapi TIDAK DITEMUKAN -->
            <div class="bg-red-50 border border-red-200 rounded-2xl p-6 text-center text-red-700 mb-8">
                <i class="fa-solid fa-triangle-exclamation text-3xl mb-2 text-red-500"></i>
                <h3 class="font-bold text-lg">Sampel Tidak Ditemukan</h3>
                <p class="text-sm mt-1">Kode <strong>{{ $code }}</strong> tidak cocok dengan data kami. Mohon periksa kembali kodenya.</p>
            </div>

        @elseif($sample)
            <!-- 2. State jika Sampel DITEMUKAN -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-8">
                
                <!-- Header Info Sampel -->
                <div class="p-6 bg-slate-900 text-white flex justify-between items-center">
                    <div>
                        <p class="text-xs text-slate-400 font-semibold uppercase">Kode Sampel</p>
                        <h2 class="text-xl font-bold mt-1">{{ $sample->sample_code }}</h2>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-slate-400 font-semibold uppercase">Pemohon Uji</p>
                        <p class="text-sm font-semibold mt-1">{{ $sample->sampleRequest->user->name ?? '-' }}</p>
                    </div>
                </div>

                <!-- Section Visual Status Timeline -->
                @php
                    $status = $currentStatus ?? 'pending';
                    
                    $statusOrder = ['pending', 'received', 'in_progress', 'completed'];
                    $currentIndex = array_search($status, $statusOrder);
                    if ($currentIndex === false) $currentIndex = 0;

                    $steps = [
                        'pending'     => ['label' => 'Didaftarkan', 'icon' => 'fa-clipboard-check'],
                        'received'    => ['label' => 'Diterima di Lab', 'icon' => 'fa-box-open'],
                        'in_progress' => ['label' => 'Proses Pengujian', 'icon' => 'fa-vial-circle-check'],
                        'completed'   => ['label' => 'Uji Selesai', 'icon' => 'fa-file-circle-check'],
                    ];
                @endphp

                <div class="p-6 sm:p-8 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-6">Progres Pengujian Sampel</h3>
                    
                    <!-- Stepper Desktop -->
                    <div class="relative hidden sm:block mb-4">
                        <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-slate-200">
                            <div style="width: {{ (($currentIndex) / (count($statusOrder) - 1)) * 100 }}%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-blue-600 transition-all duration-500"></div>
                        </div>
                        <div class="flex justify-between items-center">
                            @foreach($statusOrder as $index => $stepKey)
                                @php
                                    $isPassed = $index <= $currentIndex;
                                    $isCurrent = $index === $currentIndex;
                                @endphp
                                <div class="flex flex-col items-center text-center w-1/4 relative">
                                    <!-- Efek Ring Animasi Hanya untuk Status Aktif -->
                                    @if($isCurrent)
                                        <span class="absolute -top-1 w-12 h-12 rounded-full bg-blue-400 opacity-75 animate-ping"></span>
                                    @endif

                                    <!-- Lingkaran Ikon (Aktif = w-12 h-12 & scale-110, Biasa = w-10 h-10) -->
                                    <div class="relative z-10 rounded-full flex items-center justify-center font-bold text-sm transition-all duration-300 {{ 
                                        $isCurrent 
                                            ? 'w-12 h-12 bg-blue-600 text-white shadow-lg scale-110 ring-4 ring-blue-100 text-lg' 
                                            : ($isPassed 
                                                ? 'w-10 h-10 bg-blue-600 text-white shadow-md' 
                                                : 'w-10 h-10 bg-slate-200 text-slate-400') 
                                    }}">
                                        <i class="fa-solid {{ $steps[$stepKey]['icon'] }}"></i>
                                    </div>

                                    <span class="mt-2 text-xs font-semibold transition-all duration-300 {{ $isCurrent ? 'text-blue-600 font-bold text-sm scale-105' : ($isPassed ? 'text-slate-700' : 'text-slate-400') }}">
                                        {{ $steps[$stepKey]['label'] }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Stepper Mobile (Vertical) -->
                    <div class="sm:hidden space-y-4">
                        @foreach($statusOrder as $index => $stepKey)
                            @php
                                $isCurrent = $index === $currentIndex;
                                $isPassed = $index <= $currentIndex;
                            @endphp
                            <div class="flex items-center gap-3">
                                <div class="rounded-full flex items-center justify-center font-bold shrink-0 transition-all duration-300 {{ 
                                    $isCurrent 
                                        ? 'w-10 h-10 bg-blue-600 text-white shadow-md ring-4 ring-blue-100 text-base' 
                                        : ($isPassed 
                                            ? 'w-8 h-8 bg-blue-600 text-white text-xs' 
                                            : 'w-8 h-8 bg-slate-200 text-slate-400 text-xs') 
                                }}">
                                    <i class="fa-solid {{ $steps[$stepKey]['icon'] }}"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold {{ $isCurrent ? 'text-blue-600 font-bold text-base' : ($isPassed ? 'text-slate-800' : 'text-slate-400') }}">
                                        {{ $steps[$stepKey]['label'] }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if($status === 'issue')
                        <div class="mt-4 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm flex items-center gap-3">
                            <i class="fa-solid fa-circle-exclamation text-xl text-red-500"></i>
                            <div>
                                <strong class="block">Terdapat Kendala pada Sampel</strong>
                                Silakan hubungi admin laboratorium kami untuk informasi lebih lanjut.
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Informasi Detail -->
                <div class="p-6 sm:p-8 space-y-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-sm">
                        <div>
                            <p class="text-slate-500 mb-1">Nama Sampel / Komoditas</p>
                            <p class="font-semibold text-slate-900">{{ $sample->sample_name ?? $sample->sampleRequest->sample_type ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-slate-500 mb-1">Tanggal Pengajuan</p>
                            <p class="font-semibold text-slate-900">
                                {{ $sample->created_at ? $sample->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') : '-' }} WIB
                            </p>
                        </div>
                    </div>

                    <!-- Download LHU jika sudah selesai -->
                    @if($status === 'completed' && !empty($sample->lhu_file_path))
                        <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="bg-emerald-600 text-white p-2.5 rounded-lg">
                                    <i class="fa-solid fa-file-pdf text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-emerald-900">Laporan Hasil Uji (LHU) Sudah Terbit</h4>
                                    <p class="text-xs text-emerald-700">Anda dapat mengunduh dokumen resmi hasil pengujian laboratorium ini.</p>
                                </div>
                            </div>
                            <a href="{{ asset('storage/' . $sample->lhu_file_path) }}" target="_blank" class="w-full sm:w-auto bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition flex items-center justify-center gap-2 shadow-sm">
                                <i class="fa-solid fa-download"></i> Unduh LHU (PDF)
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        @else
            <!-- Initial State (Belum Mencari) -->
            <div class="text-center py-12 text-slate-400 bg-white rounded-2xl border border-slate-200">
                <i class="fa-solid fa-box-archive text-5xl mb-3 text-slate-300"></i>
                <p class="text-sm">Masukkan kode sampel Anda pada kolom di atas untuk melacak status terbaru.</p>
            </div>
        @endif



    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-6 text-center text-xs text-slate-500">
        <p>&copy; {{ date('Y') }} BRMP Laboratory System. All rights reserved.</p>
    </footer>

</body>
</html>