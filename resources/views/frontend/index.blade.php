<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BRMP Sayuran - System Informasi Digitalisasi Tracking Sample Lab</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- HTML5-QRCode Library CDN -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        /* Penyesuaian video kamera agar pas di container */
        #reader video {
            object-fit: cover !important;
            border-radius: 0.75rem;
            width: 100% !important;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">

    <!-- HERO SECTION (BAGIAN ATAS) -->
    <section class="relative bg-emerald-950 text-white min-h-screen flex flex-col items-center pt-6 pb-20 px-4 overflow-hidden">
        
        <!-- Background Video + Overlay Hijau Tua -->
        <div class="absolute inset-0 z-0 overflow-hidden">
            <video autoplay loop muted playsinline class="w-full h-full object-cover">
                <source src="{{ asset('vidio/profile.mp4') }}" type="video/mp4">
                Browser Anda tidak mendukung pemutaran video.
            </video>
            <div class="absolute inset-0 bg-gradient-to-b from-emerald-950/80 via-emerald-900/70 to-emerald-950/90"></div>
        </div>

        <!-- TOMBOL PENGAJUAN ONLINE (SCROLL KE FORM PENGAJUAN) -->
        <div class="relative z-20 w-full max-w-7xl flex justify-end mb-2 px-4">
            <a href="/pendaftaran" class="bg-emerald-800 hover:bg-emerald-900 text-white px-6 py-2.5 rounded-full font-semibold text-sm transition shadow-lg border border-emerald-700/50">
                Pengajuan Online
            </a>
        </div>

        <!-- 1. LOGO BRMP SAYURAN (DITENGAH PALING ATAS) -->
        <div class="relative z-10 flex flex-col items-center text-center mb-8">
            <img 
                src="{{ asset('images/logo.png') }}" 
                alt="Logo BRMP Sayuran" 
                class="h-24 w-auto object-contain mb-3 drop-shadow-md"
                onerror="this.src='https://upload.wikimedia.org/wikipedia/commons/2/22/Logo_of_the_Ministry_of_Agriculture_of_the_Republic_of_Indonesia.png';"
            >
            <h1 class="font-extrabold text-2xl md:text-3xl text-white tracking-wide">BRMP SAYURAN</h1>
            <p class="text-xs md:text-sm text-emerald-200 font-light mt-1">Balai Perakitan & Pengujian Tanaman Sayuran</p>
        </div>

        <!-- 2. CARD LACAK SAMPEL (GLASSMORPHISM WITH QR SCANNER) -->
        <div id="lacak" class="relative z-10 w-full max-w-2xl bg-white/15 backdrop-blur-md rounded-2xl shadow-2xl p-6 md:p-8 mb-12 border border-white/25 transition-all duration-300 hover:bg-white/20 hover:border-white/40">
            <div class="text-center mb-5">
                <h2 class="text-lg md:text-xl font-bold text-white drop-shadow-sm">Lacak Status Sample Lab</h2>
                <p class="text-xs text-emerald-100 mt-1 font-light">Scan QR Code atau masukkan kode tracking unik Anda</p>
            </div>

            <!-- Tombol Kamera QR Scanner -->
            <div class="mb-5 text-center">
                <button type="button" id="btn-toggle-camera" onclick="toggleCamera()" 
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-emerald-800/90 hover:bg-emerald-800 active:scale-95 text-white font-semibold px-5 py-2.5 rounded-xl shadow border border-emerald-500/40 transition duration-200 text-sm">
                    <svg class="w-5 h-5 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span id="btn-camera-text">Open Camera Scan QR</span>
                </button>
            </div>

            <!-- Box Live Kamera Scanner (Hidden) -->
            <div id="camera-box" class="hidden mb-5 bg-slate-950/80 p-3 rounded-2xl relative shadow-inner border border-white/20">
                <div id="reader" class="w-full overflow-hidden rounded-xl"></div>
                <div class="text-center mt-2">
                    <span class="text-xs text-emerald-200 bg-emerald-950/80 px-3 py-1 rounded-full border border-emerald-700/50">
                        Arahkan Camera ke QR Code sample
                    </span>
                </div>
            </div>

            <!-- Pemisah / Divider -->
            <div class="relative flex py-1 items-center mb-5">
                <div class="flex-grow border-t border-white/20"></div>
                <span class="flex-shrink mx-4 text-xs font-semibold text-emerald-200/70 uppercase tracking-wider">Atau Ketik Manual</span>
                <div class="flex-grow border-t border-white/20"></div>
            </div>
            
            <!-- Form Input Manual -->
        <div class="bg-transparant rounded-2xl shadow-sm  p-6 mb-8">
                    <form action="{{ route('tracking.search') }}" method="POST" class="space-y-3">
                        @csrf
                        <label for="code" class="block text-sm font-semibold text-white-700">
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
        </div>

        <!-- 3. JUDUL UTAMA BANNER -->
        <div class="relative z-10 text-center max-w-4xl mx-auto space-y-6">
            <h2 class="text-3xl md:text-5xl font-extrabold tracking-tight leading-tight text-white">
                Menghubungkan Mutu Benih<br>untuk Ketahanan Pangan
            </h2>
            <p class="text-sm md:text-base text-emerald-100 max-w-2xl mx-auto font-light leading-relaxed">
                Ajukan pengujian sample tanaman sayuran secara online dan pantau prosesnya secara real-time, dari pengiriman hingga hasil akhir keluar.
            </p>
            <div class="pt-2">
                <a href="#layanan-kami" class="inline-block border border-white/70 hover:border-white hover:bg-white/10 text-white font-medium px-7 py-2.5 rounded-full text-sm transition backdrop-blur-sm">
                    Jelajahi Lebih Jauh
                </a>
            </div>
        </div>

    </section>

    <!-- SECTION: FORM PENGAJUAN ONLINE -->
    

    <!-- SECTION: LAYANAN KAMI (3 CARD UTAMA) -->
    <section id="layanan-kami" class="py-20 px-6 max-w-7xl mx-auto">
        <div class="text-center mb-14">
            <span class="text-xs font-bold text-emerald-700 uppercase tracking-widest block mb-2">LAYANAN KAMI</span>
            <h2 class="text-3xl font-extrabold text-gray-900">Proses Pengujian Transparan & Terukur</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Card 1 -->
            <div class="bg-white p-8 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition">
                <div class="w-12 h-12 bg-emerald-100 text-emerald-700 rounded-xl flex items-center justify-center mb-6">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Pengajuan Online</h3>
                <p class="text-sm text-gray-500 leading-relaxed">Ajukan pengujian sample kapan saja tanpa perlu datang langsung ke kantor.</p>
            </div>

            <!-- Card 2 -->
            <div class="bg-white p-8 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition">
                <div class="w-12 h-12 bg-emerald-100 text-emerald-700 rounded-xl flex items-center justify-center mb-6">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Code Tracking Unik</h3>
                <p class="text-sm text-gray-500 leading-relaxed">Setiap pengajuan mendapat Code tracking unik untuk memantau status secara real-time.</p>
            </div>

            <!-- Card 3 -->
            <div class="bg-white p-8 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition">
                <div class="w-12 h-12 bg-emerald-100 text-emerald-700 rounded-xl flex items-center justify-center mb-6">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 00-2 2z"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Notifikasi WhatsApp</h3>
                <p class="text-sm text-gray-500 leading-relaxed">Update status pembayaran dan hasil pengujian dikirim langsung ke WhatsApp Anda.</p>
            </div>
        </div>
    </section>

    <!-- SECTION: TENTANG KAMI -->
    <section class="bg-emerald-900 text-white py-20 px-6">
        <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div>
                <span class="text-xs font-bold text-emerald-300 uppercase tracking-widest block mb-2">TENTANG KAMI</span>
                <h2 class="text-3xl md:text-4xl font-extrabold mb-6 leading-tight">Balai Perakitan dan Pengujian Tanaman Sayuran</h2>
                <p class="text-emerald-100 text-sm md:text-base leading-relaxed mb-6 font-light">
                    BRMP Sayuran berkomitmen mendukung ketahanan pangan nasional melalui perakitan varietas unggul serta pengujian mutu sample benih dan tanaman sayuran yang akurat, terpercaya, dan terdigitalisasi.
                </p>
            </div>

            <!-- Fitur Statistik / Keunggulan -->
            <div class="grid grid-cols-2 gap-6">
                <div class="bg-emerald-800/60 p-6 rounded-2xl border border-emerald-700/50 backdrop-blur-sm text-center">
                    <span class="text-3xl font-extrabold text-white block mb-1">24/7</span>
                    <span class="text-xs text-emerald-200">Pengajuan Online</span>
                </div>
                <div class="bg-emerald-800/60 p-6 rounded-2xl border border-emerald-700/50 backdrop-blur-sm text-center">
                    <span class="text-3xl font-extrabold text-white block mb-1">QR</span>
                    <span class="text-xs text-emerald-200">Pelacakan Cepat</span>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="bg-emerald-950 text-emerald-400 py-6 text-center text-xs border-t border-emerald-900">
        <p>&copy; <?php echo date('Y'); ?> BRMP Sayuran - Kementerian Pertanian RI. Hak cipta dilindungi undang-undang.</p>
    </footer>

    <!-- SCRIPT QR SCANNER & SMOOTH SCROLL -->
    <script>
        let html5QrcodeScanner = null;
        let isCameraActive = false;

        function toggleCamera() {
            const cameraBox = document.getElementById('camera-box');
            const btnText = document.getElementById('btn-camera-text');

            if (isCameraActive) {
                stopCamera();
            } else {
                cameraBox.classList.remove('hidden');
                btnText.innerText = "Tutup Kamera";

                html5QrcodeScanner = new Html5Qrcode("reader");
                const config = { fps: 15, qrbox: { width: 220, height: 220 }, aspectRatio: 1.0 };

                html5QrcodeScanner.start(
                    { facingMode: "environment" },
                    config,
                    onScanSuccess,
                    onScanFailure
                ).then(() => {
                    isCameraActive = true;
                }).catch(err => {
                    console.error("Gagal membuka kamera: ", err);
                    alert("Kamera tidak dapat diakses! Pastikan Anda memberikan izin akses kamera pada browser.");
                    stopCamera();
                });
            }
        }

        function stopCamera() {
            if (html5QrcodeScanner && isCameraActive) {
                html5QrcodeScanner.stop().then(() => {
                    html5QrcodeScanner.clear();
                    document.getElementById('camera-box').classList.add('hidden');
                    document.getElementById('btn-camera-text').innerText = "Buka Kamera Scan QR";
                    isCameraActive = false;
                }).catch(err => console.error("Gagal menghentikan kamera: ", err));
            } else {
                document.getElementById('camera-box').classList.add('hidden');
                document.getElementById('btn-camera-text').innerText = "Buka Kamera Scan QR";
                isCameraActive = false;
            }
        }

        function onScanSuccess(decodedText, decodedResult) {
            stopCamera();
            let kodeHasil = decodedText.trim();

            if (kodeHasil.includes('http://') || kodeHasil.includes('https://')) {
                try {
                    const urlObj = new URL(kodeHasil);
                    const params = new URLSearchParams(urlObj.search);
                    if (params.get('kode')) {
                        kodeHasil = params.get('kode');
                    } else if (params.get('kode_sample')) {
                        kodeHasil = params.get('kode_sample');
                    }
                } catch (e) {
                    console.log("Parsing URL error");
                }
            }

            document.getElementById('kode_input').value = kodeHasil.toUpperCase();
            playBeepSound();

            setTimeout(() => {
                document.getElementById('form-lacak').submit();
            }, 300);
        }

        function onScanFailure(error) {
            // Abaikan error per frame
        }

        function playBeepSound() {
            try {
                const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.type = "sine";
                osc.frequency.value = 800;
                gain.gain.value = 0.1;
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.start();
                osc.stop(audioCtx.currentTime + 0.15);
            } catch (e) {
                console.log("Audio Context error");
            }
        }

        // SMOOTH SCROLL SCRIPT
        document.addEventListener('DOMContentLoaded', function() {
            function customSmoothScroll(targetSelector, duration) {
                const target = document.querySelector(targetSelector);
                if (!target) return;
                
                const targetPosition = target.getBoundingClientRect().top + window.pageYOffset;
                const startPosition = window.pageYOffset;
                const distance = targetPosition - startPosition;
                let startTime = null;

                function animationLoop(currentTime) {
                    if (startTime === null) startTime = currentTime;
                    const timeElapsed = currentTime - startTime;
                    
                    const progress = Math.min(timeElapsed / duration, 1);
                    const ease = progress < 0.5 
                        ? 4 * progress * progress * progress 
                        : 1 - Math.pow(-2 * progress + 2, 3) / 2;
                    
                    window.scrollTo(0, startPosition + (distance * ease));

                    if (timeElapsed < duration) {
                        requestAnimationFrame(animationLoop);
                    }
                }

                requestAnimationFrame(animationLoop);
            }

            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('href');
                    if (targetId && targetId !== '#') {
                        customSmoothScroll(targetId, 1000);
                    }
                });
            });
        });
    </script>

</body>
</html>