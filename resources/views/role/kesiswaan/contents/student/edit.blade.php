@extends('role.kesiswaan.layouts.app')
@section('title', 'Ekskulio | Edit Siswa')
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
                <h1 class="text-xl lg:text-2xl font-semibold text-gray-600">Edit Siswa</h1>
                <p class="text-sm lg:text-base text-gray-400">Perbarui data siswa.</p>
            </div>
        </div>

        <div class="my-4 border-t-2 border-dashed border-gray-300 w-full"></div>

        <section>
            <div class="py-8 px-4 mx-auto max-w-2xl lg:py-16">
                <h2 class="mb-4 text-xl font-bold text-gray-900">Edit Data Siswa</h2>

                <form action="{{ route('student.update', $student->uuid) }}" method="POST" id="student-form">
                    @csrf
                    @method('PUT')

                    <div class="grid gap-4 sm:grid-cols-2 sm:gap-6">

                        {{-- Nama Siswa --}}
                        <div class="sm:col-span-2">
                            <label for="name" class="block mb-2 text-sm font-medium text-gray-900">
                                Nama Siswa
                                <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" id="name" placeholder="Masukkan nama siswa"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-md focus:ring-[#0083E9] focus:border-[#0083E9] block w-full p-2.5"
                                value="{{ old('name', $student->name) }}" required>
                            <p class="mt-1 text-xs text-gray-500">Masukkan nama lengkap siswa sesuai dengan dokumen resmi.</p>
                        </div>

                        {{-- NIS --}}
                        <div class="w-full">
                            <label for="id_number" class="block mb-2 text-sm font-medium text-gray-900">
                                NIS
                                <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="id_number" id="id_number" placeholder="Masukkan NIS"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-md focus:ring-[#0083E9] focus:border-[#0083E9] block w-full p-2.5"
                                value="{{ old('id_number', $student->id_number) }}" required>
                            <p class="mt-1 text-xs text-gray-500">Contoh: 120001</p>
                        </div>

                        {{-- Tahun Masuk --}}
                        <div class="w-full">
                            <label for="enrollment_year" class="block mb-2 text-sm font-medium text-gray-900">
                                Tahun Masuk
                                <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="enrollment_year" id="enrollment_year"
                                placeholder="Masukkan tahun masuk" min="2000" max="2099"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-md focus:ring-[#0083E9] focus:border-[#0083E9] block w-full p-2.5"
                                value="{{ old('enrollment_year', $student->enrollment_year) }}" required>
                            <p class="mt-1 text-xs text-gray-500">Contoh: 2024</p>
                        </div>

                        {{-- Kelas --}}
                        <div class="w-full relative">
                            <label for="student_class" class="block mb-2 text-sm font-medium text-gray-900">
                                Kelas
                                <span class="text-red-500">*</span>
                            </label>

                            <div class="relative">
                                <input type="text" name="student_class" id="student_class"
                                    placeholder="Pilih atau ketik kelas" autocomplete="off"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-md focus:ring-[#0083E9] focus:border-[#0083E9] block w-full p-2.5 pr-10"
                                    value="{{ old('student_class', $student->studentClass->name ?? '') }}" required>

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

                            <input type="hidden" name="class_id" id="class_id"
                                value="{{ old('class_id', $student->class_id) }}">
                            <p class="mt-1 text-xs text-gray-500">Pilih kelas dari daftar</p>
                        </div>

                        {{-- Tingkat --}}
                        <div class="w-full">
                            <label for="grade" class="block mb-2 text-sm font-medium text-gray-900">
                                Tingkat
                                <span class="text-red-500">*</span>
                            </label>
                            <select name="grade" id="grade"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-md focus:ring-[#0083E9] focus:border-[#0083E9] block w-full p-2.5"
                                required>
                                @foreach ($grades as $grade)
                                    <option value="{{ $grade }}" {{ old('grade', $student->grade) == $grade ? 'selected' : '' }}>
                                        Kelas {{ $grade }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">
                                <i class="fa-solid fa-info-circle mr-1"></i>
                                Grade saat ini: <strong>{{ $student->grade }}</strong>
                            </p>
                        </div>

                        {{-- Status --}}
                        <div class="w-full">
                            <label for="status" class="block mb-2 text-sm font-medium text-gray-900">
                                Status
                                <span class="text-red-500">*</span>
                            </label>
                            <select name="status" id="status"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-md focus:ring-[#0083E9] focus:border-[#0083E9] block w-full p-2.5"
                                required>
                                <option value="">-- Pilih Status --</option>
                                <option value="aktif" {{ old('status', $student->status) == 'aktif' ? 'selected' : '' }}>
                                    Aktif
                                </option>
                                <option value="lulus" {{ old('status', $student->status) == 'lulus' ? 'selected' : '' }}>
                                    Lulus
                                </option>
                                <option value="mutasi" {{ old('status', $student->status) == 'mutasi' ? 'selected' : '' }}>
                                    Mutasi
                                </option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Status keaktifan siswa</p>
                        </div>

                        {{-- Penghargaan Siswa --}}
                        <div class="sm:col-span-2">
                            <label for="award" class="block mb-2 text-sm font-medium text-gray-900">
                                Penghargaan Siswa
                            </label>
                            <textarea name="award" id="award" rows="4"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-md focus:ring-[#0083E9] focus:border-[#0083E9] block w-full p-2.5"
                                placeholder="Masukkan penghargaan siswa (opsional)">{{ old('award', $student->award) }}</textarea>
                            <p class="mt-1 text-xs text-gray-500">Penghargaan yang telah diraih oleh siswa</p>
                        </div>

                    </div>

                    <div class="flex justify-end mt-6">
                        <button type="submit"
                            class="inline-flex cursor-pointer items-center px-5 py-2.5 text-sm font-medium text-center text-white bg-[#0083E9] rounded-md focus:ring-4 focus:ring-blue-300 hover:bg-[#0d65d9] active:scale-[0.98] transition-all duration-300 ease-out">
                            Perbarui Data Siswa
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const studentClassInput = document.getElementById('student_class');
                const classDropdown = document.getElementById('class-dropdown');
                const classIdInput = document.getElementById('class_id');
                const classOptions = document.querySelectorAll('.class-option');

                studentClassInput.addEventListener('focus', function() {
                    classDropdown.classList.remove('hidden');
                });

                studentClassInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();

                    classOptions.forEach(option => {
                        const optionText = option.getAttribute('data-value').toLowerCase();
                        if (optionText.includes(searchTerm)) {
                            option.parentElement.classList.remove('hidden');
                        } else {
                            option.parentElement.classList.add('hidden');
                        }
                    });
                });

                classOptions.forEach(option => {
                    option.addEventListener('click', function() {
                        const value = this.getAttribute('data-value');
                        const id = this.getAttribute('data-id');

                        studentClassInput.value = value;
                        classIdInput.value = id;
                        classDropdown.classList.add('hidden');
                    });
                });

                document.addEventListener('click', function(e) {
                    if (!studentClassInput.contains(e.target) && !classDropdown.contains(e.target)) {
                        classDropdown.classList.add('hidden');
                    }
                });
            });
        </script>
    @endpush
@endsection
