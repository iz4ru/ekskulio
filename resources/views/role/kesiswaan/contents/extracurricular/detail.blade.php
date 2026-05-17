@extends('role.kesiswaan.layouts.app')
@section('title', 'Ekskulio | Detail Ekstrakurikuler ' . ucwords(strtolower($extracurricular->name)))
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

        <a href="{{ route('extracurricular.index') }}"
            class="text-sm inline-flex items-center gap-1 font-semibold text-gray-500 hover:text-[#0083E9] active:scale-[0.98] transition-all duration-300 ease-out">
            <i class="fa-solid fa-chevron-left"></i>
            <span>Kembali</span>
        </a>

        <div class="flex gap-4 my-4 justify-between">
            <div>
                <h1 class="text-xl lg:text-2xl font-semibold text-gray-600">Detail Ekstrakurikuler</h1>
                <p class="text-sm lg:text-base text-gray-400">Lihat detail ekstrakurikuler
                    {{ ucwords(strtolower($extracurricular->name)) }}.</p>
            </div>
        </div>
        <div class="flex gap-2 items-start">
            <a href="{{ route('extracurricular.edit', $extracurricular->id) }}"
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-[#0083E9] rounded-md hover:bg-[#0d65d9] active:scale-[0.98] transition-all duration-300 ease-out">
                <i class="fa-solid fa-edit mr-2"></i>
                Edit
            </a>
        </div>

        <div class="my-4 border-t-2 border-dashed border-gray-300 w-full"></div>

        {{-- Informasi Umum --}}
        <div class="border border-gray-200 rounded-lg p-6 mb-6">
            <h2 class="text-sm md:text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-info-circle text-[#0083E9]"></i>
                Informasi Umum
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Nama Ekstrakurikuler --}}
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Nama Ekstrakurikuler</label>
                    <p class="text-base font-semibold text-gray-900">{{ ucwords(strtolower($extracurricular->name)) }}</p>
                </div>

                {{-- Kode Ekstrakurikuler --}}
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Kode</label>
                    <p
                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-[#0083E9]/10 text-[#0083E9]">
                        {{ $extracurricular->code }}</p>
                </div>

                {{-- Kategori --}}
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Kategori</label>
                    <span
                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-[#0083E9]/10 text-[#0083E9]">
                        {{ $extracurricular->category->name }}
                    </span>
                </div>

                {{-- Status --}}
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                    @if ($extracurricular->is_active)
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            <i class="fa-solid fa-circle-check mr-1.5"></i>
                            Aktif
                        </span>
                    @else
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                            <i class="fa-solid fa-circle-xmark mr-1.5"></i>
                            Tidak Aktif
                        </span>
                    @endif
                </div>

                {{-- Pembina --}}
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Pembina</label>
                    @if ($extracurricular->users->isNotEmpty())
                        <p class="text-base font-semibold text-gray-900">
                            {{ $extracurricular->users->first()->user->name }}
                        </p>
                        <p class="text-sm text-gray-500">{{ $extracurricular->users->first()->user->email }}</p>
                    @else
                        <p class="text-base text-gray-400 italic">Belum ada pembina</p>
                    @endif
                </div>

                {{-- Jadwal Hari --}}
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Jadwal Pelaksanaan</label>
                    @if ($extracurricular->schedules->isNotEmpty())
                        <div class="flex flex-wrap gap-2">
                            @foreach ($extracurricular->schedules as $schedule)
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-md text-sm font-medium text-gray-800 border border-gray-300">
                                    <i class="fa-solid fa-calendar-day mr-1.5 text-[#0083E9]"></i>
                                    {{ $schedule->day }}
                                </span>
                            @endforeach
                        </div>
                    @else
                        <p class="text-base text-gray-400 italic">Belum ada jadwal</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Deskripsi & Penghargaan --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            {{-- Deskripsi --}}
            <div class="border border-gray-200 rounded-lg p-6">
                <h2 class="text-sm md:text-lg font-semibold text-gray-800 mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-file-lines text-[#0083E9]"></i>
                    Deskripsi
                </h2>
                @if ($extracurricular->description)
                    <p class="text-sm text-gray-700 leading-relaxed">{{ $extracurricular->description }}</p>
                @else
                    <p class="text-sm text-gray-400 italic">Belum ada deskripsi</p>
                @endif
            </div>

            {{-- Penghargaan dengan Scroll --}}
            <div class="border border-gray-200 rounded-lg p-6">
                <h2 class="text-sm md:text-lg font-semibold text-gray-800 mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-trophy text-[#0083E9]"></i>
                    Penghargaan Ekstrakurikuler
                </h2>
                @if ($extracurricular->award)
                    @php
                        $awards = array_filter(array_map('trim', explode("\n", $extracurricular->award)));
                    @endphp

                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-md">
                        <div id="award-container" class="max-h-32 overflow-hidden transition-all duration-300">
                            <ul class="text-sm text-gray-700 font-medium space-y-1.5 list-disc list-inside">
                                @foreach ($awards as $line)
                                    <li>{{ $line }}</li>
                                @endforeach
                            </ul>
                        </div>

                        @if (count($awards) > 3)
                            <button type="button" onclick="toggleAwardsHeight()"
                                class="mt-3 text-sm font-semibold text-yellow-700 hover:text-yellow-900 flex items-center gap-1 transition-colors duration-200">
                                <span id="toggle-text-height">Lihat Semua</span>
                                <i id="toggle-icon-height" class="fa-solid fa-chevron-down text-xs"></i>
                            </button>
                        @endif
                    </div>
                @else
                    <p class="text-sm text-gray-400 italic">Belum ada penghargaan</p>
                @endif
            </div>
        </div>

        {{-- Daftar Siswa --}}
        <div class=" border border-gray-200 rounded-lg p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-sm md:text-lg font-semibold text-gray-800 flex items-center gap-2">
                    <i class="fa-solid fa-users text-[#0083E9]"></i>
                    Daftar Siswa Peserta
                </h2>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-[#0083E9] text-white">
                    {{ $extracurricularStudent->count() }} Siswa
                </span>
            </div>

            @if ($extracurricularStudent->isNotEmpty())
                <table id="pagination-table" class="min-w-full text-sm text-left text-gray-600">
                    <thead>
                        <tr>
                            <th>
                                <span class="flex items-center">
                                    No
                                    <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                        width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4" />
                                    </svg>
                                </span>
                            </th>
                            <th>
                                <span class="flex items-center">
                                    NIS
                                    <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                        width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4" />
                                    </svg>
                                </span>
                            </th>
                            <th>
                                <span class="flex items-center">
                                    Nama Siswa
                                    <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                        width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4" />
                                    </svg>
                                </span>
                            </th>
                            <th>
                                <span class="flex items-center">
                                    Kelas
                                    <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                        width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4" />
                                    </svg>
                                </span>
                            </th>
                            <th>
                                <span class="flex items-center">
                                    Tahun Masuk
                                    <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                        width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4" />
                                    </svg>
                                </span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($extracurricularStudent as $student)
                            <tr class="hover:bg-gray-100 transition-colors transition-duration-300">
                                <td>{{ $loop->iteration }}</td>
                                <td class="font-medium text-gray-800 whitespace-nowrap">
                                    {{ $student->id_number }}</td>
                                <td class="font-medium text-gray-800 whitespace-nowrap">{{ $student->name }}</td>
                                <td class="font-medium text-gray-800 whitespace-nowrap">
                                    @if ($student->studentClass)
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-[#0083E9]/10 text-[#0083E9]">
                                            {{ $student->studentClass->name }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 italic text-sm">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($student->enrollment_year)
                                        <span class="text-gray-700">{{ $student->enrollment_year }}</span>
                                    @else
                                        <span class="text-gray-400 italic text-sm">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="text-center py-12 bg-gray-50 rounded-lg">
                    <i class="fa-solid fa-users-slash text-6xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500 font-medium">Belum ada siswa yang mengikuti ekstrakurikuler ini</p>
                    <p class="text-sm text-gray-400 mt-2">Siswa dapat mendaftar melalui sistem pendaftaran</p>
                </div>
            @endif
        </div>

    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Initialize DataTable for students list if exists
                if (document.getElementById("pagination-table") && typeof simpleDatatables.DataTable !== 'undefined') {
                    new simpleDatatables.DataTable("#pagination-table", {
                        paging: true,
                        perPage: 10,
                        perPageSelect: [10, 15, 20, 25],
                        sortable: true,
                        searchable: true,
                    });
                }
            });

            function toggleAwardsHeight() {
                const container = document.getElementById('award-container');
                const toggleText = document.getElementById('toggle-text-height');
                const toggleIcon = document.getElementById('toggle-icon-height');

                if (container.classList.contains('max-h-32')) {
                    container.classList.remove('max-h-32');
                    container.classList.add('max-h-96');
                    toggleText.textContent = 'Sembunyikan';
                    toggleIcon.classList.remove('fa-chevron-down');
                    toggleIcon.classList.add('fa-chevron-up');
                } else {
                    container.classList.remove('max-h-96');
                    container.classList.add('max-h-32');
                    toggleText.textContent = 'Lihat Semua';
                    toggleIcon.classList.remove('fa-chevron-up');
                    toggleIcon.classList.add('fa-chevron-down');
                }
            }
        </script>
    @endpush

@endsection
