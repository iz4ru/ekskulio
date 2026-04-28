@extends('role.kesiswaan.layouts.app')
@section('title', 'Ekskulio | Input Nilai')
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

        <a href="{{ route('scores.index') }}"
            class="text-sm inline-flex items-center gap-1 font-semibold text-gray-500 hover:text-[#0083E9] active:scale-[0.98] transition-all duration-300 ease-out">
            <i class="fa-solid fa-chevron-left"></i>
            <span>Kembali</span>
        </a>

        <div class="my-4">
            <h1 class="text-xl lg:text-2xl font-semibold text-gray-600">Input Nilai Akhir Semester</h1>
            <p class="text-sm lg:text-base text-gray-400">Masukkan nilai akhir untuk setiap siswa.</p>
            @if ($activeAY)
                <span class="inline-block mt-2 px-3 py-1 text-xs font-medium bg-blue-100 text-blue-700 rounded-full">
                    <i class="fa-solid fa-calendar mr-1"></i>
                    {{ $activeAY->display_name }}
                </span>
            @endif
        </div>

        <form action="{{ route('scores.store') }}" method="POST" id="score-form">
            @csrf

            <div class="bg-gray-50 p-4 rounded-md mb-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tahun Ajaran</label>
                        <select name="academic_year_id" id="academic_year_id" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md" required>
                            @foreach($academicYears as $ay)
                                <option value="{{ $ay->id }}" {{ $activeAY?->id == $ay->id ? 'selected' : '' }}>{{ $ay->display_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Ekstrakurikuler</label>
                        <select name="extracurricular_id" id="extracurricular_id" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md" required>
                            <option value="">-- Pilih --</option>
                            @foreach($extracurriculars as $ekskul)
                                <option value="{{ $ekskul->id }}">{{ $ekskul->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Kelas</label>
                        <select name="class_id" id="class_id" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md">
                            <option value="">Semua Kelas</option>
                            @foreach($studentClasses as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div id="student-list-section" class="hidden">
                <div class="mb-2 flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-600">Daftar Siswa</span>
                    <span class="text-xs text-gray-500">Isi nilai 0-100 (bisa menggunakan koma, contoh: 85,5)</span>
                </div>

                <div class="overflow-x-auto border-2 border-dashed border-gray-200 rounded-md p-4">
                    <table class="min-w-full text-sm text-left text-gray-600" id="student-table">
                        <thead>
                            <tr>
                                <th class="px-4 py-3">NIS</th>
                                <th class="px-4 py-3">Nama</th>
                                <th class="px-4 py-3">Kelas</th>
                                <th class="px-4 py-3 text-center w-32">Nilai (0-100)</th>
                                <th class="px-4 py-3 text-center">Predikat</th>
                                <th class="px-4 py-3">Catatan</th>
                            </tr>
                        </thead>
                        <tbody id="student-tbody">
                            <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">Pilih ekstrakurikuler terlebih dahulu</td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 flex justify-end">
                    <button type="submit" class="inline-flex cursor-pointer items-center px-5 py-2.5 gap-2 text-sm font-medium text-center text-white bg-green-600 rounded-md focus:ring-4 focus:ring-green-300 hover:bg-green-700 active:scale-[0.98] transition-all duration-300 ease-out">
                        <i class="fa-solid fa-save text-sm"></i>
                        <span>Simpan Nilai</span>
                    </button>
                </div>
            </div>

            <div id="loading" class="hidden text-center py-8">
                <i class="fa-solid fa-spinner fa-spin text-2xl text-gray-400"></i>
                <p class="text-gray-500 mt-2">Memuat data...</p>
            </div>

            <div id="empty" class="hidden text-center py-8 text-gray-500">
                <i class="fa-solid fa-users text-4xl mb-2"></i>
                <p>Tidak ada siswa aktif di ekstrakurikuler ini</p>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        let dataTable = null;

        function getPredicate(score) {
            const s = parseFloat(String(score).replace(',', '.'));
            if (isNaN(s)) return '-';
            if (s >= 90) return '<span class="px-2 py-1 text-xs font-medium rounded-md bg-green-100 text-green-700">A - Sangat Baik</span>';
            if (s >= 80) return '<span class="px-2 py-1 text-xs font-medium rounded-md bg-blue-100 text-blue-700">B - Baik</span>';
            if (s >= 70) return '<span class="px-2 py-1 text-xs font-medium rounded-md bg-yellow-100 text-yellow-700">C - Cukup</span>';
            if (s >= 60) return '<span class="px-2 py-1 text-xs font-medium rounded-md bg-orange-100 text-orange-700">D - Kurang</span>';
            return '<span class="px-2 py-1 text-xs font-medium rounded-md bg-red-100 text-red-700">E - Sangat Kurang</span>';
        }

        function updatePredicate(input) {
            const row = input.closest('tr');
            const predicateCell = row.querySelector('.predicate-cell');
            predicateCell.innerHTML = getPredicate(input.value);
        }

        function initDataTable() {
            const tableEl = document.getElementById('student-table');
            if (tableEl && typeof simpleDatatables !== 'undefined') {
                if (dataTable) {
                    dataTable.destroy();
                }
                dataTable = new simpleDatatables.DataTable("#student-table", {
                    paging: true,
                    perPage: 10,
                    perPageSelect: [10, 15, 20, 25],
                    sortable: true
                });
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const ekskulSelect = document.getElementById('extracurricular_id');
            const classSelect = document.getElementById('class_id');
            const yearSelect = document.getElementById('academic_year_id');
            const studentSection = document.getElementById('student-list-section');
            const loading = document.getElementById('loading');
            const empty = document.getElementById('empty');
            const tbody = document.getElementById('student-tbody');

            function loadStudents() {
                const ekskulId = ekskulSelect.value;
                const classId = classSelect.value;
                const yearId = yearSelect.value;

                if (!ekskulId || !yearId) {
                    studentSection.classList.add('hidden');
                    return;
                }

                studentSection.classList.add('hidden');
                loading.classList.remove('hidden');
                empty.classList.add('hidden');

                fetch(`/scores/get-students?extracurricular_id=${ekskulId}&academic_year_id=${yearId}`)
                    .then(res => res.json())
                    .then(data => {
                        loading.classList.add('hidden');

                        if (data.length === 0) {
                            empty.classList.remove('hidden');
                            return;
                        }

                        const filtered = classId ? data.filter(s => s.class == document.querySelector(`#class_id option[value="${classId}"]`)?.text) : data;

                        if (filtered.length === 0) {
                            empty.classList.remove('hidden');
                            return;
                        }

                        tbody.innerHTML = filtered.map(s => `
                            <tr class="hover:bg-gray-100 transition-colors transition-duration-300">
                                <td class="px-4 py-3 font-medium whitespace-nowrap">${s.id_number}</td>
                                <td class="px-4 py-3 font-medium whitespace-nowrap">${s.name}</td>
                                <td class="px-4 py-3 whitespace-nowrap">${s.class}</td>
                                <td class="px-4 py-3">
                                    <input type="text" name="scores[${s.membership_id}][score]" 
                                        value="${s.score ?? ''}" 
                                        class="score-input w-20 px-2 py-1 text-sm border border-gray-300 rounded-md text-center"
                                        placeholder="0-100" oninput="updatePredicate(this)">
                                </td>
                                <td class="px-4 py-3 text-center predicate-cell">
                                    ${s.score != null && s.score !== '' ? getPredicate(s.score) : '-'}
                                </td>
                                <td class="px-4 py-3">
                                    <textarea name="scores[${s.membership_id}][notes]" 
                                        maxlength="255"
                                        class="w-full px-2 py-1 text-sm border border-gray-300 rounded-md"
                                        placeholder="Catatan (opsional)" rows="1">${s.notes ?? ''}</textarea>
                                </td>
                            </tr>
                        `).join('');

                        studentSection.classList.remove('hidden');
                        initDataTable();
                    })
                    .catch(err => {
                        loading.classList.add('hidden');
                        empty.classList.remove('hidden');
                    });
            }

            ekskulSelect.addEventListener('change', loadStudents);
            classSelect.addEventListener('change', loadStudents);
            yearSelect.addEventListener('change', loadStudents);
        });
    </script>
    @endpush

@endsection