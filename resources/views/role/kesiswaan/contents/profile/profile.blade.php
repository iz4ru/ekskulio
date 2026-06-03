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

        <div class="flex gap-4 mb-4 justify-between">
            <div>
                <h1 class="text-xl lg:text-2xl font-semibold text-gray-600">Edit Profil</h1>
                <p class="text-sm lg:text-base text-gray-400">Perbarui data profil diri pribadi Anda.</p>
            </div>
        </div>

        <div class="my-4 border-t-2 border-dashed border-gray-300 w-full"></div>

        <section>
            <div class="py-8 px-4 mx-auto max-w-2xl lg:py-16">
                <h2 class="mb-4 text-xl font-bold text-gray-900">Perbarui Profil</h2>

                <form action="{{ route('profile.update', $user->uuid) }}" method="POST" id="user-form">
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
                            Perbarui Profil
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </div>

@endsection
