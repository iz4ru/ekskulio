@extends('layouts.app')

@section('title', 'Ekskulio | Lupa Password')

@section('content')
<div class="flex-1 flex flex-col mt-24">
    <div class="flex-1 flex items-center justify-center transform px-4 py-8">
        <div class="w-full max-w-sm">
            <div class="flex flex-col -translate-y-4 items-center justify-center gap-8">
                <div class="flex items-center justify-center -translate-y-5">
                    <img src="{{ asset('images/ekskulio.png') }}" class="max-w-50 lg:max-w-3xs m-4" alt="Ekskulio Logo" />
                </div>
                <div class="bg-white/30 backdrop-blur-lg rounded-xl shadow-lg p-8 w-full">
                    <div class="flex flex-col items-center justify-center gap-2 mb-6">
                        <p class="text-gray-700 text-center font-bold text-3xl">Lupa Password?</p>
                        <p class="text-gray-500 text-center">Masukkan email Anda untuk menerima link reset password</p>
                    </div>

                    <!-- Reset Password Form -->
                    <div class="">
                        <form action="{{ route('reset-password.send-link.post') }}" method="POST">
                            @csrf
                            <div class="space-y-4">
                                <div class="relative w-full">
                                    <i class="fa fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-gray-300"></i>
                                    <input placeholder="Masukkan Email" type="email" name="email" id="email"
                                        value="{{ old('email') }}"
                                        class="text-sm w-full h-14 pl-12 placeholder:text-gray-300 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#1779FF] transition duration-300 ease-in-out rounded-md shadow-sm"
                                        required autofocus autocomplete="email">
                                </div>

                                @if (session('success'))
                                    <div class="alert alert-success alert-dismissible relative text-sm py-2 px-4 bg-green-100 text-green-500 border border-green-500 rounded-md opacity-0 transition-opacity duration-150 ease-in-out"
                                        role="alert" id="successAlert">
                                        <i class="fa fa-circle-check absolute left-4 top-1/2 -translate-y-1/2"></i>
                                        <p class="ml-6">{{ session('success') }}</p>
                                    </div>
                                @endif

                                @if ($errors->any())
                                    <div class="alert alert-danger alert-dismissible relative text-sm py-2 px-4 bg-red-100 text-red-500 border border-red-500 rounded-md opacity-0 transition-opacity duration-150 ease-in-out"
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

                            <!-- Submit Button -->
                            <div class="space-y-4 mt-4">
                                <button type="submit"
                                    class="cursor-pointer flex items-center justify-center w-full px-6 py-4 text-white bg-[#0083E9] hover:bg-[#DEECFF] hover:text-[#0083E9] rounded-md transition-colors duration-300">
                                    <div class="text-center flex items-center gap-3">
                                        <span class="font-semibold">Kirim Link Reset</span>
                                        <i class="fa-solid fa-paper-plane"></i>
                                    </div>
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="mt-6 text-center">
                        <a href="{{ route('login') }}" class="text-sm text-gray-500 hover:text-[#0083E9] transition-colors duration-300">
                            <i class="fa-solid fa-chevron-left mr-1"></i> Kembali ke Login
                        </a>
                    </div>
                </div>
                <footer>
                    <p class="text-gray-500 text-sm"><span class="font-bold">© {{ date('Y') }} Ekskulio &bull;</span> Hak Cipta Dilindungi.</p>
                </footer>
            </div>
        </div>
    </div>
</div>
@endsection