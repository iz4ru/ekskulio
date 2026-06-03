@extends('role.admin.layouts.app')
@section('title', 'Ekskulio | Dashboard Admin')
@section('content')

    <div class="p-4 border-2 border-gray-200 border-dashed rounded-md w-full mt-14">

        {{-- Header --}}
        <div class="flex gap-4 mb-4 justify-between">
            <div>
                <h1 class="text-xl lg:text-2xl font-semibold text-gray-600">Beranda</h1>
                <p class="text-sm lg:text-base text-gray-400">Panel backup presensi ekstrakurikuler untuk Admin.</p>
                <span class="inline-block mt-4 px-3 py-1 text-xs font-medium bg-blue-100 text-blue-700 rounded-full">
                    <i class="fa-solid fa-calendar mr-1"></i>
                    {{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }}
                </span>
            </div>
        </div>

        <div class="my-4 border-t-2 border-dashed border-gray-300 w-full"></div>

        <div class="grid lg:gap-4 gap-3 grid-cols-2 xl:grid-cols-4">
            {{-- Tahun Ajaran --}}
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
                        <h3 class="text-base lg:text-2xl font-bold text-gray-700">{{ $activeAY?->year ?? '-' }}</h3>
                        <p class="text-xs text-gray-500">{{ ucwords($activeAY?->semester ?? '-') }}</p>
                    </div>
                </div>
            </div>

            {{-- Total Ekstrakurikuler --}}
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
                        <p class="text-sm lg:text-base text-gray-500">Ekstrakurikuler Aktif</p>
                    </div>
                </div>
            </div>

            {{-- Total Anggota Aktif --}}
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
                        <h3 class="text-2xl lg:text-3xl font-bold text-gray-700">{{ $totalMembers }}</h3>
                        <p class="text-sm lg:text-base text-gray-500">Total Anggota Aktif</p>
                    </div>
                </div>
            </div>

            {{-- Presensi Hari Ini --}}
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
                            <i class="fa-solid fa-clipboard-check fa-xl text-[#FACC15]"></i>
                        </div>
                        <h3 class="text-2xl lg:text-3xl font-bold text-gray-700">{{ $todayPresences }}</h3>
                        <p class="text-sm lg:text-base text-gray-500">Presensi Dibuat Hari Ini</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid lg:gap-4 gap-3 grid-cols-1 lg:grid-cols-3 mt-4">

            {{-- Jadwal Ekskul Hari Ini (2/3 width) --}}
            <div
                class="lg:col-span-2 relative group border-2 border-gray-200 hover:border-[#1779FC] hover:shadow-sm transition-all duration-300 backdrop-blur-lg rounded-xl p-6 overflow-hidden">
                <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-all duration-500">
                    <div
                        class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-72 h-72 bg-[#1779FC]/20 blur-[100px]">
                    </div>
                </div>
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="lg:text-lg font-semibold text-gray-700">Jadwal Ekskul Hari Ini</h3>
                        <span class="text-xs text-gray-400 bg-gray-100 rounded-full px-3 py-1">
                            {{ $todaySchedules->count() }} jadwal
                        </span>
                    </div>

                    @if ($todaySchedules->isEmpty())
                        <div class="flex flex-col items-center justify-center py-12 text-gray-400">
                            <i class="fa-solid fa-calendar-xmark fa-2xl mb-3 opacity-40"></i>
                            <p class="text-sm">Tidak ada jadwal ekstrakurikuler hari ini.</p>
                        </div>
                    @else
                        <div class="space-y-3 max-h-[420px] overflow-y-auto pr-1">
                            @foreach ($todaySchedules as $schedule)
                                <div
                                    class="flex items-center justify-between gap-3 p-3 bg-gray-50/75 rounded-md border-2 {{ $schedule['has_presence'] ? 'border-green-200 bg-green-50/50' : 'border-gray-200' }} backdrop-blur-sm transition-all duration-300">
                                    <div class="flex items-start gap-3 min-w-0">
                                        {{-- Status dot --}}
                                        <div class="mt-1 flex-shrink-0">
                                            @if ($schedule['has_presence'])
                                                <span class="block w-2.5 h-2.5 rounded-full bg-green-400"></span>
                                            @else
                                                <span class="block w-2.5 h-2.5 rounded-full bg-gray-300"></span>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold text-gray-700 truncate">{{ $schedule['name'] }}
                                            </p>
                                            <p class="text-xs text-gray-400">{{ $schedule['category'] }}</p>
                                            <div class="flex flex-wrap gap-x-3 gap-y-0.5 mt-1">
                                                <span class="text-xs text-gray-500">
                                                    <i class="fa-solid fa-chalkboard-teacher fa-xs mr-1"></i>
                                                    {{ $schedule['advisors'] }}
                                                </span>
                                                <span class="text-xs text-gray-500">
                                                    <i class="fa-solid fa-users fa-xs mr-1"></i>
                                                    {{ $schedule['member_count'] }} anggota aktif
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        @if ($schedule['has_presence'])
                                            <a href="{{ route('presence.show', $schedule['presence_id']) }}"
                                                class="px-3 py-1.5 text-xs font-medium bg-green-100 text-green-700 rounded-md hover:bg-green-200 transition-colors whitespace-nowrap">
                                                <i class="fa-solid fa-eye fa-xs mr-1"></i> Lihat
                                            </a>
                                        @else
                                            <a href="{{ route('presence.create', ['extracurricular_id' => $schedule['extracurricular_id']]) }}"
                                                class="px-3 py-1.5 text-xs font-medium bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors whitespace-nowrap">
                                                <i class="fa-solid fa-plus fa-xs mr-1"></i> Presensi
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Mini Chart: 7 Hari Terakhir (1/3) --}}
            <div
                class="relative group border-2 border-gray-200 hover:border-[#6366F1] hover:shadow-sm transition-all duration-300 backdrop-blur-lg rounded-xl p-6 overflow-hidden">
                <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-all duration-500">
                    <div
                        class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-72 h-72 bg-[#6366F1]/20 blur-[100px]">
                    </div>
                </div>
                <div class="relative z-10 flex flex-col h-full">
                    <h3 class="lg:text-lg font-semibold text-gray-700 mb-1">Aktivitas Presensi</h3>
                    <p class="text-xs text-gray-400 mb-4">7 hari terakhir</p>

                    {{-- Summary bulan ini --}}
                    <div
                        class="flex items-center justify-between p-3 bg-indigo-50 rounded-md border-2 border-indigo-100 mb-4">
                        <div>
                            <p class="text-xs text-indigo-400">Bulan ini</p>
                            <p class="text-2xl font-bold text-indigo-600">{{ $monthPresences }}</p>
                        </div>
                        <i class="fa-solid fa-chart-bar fa-xl text-indigo-300"></i>
                    </div>

                    <div id="last7days-chart" class="flex-1"></div>
                </div>
            </div>
        </div>

        {{-- Info Banner --}}
        <div class="mt-4 flex items-start gap-3 p-4 bg-amber-50 border-2 border-amber-200 rounded-xl">
            <div class="flex-shrink-0 w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center mt-0.5">
                <i class="fa-solid fa-triangle-exclamation text-amber-500 fa-sm"></i>
            </div>
            <div>
                <p class="text-sm font-semibold text-amber-700">Akses Terbatas — Mode Backup</p>
                <p class="text-xs text-amber-600 mt-0.5 leading-relaxed">
                    Akun ini hanya dapat melakukan presensi sebagai pengganti pembina yang berhalangan hadir.
                    Pengelolaan data master (siswa, ekskul, anggota) dilakukan oleh <strong>Kesiswaan</strong>.
                    Pastikan koordinasi dengan pembina sebelum membuat presensi.
                </p>
            </div>
        </div>

    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <script>
            window.Apex = {
                chart: {
                    fontFamily: ['DM Sans', 'ui-sans-serif', 'system-ui', 'sans-serif'].join(', '),
                }
            };

            const last7DaysLabels = @json($last7Days->pluck('date'));
            const last7DaysCounts = @json($last7Days->pluck('count'));

            new ApexCharts(document.querySelector("#last7days-chart"), {
                chart: {
                    type: 'area',
                    height: 180,
                    toolbar: {
                        show: false
                    },
                },
                series: [{
                    name: 'Presensi',
                    data: last7DaysCounts
                }],
                xaxis: {
                    categories: last7DaysLabels,
                    labels: {
                        style: {
                            fontSize: '10px',
                            colors: '#9ca3af'
                        }
                    },
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    },
                },
                yaxis: {
                    labels: {
                        style: {
                            fontSize: '10px',
                            colors: '#9ca3af'
                        }
                    },
                    min: 0,
                },
                colors: ['#6366F1'],
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.4,
                        opacityTo: 0.0,
                        stops: [0, 100],
                    },
                },
                stroke: {
                    curve: 'smooth',
                    width: 2
                },
                dataLabels: {
                    enabled: false
                },
                grid: {
                    borderColor: '#f3f4f6'
                },
                tooltip: {
                    x: {
                        show: true
                    }
                },
            }).render();
        </script>
    @endpush

@endsection
