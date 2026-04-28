@extends('role.kesiswaan.layouts.app')
@section('title', 'Ekskulio | Daftar Siswa ke Ekskul')
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

        <a href="{{ route('memberships.index') }}"
            class="text-sm inline-flex items-center gap-1 font-semibold text-gray-500 hover:text-[#0083E9] active:scale-[0.98] transition-all duration-300 ease-out">
            <i class="fa-solid fa-chevron-left"></i>
            <span>Kembali</span>
        </a>

        <div class="flex gap-4 my-4 justify-between">
            <div>
                <h1 class="text-xl lg:text-2xl font-semibold text-gray-600">Daftarkan Siswa ke Ekskul</h1>
                <p class="text-sm lg:text-base text-gray-400">Pilih siswa dan ekstrakurikuler yang akan diikuti.</p>
                @if ($activeAY)
                    <span class="inline-block mt-4 px-3 py-1 text-xs font-medium bg-blue-100 text-blue-700 rounded-full">
                        <i class="fa-solid fa-calendar mr-1"></i>
                        {{ $activeAY->display_name }}
                    </span>
                @endif
            </div>
        </div>

        <div class="my-4 border-t-2 border-dashed border-gray-300 w-full"></div>

        <div class="bg-amber-50 border border-amber-200 rounded-md p-4 mb-6">
            <div class="flex items-start gap-3">
                <i class="fa-solid fa-info-circle text-amber-500 mt-0.5"></i>
                <div>
                    <p class="text-sm text-amber-700">
                        <strong>Catatan:</strong> Hanya siswa dengan status <span class="font-semibold">AKTIF</span> dan tingkat
                        <span class="font-semibold">Kelas X atau XI</span> yang dapat didaftarkan ke ekstrakurikuler.
                        Siswa Kelas XII tidak diperkenankan mengikuti ekstrakurikuler.
                    </p>
                </div>
            </div>
        </div>

        <section>
            <div class="py-8 px-4 mx-auto max-w-2xl lg:py-16">
                <h2 class="mb-4 text-xl font-bold text-gray-900">Form Pendaftaran</h2>

                <form action="{{ route('memberships.store') }}" method="POST" id="membership-form">
                    @csrf

                    <div class="grid gap-4 sm:grid-cols-2 sm:gap-6">

                        {{-- Ekstrakurikuler --}}
                        <div class="sm:col-span-2 relative">
                            <label for="extracurricular_name" class="block mb-2 text-sm font-medium text-gray-900">
                                Ekstrakurikuler
                                <span class="text-red-500">*</span>
                            </label>

                            <div class="relative">
                                <input type="text" name="extracurricular_name" id="extracurricular_name"
                                    placeholder="Pilih atau ketik ekstrakurikuler" autocomplete="off"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-md focus:ring-[#0083E9] focus:border-[#0083E9] block w-full p-2.5 pr-10"
                                    value="{{ old('extracurricular_name') }}" required>

                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="m19 9-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>

                            <div id="extracurricular-dropdown"
                                class="hidden absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto">
                                <ul class="py-1" id="extracurricular-list">
                                    @foreach ($extracurriculars as $ekskul)
                                        <li>
                                            <button type="button"
                                                class="extracurricular-option w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-[#0083E9] transition-colors duration-150"
                                                data-value="{{ $ekskul->name }}" data-id="{{ $ekskul->id }}">
                                                {{ $ekskul->name }}
                                                @if ($ekskul->category)
                                                    <span class="text-gray-400">({{ $ekskul->category->name }})</span>
                                                @endif
                                            </button>
                                        </li>
                                    @endforeach
                                    @if ($extracurriculars->isEmpty())
                                        <li class="px-4 py-2 text-sm text-gray-500 italic">Tidak ada ekstrakurikuler aktif</li>
                                    @endif
                                </ul>
                            </div>

                            <input type="hidden" name="extracurricular_id" id="extracurricular_id" value="{{ old('extracurricular_id') }}">
                            <p class="mt-1 text-xs text-gray-500">Pilih ekstrakurikuler yang akan diikuti</p>
                        </div>

                        {{-- Kelas --}}
                        <div class="sm:col-span-2 relative">
                            <label for="student_class" class="block mb-2 text-sm font-medium text-gray-900">
                                Kelas
                                <span class="text-red-500">*</span>
                            </label>

                            <div class="relative">
                                <input type="text" name="student_class" id="student_class"
                                    placeholder="Pilih atau ketik kelas" autocomplete="off"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-md focus:ring-[#0083E9] focus:border-[#0083E9] block w-full p-2.5 pr-10"
                                    value="{{ old('student_class') }}" required>

                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="m19 9-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>

                            <div id="class-dropdown"
                                class="hidden absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto">
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
                            <p class="mt-1 text-xs text-gray-500">Pilih kelas dari daftar</p>
                        </div>

                        {{-- Siswa --}}
                        <div class="sm:col-span-2 relative">
                            <label for="student_id" class="block mb-2 text-sm font-medium text-gray-900">
                                Siswa
                                <span class="text-red-500">*</span>
                            </label>

                            <div class="relative">
                                <input type="text" name="student_name" id="student_name"
                                    placeholder="Pilih siswa" autocomplete="off" disabled
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-md focus:ring-[#0083E9] focus:border-[#0083E9] block w-full p-2.5 pr-10 disabled:bg-gray-100 disabled:text-gray-400"
                                    value="{{ old('student_name') }}" required>

                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="m19 9-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>

                            <div id="student-dropdown"
                                class="hidden absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto">
                                <ul class="py-1" id="student-list">
                                    <li class="px-4 py-2 text-sm text-gray-500 italic">Pilih kelas terlebih dahulu</li>
                                </ul>
                            </div>

                            <input type="hidden" name="student_id" id="student_id" value="{{ old('student_id') }}">
                            <p class="mt-1 text-xs text-gray-500">
                                Hanya menampilkan siswa eligible (Aktif, Kelas X-XI)
                            </p>
                        </div>

                    </div>

                    <div class="flex justify-end mt-6">
                        <button type="submit"
                            class="inline-flex cursor-pointer items-center px-5 py-2.5 text-sm font-medium text-center text-white bg-[#0083E9] rounded-md focus:ring-4 focus:ring-blue-300 hover:bg-[#0d65d9] active:scale-[0.98] transition-all duration-300 ease-out">
                            <i class="fa-solid fa-user-plus mr-2"></i>
                            Daftarkan Siswa
                        </button>
                    </div>
                </form>
            </div>
        </section>

        {{-- Info Section --}}
        <div class="px-4 mx-auto max-w-2xl pb-8">
            <div class="p-4 bg-gray-50 border border-gray-200 rounded-md">
                <h3 class="font-medium text-gray-700 mb-2">
                    <i class="fa-solid fa-circle-info mr-2 text-blue-500"></i>
                    Informasi Pendaftaran
                </h3>
                <ul class="list-disc list-inside text-sm text-gray-600 space-y-1">
                    <li>Satu siswa hanya dapat terdaftar di satu ekstrakurikuler per tahun ajaran</li>
                    <li>Status keanggotaan default adalah <span class="font-medium text-green-600">Aktif</span></li>
                    <li>Keanggotaan dapat diubah statusnya nanti melalui halaman daftar keanggotaan</li>
                </ul>
            </div>
        </div>

    </div>

@push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const extracurricularInput = document.getElementById('extracurricular_name');
                const extracurricularDropdown = document.getElementById('extracurricular-dropdown');
                const extracurricularIdInput = document.getElementById('extracurricular_id');

                const studentClassInput = document.getElementById('student_class');
                const classDropdown = document.getElementById('class-dropdown');
                const classIdInput = document.getElementById('class_id');
                
                const studentNameInput = document.getElementById('student_name');
                const studentDropdown = document.getElementById('student-dropdown');
                const studentIdInput = document.getElementById('student_id');
                const studentList = document.getElementById('student-list');

                extracurricularInput.addEventListener('input', function() {
                    const query = this.value.toLowerCase();
                    filterExtracurricularDropdown(query);
                });

                extracurricularInput.addEventListener('focus', function() {
                    extracurricularDropdown.classList.remove('hidden');
                    filterExtracurricularDropdown('');
                });

                extracurricularInput.addEventListener('blur', function() {
                    setTimeout(() => extracurricularDropdown.classList.add('hidden'), 200);
                });

                function filterExtracurricularDropdown(query) {
                    const ekskuls = @json($extracurriculars);
                    const filtered = ekskuls.filter(e => e.name.toLowerCase().includes(query));
                    const ul = extracurricularDropdown.querySelector('ul');
                    ul.innerHTML = '';
                    
                    if (filtered.length === 0) {
                        ul.innerHTML = '<li class="px-4 py-2 text-sm text-gray-500 italic">Tidak ada ekstrakurikuler</li>';
                        return;
                    }

                    filtered.forEach(e => {
                        const li = document.createElement('li');
                        const categoryName = e.category ? ` <span class="text-gray-400">(${e.category.name})</span>` : '';
                        li.innerHTML = `<button type="button" class="extracurricular-option w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-[#0083E9] transition-colors duration-150" data-value="${e.name}" data-id="${e.id}">${e.name}${categoryName}</button>`;
                        ul.appendChild(li);
                    });
                }

                extracurricularDropdown.addEventListener('click', function(e) {
                    const btn = e.target.closest('.extracurricular-option');
                    if (btn) {
                        const ekskulName = btn.dataset.value;
                        const ekskulId = btn.dataset.id;
                        
                        extracurricularInput.value = ekskulName;
                        extracurricularIdInput.value = ekskulId;
                    }
                });

                studentClassInput.addEventListener('input', function() {
                    const query = this.value.toLowerCase();
                    filterClassDropdown(query);
                });

                studentClassInput.addEventListener('focus', function() {
                    classDropdown.classList.remove('hidden');
                    filterClassDropdown('');
                });

                studentClassInput.addEventListener('blur', function() {
                    setTimeout(() => classDropdown.classList.add('hidden'), 200);
                });

                function filterClassDropdown(query) {
                    const classes = @json($studentClasses);
                    const filtered = classes.filter(c => c.name.toLowerCase().includes(query));
                    const ul = classDropdown.querySelector('ul');
                    ul.innerHTML = '';
                    
                    if (filtered.length === 0) {
                        ul.innerHTML = '<li class="px-4 py-2 text-sm text-gray-500 italic">Tidak ada kelas</li>';
                        return;
                    }

                    filtered.forEach(c => {
                        const li = document.createElement('li');
                        li.innerHTML = `<button type="button" class="class-option w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-[#0083E9] transition-colors duration-150" data-value="${c.name}" data-id="${c.id}">${c.name}</button>`;
                        ul.appendChild(li);
                    });
                }

                classDropdown.addEventListener('click', function(e) {
                    const btn = e.target.closest('.class-option');
                    if (btn) {
                        const className = btn.dataset.value;
                        const classId = btn.dataset.id;
                        
                        studentClassInput.value = className;
                        classIdInput.value = classId;
                        
                        loadStudentsForClass(classId);
                        studentNameInput.disabled = false;
                        studentNameInput.value = '';
                        studentIdInput.value = '';
                        studentNameInput.placeholder = 'Pilih siswa';
                    }
                });

                function loadStudentsForClass(classId) {
                    studentList.innerHTML = '<li class="px-4 py-2 text-sm text-gray-500">Memuat...</li>';
                    
                    fetch(`/memberships/eligible-students?class_id=${classId}`)
                        .then(response => response.json())
                        .then(students => {
                            studentList.innerHTML = '';
                            
                            if (students.length === 0) {
                                studentList.innerHTML = '<li class="px-4 py-2 text-sm text-gray-500 italic">Tidak ada siswa eligible di kelas ini</li>';
                                return;
                            }

                            students.forEach(s => {
                                const li = document.createElement('li');
                                li.innerHTML = `<button type="button" class="student-option w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-[#0083E9] transition-colors duration-150" data-id="${s.id}" data-name="${s.name} - ${s.id_number} (Kelas ${s.grade})">${s.name} - ${s.id_number} (Kelas ${s.grade})</button>`;
                                studentList.appendChild(li);
                            });
                        })
                        .catch(error => {
                            studentList.innerHTML = '<li class="px-4 py-2 text-sm text-gray-500 italic">G memuat data</li>';
                        });
                }

                studentNameInput.addEventListener('focus', function() {
                    const classId = classIdInput.value;
                    if (classId) {
                        loadStudentsForClass(classId);
                        studentDropdown.classList.remove('hidden');
                    }
                });

                studentNameInput.addEventListener('input', function() {
                    const classId = classIdInput.value;
                    if (classId) {
                        const query = this.value.toLowerCase();
                        fetch(`/memberships/eligible-students?class_id=${classId}`)
                            .then(response => response.json())
                            .then(students => {
                                const filtered = students.filter(s => s.name.toLowerCase().includes(query));
                                renderStudentList(filtered);
                            });
                    }
                });

                studentNameInput.addEventListener('blur', function() {
                    setTimeout(() => studentDropdown.classList.add('hidden'), 200);
                });

                function renderStudentList(students) {
                    studentList.innerHTML = '';
                    
                    if (students.length === 0) {
                        studentList.innerHTML = '<li class="px-4 py-2 text-sm text-gray-500 italic">Tidak ada siswa</li>';
                        return;
                    }

                    students.forEach(s => {
                        const li = document.createElement('li');
                        li.innerHTML = `<button type="button" class="student-option w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-[#0083E9] transition-colors duration-150" data-id="${s.id}" data-name="${s.name} - ${s.id_number} (Kelas ${s.grade})">${s.name} - ${s.id_number} (Kelas ${s.grade})</button>`;
                        studentList.appendChild(li);
                    });
                }

                studentDropdown.addEventListener('click', function(e) {
                    const btn = e.target.closest('.student-option');
                    if (btn) {
                        const sid = btn.dataset.id;
                        const sname = btn.dataset.name;
                        
                        studentNameInput.value = sname;
                        studentIdInput.value = sid;
                    }
                });
            });
        </script>
    @endpush

@endsection
