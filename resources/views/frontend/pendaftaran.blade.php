<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Online BRMP SAYURAN </title>
</head>
<body>
    

<script src="https://cdn.tailwindcss.com"></script>
<!-- CDN Lucide Icons -->
<script src="https://unpkg.com/lucide@latest"></script>



    <button class="bg-green-600 ml-4 sm:ml-40 mt-6 hover:bg-green-700 text-white font-bold py-2.5 px-6 rounded-md shadow transition">
        <a href="/" class="text-sm text-white font-medium">Kembali Ke Dashboard</a>
    </button>
    
    <div class="min-h-screen bg-slate-50/50 py-10 px-4 sm:px-6 lg:px-8 flex justify-center items-center font-sans antialiased">
        <div class="max-w-5xl w-full bg-white rounded-3xl shadow-xl shadow-slate-200/60 border border-slate-100 overflow-hidden">
            
            <!-- Header Form dengan Gradient UI -->
            <div class="bg-gradient-to-r from-green-600 via-green-800 to-green-700 p-6 sm:p-8 text-white relative overflow-hidden">
                <div class="relative z-10">
                    <h2 class="text-2xl sm:text-3xl font-bold tracking-tight">Form Pendaftaran Pengujian Sampel</h2>
                    <p class="text-green-100 text-sm mt-1 leading-relaxed">Isi formulir di bawah ini dengan data sampel yang valid untuk memulai proses pengujian.</p>
                </div>
                <!-- Accent Lighting Glow Background -->
                <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
            </div>

            <!-- Form Pengajuan -->
            <form action="{{ route('pendaftaran.store') }}" method="POST" id="formPengajuan" class="p-6 sm:p-8 space-y-6">
                @csrf

                <!-- Section 1: Data Pemohon -->
                <div class="space-y-4">
                    <h3 class="text-lg font-bold text-slate-800 border-b pb-2">1. Informasi Pemohon</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Nama Pemohon -->
                        <div class="space-y-1.5">
                            <label class="block text-sm font-semibold text-slate-700">Nama Lengkap <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <i data-lucide="user" class="w-4 h-4"></i>
                                </div>
                                <input type="text" name="pemohon_name" required value="{{ old('pemohon_name') }}"
                                    placeholder="Masukkan nama lengkap" 
                                    class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-600 transition">
                            </div>
                        </div>

                        <!-- No. WhatsApp / HP -->
                        <div class="space-y-1.5">
                            <label class="block text-sm font-semibold text-slate-700">No. WhatsApp / HP <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <i data-lucide="phone" class="w-4 h-4"></i>
                                </div>
                                <input type="tel" name="phone_number" required value="{{ old('phone_number') }}"
                                    placeholder="081234567890" 
                                    class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-600 transition">
                            </div>
                        </div>
                    </div>

                    <!-- Informasi Lokasi -->
                    <div class="pt-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Alamat Lengkap <span class="text-rose-500">*</span></label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs text-slate-600 mb-1">Desa/Kelurahan</label>
                                <input type="text" name="village" value="{{ old('village') }}" required placeholder="Desa / Kelurahan" class="w-full pl-4 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-600 transition">
                            </div>
                            <div>
                                <label class="block text-xs text-slate-600 mb-1">Kecamatan</label>
                                <input type="text" name="district" value="{{ old('district') }}" required placeholder="Kecamatan" class="w-full pl-4 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-600 transition">
                            </div>
                            <div>
                                <label class="block text-xs text-slate-600 mb-1">Kabupaten/Kota</label>
                                <input type="text" name="regency" value="{{ old('regency') }}" required placeholder="Kabupaten / Kota" class="w-full pl-4 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-600 transition">
                            </div>
                            <div>
                                <label class="block text-xs text-slate-600 mb-1">Provinsi</label>
                                <input type="text" name="province" value="{{ old('province') }}" required placeholder="Provinsi" class="w-full pl-4 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-600 transition">
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="border-slate-100 my-6">

                <!-- Section 2: Detail Sampel & Parameter -->
                <div class="space-y-4">
                    <h3 class="text-lg font-bold text-slate-800 border-b pb-2">2. Detail Sampel & Pengujian</h3>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <!-- Jenis Sampel -->
                        <div class="sm:col-span-2 space-y-1.5">
                            <label for="sample_type" class="block text-sm font-semibold text-slate-700">
                                Jenis Sampel <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <i data-lucide="box" class="w-4 h-4"></i>
                                </div>
                                <input type="text" name="sample_type" id="sample_type" required placeholder="Contoh: Tanah / Air / Pupuk" class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-600 transition">
                            </div>
                        </div>

                        <!-- Jumlah Sampel -->
                        <div class="space-y-1.5">
                            <label for="sample_quantity" class="block text-sm font-semibold text-slate-700">
                                Jumlah Sampel <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <i data-lucide="layers" class="w-4 h-4"></i>
                                </div>
                                <input type="number" name="sample_quantity" id="sample_quantity" min="1" value="1" required class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-600 transition">
                            </div>
                        </div>
                    </div>

                    <!-- Kategori Lab -->
                    <div class="space-y-1.5">
                        <label for="lab_type" class="block text-sm font-semibold text-slate-700">
                            Kategori Lab <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i data-lucide="building-2" class="w-4 h-4"></i>
                            </div>
                            <!-- ID disamakan dengan JavaScript (lab_type) -->
                            <select name="lab_type" id="lab_type" required class="w-full pl-10 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-600 transition appearance-none cursor-pointer">
                                <option value="" disabled selected>Pilih Kategori Laboratorium</option>
                                <!-- Gunakan ID atau Slug Laboratorium sebagai value -->
                                <option value="kimia">Lab Kimia</option>
                                <option value="biologi">Lab Biologi</option>
                                <option value="tanah">Lab Tanah</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-400">
                                <i data-lucide="chevron-down" class="w-4 h-4"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Parameter Pengujian Async Load Container -->
                    <div class="mt-4">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Pilih Parameter Pengujian <span class="text-rose-500">*</span></label>
                        <div id="parameter-container" class="p-4 border border-slate-200 rounded-xl bg-slate-50/50 min-h-[100px] flex items-center justify-center">
                            <p class="text-sm text-amber-600 italic">Silakan pilih Kategori Laboratorium terlebih dahulu.</p>
                        </div>

                        <div class="mt-4 p-3 bg-slate-100 rounded-lg flex justify-between items-center">
                            <span class="font-semibold text-slate-700">Total Biaya:</span>
                            <span id="total-amount-display" class="text-lg font-bold text-green-600">Rp 0</span>
                        </div>
                        
                    </div>

                </div>

                <!-- Footer Form & Tombol Submit -->
                <div class="pt-6 flex flex-col sm:flex-row items-center justify-between gap-4 border-t border-slate-100 mt-8">
                    <p class="text-xs text-slate-400 flex items-center gap-1.5">
                        <i data-lucide="shield-check" class="w-4 h-4 text-emerald-500"></i> Data terlindungi oleh sistem.
                    </p>
                    <button type="submit" id="btnSubmit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-semibold text-sm rounded-xl shadow-lg shadow-green-200 hover:shadow-green-300 transform active:scale-95 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                        <span>Kirim Pendaftaran</span>
                        <i data-lucide="send" class="w-4 h-4"></i>
                    </button>
                </div>
            </form>
        </body>
