@extends('role.kesiswaan.layouts.app')
@section('title', 'Ekskulio | Tutup Periode')
@section('content')

    <div class="p-4 border-2 border-gray-200 border-dashed rounded-md w-full mt-14">

        <a href="{{ route('academic-years.index') }}"
            class="text-sm inline-flex items-center gap-1 font-semibold text-gray-500 hover:text-[#0083E9] mb-4">
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
                    <h3 class="font-semibold text-[#0083E9]">Periode Saat Ini</h3>
                    <p class="text-sm text-[#0083E9] mt-1">
                        <strong>{{ $currentYear->year }} - {{ ucfirst($currentYear->semester) }}</strong>
                    </p>
                    <p class="text-xs text-[#0083E9] mt-1">
                        Periode ini akan dinonaktifkan setelah proses selesai.
                    </p>
                </div>
            </div>
        </div>

        {{-- Form Pemilihan Target --}}
        <form action="{{ route('academic-years.close.process') }}" method="POST" id="close-form">
            @csrf

            <div class="relative border-2 border-dashed border-gray-200 rounded-md p-4 mb-6">
                <h3 class="font-semibold text-gray-700 mb-4">Pilih Periode Tujuan</h3>

                <div class="mb-4">
                    <label for="target_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Tahun Ajaran / Semester Tujuan <span class="text-red-500">*</span>
                    </label>
                    <select name="target_id" id="target_id"
                        class="w-full md:w-1/2 px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 text-sm @error('target_id') border-red-500 @enderror"
                        required>
                        <option value="">-- Pilih Periode Tujuan --</option>
                        @foreach ($availableTargets as $target)
                            @php
                                $currentYearStart = (int) explode('/', $currentYear->year)[0];
                                $targetYearStart = (int) explode('/', $target->year)[0];
                                $isYearChange = $targetYearStart > $currentYearStart;
                            @endphp
                            <option value="{{ $target->id }}"
                                data-type="{{ $isYearChange ? 'academic_year' : 'semester' }}"
                                {{ old('target_id') == $target->id ? 'selected' : '' }}>
                                {{ $target->year }} - {{ ucfirst($target->semester) }}
                                @if ($isYearChange)
                                    (Naik Kelas & Lulus)
                                @else
                                    (Ganti Semester)
                                @endif
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
                        class="w-full md:w-1/2 px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 text-sm @error('password') border-red-500 @enderror"
                        placeholder="Masukkan password Anda" required>
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

            {{-- Preview Promosi Kelas (Hanya untuk Transisi Tahun Ajaran) --}}
            @if ($preview['type'] === 'academic_year' && !empty($preview['class_promotions']))
                <div class="relative border-2 border-dashed border-gray-200 rounded-md p-4 mb-6">
                    <h3 class="font-semibold text-gray-700 mb-4">
                        <i class="fa-solid fa-arrow-up-right-from-square text-[#0083E9] mr-2"></i>
                        Preview Promosi Kelas
                    </h3>

                    <div class="rounded-lg border border-gray-200">
                        <table id="pagination-table" class="min-w-full text-sm text-left text-gray-600">
                            <thead class="border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-3 text-xs uppercase">Kelas Saat Ini</th>
                                    <th class="px-4 py-3 text-sm uppercase text-center">
                                        <i class="fa-solid fa-arrow-right"></i>
                                    </th>
                                    <th class="px-4 py-3 text-xs uppercase">Kelas Tujuan</th>
                                    <th class="px-4 py-3 text-xs uppercase text-right">Jumlah Siswa</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($preview['class_promotions'] as $index => $promo)
                                    <tr class="hover:bg-gray-100 transition-colors duration-300 border-b border-gray-200">
                                        {{-- Kelas Saat Ini (Readonly) --}}
                                        <td class="px-4 py-3 font-medium whitespace-nowrap">
                                            <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-md text-xs">
                                                {{ $promo['from'] }}
                                            </span>
                                            <input type="hidden" name="class_mappings[{{ $index }}][from]"
                                                value="{{ $promo['from'] }}">
                                        </td>

                                        {{-- Arrow --}}
                                        <td class="px-4 py-3 text-center text-gray-400">
                                            <i class="fa-solid fa-arrow-right"></i>
                                        </td>

                                        {{-- Kelas Tujuan (Searchable Dropdown) --}}
                                        <td class="px-4 py-3 font-medium">
                                            <div class="relative">
                                                <input type="text" id="class-to-{{ $index }}"
                                                    name="class_mappings[{{ $index }}][to]"
                                                    value="{{ old("class_mappings.$index.to", $promo['to']) }}"
                                                    class="w-full px-3 py-1.5 text-sm border rounded-md focus:ring-[#0083E9] focus:border-[#0083E9] bg-white placeholder-gray-400 pr-8
        @error('class_mappings.' . $index . '.to') border-red-500 @else border-gray-300 @enderror"
                                                    placeholder="Ketik atau pilih kelas tujuan" autocomplete="off"
                                                    data-index="{{ $index }}">

                                                <div
                                                    class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="m19 9-7 7-7-7" />
                                                    </svg>
                                                </div>

                                                {{-- Dropdown List --}}
                                                <div id="dropdown-class-to-{{ $index }}"
                                                    class="hidden absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-48 overflow-y-auto">
                                                    <ul class="py-1">
                                                        @foreach ($allStudentClasses as $class)
                                                            <li>
                                                                <button type="button"
                                                                    class="dropdown-class-item w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-[#0083E9] transition-colors"
                                                                    data-value="{{ $class->name }}"
                                                                    data-target-index="{{ $index }}">
                                                                    {{ $class->name }}
                                                                </button>
                                                            </li>
                                                        @endforeach
                                                        @if ($allStudentClasses->isEmpty())
                                                            <li class="px-3 py-2 text-sm text-gray-500 italic">Tidak ada
                                                                data kelas</li>
                                                        @endif
                                                    </ul>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Jumlah Siswa --}}
                                        <td class="px-4 py-3 text-right font-semibold text-gray-800">
                                            {{ $promo['count'] }} siswa
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <p class="text-xs text-gray-500 mt-3">
                        <i class="fa-solid fa-info-circle mr-1"></i>
                        Nama kelas tujuan dibuat otomatis berdasarkan pola. Jika ada yang tidak sesuai,
                        Anda dapat mengeditnya manual setelah import Excel nanti.
                    </p>
                </div>
            @endif

            <div class="flex gap-4 justify-end">
                <a href="{{ route('academic-years.index') }}"
                    class="inline-flex items-center px-6 py-2.5 gap-2 text-sm font-medium text-gray-600 border border-gray-300 rounded-md hover:bg-gray-100">
                    Batal
                </a>
                <button type="submit" id="submit-btn"
                    class="inline-flex cursor-pointer items-center px-5 py-2.5 gap-2 text-sm font-medium text-center text-white bg-red-500 rounded-md focus:ring-4 focus:ring-red-300 hover:bg-[#FFDEDE] hover:text-red-500 active:scale-[0.98]
            transition-all duration-300 ease-out">
                    <i class="fa-solid fa-lock text-sm"></i>
                    <span>Tutup Periode & Arsipkan</span>
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {

                // ─── Elements
                const targetSelect = document.getElementById('target_id');
                const warningBox = document.getElementById('warning-box');
                const warningTitle = document.getElementById('warning-title');
                const warningText = document.getElementById('warning-text');
                const form = document.getElementById('close-form');
                const submitBtn = document.getElementById('submit-btn');
                const classCount = {{ count($preview['class_promotions'] ?? []) }};

                // ─── Warning Box
                const WARNING_CONTENT = {
                    academic_year: {
                        title: 'Transisi Tahun Ajaran',
                        text: () => `
                Aksi ini akan:<br>
                • Meluluskan semua siswa kelas <strong>XII</strong><br>
                • Menaikkan kelas X → XI dan XI → XII<br>
                • Mengupdate nama kelas: <strong>${classCount} kelas</strong> akan berubah<br>
                • Mengarsipkan semua keanggotaan ekskul periode lama<br>
                <em class="text-amber-600">Setelah ini, download template Excel untuk menambah siswa baru kelas X.</em>
            `,
                    },
                    semester: {
                        title: 'Ganti Semester',
                        text: () => `
                Aksi ini akan:<br>
                • Mengarsipkan semua keanggotaan ekskul semester sebelumnya<br>
                • <strong class="text-blue-600">Grade siswa tetap</strong> (tidak naik kelas)<br>
                <em class="text-amber-600">Setelah ini, download template Excel untuk memilih ekskul semester baru.</em>
            `,
                    },
                };

                function updateWarning() {
                    const type = targetSelect.options[targetSelect.selectedIndex]?.dataset?.type;
                    const content = WARNING_CONTENT[type];

                    if (!content) {
                        warningBox.classList.add('hidden');
                        return;
                    }

                    warningTitle.textContent = content.title;
                    warningText.innerHTML = content.text();
                    warningBox.classList.remove('hidden');
                }

                targetSelect?.addEventListener('change', updateWarning);
                updateWarning();

                // ─── Class Mapping Dropdown
                function getClassInput(index) {
                    return document.getElementById(`class-to-${index}`);
                }

                function getClassDropdown(index) {
                    return document.getElementById(`dropdown-class-to-${index}`);
                }

                function showDropdown(index) {
                    const dropdown = getClassDropdown(index);
                    if (!dropdown) return;
                    dropdown.querySelectorAll('.dropdown-class-item').forEach(item => {
                        item.parentElement.style.display = 'block';
                    });
                    dropdown.classList.remove('hidden');
                }

                function hideDropdown(index) {
                    getClassDropdown(index)?.classList.add('hidden');
                }

                function filterDropdown(index, query) {
                    const dropdown = getClassDropdown(index);
                    if (!dropdown) return;

                    let hasResults = false;
                    dropdown.querySelectorAll('.dropdown-class-item').forEach(item => {
                        const match = item.dataset.value.toLowerCase().includes(query);
                        item.parentElement.style.display = match ? 'block' : 'none';
                        if (match) hasResults = true;
                    });

                    dropdown.classList.toggle('hidden', !hasResults);
                }

                document.querySelectorAll('[id^="class-to-"]').forEach(input => {
                    const index = input.dataset.index;

                    input.addEventListener('focus', () => showDropdown(index));
                    input.addEventListener('blur', () => setTimeout(() => hideDropdown(index), 200));
                    input.addEventListener('input', () => filterDropdown(index, input.value.toLowerCase()));
                });

                document.querySelectorAll('.dropdown-class-item').forEach(item => {
                    item.addEventListener('click', function() {
                        const index = this.dataset.targetIndex;
                        const input = getClassInput(index);
                        if (input) input.value = this.dataset.value;
                        hideDropdown(index);
                    });
                });

                // Tutup semua dropdown saat klik di luar
                document.addEventListener('click', function(e) {
                    const isInsideInput = e.target.closest('[id^="class-to-"]');
                    const isInsideDropdown = e.target.closest('[id^="dropdown-class-to-"]');
                    if (!isInsideInput && !isInsideDropdown) {
                        document.querySelectorAll('[id^="dropdown-class-to-"]')
                            .forEach(d => d.classList.add('hidden'));
                    }
                });

                // ─── Validasi Duplikat Kelas Tujua
                function validateNoDuplicateMappings() {
                    const inputs = document.querySelectorAll('input[name^="class_mappings"][name$="[to]"]');
                    const seen = [];
                    let hasDupe = false;

                    inputs.forEach(input => {
                        const val = input.value.trim().toUpperCase();
                        if (!val) return;

                        if (seen.includes(val)) {
                            input.classList.add('border-red-500', 'ring-1', 'ring-red-500');
                            hasDupe = true;
                        } else {
                            input.classList.remove('border-red-500', 'ring-1', 'ring-red-500');
                            seen.push(val);
                        }
                    });

                    return !hasDupe;
                }

                // ─── Form Submit
                form?.addEventListener('submit', function(e) {
                    e.preventDefault();

                    if (!validateNoDuplicateMappings()) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Duplikat Kelas Tujuan',
                            text: 'Tidak boleh ada kelas tujuan yang sama. Pastikan setiap mapping unik.',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#EF4444',
                        });
                        return;
                    }

                    const selected = targetSelect.options[targetSelect.selectedIndex];
                    const type = selected?.dataset?.type || 'semester';
                    const targetName = selected?.text || '';

                    Swal.fire({
                        icon: 'warning',
                        title: type === 'academic_year' ?
                            'Konfirmasi Tutup Tahun Ajaran' : 'Konfirmasi Ganti Semester',
                        html: `
                <p class="text-left text-sm text-gray-600">Anda akan menutup periode saat ini dan berpindah ke:</p>
                <p class="text-left font-semibold mt-2">${targetName}</p>
                <p class="text-left text-sm text-amber-600 mt-3">Aksi ini tidak dapat dibatalkan!</p>
            `,
                        showCancelButton: true,
                        cancelButtonText: 'Batal',
                        confirmButtonText: 'Ya, Tutup Periode',
                        confirmButtonColor: '#EF4444',
                        cancelButtonColor: '#6B7280',
                        allowOutsideClick: false,
                    }).then(result => {
                        if (!result.isConfirmed) return;
                        submitBtn.disabled = true;
                        submitBtn.innerHTML =
                            '<i class="fa-solid fa-spinner fa-spin"></i> <span>Memproses...</span>';
                        form.submit();
                    });
                });

            });
        </script>
    @endpush

@endsection
