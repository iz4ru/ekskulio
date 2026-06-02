@extends('role.kesiswaan.layouts.app')
@section('title', 'Ekskulio | Tambah Kategori Ekstrakurikuler')
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

        <a href="{{ route('extracurricular-category.index') }}"
            class="text-sm inline-flex items-center gap-1 font-semibold text-gray-500 hover:text-[#0083E9] active:scale-[0.98] transition-all duration-300 ease-out">
            <i class="fa-solid fa-chevron-left"></i>
            <span>Kembali</span>
        </a>

        <div class="flex gap-4 my-4 justify-between">
            <div>
                <h1 class="text-xl lg:text-2xl font-semibold text-gray-600">Tambah Kategori Ekstrakurikuler</h1>
                <p class="text-sm lg:text-base text-gray-400">Tambahkan kategori ekstrakurikuler baru.</p>
            </div>
        </div>

        <div class="my-4 border-t-2 border-dashed border-gray-300 w-full"></div>

        <section>
            <div class="py-8 px-4 mx-auto max-w-2xl lg:py-16">
                <h2 class="mb-4 text-xl font-bold text-gray-900">Tambahkan Kategori Ekstrakurikuler Baru</h2>

                <form action="{{ route('extracurricular-category.store') }}" method="POST" id="academic-year-form">
                    @csrf

                    <div class="grid gap-4 sm:grid-cols-2 sm:gap-6">

                        {{-- Kategori Ekstrakurikuler --}}
                        <div class="sm:col-span-2">
                            <label for="category_name" class="block mb-2 text-sm font-medium text-gray-900">
                                Nama Kategori Ekstrakurikuler
                                <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="category_name" id="category_name" placeholder="Masukkan nama kategori"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-md focus:ring-[#0083E9] focus:border-[#0083E9] block w-full p-2.5"
                                value="{{ old('category_name') }}" required>
                            <p class="mt-1 text-xs text-gray-500">Contoh: Olahraga, Seni, atau Karya Ilmiah</p>
                        </div>

                        {{-- Kode Kategori Ekstrakurikuler --}}
                        <div class="sm:col-span-2">
                            <label for="category_code" class="block mb-2 text-sm font-medium text-gray-900">
                                Kode Kategori Ekstrakurikuler
                                <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="text" name="category_code" id="category_code"
                                    placeholder="Akan diisi otomatis"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-md focus:ring-[#0083E9] focus:border-[#0083E9] block w-full p-2.5 pr-10"
                                    value="{{ old('category_code') }}" required readonly>

                                <!-- Loading spinner -->
                                <div id="code-loader" class="hidden absolute inset-y-0 right-0 flex items-center pr-3">
                                    <svg class="animate-spin h-4 w-4 text-[#0083E9]" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                </div>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Kode akan terisi otomatis berdasarkan nama</p>
                        </div>

                    </div>

                    <div class="flex justify-end mt-6">
                        <button type="submit"
                            class="inline-flex cursor-pointer items-center px-5 py-2.5 text-sm font-medium text-center text-white bg-[#0083E9] rounded-md focus:ring-4 focus:ring-blue-300 hover:bg-[#0d65d9] active:scale-[0.98] transition-all duration-300 ease-out">
                            Tambahkan Kategori Ekstrakurikuler
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </div>

    @push('scripts')
        <script>
            const nameInput = document.getElementById('category_name');
            const codeInput = document.getElementById('category_code');
            const codeLoader = document.getElementById('code-loader');

            // Generate Code Function
            const generateExtracurricularCode = async () => {
                const name = nameInput.value.trim();

                if (!name) {
                    codeInput.value = '';
                    return;
                }

                codeLoader.classList.remove('hidden');
                codeInput.classList.add('opacity-50');

                try {
                    const response = await fetch(`/extracurricular-category/generate-code/${encodeURIComponent(name)}`);
                    const data = await response.json();
                    codeInput.value = data.code;
                } catch (error) {
                    console.error('Error generating extracurricular category code:', error);
                    codeInput.value = '';
                } finally {
                    codeLoader.classList.add('hidden');
                    codeInput.classList.remove('opacity-50');
                }
            };

            let debounceTimer;
            nameInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    generateExtracurricularCode();
                }, 500);
            });
        </script>
    @endpush

@endsection
