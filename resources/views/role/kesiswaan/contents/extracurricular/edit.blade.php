@extends('role.kesiswaan.layouts.app')
@section('title', 'Ekskulio | Edit Ekstrakurikuler')
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
                <h1 class="text-xl lg:text-2xl font-semibold text-gray-600">Edit Ekstrakurikuler</h1>
                <p class="text-sm lg:text-base text-gray-400">Ubah data ekstrakurikuler yang sudah ada.</p>
            </div>
        </div>

        <div class="my-4 border-t-2 border-dashed border-gray-300 w-full"></div>

        <section>
            <div class="py-8 px-4 mx-auto max-w-2xl lg:py-16">
                <h2 class="mb-4 text-xl font-bold text-gray-900">Edit Data Ekstrakurikuler</h2>

                <form action="{{ route('extracurricular.update', $extracurricular->id) }}" method="POST"
                    id="extracurricular-form">
                    @csrf
                    @method('PUT')

                    <div class="grid gap-4 sm:grid-cols-2 sm:gap-6">

                        {{-- Nama Ekstrakurikuler --}}
                        <div class="w-full">
                            <label for="extracurricular_name" class="block mb-2 text-sm font-medium text-gray-900">
                                Nama Ekstrakurikuler
                                <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="extracurricular_name" id="extracurricular_name"
                                placeholder="Masukkan nama ekstrakurikuler"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-md focus:ring-[#0083E9] focus:border-[#0083E9] block w-full p-2.5"
                                value="{{ old('extracurricular_name', $extracurricular->name) }}" required>
                            <p class="mt-1 text-xs text-gray-500">Contoh: Pramuka, PMR, atau Paskibra</p>
                        </div>

                        {{-- Kode Ekstrakurikuler --}}
                        <div class="w-full">
                            <label for="extracurricular_code" class="block mb-2 text-sm font-medium text-gray-900">
                                Kode Ekstrakurikuler
                                <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="text" name="extracurricular_code" id="extracurricular_code"
                                    placeholder="Edit kode ekstrakurikuler"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-md focus:ring-[#0083E9] focus:border-[#0083E9] block w-full p-2.5"
                                    value="{{ old('extracurricular_code', $extracurricular->code) }}" required>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Edit kode ekstrakurikuler jika diperlukan</p>
                        </div>

                        {{-- Kategori Ekstrakurikuler --}}
                        <div class="w-full relative">
                            <label for="extracurricular_category" class="block mb-2 text-sm font-medium text-gray-900">
                                Kategori Ekstrakurikuler
                                <span class="text-red-500">*</span>
                            </label>

                            <div class="relative">
                                <input type="text" name="extracurricular_category" id="extracurricular_category"
                                    placeholder="Pilih atau ketik kategori ekstrakurikuler" autocomplete="off"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-md focus:ring-[#0083E9] focus:border-[#0083E9] block w-full p-2.5 pr-10"
                                    value="{{ old('extracurricular_category', $extracurricular->category?->name ?? '') }}">

                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="m19 9-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>


                            <div id="category-dropdown"
                                class="hidden absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto">
                                <ul class="py-1">
                                    @foreach ($extracurricularCategories as $category)
                                        <li>
                                            <button type="button"
                                                class="category-option w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-[#0083E9] transition-colors duration-150"
                                                data-value="{{ $category->name }}" data-id="{{ $category->id }}">
                                                {{ $category->name }}
                                            </button>
                                        </li>
                                    @endforeach
                                    @if ($extracurricularCategories->isEmpty())
                                        <li class="px-4 py-2 text-sm text-gray-500 italic">Tidak ada kategori</li>
                                    @endif
                                </ul>
                            </div>

                            <input type="hidden" name="category_id" id="category_id"
                                value="{{ old('category_id', $extracurricular->category_id) }}">
                            <p class="mt-1 text-xs text-gray-500">Pilih kategori ekstrakurikuler dari daftar</p>
                        </div>

                        {{-- Pembina Ekstrakurikuler --}}
                        <div class="w-full relative">
                            <label for="extracurricular_user" class="block mb-2 text-sm font-medium text-gray-900">
                                Pembina Ekstrakurikuler
                                <span class="text-red-500">*</span>
                            </label>

                            <div class="relative">
                                <input type="text" name="extracurricular_user" id="extracurricular_user"
                                    placeholder="Pilih atau ketik pembina ekstrakurikuler" autocomplete="off"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-md focus:ring-[#0083E9] focus:border-[#0083E9] block w-full p-2.5 pr-10"
                                    value="{{ old('extracurricular_user', $extracurricular->users->first()->user->name ?? '') }}">

                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="m19 9-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>

                            <div id="user-dropdown"
                                class="hidden absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto">
                                <ul class="py-1">
                                    @foreach ($extracurricularUsers as $user)
                                        <li>
                                            <button type="button"
                                                class="user-option w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-[#0083E9] transition-colors duration-150"
                                                data-value="{{ $user->name }}" data-id="{{ $user->id }}">
                                                {{ $user->name }} ({{ $user->email }})
                                            </button>
                                        </li>
                                    @endforeach
                                    @if ($extracurricularUsers->isEmpty())
                                        <li class="px-4 py-2 text-sm text-gray-500 italic">Tidak ada data pembina</li>
                                    @endif
                                </ul>
                            </div>

                            <input type="hidden" name="user_id" id="user_id"
                                value="{{ old('user_id', $extracurricular->users->first()->user_id ?? '') }}">
                            <p class="mt-1 text-xs text-gray-500">Pilih pembina ekstrakurikuler dari daftar</p>
                        </div>

                        {{-- Jadwal Ekstrakurikuler --}}
                        <div class="sm:col-span-2">
                            <label class="block mb-2 text-sm font-medium text-gray-900">Jadwal Ekstrakurikuler <span
                                    class="text-red-500">*</span></label>
                            @php
                                $selectedDays = old('days', $extracurricular->schedules->pluck('day')->toArray());
                            @endphp
                            <ul
                                class="items-center select-none w-full text-sm font-medium text-gray-900 bg-white border border-gray-300 rounded-md sm:flex">
                                <li class="w-full border-b border-gray-300 sm:border-b-0 sm:border-r">
                                    <div class="flex items-center ps-3">
                                        <input id="monday-checkbox" name="days[]" type="checkbox" value="Senin"
                                            class="cursor-pointer w-4 h-4 text-[#0083E9] bg-gray-100 border-gray-300 rounded focus:ring-[#0083E9] focus:ring-2"
                                            {{ in_array('Senin', $selectedDays) ? 'checked' : '' }}>
                                        <label for="monday-checkbox"
                                            class="cursor-pointer w-full py-3 ms-2 text-sm font-medium text-gray-900">Senin</label>
                                    </div>
                                </li>
                                <li class="w-full border-b border-gray-300 sm:border-b-0 sm:border-r">
                                    <div class="flex items-center ps-3">
                                        <input id="tuesday-checkbox" name="days[]" type="checkbox" value="Selasa"
                                            class="cursor-pointer w-4 h-4 text-[#0083E9] bg-gray-100 border-gray-300 rounded focus:ring-[#0083E9] focus:ring-2"
                                            {{ in_array('Selasa', $selectedDays) ? 'checked' : '' }}>
                                        <label for="tuesday-checkbox"
                                            class="cursor-pointer w-full py-3 ms-2 text-sm font-medium text-gray-900">Selasa</label>
                                    </div>
                                </li>
                                <li class="w-full border-b border-gray-300 sm:border-b-0 sm:border-r">
                                    <div class="flex items-center ps-3">
                                        <input id="wednesday-checkbox" name="days[]" type="checkbox" value="Rabu"
                                            class="cursor-pointer w-4 h-4 text-[#0083E9] bg-gray-100 border-gray-300 rounded focus:ring-[#0083E9] focus:ring-2"
                                            {{ in_array('Rabu', $selectedDays) ? 'checked' : '' }}>
                                        <label for="wednesday-checkbox"
                                            class="cursor-pointer w-full py-3 ms-2 text-sm font-medium text-gray-900">Rabu</label>
                                    </div>
                                </li>
                                <li class="w-full border-b border-gray-300 sm:border-b-0 sm:border-r">
                                    <div class="flex items-center ps-3">
                                        <input id="thursday-checkbox" name="days[]" type="checkbox" value="Kamis"
                                            class="cursor-pointer w-4 h-4 text-[#0083E9] bg-gray-100 border-gray-300 rounded focus:ring-[#0083E9] focus:ring-2"
                                            {{ in_array('Kamis', $selectedDays) ? 'checked' : '' }}>
                                        <label for="thursday-checkbox"
                                            class="cursor-pointer w-full py-3 ms-2 text-sm font-medium text-gray-900">Kamis</label>
                                    </div>
                                </li>
                                <li class="w-full border-b border-gray-300 sm:border-b-0 sm:border-r">
                                    <div class="flex items-center ps-3">
                                        <input id="friday-checkbox" name="days[]" type="checkbox" value="Jumat"
                                            class="cursor-pointer w-4 h-4 text-[#0083E9] bg-gray-100 border-gray-300 rounded focus:ring-[#0083E9] focus:ring-2"
                                            {{ in_array('Jumat', $selectedDays) ? 'checked' : '' }}>
                                        <label for="friday-checkbox"
                                            class="cursor-pointer w-full py-3 ms-2 text-sm font-medium text-gray-900">Jumat</label>
                                    </div>
                                </li>
                                <li class="w-full border-b border-gray-300 sm:border-b-0 sm:border-r">
                                    <div class="flex items-center ps-3">
                                        <input id="saturday-checkbox" name="days[]" type="checkbox" value="Sabtu"
                                            class="cursor-pointer w-4 h-4 text-[#0083E9] bg-gray-100 border-gray-300 rounded focus:ring-[#0083E9] focus:ring-2"
                                            {{ in_array('Sabtu', $selectedDays) ? 'checked' : '' }}>
                                        <label for="saturday-checkbox"
                                            class="cursor-pointer w-full py-3 ms-2 text-sm font-medium text-gray-900">Sabtu</label>
                                    </div>
                                </li>
                                <li class="w-full">
                                    <div class="flex items-center ps-3">
                                        <input id="sunday-checkbox" name="days[]" type="checkbox" value="Minggu"
                                            class="cursor-pointer w-4 h-4 text-[#0083E9] bg-gray-100 border-gray-300 rounded focus:ring-[#0083E9] focus:ring-2"
                                            {{ in_array('Minggu', $selectedDays) ? 'checked' : '' }}>
                                        <label for="sunday-checkbox"
                                            class="cursor-pointer w-full py-3 ms-2 text-sm font-medium text-gray-900">Minggu</label>
                                    </div>
                                </li>
                            </ul>
                            <p class="mt-1 text-xs text-gray-500">Pilih jadwal hari pelaksanaan ekstrakurikuler (minimal 1)
                            </p>
                        </div>

                        {{-- Deskripsi Ekstrakurikuler --}}
                        <div class="sm:col-span-2">
                            <label for="description" class="block mb-2 text-sm font-medium text-gray-900">
                                Deskripsi Ekstrakurikuler
                            </label>
                            <textarea name="description" id="description" rows="4"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-md focus:ring-[#0083E9] focus:border-[#0083E9] block w-full p-2.5"
                                placeholder="Masukkan deskripsi ekstrakurikuler (opsional)">{{ old('description', $extracurricular->description) }}</textarea>
                            <p class="mt-1 text-xs text-gray-500">Deskripsi singkat tentang ekstrakurikuler</p>
                        </div>

                        {{-- Penghargaan Ekstrakurikuler --}}
                        <div class="sm:col-span-2">
                            <label for="award" class="block mb-2 text-sm font-medium text-gray-900">
                                Penghargaan Ekstrakurikuler
                            </label>
                            <textarea name="award" id="award" rows="4"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-md focus:ring-[#0083E9] focus:border-[#0083E9] block w-full p-2.5"
                                placeholder="Masukkan penghargaan ekstrakurikuler (opsional)">{{ old('award', $extracurricular->award) }}</textarea>
                            <p class="mt-1 text-xs text-gray-500">Penghargaan yang telah diraih oleh ekstrakurikuler</p>
                        </div>

                    </div>

                    <div class="flex justify-end mt-6">
                        <button type="submit"
                            class="inline-flex cursor-pointer items-center px-5 py-2.5 text-sm font-medium text-center text-white bg-[#0083E9] rounded-md focus:ring-4 focus:ring-blue-300 hover:bg-[#0d65d9] active:scale-[0.98] transition-all duration-300 ease-out">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </section>

    </div>

    @push('scripts')
        <script>
            const categoryInput = document.getElementById('extracurricular_category');
            const categoryDropdown = document.getElementById('category-dropdown');
            const categoryOptions = document.querySelectorAll('.category-option');
            const categoryIdInput = document.getElementById('category_id');

            const userInput = document.getElementById('extracurricular_user');
            const userDropdown = document.getElementById('user-dropdown');
            const userOptions = document.querySelectorAll('.user-option');
            const userIdInput = document.getElementById('user_id');

            // Category Dropdown Logic
            categoryInput.addEventListener('focus', function() {
                categoryDropdown.classList.remove('hidden');
                userDropdown.classList.add('hidden');
                filterCategoryOptions(this.value);
            });

            categoryInput.addEventListener('input', function() {
                if (this.value.trim() === '') {
                    categoryIdInput.value = '';
                }
                filterCategoryOptions(this.value);
            });

            categoryOptions.forEach(option => {
                option.addEventListener('click', function() {
                    categoryInput.value = this.getAttribute('data-value');
                    categoryIdInput.value = this.getAttribute('data-id');
                    categoryDropdown.classList.add('hidden');
                });
            });

            function filterCategoryOptions(searchTerm) {
                const term = searchTerm.toLowerCase();
                let hasVisible = false;

                categoryOptions.forEach(option => {
                    const text = option.textContent.toLowerCase();
                    if (text.includes(term)) {
                        option.parentElement.classList.remove('hidden');
                        hasVisible = true;
                    } else {
                        option.parentElement.classList.add('hidden');
                    }
                });

                if (hasVisible) {
                    categoryDropdown.classList.remove('hidden');
                } else {
                    categoryDropdown.classList.add('hidden');
                }
            }

            // User Dropdown Logic
            userInput.addEventListener('focus', function() {
                userDropdown.classList.remove('hidden');
                categoryDropdown.classList.add('hidden');
                filterUserOptions(this.value);
            });

            userInput.addEventListener('input', function() {
                if (this.value.trim() === '') {
                    userIdInput.value = '';
                }
                filterUserOptions(this.value);
            });

            userOptions.forEach(option => {
                option.addEventListener('click', function() {
                    userInput.value = this.getAttribute('data-value');
                    userIdInput.value = this.getAttribute('data-id');
                    userDropdown.classList.add('hidden');
                });
            });

            function filterUserOptions(searchTerm) {
                const term = searchTerm.toLowerCase();
                let hasVisible = false;

                userOptions.forEach(option => {
                    const text = option.textContent.toLowerCase();
                    if (text.includes(term)) {
                        option.parentElement.classList.remove('hidden');
                        hasVisible = true;
                    } else {
                        option.parentElement.classList.add('hidden');
                    }
                });

                if (hasVisible) {
                    userDropdown.classList.remove('hidden');
                } else {
                    userDropdown.classList.add('hidden');
                }
            }

            // Close dropdowns when clicking outside
            document.addEventListener('click', function(e) {
                if (!categoryInput.contains(e.target) && !categoryDropdown.contains(e.target)) {
                    categoryDropdown.classList.add('hidden');
                }
                if (!userInput.contains(e.target) && !userDropdown.contains(e.target)) {
                    userDropdown.classList.add('hidden');
                }
            });
        </script>
    @endpush

@endsection
