@extends('role.kesiswaan.layouts.app')
@section('title', 'Ekskulio | Kesiswaan Dashboard')
@section('content')

    <div x-data="{
        active: localStorage.getItem('activeTab') || 'image'
    }" x-init="$watch('active', value => localStorage.setItem('activeTab', value))"
        class="p-4 border-2 border-gray-200 border-dashed rounded-md w-full mt-14">

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
                <h1 class="text-xl lg:text-2xl font-semibold text-gray-600">Beranda</h1>
                <p class="text-sm lg:text-base text-gray-400">Lihat informasi terbaru dan ringkasan aktivitas.</p>
            </div>
        </div>

        <div class="my-4 border-t-2 border-dashed border-gray-300 w-full"></div>

        <div class="grid lg:gap-4 gap-3 grid-cols-2 xl:grid-cols-4">

            <!-- Tahun Ajaran -->
            <div
                class="relative group border-2 border-gray-200 hover:border-[#0083E9] hover:shadow-sm transition-all duration-300 backdrop-blur-lg rounded-xl p-6 overflow-hidden">
                <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-all duration-500">
                    <div
                        class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-72 h-72 bg-[#0083E9]/20 blur-[100px]">
                    </div>
                </div>
                <div class="flex justify-between items-start relative z-10">
                    <div>
                        <div class="w-12 h-12 mb-4 bg-[#DEECFF]/20 rounded-lg flex items-center justify-center">
                            <i class="fa-solid fa-calendar fa-xl text-[#0083E9]"></i>
                        </div>
                        <p class="text-sm lg:text-base text-gray-500">Tahun Ajaran</p>
                        <h3 class="text-base lg:text-2xl font-bold text-gray-700">2025/2026</h3>
                    </div>
                </div>
            </div>

            <!-- Siswa Aktif -->
            <div
                class="relative group border-2 border-gray-200 hover:border-[#00B67A] hover:shadow-sm transition-all duration-300 backdrop-blur-lg rounded-xl p-6 overflow-hidden">
                <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-all duration-500">
                    <div
                        class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-72 h-72 bg-[#00B67A]/20 blur-[100px]">
                    </div>
                </div>
                <div class="flex justify-between items-start relative z-10">
                    <div>
                        <div class="w-12 h-12 mb-4 bg-[#CFFFEA]/20 rounded-lg flex items-center justify-center">
                            <i class="fa-solid fa-people-group fa-xl text-[#00B67A]"></i>
                        </div>
                        <h3 class="text-2xl lg:text-3xl font-bold text-gray-700">1536</h3>
                        <p class="text-sm lg:text-base text-gray-500">Siswa Aktif</p>
                    </div>
                </div>
            </div>

            <!-- Ekstrakurikuler -->
            <div
                class="relative group border-2 border-gray-200 hover:border-[#A855F7] hover:shadow-sm transition-all duration-300 backdrop-blur-lg rounded-xl p-6 overflow-hidden">
                <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-all duration-500">
                    <div
                        class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-72 h-72 bg-[#A855F7]/20 blur-[100px]">
                    </div>
                </div>
                <div class="flex justify-between items-start relative z-10">
                    <div>
                        <div class="w-12 h-12 mb-4 bg-[#F3E8FF]/20 rounded-lg flex items-center justify-center">
                            <i class="fa-solid fa-people-roof fa-xl text-[#A855F7]"></i>
                        </div>
                        <h3 class="text-2xl lg:text-3xl font-bold text-gray-700">42</h3>
                        <p class="text-sm lg:text-base text-gray-500">Ekstrakurikuler</p>
                    </div>
                </div>
            </div>

            <!-- Pembina -->
            <div
                class="relative group border-2 border-gray-200 hover:border-[#FACC15] hover:shadow-sm transition-all duration-300 backdrop-blur-lg rounded-xl p-6 overflow-hidden">
                <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-all duration-500">
                    <div
                        class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-72 h-72 bg-[#FACC15]/20 blur-[100px]">
                    </div>
                </div>
                <div class="flex justify-between items-start relative z-10">
                    <div>
                        <div class="w-12 h-12 mb-4 bg-[#FEF9C3]/20 rounded-lg flex items-center justify-center">
                            <i class="fa-solid fa-chalkboard-teacher fa-xl text-[#FACC15]"></i>
                        </div>
                        <h3 class="text-2xl lg:text-3xl font-bold text-gray-700">53</h3>
                        <p class="text-sm lg:text-base text-gray-500">Pembina</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="grid lg:gap-4 gap-3 grid-cols-1 lg:grid-cols-2 mt-4">

            <!-- Chart Pemerataan Ekskul -->
            <div
                class="relative group border-2 border-gray-200 hover:border-[#6366F1] hover:shadow-sm transition-all duration-300 backdrop-blur-lg rounded-xl p-6 overflow-hidden">
                <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-all duration-500">
                    <div
                        class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-72 h-72 bg-[#6366F1]/20 blur-[100px]">
                    </div>
                </div>
                <div class="flex flex-col relative z-10">
                    <h3 class="lg:text-lg font-semibold text-gray-700 mb-4">Pemerataan Ekstrakurikuler</h3>
                    <div id="chart-ekskul"></div>
                </div>
            </div>

            <!-- Chart Top 10 Ekskul Terfavorit -->
            <div
                class="relative group border-2 border-gray-200 hover:border-[#1779FC] hover:shadow-sm transition-all duration-300 backdrop-blur-lg rounded-xl p-6 overflow-hidden">
                <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-all duration-500">
                    <div
                        class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-72 h-72 bg-[#1779FC]/20 blur-[100px]">
                    </div>
                </div>
                <div class="flex flex-col relative z-10">
                    <h3 class="lg:text-lg font-semibold text-gray-700 mb-4">Top 10 Ekskul Terfavorit</h3>
                    <div id="chart-top-ekskul"></div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <script>
            window.Apex = {
                chart: {
                    fontFamily: [
                        'DM Sans',
                        'ui-sans-serif',
                        'system-ui',
                        'sans-serif',
                        '"Apple Color Emoji"',
                        '"Segoe UI Emoji"',
                        '"Segoe UI Symbol"',
                        '"Noto Color Emoji"'
                    ].join(', '),
                }
            };
        </script>

        <script>
            // =========================
            // DUMMY DATA (nanti ganti dari backend)
            // =========================

            // 40 ekskul (contoh, bisa ganti dari DB)
            const allEkskul = [
                'Pramuka', 'Paskibra', 'Rohis', 'IT Club', 'Basket', 'Futsal', 'Voli', 'Badminton',
                'PMR', 'KIR', 'English Club', 'Japanese Club', 'Paduan Suara', 'Band', 'Tari Tradisional',
                'Teater', 'Sinematografi', 'Jurnalistik', 'Desain Grafis', 'Robotik', 'Broadcasting',
                'Pencak Silat', 'Taekwondo', 'Karate', 'Renang', 'Catur', 'Panahan', 'Karya Ilmiah',
                'Matematika', 'Fisika', 'Biologi', 'Kimia', 'Geografi', 'Ekonomi', 'Entrepreneurship',
                'Tahfidz', 'Hadrah', 'Fotografi', 'Kuliner'
            ];

            // Dummy jumlah anggota per ekskul (random saja)
            const allEkskulMembers = allEkskul.map(() => Math.floor(Math.random() * 40) + 10);

            // =========================
            // 1. Donut Pemerataan Ekskul (dikelompokkan)
            // =========================

            // Kelompok kategori ekskul
            const ekskulGroups = ['Olahraga', 'Seni', 'Keagamaan', 'Akademik', 'Lainnya'];
            const ekskulGroupCounts = [0, 0, 0, 0, 0];

            // Mapping sederhana: index ekskul -> group
            allEkskul.forEach((name, index) => {
                const memberCount = allEkskulMembers[index];

                if (['Basket', 'Futsal', 'Voli', 'Badminton', 'Pencak Silat', 'Taekwondo', 'Karate', 'Renang',
                        'Panahan'
                    ].includes(name)) {
                    ekskulGroupCounts[0] += memberCount; // Olahraga
                } else if (['Paduan Suara', 'Band', 'Tari Tradisional', 'Teater', 'Sinematografi', 'Fotografi']
                    .includes(name)) {
                    ekskulGroupCounts[1] += memberCount; // Seni
                } else if (['Rohis', 'Tahfidz', 'Hadrah'].includes(name)) {
                    ekskulGroupCounts[2] += memberCount; // Keagamaan
                } else if (['KIR', 'English Club', 'Japanese Club', 'Jurnalistik', 'Desain Grafis',
                        'Robotik', 'Broadcasting', 'Karya Ilmiah', 'Matematika', 'Fisika',
                        'Biologi', 'Kimia', 'Geografi', 'Ekonomi', 'Entrepreneurship', 'IT Club'
                    ].includes(name)) {
                    ekskulGroupCounts[3] += memberCount; // Akademik
                } else {
                    ekskulGroupCounts[4] += memberCount; // Lainnya (Pramuka, Paskibra, PMR, dll)
                }
            });

            var optionsEkskulDonut = {
                chart: {
                    type: 'donut',
                    height: 320,
                    toolbar: {
                        show: false
                    }
                },
                series: ekskulGroupCounts,
                labels: ekskulGroups,
                colors: ['#6366F1', '#F97316', '#22C55E', '#0EA5E9', '#A855F7'],
                dataLabels: {
                    enabled: true
                },
                legend: {
                    position: 'bottom'
                },
                stroke: {
                    width: 1
                },
                responsive: [{
                    breakpoint: 1024,
                    options: {
                        chart: {
                            height: 280
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }]
            };

            new ApexCharts(document.querySelector("#chart-ekskul"), optionsEkskulDonut).render();

            // =========================
            // 2. Grafik tambahan: Top 10 ekskul terfavorit (bar chart)
            // =========================

            // Sort by anggota desc dan ambil 10 teratas
            const sorted = allEkskul
                .map((name, i) => ({
                    name,
                    members: allEkskulMembers[i]
                }))
                .sort((a, b) => b.members - a.members)
                .slice(0, 10);

            const topEkskulNames = sorted.map(e => e.name);
            const topEkskulMembers = sorted.map(e => e.members);

            var optionsEkskulTop = {
                chart: {
                    type: 'bar',
                    height: 320,
                    toolbar: {
                        show: false
                    }
                },
                series: [{
                    name: 'Jumlah Siswa',
                    data: topEkskulMembers
                }],
                xaxis: {
                    categories: topEkskulNames
                },
                colors: ['#1779FC'],
                plotOptions: {
                    bar: {
                        borderRadius: 6,
                        columnWidth: '55%'
                    }
                },
                dataLabels: {
                    enabled: false
                },
                grid: {
                    borderColor: '#eee'
                },
                yaxis: {
                    title: {
                        text: 'Siswa'
                    }
                }
            };

            new ApexCharts(document.querySelector("#chart-top-ekskul"), optionsEkskulTop).render();
        </script>
    @endpush

@endsection
