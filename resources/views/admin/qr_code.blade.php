<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Label QR Code - {{ $sampleRequest->request_code }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body class="bg-gray-100 flex flex-col items-center justify-center min-h-screen p-4">

    <!-- Tombol Cetak -->
    <div class="mb-4 no-print">
        <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg shadow">
            Cetak Label QR Code
        </button>
    </div>

    <!-- Kartu Label QR Code -->
    <div class="bg-white border-2 border-gray-800 p-6 rounded-xl shadow-lg w-80 text-center">
        <h2 class="font-bold text-lg text-gray-800 border-b pb-2 mb-4">LABORATORIUM BRMP</h2>
        
        <!-- Render QR Code SVG -->
        <div class="flex justify-center mb-4">
            {!! QrCode::size(150)->generate($sampleRequest->request_code) !!}
        </div>

        <div class="space-y-1 text-sm text-gray-700">
            <p class="font-bold text-base text-blue-700">{{ $sampleRequest->request_code }}</p>
            <p><span class="font-semibold">Pemohon:</span> {{ $sampleRequest->user->name ?? '-' }}</p>
            <p><span class="font-semibold">Sampel:</span> {{ $sampleRequest->sample_type }} ({{ $sampleRequest->sample_quantity }} item)</p>
            <p class="text-xs text-gray-400 mt-2">Scan untuk melihat detail permohonan</p>
        </div>
    </div>

    <div class="mb-4 no-print">
        <a href="/admin/dashboard" class="relative top-8 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-5 rounded-lg shadow">
            Kembali Ke Dashboard
    </a>
    </div>

</body>
</html>