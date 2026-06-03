<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>404 - Halaman Tidak Ditemukan</title>

    <!-- Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css">

    <!-- Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,300..900;1,300..900&display=swap"
        rel="stylesheet">

    <!-- Cloak -->
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
    @stack('styles')
</head>

<body class="bg-gray-50 flex items-center justify-center min-h-screen p-4">
    <div class="text-center p-8 border-2 border-dashed border-gray-200 rounded-xl md:max-w-lg max-w-sm w-full">
        <div
            style="width: 64px; height: 64px; background-color: #dbeafe; border-radius: 9999px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem auto;">
            <i class="fa-solid fa-map-location-dot" style="font-size: 1.5rem; color: #0083E9;"></i>
        </div>
        <h1 class="text-6xl font-bold text-[#0083E9] mb-2">404</h1>
        <h2 class="text-xl font-semibold text-gray-600 mb-2">Halaman Tidak Ditemukan</h2>
        <p class="text-sm text-gray-400 mb-6">
            Halaman yang Anda cari tidak ada atau telah dipindahkan.
            Periksa kembali URL yang Anda masukkan.
        </p>
        <a href="{{ url('/') }}"
            class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-[#0083E9] rounded-md hover:bg-[#0d65d9] active:scale-[0.98] transition-all duration-300">
            <i class="fa-solid fa-chevron-left"></i>
            Kembali
        </a>
    </div>
</body>

</html>
