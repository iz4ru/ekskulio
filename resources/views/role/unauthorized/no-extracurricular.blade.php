@extends('role.unauthorized.layouts.app')
@section('title', 'Ekskulio | Akses Terbatas')
@section('content')
<div class="flex items-center justify-center min-h-screen">
    <div class="text-center p-8 border-2 border-dashed border-gray-200 rounded-xl md:max-w-lg max-w-sm">
        <i class="fa-solid fa-lock text-4xl text-gray-400 mb-4"></i>
        <h1 class="text-xl font-semibold text-gray-600 mb-2">Akses Terbatas</h1>
        <p class="text-sm text-gray-400">
            Anda belum diampu ke ekstrakurikuler manapun. 
            Hubungi kesiswaan untuk mendapatkan akses.
        </p>
    </div>
</div>
@endsection