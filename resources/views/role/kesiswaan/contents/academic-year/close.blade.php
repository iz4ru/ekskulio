@extends('role.kesiswaan.layouts.app')
@section('title', 'Ekskulio | Tutup Periode')
@section('content')

<div class="p-4 border-2 border-gray-200 border-dashed rounded-md w-full mt-14">

    <a href="{{ route('academic-years.index') }}" class="text-sm inline-flex items-center gap-1 font-semibold text-gray-500 hover:text-[#0083E9] mb-4">
        <i class="fa-solid fa-chevron-left"></i>
        <span>Kembali</span>
    </a>

    <h1 class="text-xl lg:text-2xl font-semibold text-gray-600">Tutup Periode Ajaran</h1>
    <p class="text-sm lg:text-base text-gray-400 mb-4">Arsipkan keanggotaan & siapkan data untuk periode berikutnya.</p>

    {{-- Info Periode Saat Ini --}}
    <div class="bg-blue-50 border border-blue-200 rounded-md p-4 mb-4">
        <div class="flex items-start gap-3">
            <i class="fa-solid fa-calendar-check text-blue-500 mt-1"></i>
            <div>
                <h3 class="font-semibold text-blue-800">Periode Saat Ini</h3>
                <p class="text-sm text-blue-700 mt-1">
                    <strong>{{ $currentYear->year }} - {{ ucfirst($currentYear->semester) }}</strong>
                </p>
                <p class="text-xs text-blue-600 mt-1">
                    Periode ini akan dinonaktifkan setelah proses selesai.
                </p>
            </div>
        </div>
    </div>

    {{-- Form Pemilihan Target --}}
    <form action="{{ route('academic-years.close.process') }}" method="POST" id="close-form">
        @csrf
        
        <div class="bg-white border-2 border-dashed border-gray-200 rounded-md p-6 mb-6">
            <h3 class="font-semibold text-gray-700 mb-4">Pilih Periode Tujuan</h3>
            
            <div class="mb-4">
                <label for="target_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Tahun Ajaran / Semester Tujuan <span class="text-red-500">*</span>
                </label>
                <select name="target_id" id="target_id" 
                    class="w-full md:w-1/2 px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 @error('target_id') border-red-500 @enderror"
                    required>
                    <option value="">-- Pilih Periode Tujuan --</option>
                    @foreach($availableTargets as $target)
                        @php
                            $currentYearStart = (int) explode('/', $currentYear->year)[0];
                            $targetYearStart = (int) explode('/', $target->year)[0];
                            $isYearChange = $targetYearStart > $currentYearStart;
                        @endphp
                        <option value="{{ $target->id }}" 
                                data-type="{{ $isYearChange ? 'academic_year' : 'semester' }}"
                                {{ old('target_id') == $target->id ? 'selected' : '' }}>
                            {{ $target->year }} - {{ ucfirst($target->semester) }}
                            @if($isYearChange) (🎓 Naik Kelas & Lulus) @else (🔄 Ganti Semester) @endif
                        </option>
                    @endforeach
                </select>
                @error('target_id')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                    Password Konfirmasi <span class="text-red-500">*</span>
                </label>
                <input type="password" name="password" id="password" 
                    class="w-full md:w-1/2 px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 @error('password') border-red-500 @enderror"
                    placeholder="Masukkan password Anda"
                    required>
                @error('password')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Warning Box Dinamis --}}
        <div id="warning-box" class="bg-amber-50 border border-amber-200 rounded-md p-4 mb-6 hidden">
            <div class="flex items-start gap-3">
                <i class="fa-solid fa-triangle-exclamation text-amber-500 mt-1"></i>
                <div>
                    <h3 class="font-semibold text-amber-800" id="warning-title">Perhatian!</h3>
                    <p class="text-sm text-amber-700 mt-1" id="warning-text"></p>
                </div>
            </div>
        </div>

        <div class="flex gap-4">
            <button type="submit" id="submit-btn"
                class="inline-flex cursor-pointer items-center px-6 py-2.5 gap-2 text-sm font-medium text-white bg-red-500 rounded-md hover:bg-red-600 active:scale-[0.98] transition-all">
                <i class="fa-solid fa-lock text-sm"></i>
                <span>Tutup Periode & Arsipkan</span>
            </button>
            <a href="{{ route('academic-years.index') }}"
                class="inline-flex items-center px-6 py-2.5 gap-2 text-sm font-medium text-gray-600 border border-gray-300 rounded-md hover:bg-gray-100">
                Batal
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const targetSelect = document.getElementById('target_id');
    const warningBox = document.getElementById('warning-box');
    const warningTitle = document.getElementById('warning-title');
    const warningText = document.getElementById('warning-text');
    const form = document.getElementById('close-form');
    const submitBtn = document.getElementById('submit-btn');

    function updateWarning() {
        const selected = targetSelect.options[targetSelect.selectedIndex];
        const type = selected?.dataset?.type;

        if (!type) {
            warningBox.classList.add('hidden');
            return;
        }

        warningBox.classList.remove('hidden');

        if (type === 'academic_year') {
            warningTitle.textContent = '⚠️ Transisi Tahun Ajaran';
            warningText.innerHTML = `
                Aksi ini akan:<br>
                • Meluluskan semua siswa kelas <strong>XII</strong><br>
                • Menaikkan kelas X → XI dan XI → XII<br>
                • Mengarsipkan semua keanggotaan ekskul periode lama<br>
                <em class="text-amber-600">Setelah ini, download template Excel untuk menambah siswa baru kelas X.</em>
            `;
        } else {
            warningTitle.textContent = '🔄 Ganti Semester';
            warningText.innerHTML = `
                Aksi ini akan:<br>
                • Mengarsipkan semua keanggotaan ekskul semester sebelumnya<br>
                • <strong class="text-blue-600">Grade siswa tetap</strong> (tidak naik kelas)<br>
                <em class="text-amber-600">Setelah ini, download template Excel untuk memilih ekskul semester baru.</em>
            `;
        }
    }

    targetSelect?.addEventListener('change', updateWarning);
    updateWarning(); // Init

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const selected = targetSelect.options[targetSelect.selectedIndex];
        const type = selected?.dataset?.type || 'semester';
        const targetName = selected?.text || '';

        Swal.fire({
            title: type === 'academic_year' ? 'Konfirmasi Tutup Tahun Ajaran' : 'Konfirmasi Ganti Semester',
            html: `<p class="text-left text-sm text-gray-600">Anda akan menutup periode saat ini dan berpindah ke:</p>
                   <p class="text-left font-semibold mt-2">${targetName}</p>
                   <p class="text-left text-sm text-amber-600 mt-3">Aksi ini tidak dapat dibatalkan!</p>`,
            icon: 'warning',
            showCancelButton: true,
            cancelButtonText: 'Batal',
            confirmButtonText: 'Ya, Tutup Periode',
            confirmButtonColor: '#EF4444',
            cancelButtonColor: '#6B7280',
            allowOutsideClick: false
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