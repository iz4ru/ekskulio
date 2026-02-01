@extends('role.kesiswaan.layouts.app')
@section('title', 'Ekskulio | Edit Tahun Ajaran')
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

        <a href="{{ route('academic-years.index') }}"
            class="text-sm inline-flex items-center gap-1 font-semibold text-gray-500 hover:text-[#0083E9] active:scale-[0.98] transition-all duration-300 ease-out">
            <i class="fa-solid fa-chevron-left"></i>
            <span>Kembali</span>
        </a>

        <div class="flex gap-4 my-4 justify-between">
            <div>
                <h1 class="text-xl lg:text-2xl font-semibold text-gray-600">Edit Tahun Ajaran</h1>
                <p class="text-sm lg:text-base text-gray-400">Ubah data tahun ajaran yang sudah ada.</p>
            </div>
        </div>

        <div class="my-4 border-t-2 border-dashed border-gray-300 w-full"></div>

        <section>
            <div class="py-8 px-4 mx-auto max-w-2xl lg:py-16">
                <h2 class="mb-4 text-xl font-bold text-gray-900">Edit Tahun Ajaran</h2>

                <form action="{{ route('academic-years.update', $academicYear->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="grid gap-4 sm:grid-cols-2 sm:gap-6">

                        {{-- Tahun Ajaran Awal --}}
                        <div class="w-full">
                            <label for="year-start" class="block mb-2 text-sm font-medium text-gray-900">
                                Tahun Ajaran Awal
                                <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="year-start" id="year-start" placeholder="Masukkan tahun awal"
                                min="2000" max="2099" step="1"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-md focus:ring-[#0083E9] focus:border-[#0083E9] block w-full p-2.5"
                                value="{{ old('year-start', explode('/', $academicYear->year)[0]) }}" required>
                            <p class="mt-1 text-xs text-gray-500">Tahun akhir akan terisi otomatis</p>
                        </div>

                        {{-- Tahun Ajaran Akhir --}}
                        <div class="w-full">
                            <label for="year-end" class="block mb-2 text-sm font-medium text-gray-900">
                                Tahun Ajaran Akhir
                                <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="year-end" id="year-end" placeholder="Masukkan tahun akhir"
                                min="2000" max="2099" step="1"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-md focus:ring-[#0083E9] focus:border-[#0083E9] block w-full p-2.5"
                                value="{{ old('year-end', explode('/', $academicYear->year)[1]) }}" required>
                            <p class="mt-1 text-xs text-gray-500">Tahun awal akan terisi otomatis</p>
                        </div>

                        {{-- Semester --}}
                        <div class="w-full">
                            <label class="block mb-2 text-sm font-medium text-gray-900">
                                Semester
                                <span class="text-red-500">*</span>
                            </label>
                            <div class="flex flex-row gap-4 w-full">
                                <div
                                    class="flex-1 flex items-center ps-4 border border-gray-300 bg-gray-50 rounded-md hover:bg-gray-100 transition-colors cursor-pointer">
                                    <input id="semester-ganjil" type="radio" value="Ganjil" name="semester"
                                        class="w-4 h-4 text-[#0083E9] bg-gray-100 border-gray-300 focus:ring-[#0083E9] focus:ring-2 cursor-pointer"
                                        {{ old('semester', ucfirst($academicYear->semester)) == 'Ganjil' ? 'checked' : '' }}>
                                    <label for="semester-ganjil"
                                        class="w-full py-3 select-none ms-2 text-sm font-medium text-gray-900 cursor-pointer">
                                        Ganjil
                                    </label>
                                </div>

                                <div
                                    class="flex-1 flex items-center ps-4 border border-gray-300 bg-gray-50 rounded-md hover:bg-gray-100 transition-colors cursor-pointer">
                                    <input id="semester-genap" type="radio" value="Genap" name="semester"
                                        class="w-4 h-4 text-[#0083E9] bg-gray-100 border-gray-300 focus:ring-[#0083E9] focus:ring-2 cursor-pointer"
                                        {{ old('semester', ucfirst($academicYear->semester)) == 'Genap' ? 'checked' : '' }}>
                                    <label for="semester-genap"
                                        class="w-full py-3 select-none ms-2 text-sm font-medium text-gray-900 cursor-pointer">
                                        Genap
                                    </label>
                                </div>
                            </div>
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
            const yearStart = document.getElementById('year-start');
            const yearEnd = document.getElementById('year-end');

            yearStart.addEventListener('input', function() {
                if (this.value && this.value.length === 4) {
                    yearEnd.value = parseInt(this.value) + 1;
                }
            });

            yearEnd.addEventListener('input', function() {
                if (this.value && this.value.length === 4) {
                    yearStart.value = parseInt(this.value) - 1;
                }
            });

            const statusToggle = document.getElementById('status-toggle');
            const statusText = document.getElementById('status-text');

            function updateStatusText() {
                if (statusToggle.checked) {
                    statusText.textContent = 'Diaktifkan';
                    statusText.classList.add('text-[#0083E9]', 'font-semibold');
                } else {
                    statusText.textContent = 'Tidak Aktif';
                    statusText.classList.remove('text-[#0083E9]', 'font-semibold');
                }
            }

            // Initialize on page load
            updateStatusText();

            statusToggle.addEventListener('change', updateStatusText);
        </script>
    @endpush

@endsection
