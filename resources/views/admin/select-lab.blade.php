<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight ml-10">
            Pilih Laboratorium Penguji
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form action="{{ route('admin.create') }}" method="GET">
                    <div class="mb-6">
                        <label class="block font-bold text-gray-700 text-lg mb-3">Pilih Laboratorium Tujuan</label>
                        <select name="lab_type" required class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 p-2.5">
                            <option value="">Pilih Laboratorium </option>
                            <option value="biologi">Laboratorium Biologi </option>
                            <option value="kimia">Laboratorium Kimia </option>
                            <option value="tanah">Laboratorium Tanah </option>
                        </select>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-md shadow">
                            Lanjut ke Pilih Parameter
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>