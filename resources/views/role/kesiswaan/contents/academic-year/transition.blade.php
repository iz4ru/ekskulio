@extends('role.kesiswaan.layouts.app')
@section('title', 'Ekskulio | Transisi Tahun Ajaran')
@section('content')

    <div class="p-4 border-2 border-gray-200 border-dashed rounded-md w-full mt-14">

                {{-- Alert Section --}}
        <div class="w-full space-y-3">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible relative mb-4 w-full text-sm py-2 px-4 bg-green-100 text-green-500 border border-green-500 rounded-md opacity-0 transition-opacity duration-150 ease-in-out"
                    role="alert" id="successAlert">
                    <i class="fa fa-circle-check absolute left-4 top-1/2 -translate-y-1/2"></i>
                    <p class="ml-6">{{ session('success') }}</p>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible relative mb-4 w-full text-sm py-2 px-4 bg-red-100 text-red-500 border border-red-500 rounded-md opacity-0 transition-opacity duration-150 ease-in-out"
                    role="alert" id="errorAlert">
                    <i class="fa fa-circle-exclamation absolute left-4 top-1/2 -translate-y-1/2"></i>
                    <ul class="list-none m-0 p-0">
                        @foreach ($errors->all() as $error)
                            <li class="ml-6">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <a href="{{ route('academic-years.index') }}"
            class="text-sm inline-flex items-center gap-1 font-semibold text-gray-500 hover:text-[#0083E9] active:scale-[0.98] transition-all duration-300 ease-out">
            <i class="fa-solid fa-chevron-left"></i>
            <span>Kembali</span>
        </a>

        <div class="flex gap-4 my-4 justify-between items-start">
            <div>
                <h1 class="text-xl lg:text-2xl font-semibold text-gray-600">Transisi Tahun Ajaran</h1>
                <p class="text-sm lg:text-base text-gray-400">Naikkan kelas siswa dan pindahkan ke tahun ajaran baru.</p>
            </div>
        </div>

        <div class="my-4 border-t-2 border-dashed border-gray-300 w-full"></div>

        <div class="bg-amber-50 border border-amber-200 rounded-md p-4 mb-6">
            <div class="flex items-start gap-3">
                <i class="fa-solid fa-triangle-exclamation text-amber-500 mt-1"></i>
                <div>
                    <h3 class="font-semibold text-amber-800">Perhatian!</h3>
                    <p class="text-sm text-amber-700 mt-1">
                        {{ $previewInfo['description'] ?? 'Fitur ini akan melakukan hal berikut secara otomatis:' }}
                    </p>
                    @if(isset($previewInfo['warning']))
                        <p class="text-sm text-amber-700 mt-2 font-medium">{{ $previewInfo['warning'] }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="bg-white border-2 border-dashed border-gray-200 rounded-md p-6">
                <h3 class="font-semibold text-gray-700 mb-4">
                    <i class="fa-solid fa-users text-blue-500 mr-2"></i>
                    Rekap Siswa ({{ $currentYear->year }})
                </h3>
                <div class="space-y-3">
                    @if($previewInfo['type'] === 'academic_year')
                        <div class="flex justify-between items-center p-3 bg-blue-50 rounded-md">
                            <span class="text-gray-600">Kelas X</span>
                            <span class="font-bold text-blue-600">{{ $studentsReady['x'] ?? 0 }} siswa</span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-green-50 rounded-md">
                            <span class="text-gray-600">Kelas XI</span>
                            <span class="font-bold text-green-600">{{ $studentsReady['xi'] ?? 0 }} siswa</span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-gray-100 rounded-md">
                            <span class="text-gray-600">Kelas XII</span>
                            <span class="font-bold text-gray-600">{{ $studentsReady['xii'] ?? 0 }} siswa</span>
                        </div>
                    @else
                        <div class="flex justify-between items-center p-3 bg-blue-50 rounded-md">
                            <span class="text-gray-600">Total Siswa Aktif</span>
                            <span class="font-bold text-blue-600">{{ ($studentsReady['x'] ?? 0) + ($studentsReady['xi'] ?? 0) + ($studentsReady['xii'] ?? 0) }} siswa</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="bg-white border-2 border-dashed border-gray-200 rounded-md p-6">
                <h3 class="font-semibold text-gray-700 mb-4">
                    <i class="fa-solid fa-clipboard-list text-purple-500 mr-2"></i>
                    Preview Keanggotaan
                </h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center p-3 bg-blue-50 rounded-md">
                        <span class="text-gray-600">Total Keanggotaan Aktif</span>
                        <span class="font-bold text-blue-600">{{ $membershipPreview['total_active'] ?? 0 }}</span>
                    </div>
                    @if($previewInfo['type'] === 'academic_year')
                        <div class="flex justify-between items-center p-3 bg-green-50 rounded-md">
                            <span class="text-gray-600">Akan Dipindahkan</span>
                            <span class="font-bold text-green-600">{{ $membershipPreview['to_migrate'] ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-red-50 rounded-md">
                            <span class="text-gray-600">Akan Ditandai Selesai</span>
                            <span class="font-bold text-red-600">{{ $membershipPreview['to_close'] ?? 0 }}</span>
                        </div>
                    @else
                        <div class="flex justify-between items-center p-3 bg-green-50 rounded-md">
                            <span class="text-gray-600">Akan Dipindahkan</span>
                            <span class="font-bold text-green-600">{{ $membershipPreview['to_migrate'] ?? 0 }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            @if($previewInfo['type'] === 'academic_year')
                <div class="bg-green-50 border border-green-200 rounded-md p-4">
                    <h4 class="font-medium text-green-800 mb-2">
                        <i class="fa-solid fa-arrow-up mr-2"></i>
                        Hasil Upgrade
                    </h4>
                    <ul class="text-sm text-green-700 space-y-1">
                        <li>X → XI: {{ $studentsReady['x'] ?? 0 }} siswa</li>
                        <li>XI → XII (LULUS): {{ $studentsReady['xi'] ?? 0 }} siswa</li>
                        <li>XII (Lulus): {{ $studentsReady['xii'] ?? 0 }} siswa</li>
                    </ul>
                </div>
            @endif

            <div class="bg-purple-50 border border-purple-200 rounded-md p-4">
                <h4 class="font-medium text-purple-800 mb-2">
                    <i class="fa-solid fa-shirt mr-2"></i>
                    Hasil Transisi Membership
                </h4>
                <ul class="text-sm text-purple-700 space-y-1">
                    <li>Membership dipindahkan: {{ $membershipPreview['to_migrate'] ?? 0 }}</li>
                    @if($previewInfo['type'] === 'academic_year')
                        <li>Membership ditutup: {{ $membershipPreview['to_close'] ?? 0 }}</li>
                    @endif
                </ul>
            </div>
        </div>

        <form action="{{ route('academic-years.transition.process') }}" method="POST" id="transition-form">
            @csrf

            <div class="bg-white border-2 border-dashed border-gray-200 rounded-md p-6 mb-6">
                <h3 class="font-semibold text-gray-700 mb-4">Pilih Tahun Ajaran Tujuan</h3>

                <div class="mb-4">
                    <label for="new_academic_year_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Tahun Ajaran Baru <span class="text-red-500">*</span>
                    </label>
                    <select name="new_academic_year_id" id="new_academic_year_id"
                        class="w-full md:w-1/2 px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('new_academic_year_id') border-red-500 @enderror"
                        required>
                        <option value="">-- Pilih Tahun Ajaran --</option>
                        @foreach (\App\Models\AcademicYear::where('is_active', false)->orderBy('year')->get() as $ay)
                            <option value="{{ $ay->id }}" {{ old('new_academic_year_id') == $ay->id ? 'selected' : '' }}>
                                {{ $ay->display_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('new_academic_year_id')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Password <span class="text-red-500">*</span>
                    </label>
                    <input type="password" name="password" id="password"
                        class="w-full md:w-1/2 px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('password') border-red-500 @enderror"
                        placeholder="Masukkan password Anda untuk konfirmasi"
                        required>
                    @error('password')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex gap-4">
                <button type="submit" id="submit-btn"
                    class="inline-flex cursor-pointer items-center px-6 py-2.5 gap-2 text-sm font-medium text-center text-white bg-amber-500 rounded-md hover:bg-amber-600 active:scale-[0.98] transition-all duration-300">
                    <i class="fa-solid fa-forward text-sm"></i>
                    <span>Proses Transisi</span>
                </button>
            </div>
        </form>

    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.getElementById('transition-form');
                const submitBtn = document.getElementById('submit-btn');

                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    Swal.fire({
                        title: 'Konfirmasi Transisi Tahun Ajaran',
                        html: `<p class="text-left">Apakah Anda yakin ingin melanjutkan proses transisi?</p>
                           <p class="text-left text-sm text-gray-600 mt-2">Aksi ini akan:</p>
                           <ul class="text-left text-sm text-gray-600 mt-1 list-disc list-inside">
                               <li>Menaikkan kelas semua siswa aktif</li>
                               <li>Mengubah status siswa kelas XII menjadi LULUS</li>
                               <li>Memindahkan {{ $membershipPreview['to_promote'] ?? 0 }} keanggotaan ekstrakurikuler</li>
                               <li>Menandai {{ $membershipPreview['to_stop'] ?? 0 }} keanggotaan sebagai selesai</li>
                           </ul>
                           <p class="text-left text-sm text-amber-600 mt-3 font-medium">Aksi ini tidak dapat dibatalkan!</p>`,
                        icon: 'warning',
                        showCancelButton: true,
                        cancelButtonText: 'Batal',
                        confirmButtonText: 'Ya, Proses Transisi',
                        confirmButtonColor: '#F59E0B',
                        cancelButtonColor: '#6B7280',
                        allowOutsideClick: false,
                        allowEscapeKey: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            submitBtn.disabled = true;
                            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>Memproses...</span>';
                            form.submit();
                        }
                    });
                });
            });
        </script>
    @endpush

@endsection
