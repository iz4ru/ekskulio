@extends('layouts.app')

@section('title', 'Ekskulio | Link Terkirim')

@section('content')
<div class="flex-1 flex flex-col mt-24">
    <div class="flex-1 flex items-center justify-center transform px-4 py-8">
        <div class="w-full max-w-sm">
            <div class="flex flex-col -translate-y-4 items-center justify-center gap-8">
                <div class="flex items-center justify-center -translate-y-5">
                    <img src="{{ asset('images/ekskulio.png') }}" class="max-w-50 lg:max-w-3xs m-4" alt="Ekskulio Logo" />
                </div>
                <div class="bg-white/30 backdrop-blur-lg rounded-xl shadow-lg p-8 w-full">
                    <div class="flex flex-col items-center justify-center gap-4 mb-6">
                        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center">
                            <i class="fa-solid fa-circle-check text-4xl text-green-500"></i>
                        </div>
                        <div class="flex flex-col items-center justify-center gap-2">
                            <p class="text-gray-700 text-center font-bold text-3xl">Link Terkirim!</p>
                            <p class="text-gray-500 text-center">Kami telah mengirimkan link reset password ke email Anda. Cek juga folder spam jika pesan masih tidak kunjung muncul.</p>
                        </div>
                    </div>

                    @if(session('retry_after'))
                        <div class="relative text-sm py-3 px-4 bg-blue-50 text-blue-600 border border-blue-200 rounded-md mb-4 flex items-center">
                            <i class="fa fa-info-circle absolute left-4 top-1/2 -translate-y-1/2"></i>
                            <p class="ml-6">Anda dapat meminta ulang link dalam <span class="font-bold">{{ session('retry_after') }} menit</span>.</p>
                        </div>
                    @endif

                    <div class="space-y-4 mt-4">
                        <a href="{{ route('login') }}"
                            class="cursor-pointer flex items-center justify-center w-full px-6 py-4 text-white bg-[#0083E9] hover:bg-[#DEECFF] hover:text-[#0083E9] rounded-md transition-colors duration-300">
                            <div class="text-center flex items-center gap-3">
                                <span class="font-semibold">Kembali ke Login</span>
                                <i class="fa-solid fa-chevron-right"></i>
                            </div>
                        </a>
                        
                        <a href="{{ route('reset-password.send-link.index') }}"
                            class="cursor-pointer flex items-center justify-center w-full px-6 py-4 text-[#0083E9] bg-white border border-[#0083E9] hover:bg-[#DEECFF] rounded-md transition-colors duration-300">
                            <div class="text-center flex items-center gap-3">
                                <span class="font-semibold">Kirim Ulang Link</span>
                                <i class="fa-solid fa-rotate-right"></i>
                            </div>
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