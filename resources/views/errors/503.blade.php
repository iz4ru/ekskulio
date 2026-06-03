<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>503 - Sedang Dalam Pemeliharaan</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen p-4">
    <div class="text-center p-8 border-2 border-dashed border-gray-200 rounded-xl md:max-w-lg max-w-sm w-full">
        <div style="width: 64px; height: 64px; background-color: #dbeafe; border-radius: 9999px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem auto;">
            <i class="fa-solid fa-screwdriver-wrench text-2xl text-blue-500"></i>
        </div>
        <h1 class="text-6xl font-bold text-blue-400 mb-2">503</h1>
        <h2 class="text-xl font-semibold text-gray-600 mb-2">Sedang Dalam Pemeliharaan</h2>
        <p class="text-sm text-gray-400 mb-6">
            Sistem sedang dalam pemeliharaan untuk meningkatkan layanan.
            Kami akan kembali sebentar lagi. Terima kasih atas kesabaran Anda.
        </p>
        @if(isset($exception) && $exception->getMessage())
            <p class="text-xs text-gray-300 mb-6 italic">{{ $exception->getMessage() }}</p>
        @endif
        <a href="{{ url('/') }}"
            class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-[#0083E9] rounded-md hover:bg-[#0d65d9] active:scale-[0.98] transition-all duration-300">
            <i class="fa-solid fa-rotate-right"></i>
            Coba Lagi
        </a>
    </div>
</body>
</html>