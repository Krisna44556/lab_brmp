<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Form Pengajuan Pengujian Sampel Online') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded shadow">

                @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>- {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('user.store_request') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- Informasi Akun Pemohon (Otomatis) -->
                    <div class="mb-4 bg-gray-50 p-3 rounded border">
                        <p class="text-xs text-gray-500 font-bold uppercase">Informasi Pemohon</p>
                        <p class="text-sm font-semibold text-gray-800">{{ Auth::user()->name }} ({{ Auth::user()->phone_number ?? 'No. HP belum diisi' }})</p>
                    </div>

                    <!-- Nama Sampel -->
                    <div class="mb-4">
                        <label class="block font-bold mb-1">Nama / Jenis Sampel:</label>
                        <input type="text" name="sample_name" class="w-full border p-2 rounded" placeholder="Contoh: Benih Jagung Hibrida" required>
                    </div>

                    <!-- Bukti Transfer -->
                    <div class="mb-4">
                        <label class="block font-bold mb-1">Bukti Transfer Pembayaran:</label>
                        <input type="file" name="payment_proof" class="w-full border p-2 rounded" required>
                        <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG (Maksimal 2MB)</p>
                    </div>

                    <div class="flex items-center justify-between mt-6">
                        <a href="{{ route('dashboard') }}" class="text-gray-600 hover:underline text-sm">Batal</a>
                        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Kirim Permohonan</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>