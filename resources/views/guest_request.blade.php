<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Pengajuan Pengujian Sampel</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans antialiased">
    <div class="min-h-screen flex flex-col justify-center items-center py-12 px-4 sm:px-6 lg:px-8">
        
        <div class="max-w-xl w-full bg-white p-8 rounded-lg shadow-md border border-gray-200">
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-2">Form Pengajuan Pengujian Sampel</h2>
            <p class="text-sm text-center text-gray-600 mb-6">Isi formulir di bawah ini untuk mengajukan pengujian online secara cepat.</p>

            <!-- Tampilkan Error Jika Ada -->
            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li class="text-sm">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('guest.store_request') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- Nama Lengkap Pemohon -->
                <div class="mb-4">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Nama Lengkap Pemohon / Instansi:</label>
                    <input type="text" name="applicant_name" value="{{ old('applicant_name') }}" placeholder="Contoh: Ahmad Subagja" class="w-full border border-gray-300 p-2.5 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
                </div>

                <!-- Nomor WhatsApp / HP -->
                <div class="mb-4">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Nomor WhatsApp / HP Aktif:</label>
                    <input type="text" name="phone_number" value="{{ old('phone_number') }}" placeholder="Contoh: 08123456789" class="w-full border border-gray-300 p-2.5 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
                    <p class="text-xs text-gray-500 mt-1">Digunakan untuk notifikasi WhatsApp & pembuatan akun login Anda.</p>
                </div>

                <!-- Nama / Jenis Sampel -->
                <div class="mb-4">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Nama / Jenis Sampel:</label>
                    <input type="text" name="sample_name" value="{{ old('sample_name') }}" placeholder="Contoh: Benih Jagung Hibrida" class="w-full border border-gray-300 p-2.5 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
                </div>

                <!-- Unggah Bukti Transfer -->
                <div class="mb-6">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Bukti Transfer Pembayaran:</label>
                    <input type="file" name="payment_proof" class="w-full border border-gray-300 p-2 rounded-md bg-gray-50 text-sm" required>
                    <p class="text-xs text-gray-500 mt-1">Format: JPG, JPEG, PNG (Maksimal 2MB)</p>
                </div>

                <!-- Tombol Submit -->
                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-md shadow transition duration-200">
                    Kirim Permohonan & Masuk
                </button>
            </form>
        </div>

    </div>
</body>
</html>