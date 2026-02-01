@extends('role.kesiswaan.layouts.app')
@section('title', 'Ekskulio | Edit Pengguna')
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

    <a href="{{ route('user-management.index') }}"
        class="text-sm inline-flex items-center gap-1 font-semibold text-gray-500 hover:text-[#0083E9] active:scale-[0.98] transition-all duration-300 ease-out">
        <i class="fa-solid fa-chevron-left"></i>
        <span>Kembali</span>
    </a>

    <div class="flex gap-4 my-4 justify-between">
        <div>
            <h1 class="text-xl lg:text-2xl font-semibold text-gray-600">Edit Pengguna</h1>
            <p class="text-sm lg:text-base text-gray-400">Perbarui data pengguna {{ $user->name }}.</p>
        </div>
    </div>

    <div class="my-4 border-t-2 border-dashed border-gray-300 w-full"></div>

    <section>
        <div class="py-8 px-4 mx-auto max-w-2xl lg:py-16">
            <h2 class="mb-4 text-xl font-bold text-gray-900">Perbarui Data Pengguna</h2>

            <form action="{{ route('user-management.update', $user->uuid) }}" method="POST" id="user-form">
                @csrf
                @method('PUT')

                <div class="grid gap-4 sm:grid-cols-2 sm:gap-6">

                    {{-- Nama Pengguna --}}
                    <div class="w-full">
                        <label for="name" class="block mb-2 text-sm font-medium text-gray-900">
                            Nama Pengguna <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" placeholder="Masukkan nama pengguna"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-md focus:ring-[#0083E9] focus:border-[#0083E9] block w-full p-2.5"
                            value="{{ old('name', $user->name) }}" required>
                        <p class="mt-1 text-xs text-gray-500">Masukkan nama lengkap pengguna</p>
                    </div>

                    {{-- Username --}}
                    <div class="w-full">
                        <label for="username" class="block mb-2 text-sm font-medium text-gray-900">
                            Username <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="username" id="username" placeholder="Masukkan username"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-md focus:ring-[#0083E9] focus:border-[#0083E9] block w-full p-2.5"
                            value="{{ old('username', $user->username) }}" required>
                        <p class="mt-1 text-xs text-gray-500">Username untuk login</p>
                    </div>

                    {{-- Email --}}
                    <div class="w-full">
                        <label for="email" class="block mb-2 text-sm font-medium text-gray-900">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email" name="email" id="email" placeholder="Masukkan email pengguna"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-md focus:ring-[#0083E9] focus:border-[#0083E9] block w-full p-2.5"
                            value="{{ old('email', $user->email) }}" required>
                        <p class="mt-1 text-xs text-gray-500">Contoh: user@example.com</p>
                    </div>

                    {{-- Nomor Telepon --}}
                    <div class="w-full">
                        <label for="phone" class="block mb-2 text-sm font-medium text-gray-900">
                            Nomor Telepon <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="phone" id="phone" placeholder="Masukkan nomor telepon"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-md focus:ring-[#0083E9] focus:border-[#0083E9] block w-full p-2.5"
                            value="{{ old('phone', $user->phone) }}" required>
                        <p class="mt-1 text-xs text-gray-500">Contoh: 08123456789</p>
                    </div>

                    {{-- Role - RADIO BUTTON --}}
                    <div class="sm:col-span-2">
                        <label class="block mb-2 text-sm font-medium text-gray-900">
                            Role <span class="text-red-500">*</span>
                        </label>

                        <div class="grid grid-cols-2 gap-4 w-full">
                            {{-- Admin --}}
                            <label class="flex items-center p-3 bg-gray-50 border-2 border-gray-200 rounded-md hover:border-[#0083E9] hover:bg-blue-50 transition-all duration-200 cursor-pointer group">
                                <input type="radio" name="role" value="admin"
                                    class="w-4 h-4 text-[#0083E9] bg-gray-100 border-gray-300 focus:ring-[#0083E9] focus:ring-2 mr-3 group-hover:scale-110 transition-transform duration-200"
                                    {{ old('role', $user->role) == 'admin' ? 'checked' : '' }} required>
                                <div>
                                    <div class="font-medium text-gray-900 group-hover:text-[#0083E9]">Admin</div>
                                    <div class="text-xs text-gray-500">Kelola seluruh sistem</div>
                                </div>
                            </label>

                            {{-- Pembina --}}
                            <label class="flex items-center p-3 bg-gray-50 border-2 border-gray-200 rounded-md hover:border-[#0083E9] hover:bg-blue-50 transition-all duration-200 cursor-pointer group">
                                <input type="radio" name="role" value="pembina"
                                    class="w-4 h-4 text-[#0083E9] bg-gray-100 border-gray-300 focus:ring-[#0083E9] focus:ring-2 mr-3 group-hover:scale-110 transition-transform duration-200"
                                    {{ old('role', $user->role) == 'pembina' ? 'checked' : '' }} required>
                                <div>
                                    <div class="font-medium text-gray-900 group-hover:text-[#0083E9]">Pembina</div>
                                    <div class="text-xs text-gray-500">Kelola ekstrakurikuler</div>
                                </div>
                            </label>
                        </div>

                        @error('role')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-xs text-gray-500">Pilih role pengguna</p>
                    </div>

                    {{-- Ekstrakurikuler --}}
                    <div class="sm:col-span-2 relative">
                        <label for="user_extracurricular" class="block mb-2 text-sm font-medium text-gray-900">
                            Ekstrakurikuler
                        </label>

                        <div class="relative">
                            <input type="text" name="user_extracurricular" id="user_extracurricular"
                                placeholder="Pilih atau ketik ekstrakurikuler" autocomplete="off"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-md focus:ring-[#0083E9] focus:border-[#0083E9] block w-full p-2.5 pr-10"
                                value="{{ old('user_extracurricular', $user->extracurriculars->first()->extracurricular->name ?? '') }}">

                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7" />
                                </svg>
                            </div>
                        </div>

                        <div id="extracurricular-dropdown"
                            class="hidden absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto">
                            <ul class="py-1">
                                @foreach ($extracurriculars as $extracurricular)
                                    <li>
                                        <button type="button"
                                            class="extracurricular-option w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-[#0083E9] transition-colors duration-150"
                                            data-value="{{ $extracurricular->name }}"
                                            data-id="{{ $extracurricular->id }}">
                                            {{ $extracurricular->name }}
                                        </button>
                                    </li>
                                @endforeach
                                @if ($extracurriculars->isEmpty())
                                    <li class="px-4 py-2 text-sm text-gray-500 italic">Tidak ada data ekstrakurikuler</li>
                                @endif
                            </ul>
                        </div>

                        <input type="hidden" name="extracurricular_id" id="extracurricular_id"
                            value="{{ old('extracurricular_id', $user->extracurriculars->first()->extracurricular_id ?? '') }}">
                        <p class="mt-1 text-xs text-gray-500">Pilih ekstrakurikuler dari daftar (opsional)</p>
                    </div>

                    {{-- Password --}}
                    <div class="w-full">
                        <label for="password" class="block mb-2 text-sm font-medium text-gray-900">
                            Password Baru
                        </label>
                        <input type="password" name="password" id="password"
                            placeholder="Kosongkan jika tidak ingin mengubah"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-md focus:ring-[#0083E9] focus:border-[#0083E9] block w-full p-2.5">
                        <p class="mt-1 text-xs text-gray-500">Minimal 8 karakter (kosongkan jika tidak diubah)</p>
                    </div>

                    {{-- Konfirmasi Password --}}
                    <div class="w-full">
                        <label for="password_confirmation" class="block mb-2 text-sm font-medium text-gray-900">
                            Konfirmasi Password
                        </label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                            placeholder="Kosongkan jika tidak ingin mengubah"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-md focus:ring-[#0083E9] focus:border-[#0083E9] block w-full p-2.5">
                        <p class="mt-1 text-xs text-gray-500">Harus sama dengan password baru</p>
                    </div>

                </div>

                <div class="flex justify-end mt-6">
                    <button type="submit"
                        class="inline-flex cursor-pointer items-center px-5 py-2.5 text-sm font-medium text-center text-white bg-[#0083E9] rounded-md focus:ring-4 focus:ring-blue-300 hover:bg-[#0d65d9] active:scale-[0.98] transition-all duration-300 ease-out">
                        Perbarui Pengguna
                    </button>
                </div>
            </form>
        </div>
    </section>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ===== EKSTRAKURIKULER DROPDOWN =====
    const extracurricularInput = document.getElementById('user_extracurricular');
    const extracurricularDropdown = document.getElementById('extracurricular-dropdown');
    const extracurricularIdInput = document.getElementById('extracurricular_id');
    const extracurricularOptions = document.querySelectorAll('.extracurricular-option');

    if (extracurricularInput && extracurricularDropdown) {
        extracurricularInput.addEventListener('focus', function() {
            extracurricularDropdown.classList.remove('hidden');
        });

        extracurricularInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            extracurricularOptions.forEach(option => {
                const li = option.closest('li');
                const optionText = option.getAttribute('data-value').toLowerCase();
                if (optionText.includes(searchTerm)) {
                    li.style.display = 'block';
                } else {
                    li.style.display = 'none';
                }
            });
        });

        extracurricularOptions.forEach(option => {
            option.addEventListener('click', function(e) {
                e.preventDefault();
                extracurricularInput.value = this.getAttribute('data-value');
                extracurricularIdInput.value = this.getAttribute('data-id');
                extracurricularDropdown.classList.add('hidden');
            });
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!extracurricularInput.contains(e.target) && !extracurricularDropdown.contains(e.target)) {
                extracurricularDropdown.classList.add('hidden');
            }
        });
    }
});
</script>
@endpush

@endsection
