@extends('role.kesiswaan.layouts.app')
@section('title', 'Ekskulio | Tambah Kelas')
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

        <a href="{{ route('student-class.index') }}"
            class="text-sm inline-flex items-center gap-1 font-semibold text-gray-500 hover:text-[#0083E9] active:scale-[0.98] transition-all duration-300 ease-out">
            <i class="fa-solid fa-chevron-left"></i>
            <span>Kembali</span>
        </a>

        <div class="flex gap-4 my-4 justify-between">
            <div>
                <h1 class="text-xl lg:text-2xl font-semibold text-gray-600">Tambah Kelas</h1>
                <p class="text-sm lg:text-base text-gray-400">Tambahkan kelas baru.</p>
            </div>
        </div>

        <div class="my-4 border-t-2 border-dashed border-gray-300 w-full"></div>

        <section>
            <div class="py-8 px-4 mx-auto max-w-2xl lg:py-16">
                <h2 class="mb-4 text-xl font-bold text-gray-900">Tambahkan Kelas Baru</h2>

                <form action="{{ route('student-class.store') }}" method="POST" id="class-form">
                    @csrf

                    <div class="grid gap-4 sm:grid-cols-2 sm:gap-6">

                        {{-- Nama Kelas --}}
                        <div class="sm:col-span-2">
                            <label for="class_name" class="block mb-2 text-sm font-medium text-gray-900">
                                Nama Kelas
                                <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="class_name" id="class_name" placeholder="Masukkan nama kelas"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-md focus:ring-[#0083E9] focus:border-[#0083E9] block w-full p-2.5"
                                value="{{ old('class_name') }}" required autofocus>
                            <p class="mt-1 text-xs text-gray-500">Contoh: X AKL 1, XI RPL 2, XI MIPA 3</p>
                        </div>

                        {{-- Status --}}
                        <div class="sm:col-span-2">
                            <label for="status-toggle" class="block mb-2 text-sm font-medium text-gray-900">Status</label>
                            <div class="mt-4">
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_active" id="status-toggle" class="sr-only peer"
                                        value="1" {{ old('is_active') ? 'checked' : '' }}>
                                    <div
                                        class="relative w-14 h-8 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-6 after:content-[''] after:absolute after:top-1 after:start-1 after:bg-white after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-[#0083E9]">
                                    </div>
                                    <span class="ms-3 text-sm font-medium text-gray-900" id="status-text">Tidak Aktif</span>
                                </label>
                                <p class="mt-1 text-xs text-gray-500">Aktifkan atau nonaktifkan kelas</p>
                            </div>
                        </div>

                    </div>

                    <div class="flex justify-end mt-6">
                        <button type="submit"
                            class="inline-flex cursor-pointer items-center px-5 py-2.5 text-sm font-medium text-center text-white bg-[#0083E9] rounded-md focus:ring-4 focus:ring-blue-300 hover:bg-[#0d65d9] active:scale-[0.98] transition-all duration-300 ease-out">
                            Tambahkan Kelas
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </div>

    @push('scripts')
        <script>
            // Status toggle
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

            updateStatusText();
            statusToggle.addEventListener('change', updateStatusText);
        </script>
    @endpush

@endsection