</html>

   <script>
        document.addEventListener('DOMContentLoaded', function () {
            const labSelect = document.getElementById('lab_type');
            const parameterContainer = document.getElementById('parameter-container');
            const formPengajuan = document.getElementById('formPengajuan');
            const inputQuantity = document.querySelector('input[name="sample_quantity"]');

            // 1. FUNGSI HITUNG TOTAL BIAYA
            function calculateTotal() {
                let pricePerSample = 0;

                // Ambil semua checkbox dengan name="services[]" yang dicentang
                const checkedBoxes = document.querySelectorAll('input[name="services[]"]:checked');
                
                checkedBoxes.forEach(function (checkbox) {
                    pricePerSample += parseFloat(checkbox.getAttribute('data-price') || 0);
                });

                // Ambil jumlah sampel (default 1)
                const quantity = parseInt(inputQuantity ? inputQuantity.value : 1) || 1;

                // Total = Harga per sampel x Jumlah Sampel
                const total = pricePerSample * quantity;

                // Update tampilan ke elemen #total-amount-display
                const displayTotal = document.getElementById('total-amount-display');
                if (displayTotal) {
                    displayTotal.innerText = 'Rp ' + total.toLocaleString('id-ID');
                }
            }

            // 2. EVENT LISTENER DROPDOWN KATEGORI LAB
            if (labSelect) {
                labSelect.addEventListener('change', function () {
                    const labId = this.value;

                    if (!labId) {
                        parameterContainer.className = "p-4 border border-slate-200 rounded-xl bg-slate-50/50 min-h-[100px] flex items-center justify-center";
                        parameterContainer.innerHTML = `<p class="text-sm text-amber-600 italic">Silakan pilih Kategori Laboratorium terlebih dahulu.</p>`;
                        calculateTotal();
                        return;
                    }

                    // Tampilkan status loading
                    parameterContainer.className = "p-4 border border-slate-200 rounded-xl bg-slate-50/50 min-h-[100px] flex items-center justify-center";
                    parameterContainer.innerHTML = `<p class="text-sm text-slate-500 italic flex items-center gap-2"><i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> Memuat parameter pengujian...</p>`;
                    if (typeof lucide !== 'undefined') lucide.createIcons();

                    // Fetch Data dari API Web
                    fetch(`/get-parameters-by-lab/${encodeURIComponent(labId)}`)
                        .then(response => {
                            if (!response.ok) throw new Error('Gagal memuat data dari server');
                            return response.json();
                        })
                        .then(res => {
                            const parameters = res.data || res;

                            if (!parameters || parameters.length === 0) {
                                parameterContainer.className = "p-4 border border-slate-200 rounded-xl bg-slate-50/50 min-h-[100px] flex items-center justify-center";
                                parameterContainer.innerHTML = `<p class="text-sm text-amber-600 italic">Belum ada layanan parameter yang terdaftar untuk laboratorium ini.</p>`;
                                calculateTotal();
                                return;
                            }

                            // Render Checkbox Parameter
                            parameterContainer.className = "p-3 border border-slate-200 rounded-xl bg-slate-50 max-h-60 overflow-y-auto";
                            let html = '<div class="grid grid-cols-1 md:grid-cols-2 gap-3">';
                            
                            parameters.forEach(param => {
                                const id = param.id;
                                const name = param.name || param.service_name || 'Parameter';
                                const price = param.price ? `Rp ${parseInt(param.price).toLocaleString('id-ID')}` : 'Rp 0';

                                html += `
                                    <label class="flex items-center space-x-3 p-3 bg-white border border-slate-200 rounded-xl shadow-sm hover:bg-slate-100/80 cursor-pointer transition">
                                        <input type="checkbox" 
                                            name="services[]" 
                                            value="${id}" 
                                            data-price="${param.price || 0}" 
                                            class="service-checkbox rounded text-green-600 focus:ring-green-500 w-4 h-4 border-slate-300">
                                        <div class="flex-1 min-w-0">
                                            <span class="block text-sm font-medium text-slate-800 truncate">${name}</span>
                                            <span class="block text-xs font-semibold text-green-600">${price}</span>
                                        </div>
                                    </label>
                                `;
                            });
                            
                            html += '</div>';
                            parameterContainer.innerHTML = html;
                            calculateTotal(); // Hitung ulang total awal
                        })
                        .catch(error => {
                            console.error('Error fetching parameters:', error);
                            parameterContainer.className = "p-4 border border-rose-200 bg-rose-50/50 rounded-xl min-h-[100px] flex items-center justify-center";
                            parameterContainer.innerHTML = `<p class="text-sm text-rose-600 font-medium">Gagal mengambil parameter. Pastikan koneksi terhubung.</p>`;
                            calculateTotal();
                        });
                });
            }

            // 3. EVENT LISTENER DETEKSI UBAH CENTANG & JUMLAH SAMPEL
            document.addEventListener('change', function (e) {
                if (e.target && (e.target.name === 'services[]' || e.target.name === 'sample_quantity')) {
                    calculateTotal();
                }
            });

            if (inputQuantity) {
                inputQuantity.addEventListener('input', calculateTotal);
            }

            // 4. SUBMIT FORM HANDLER
            if (formPengajuan) {
                formPengajuan.addEventListener('submit', async function(e) {
                    e.preventDefault();

                    const checkedServices = document.querySelectorAll('input[name="services[]"]:checked');
                    if (checkedServices.length === 0) {
                        alert('Silakan pilih setidaknya satu parameter pengujian.');
                        return;
                    }

                    const btnSubmit = document.getElementById('btnSubmit');
                    btnSubmit.disabled = true;
                    btnSubmit.innerHTML = '<span>Mengirim...</span>';

                    const formData = new FormData(this);
                    
                    try {
                        const response = await fetch("{{ url('/api/public/sample-request/store') }}", {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: formData
                        });

                        const result = await response.json();

                        if (response.ok && (result.status === 'success' || result.success)) {
                            alert('Pendaftaran Berhasil!\nKode Request Anda: ' + (result.request_code || result.data?.request_code || 'OK'));
                            this.reset();
                            if (parameterContainer) {
                                parameterContainer.className = "p-4 border border-slate-200 rounded-xl bg-slate-50/50 min-h-[100px] flex items-center justify-center";
                                parameterContainer.innerHTML = `<p class="text-sm text-amber-600 italic">Silakan pilih Kategori Laboratorium terlebih dahulu.</p>`;
                            }
                            calculateTotal();
                        } else {
                            alert('Gagal: ' + (result.message || 'Terjadi kesalahan pada data submission.'));
                        }

                    } catch (error) {
                        console.error('Error Connection:', error);
                        alert('Gagal terhubung ke server Backend API!');
                    } finally {
                        btnSubmit.disabled = false;
                        btnSubmit.innerHTML = '<span>Kirim Pendaftaran</span><i data-lucide="send" class="w-4 h-4"></i>';
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    }
                });
            }

            // Inisialisasi awal Ikon Lucide
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
</script>
        </div>
    </div>
