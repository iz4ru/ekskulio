@extends('role.kesiswaan.layouts.app')
@section('title', 'Ekskulio | Impor Data Siswa')
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

        <a href="{{ route('student.index') }}"
            class="text-sm inline-flex items-center gap-1 font-semibold text-gray-500 hover:text-[#0083E9] active:scale-[0.98] transition-all duration-300 ease-out">
            <i class="fa-solid fa-chevron-left"></i>
            <span>Kembali</span>
        </a>

        <div class="flex gap-4 my-4 justify-between">
            <div>
                <h1 class="text-xl lg:text-2xl font-semibold text-gray-600">Impor Data Siswa</h1>
                <p class="text-sm lg:text-base text-gray-400">Impor data siswa dari file.</p>
            </div>
        </div>

        <div class="my-4 border-t-2 border-dashed border-gray-300 w-full"></div>

        <section>
            <div class="py-8 px-4 mx-auto max-w-2xl lg:py-16">
                <h2 class="mb-4 text-xl font-bold text-gray-900">Impor Kelas</h2>

                <form action="{{ route('student.import.store') }}" method="POST" enctype="multipart/form-data"
                    id="academic-year-form">
                    @csrf

                    <div class="grid gap-4 sm:grid-cols-2 sm:gap-6">

                        <div class="sm:col-span-2 border-2 p-4 border-gray-200 border-dashed rounded-md w-full">
                            {{-- Download Contoh --}}
                            <div class="">
                                <label class="block mb-2 text-sm font-medium text-gray-900">
                                    Download Template
                                </label>
                                <div class="flex items-center bg-gray-50 border border-gray-300 rounded-md p-2.5">
                                    <a href="{{ asset('templates/import-siswa.xlsx') }}" download
                                        class="px-5 py-2 text-sm font-medium text-white bg-green-600 rounded-md cursor-pointer hover:bg-green-700 transition-all duration-300 ease-out inline-flex items-center gap-2">
                                        <i class="fa-solid fa-download text-sm"></i>
                                        Download Template
                                    </a>
                                    <span class="ml-3 text-sm text-gray-500">Format: .xlsx</span>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Download template untuk melihat format yang benar</p>
                            </div>
                        </div>


                        {{-- Upload File --}}
                        <div class="w-full">
                            <label for="upload" class="block mb-2 text-sm font-medium text-gray-900">
                                Upload File
                                <span class="text-red-500">*</span>
                            </label>
                            <div
                                class="flex items-center bg-gray-50 border border-gray-300 rounded-md focus-within:ring-2 focus-within:ring-[#0083E9] focus-within:border-[#0083E9] p-2.5">
                                <label for="upload"
                                    class="px-5 py-2 text-sm font-medium text-white bg-[#0083E9] rounded-md cursor-pointer hover:bg-[#DEECFF] hover:text-[#0083E9] transition-all duration-300 ease-out">
                                    Pilih File
                                </label>
                                <span id="file-name" class="ml-3 text-sm text-gray-500 truncate">Belum ada file
                                    dipilih</span>
                            </div>

                            <input type="file" name="upload" id="upload" accept=".xlsx,.xls,.csv" class="hidden">

                            <p class="mt-1 text-xs text-gray-500">File yang didukung:
                                <span class="text-green-800">.xlsx, .csv</span>
                            </p>
                        </div>

                        {{-- Kelas (Opsional) --}}
                        <div class="w-full relative">
                            <label for="student_class" class="block mb-2 text-sm font-medium text-gray-900">
                                Kelas (Opsional)
                            </label>

                            <div class="relative">
                                <input type="text" name="student_class" id="student_class"
                                    placeholder="Pilih kelas atau kosongkan jika ada di Excel" autocomplete="off"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-md focus:ring-[#0083E9] focus:border-[#0083E9] block w-full p-2.5 pr-10"
                                    value="{{ old('student_class') }}">

                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="m19 9-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>

                            <div id="class-dropdown"
                                class="hidden absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto">
                                <ul class="py-1">
                                    @foreach ($studentClasses as $class)
                                        <li>
                                            <button type="button"
                                                class="class-option w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-[#0083E9] transition-colors duration-150"
                                                data-value="{{ $class->name }}" data-id="{{ $class->id }}">
                                                {{ $class->name }}
                                            </button>
                                        </li>
                                    @endforeach
                                    @if ($studentClasses->isEmpty())
                                        <li class="px-4 py-2 text-sm text-gray-500 italic">Tidak ada data kelas</li>
                                    @endif
                                </ul>
                            </div>

                            <input type="hidden" name="class_id" id="class_id" value="{{ old('class_id') }}">
                            <p class="mt-1 text-xs text-gray-500">
                                Jika kosong, sistem akan menggunakan kolom "kelas" dari file Excel
                            </p>
                        </div>

                        {{-- Info --}}
                        <div class="sm:col-span-2">
                            <div class="p-4 bg-blue-50 border border-blue-200 rounded-md">
                                <h4 class="font-medium text-[#0083E9] mb-2">
                                    <i class="fa-solid fa-info-circle mr-2"></i>
                                    Informasi Grade Otomatis
                                </h4>
                                <ul class="text-sm text-[#0083E9] space-y-1">
                                    <li>Grade akan <strong>dihitung otomatis</strong> dari tahun masuk siswa</li>
                                    <li>Jika ada kolom <code class="bg-blue-100 px-1 rounded">tingkat</code> di Excel, 
                                        nilai tersebut akan digunakan sebagai override</li>
                                    <li>Contoh: Tahun masuk 2024 = Grade X, 2023 = Grade XI, 2022 = Grade XII</li>
                                </ul>
                            </div>
                        </div>

                    </div>

                    <div class="flex justify-end mt-6">
                        <button type="submit"
                            class="inline-flex cursor-pointer items-center px-5 py-2.5 text-sm font-medium text-center text-white bg-[#0083E9] rounded-md focus:ring-4 focus:ring-blue-300 hover:bg-[#0d65d9] active:scale-[0.98] transition-all duration-300 ease-out">
                            Impor Data
                        </button>
                    </div>
                </form>
            </div>
        </section>

    </div>

    @push('scripts')
        <script>
            document.getElementById("upload").addEventListener("change", function() {
                const fileNameSpan = document.getElementById('file-name');
                const file = this.files[0];

                // Update nama file
                fileNameSpan.textContent = file ? file.name : "Belum ada file dipilih";
            });

            const studentClassInput = document.getElementById('student_class');
            const classDropdown = document.getElementById('class-dropdown');
            const classIdInput = document.getElementById('class_id');
            const classOptions = document.querySelectorAll('.class-option');

            // Toggle dropdown kelas
            studentClassInput.addEventListener('focus', function() {
                classDropdown.classList.remove('hidden');
            });

            // Filter kelas berdasarkan input
            studentClassInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                let hasResults = false;

                classOptions.forEach(option => {
                    const optionText = option.getAttribute('data-value').toLowerCase();
                    if (optionText.includes(searchTerm)) {
                        option.parentElement.classList.remove('hidden');
                        hasResults = true;
                    } else {
                        option.parentElement.classList.add('hidden');
                    }
                });
            });

            // Pilih kelas dari dropdown
            classOptions.forEach(option => {
                option.addEventListener('click', function() {
                    const value = this.getAttribute('data-value');
                    const id = this.getAttribute('data-id');

                    studentClassInput.value = value;
                    classIdInput.value = id;
                    classDropdown.classList.add('hidden');
                });
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!studentClassInput.contains(e.target) && !classDropdown.contains(e.target)) {
                    classDropdown.classList.add('hidden');
                }
            });
        </script>
    @endpush


@endsection
