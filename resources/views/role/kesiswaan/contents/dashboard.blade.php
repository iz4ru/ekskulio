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
                        <h3 class="text-base lg:text-2xl font-bold text-gray-700">
                            {{ $activeAY?->year ?? '-' }}
                        </h3>
                        <p class="text-xs text-gray-500">{{ ucwords($activeAY?->semester ?? '-') }}</p>
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
                        <h3 class="text-2xl lg:text-3xl font-bold text-gray-700">{{ $totalStudents }}</h3>
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
                        <h3 class="text-2xl lg:text-3xl font-bold text-gray-700">{{ $totalExtracurriculars }}</h3>
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
                        <h3 class="text-2xl lg:text-3xl font-bold text-gray-700">{{ $totalAdvisor }}</h3>
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
                    <div id="extracurricular-chart" class="md:h-[350px] h-[400px]"></div>
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
                    <h3 class="lg:text-lg font-semibold text-gray-700 mb-4">Top 10 Ekstrakurikuler Terfavorit</h3>
                    <div id="top-extracurricular-chart" class="md:h-[350px] h-[400px]"></div>
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
            // 1. Donut Pemerataan Ekskul (dikelompokkan)
            // =========================

            // Kelompok kategori ekskul
            const extracurricularGroups = @json($categoryDist->pluck('name'));
            const extracurricularGroupCounts = @json($categoryDist->pluck('count'));

            var optionsExtracurricularDonut = {
                chart: {
                    type: 'donut',
                    height: 400,
                    toolbar: { show: false }
                },
                series: extracurricularGroupCounts,
                labels: extracurricularGroups,
                colors: ['#6366F1', '#F97316', '#22C55E', '#0EA5E9', '#A855F7', '#EC4899'],
                dataLabels: { enabled: true },
                legend: {
                    position: 'bottom',
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '50%',
                        }
                    }
                },
                stroke: { width: 1 },
                responsive: []
            };

            new ApexCharts(document.querySelector("#extracurricular-chart"), optionsExtracurricularDonut).render();

            // =========================
            // 2. Grafik tambahan: Top 10 ekskul terfavorit (bar chart)
            // =========================

            // Sort by anggota desc dan ambil 10 teratas

            const topExtracurricularsNames = @json($topExtracurriculars->pluck('name'));
            const topExtracurricularsMembers = @json($topExtracurriculars->pluck('count'));

            var optionsExtracurricularTop = {
                chart: {
                    type: 'bar',
                    height: 400,
                    toolbar: {
                        show: false
                    }
                },
                series: [{
                    name: 'Jumlah Siswa',
                    data: topExtracurricularsMembers
                }],
                xaxis: {
                    categories: topExtracurricularsNames
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

            new ApexCharts(document.querySelector("#top-extracurricular-chart"), optionsExtracurricularTop).render();
        </script>
    @endpush

@endsection
