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

                        <div x-data="{
                            role: '{{ old('role', $user->role) }}',
                            selectedEkskul: @js(
    collect(old('extracurricular_ids', $user->extracurriculars->pluck('extracurricular_id')))
        ->map(fn($id) => $extracurriculars->firstWhere('id', $id) ? ['id' => (int) $id, 'name' => $extracurriculars->firstWhere('id', $id)->name] : null)
        ->filter(),
),
                            searchEkskul: '',
                            showDropdown: false,
                        
                            addEkskul(id, name) {
                                id = parseInt(id);
                                if (!this.selectedEkskul.some(s => s.id === id)) {
                                    this.selectedEkskul.push({ id: id, name: name });
                                }
                                this.searchEkskul = '';
                            },
                        
                            removeEkskul(id) {
                                id = parseInt(id);
                                this.selectedEkskul = this.selectedEkskul.filter(s => s.id !== id);
                            },
                        
                            isNotSelected(id) {
                                return !this.selectedEkskul.some(s => s.id === parseInt(id));
                            },
                        
                            matchesSearch(name) {
                                if (!this.searchEkskul || this.searchEkskul.trim() === '') return true;
                                return name.toLowerCase().includes(this.searchEkskul.toLowerCase());
                            }
                        }" class="sm:col-span-2 flex flex-col gap-4 sm:gap-6"
                            @click.outside="showDropdown = false">

                            {{-- Role - RADIO BUTTON --}}
                            <div class="sm:col-span-2">
                                <label class="block mb-2 text-sm font-medium text-gray-900">
                                    Role <span class="text-red-500">*</span>
                                </label>

                                <div class="grid grid-cols-2 gap-4 w-full">
                                    <label
                                        class="flex items-center p-3 bg-gray-50 border-2 border-gray-200 rounded-md hover:border-[#0083E9] hover:bg-blue-50 transition-all duration-200 cursor-pointer group">
                                        <input type="radio" name="role" value="admin" x-model="role"
                                            class="w-4 h-4 text-[#0083E9] bg-gray-100 border-gray-300 focus:ring-[#0083E9] focus:ring-2 mr-3 group-hover:scale-110 transition-transform duration-200"
                                            {{ old('role', $user->role) == 'admin' ? 'checked' : '' }} required>
                                        <div>
                                            <div class="font-medium text-gray-900 group-hover:text-[#0083E9]">Admin</div>
                                            <div class="text-xs text-gray-500">Kelola seluruh sistem</div>
                                        </div>
                                    </label>

                                    <label
                                        class="flex items-center p-3 bg-gray-50 border-2 border-gray-200 rounded-md hover:border-[#0083E9] hover:bg-blue-50 transition-all duration-200 cursor-pointer group">
                                        <input type="radio" name="role" value="pembina" x-model="role"
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

                            {{-- Ekstrakurikuler Multi-Select --}}
                            <div class="sm:col-span-2 relative" x-show="role === 'pembina'" x-transition>
                                <label class="block mb-2 text-sm font-medium text-gray-900">
                                    Ekstrakurikuler
                                </label>

                                {{-- Container Input & Chips --}}
                                <div
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-md focus-within:ring-[#0083E9] focus-within:border-[#0083E9] block w-full p-2 min-h-[42px] flex flex-wrap gap-2 items-center">

                                    <template x-for="item in selectedEkskul" :key="item.id">
                                        <span
                                            class="inline-flex items-center gap-1 bg-[#0083E9]/10 text-[#0083E9] text-xs font-medium px-2.5 py-1 rounded-full">
                                            <span x-text="item.name"></span>
                                            <button type="button" @click="removeEkskul(item.id)"
                                                class="hover:text-red-500">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"></path>
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <input type="text" x-model="searchEkskul" @focus="showDropdown = true"
                                        @keydown.escape="showDropdown = false" placeholder="Ketik untuk mencari ekskul..."
                                        class="flex-1 min-w-[120px] bg-transparent border-none focus:ring-0 text-sm p-0 outline-none"
                                        autocomplete="off">
                                </div>

                                {{-- Hidden Inputs --}}
                                <template x-for="item in selectedEkskul" :key="item.id">
                                    <input type="hidden" name="extracurricular_ids[]" :value="item.id">
                                </template>

                                {{-- Dropdown --}}
                                <div x-show="showDropdown" x-transition x-cloak
                                    class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto"
                                    style="max-height: 200px;" @click.stop>
                                    <ul class="py-1">
                                        @foreach ($extracurriculars as $extracurricular)
                                            <li x-show="isNotSelected({{ $extracurricular->id }}) && matchesSearch('{{ addslashes(strtolower($extracurricular->name)) }}')"
                                                class="cursor-pointer">
                                                <button type="button"
                                                    @click="addEkskul({{ $extracurricular->id }}, '{{ addslashes($extracurricular->name) }}')"
                                                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-[#0083E9]">
                                                    {{ $extracurricular->name }}
                                                </button>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>

                                @error('extracurricular_ids')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                @error('extracurricular_ids.*')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror

                                <p class="mt-1 text-xs text-gray-500">Pilih satu atau lebih ekstrakurikuler (opsional)</p>
                            </div>
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

@endsection
