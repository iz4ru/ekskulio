@extends('role.kesiswaan.layouts.app')
@section('title', 'Ekskulio | Edit Presensi - ' . $extracurricular->name)
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

        <a href="{{ route('presence.show', $extracurricular->id) }}"
            class="text-sm inline-flex items-center gap-1 font-semibold text-gray-500 hover:text-[#0083E9] active:scale-[0.98] transition-all duration-300 ease-out">
            <i class="fa-solid fa-chevron-left"></i>
            <span>Kembali</span>
        </a>

        <div class="flex gap-4 my-4 justify-between">
            <div>
                <h1 class="text-xl lg:text-2xl font-semibold text-gray-600">Edit Presensi {{ $extracurricular->name }}</h1>
                <p class="text-sm lg:text-base text-gray-400">Perbarui data presensi tanggal
                    {{ $presence->date->isoFormat('D MMMM Y') }}.</p>
            </div>
        </div>

        <div class="my-4 border-t-2 border-dashed border-gray-300 w-full"></div>

        <section>
            <div class="py-8 px-4 mx-auto max-w-5xl lg:py-16">
                <form action="{{ route('presence.update', $presence->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- Info Ekstrakurikuler --}}
                    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-md">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <p class="text-sm text-gray-600">Ekstrakurikuler</p>
                                <p class="font-semibold text-gray-900">{{ $extracurricular->name }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Tanggal Pertemuan</p>
                                <p class="font-semibold text-gray-900">{{ $presence->date->isoFormat('D MMMM Y') }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Hari</p>
                                <p class="font-semibold text-gray-900">{{ $presence->day }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Form Tanggal & Catatan --}}
                    <div class="grid gap-4 sm:grid-cols-2 sm:gap-6 mb-6">
                        {{-- Tanggal --}}
                        <div class="w-full">
                            <label for="date" class="block mb-2 text-sm font-medium text-gray-900">
                                Tanggal Pertemuan <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="date" id="date"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-md focus:ring-[#0083E9] focus:border-[#0083E9] block w-full p-2.5"
                                value="{{ old('date', $presence->date->format('Y-m-d')) }}" required>
                            <p class="mt-1 text-xs text-gray-500">Ubah tanggal jika perlu koreksi</p>
                        </div>

                        {{-- Hari (Auto-detect) --}}
                        <div class="w-full">
                            <label class="block mb-2 text-sm font-medium text-gray-900">
                                Hari
                            </label>
                            <input type="text" id="day-display" readonly
                                class="bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-md block w-full p-2.5 cursor-not-allowed"
                                value="{{ old('date') ? \Carbon\Carbon::parse(old('date'))->locale('id')->isoFormat('dddd') : $presence->day }}">
                            <p class="mt-1 text-xs text-gray-500">Hari otomatis terdeteksi dari tanggal</p>
                        </div>

                        {{-- Catatan --}}
                        <div class="sm:col-span-2">
                            <label for="notes" class="block mb-2 text-sm font-medium text-gray-900">
                                Catatan
                            </label>
                            <textarea name="notes" id="notes" rows="3"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-md focus:ring-[#0083E9] focus:border-[#0083E9] block w-full p-2.5"
                                placeholder="Materi: Latihan dasar, Keterangan tambahan, dll...">{{ old('notes', $presence->notes) }}</textarea>
                            <p class="mt-1 text-xs text-gray-500">Opsional: Catatan materi atau keterangan pertemuan</p>
                        </div>
                    </div>

                    {{-- Tabel Presensi --}}
                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Daftar Kehadiran Siswa</h3>
                            <div class="flex gap-2">
                                <button type="button" id="mark-all-present"
                                    class="cursor-pointer px-3 py-1.5 text-xs font-medium text-green-700 bg-green-100 rounded-md hover:bg-green-200 transition-colors">
                                    <i class="fa-solid fa-check-circle mr-1"></i> Semua Hadir
                                </button>
                                <button type="button" id="mark-all-absent"
                                    class="cursor-pointer px-3 py-1.5 text-xs font-medium text-red-700 bg-red-100 rounded-md hover:bg-red-200 transition-colors">
                                    <i class="fa-solid fa-times-circle mr-1"></i> Semua Alfa
                                </button>
                            </div>
                        </div>

                        <div class="relative overflow-x-auto border-2 border-dashed border-gray-200 rounded-md p-4">
                            <table id="pagination-table" class="min-w-full text-sm text-left text-gray-600">
                                <thead>
                                    <tr>
                                        <th>
                                            <span class="flex items-center">
                                                No
                                                <svg class="w-4 h-4 ms-1" aria-hidden="true"
                                                    xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    fill="none" viewBox="0 0 24 24">
                                                    <path stroke="currentColor" stroke-linecap="round"
                                                        stroke-linejoin="round" stroke-width="2"
                                                        d="m8 15 4 4 4-4m0-6-4-4-4 4" />
                                                </svg>
                                            </span>
                                        </th>
                                        <th>
                                            <span class="flex items-center">
                                                NIS
                                                <svg class="w-4 h-4 ms-1" aria-hidden="true"
                                                    xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    fill="none" viewBox="0 0 24 24">
                                                    <path stroke="currentColor" stroke-linecap="round"
                                                        stroke-linejoin="round" stroke-width="2"
                                                        d="m8 15 4 4 4-4m0-6-4-4-4 4" />
                                                </svg>
                                            </span>
                                        </th>
                                        <th>
                                            <span class="flex items-center">
                                                Nama Siswa
                                                <svg class="w-4 h-4 ms-1" aria-hidden="true"
                                                    xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    fill="none" viewBox="0 0 24 24">
                                                    <path stroke="currentColor" stroke-linecap="round"
                                                        stroke-linejoin="round" stroke-width="2"
                                                        d="m8 15 4 4 4-4m0-6-4-4-4 4" />
                                                </svg>
                                            </span>
                                        </th>
                                        <th>
                                            <span class="flex items-center">
                                                Kelas
                                                <svg class="w-4 h-4 ms-1" aria-hidden="true"
                                                    xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    fill="none" viewBox="0 0 24 24">
                                                    <path stroke="currentColor" stroke-linecap="round"
                                                        stroke-linejoin="round" stroke-width="2"
                                                        d="m8 15 4 4 4-4m0-6-4-4-4 4" />
                                                </svg>
                                            </span>
                                        </th>
                                        <th data-sortable="false">
                                            <span class="flex items-center justify-center">
                                                Status Kehadiran
                                            </span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($presence->details as $detail)
                                        <tr class="hover:bg-gray-100 transition-colors transition-duration-300">
                                            <td class="font-medium text-gray-800 whitespace-nowrap">{{ $loop->iteration }}
                                            </td>
                                            <td class="font-medium text-gray-800 whitespace-nowrap">
                                                {{ $detail->student->id_number }}</td>
                                            <td class="font-medium text-gray-800 whitespace-nowrap">
                                                {{ $detail->student->name }}</td>
                                            <td class="font-medium text-gray-800 whitespace-nowrap">
                                                {{ $detail->student->studentClass->name ?? '-' }}</td>
                                            @php
                                                $statusConfig = [
                                                    'present' => [
                                                        'label' => 'Hadir',
                                                        'classes' =>
                                                            'border-green-200  hover:border-green-400  hover:bg-green-50  has-[:checked]:bg-green-100  has-[:checked]:border-green-500  bg-green-50',
                                                        'input' => 'text-green-600  focus:ring-green-500',
                                                        'text' => 'text-green-700',
                                                    ],
                                                    'sick' => [
                                                        'label' => 'Sakit',
                                                        'classes' =>
                                                            'border-yellow-200 hover:border-yellow-400 hover:bg-yellow-50 has-[:checked]:bg-yellow-100 has-[:checked]:border-yellow-500 bg-yellow-50',
                                                        'input' => 'text-yellow-600 focus:ring-yellow-500',
                                                        'text' => 'text-yellow-700',
                                                    ],
                                                    'permission' => [
                                                        'label' => 'Izin',
                                                        'classes' =>
                                                            'border-blue-200   hover:border-blue-400   hover:bg-blue-50   has-[:checked]:bg-blue-100   has-[:checked]:border-blue-500   bg-blue-50',
                                                        'input' => 'text-blue-600   focus:ring-blue-500',
                                                        'text' => 'text-blue-700',
                                                    ],
                                                    'absent' => [
                                                        'label' => 'Alfa',
                                                        'classes' =>
                                                            'border-red-200    hover:border-red-400    hover:bg-red-50    has-[:checked]:bg-red-100    has-[:checked]:border-red-500    bg-red-50',
                                                        'input' => 'text-red-600    focus:ring-red-500',
                                                        'text' => 'text-red-700',
                                                    ],
                                                ];
                                            @endphp

                                            <td class="font-medium text-gray-800 whitespace-nowrap">
                                                <div class="flex items-center justify-center gap-2">
                                                    @foreach ($statusConfig as $status => $config)
                                                        <label
                                                            class="flex items-center px-3 py-2 border-2 rounded-md cursor-pointer transition-all duration-200 hover:shadow-md {{ $config['classes'] }}">
                                                            <input type="radio"
                                                                name="attendance[{{ $detail->student->id }}]"
                                                                value="{{ $status }}"
                                                                class="w-4 h-4 bg-gray-100 border-gray-300 focus:ring-2 mr-2 {{ $config['input'] }}"
                                                                {{ old("attendance.{$detail->student->id}", $detail->status?->value ?? $detail->status) == $status ? 'checked' : '' }}
                                                                required>
                                                            <span
                                                                class="text-xs font-medium {{ $config['text'] }}">{{ $config['label'] }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Statistik Real-time --}}
                        <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-3">
                            <div class="p-3 bg-green-50 border border-green-200 rounded-md">
                                <p class="text-xs text-green-600 font-medium">Hadir</p>
                                <p class="text-2xl font-bold text-green-700" id="count-present">0</p>
                            </div>
                            <div class="p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                                <p class="text-xs text-yellow-600 font-medium">Sakit</p>
                                <p class="text-2xl font-bold text-yellow-700" id="count-sick">0</p>
                            </div>
                            <div class="p-3 bg-blue-50 border border-blue-200 rounded-md">
                                <p class="text-xs text-blue-600 font-medium">Izin</p>
                                <p class="text-2xl font-bold text-blue-700" id="count-permission">0</p>
                            </div>
                            <div class="p-3 bg-red-50 border border-red-200 rounded-md">
                                <p class="text-xs text-red-600 font-medium">Alfa</p>
                                <p class="text-2xl font-bold text-red-700" id="count-absent">0</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end mt-6">
                        <button type="submit"
                            class="inline-flex cursor-pointer items-center px-5 py-2.5 text-sm font-medium text-center text-white bg-[#0083E9] rounded-md focus:ring-4 focus:ring-blue-300 hover:bg-[#0d65d9] active:scale-[0.98] transition-all duration-300 ease-out">
                            Perbarui Presensi
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {

                let dataTable = null;

                if (document.getElementById("pagination-table") && typeof simpleDatatables.DataTable !== 'undefined') {
                    dataTable = new simpleDatatables.DataTable("#pagination-table", {
                        paging: false,
                        sortable: true
                    });
                }

                // ===== AUTO-DETECT HARI =====
                const dateInput = document.getElementById('date');
                const dayDisplay = document.getElementById('day-display');

                const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

                dateInput.addEventListener('change', function() {
                    const selectedDate = new Date(this.value);
                    const dayName = days[selectedDate.getDay()];
                    dayDisplay.value = dayName;
                });

                // ===== MARK ALL BUTTONS =====
                document.getElementById('mark-all-present')?.addEventListener('click', function() {
                    document.querySelectorAll('input[type="radio"][value="present"]').forEach(radio => {
                        radio.checked = true;
                    });
                    updateStats();
                });

                document.getElementById('mark-all-absent')?.addEventListener('click', function() {
                    document.querySelectorAll('input[type="radio"][value="absent"]').forEach(radio => {
                        radio.checked = true;
                    });
                    updateStats();
                });

                // ===== REAL-TIME STATISTICS =====
                function updateStats() {
                    const present = document.querySelectorAll('input[type="radio"][value="present"]:checked').length;
                    const sick = document.querySelectorAll('input[type="radio"][value="sick"]:checked').length;
                    const permission = document.querySelectorAll('input[type="radio"][value="permission"]:checked')
                        .length;
                    const absent = document.querySelectorAll('input[type="radio"][value="absent"]:checked').length;

                    document.getElementById('count-present').textContent = present;
                    document.getElementById('count-sick').textContent = sick;
                    document.getElementById('count-permission').textContent = permission;
                    document.getElementById('count-absent').textContent = absent;
                }

                // Update stats on radio change
                document.querySelectorAll('input[type="radio"][name^="attendance"]').forEach(radio => {
                    radio.addEventListener('change', updateStats);
                });

                // Initial update
                updateStats();
            });
        </script>
    @endpush

@endsection
