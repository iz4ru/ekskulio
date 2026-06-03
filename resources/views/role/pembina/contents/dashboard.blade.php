@extends('role.pembina.layouts.app')
@section('title', 'Ekskulio | Dashboard Pembina')
@section('content')

<div class="p-4 border-2 border-gray-200 border-dashed rounded-md w-full mt-14">

    <div class="flex gap-4 mb-4 justify-between">
        <div>
            <h1 class="text-xl lg:text-2xl font-semibold text-gray-600">Beranda</h1>
            <p class="text-sm lg:text-base text-gray-400">Ringkasan aktivitas ekstrakurikuler Anda.</p>
        </div>
    </div>

    <div class="my-4 border-t-2 border-dashed border-gray-300 w-full"></div>

    {{-- Stat Cards --}}
    <div class="grid lg:gap-4 gap-3 grid-cols-2 xl:grid-cols-4">

        {{-- Tahun Ajaran --}}
        <div class="relative group border-2 border-gray-200 hover:border-[#0083E9] hover:shadow-sm transition-all duration-300 backdrop-blur-lg rounded-xl p-6 overflow-hidden">
            <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-all duration-500">
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-72 h-72 bg-[#0083E9]/20 blur-[100px]"></div>
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

        {{-- Ekstrakurikuler Diampu --}}
        <div class="relative group border-2 border-gray-200 hover:border-[#A855F7] hover:shadow-sm transition-all duration-300 backdrop-blur-lg rounded-xl p-6 overflow-hidden">
            <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-all duration-500">
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-72 h-72 bg-[#A855F7]/20 blur-[100px]"></div>
            </div>
            <div class="flex justify-between items-start relative z-10">
                <div>
                    <div class="w-12 h-12 mb-4 bg-[#F3E8FF]/20 rounded-lg flex items-center justify-center">
                        <i class="fa-solid fa-people-roof fa-xl text-[#A855F7]"></i>
                    </div>
                    <h3 class="text-2xl lg:text-3xl font-bold text-gray-700">{{ $myExtracurriculars->count() }}</h3>
                    <p class="text-sm lg:text-base text-gray-500">Ekstrakurikuler Diampu</p>
                </div>
            </div>
        </div>

        {{-- Total Anggota Aktif --}}
        <div class="relative group border-2 border-gray-200 hover:border-[#00B67A] hover:shadow-sm transition-all duration-300 backdrop-blur-lg rounded-xl p-6 overflow-hidden">
            <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-all duration-500">
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-72 h-72 bg-[#00B67A]/20 blur-[100px]"></div>
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

        {{-- Pertemuan Bulan Ini --}}
        <div class="relative group border-2 border-gray-200 hover:border-[#FACC15] hover:shadow-sm transition-all duration-300 backdrop-blur-lg rounded-xl p-6 overflow-hidden">
            <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-all duration-500">
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-72 h-72 bg-[#FACC15]/20 blur-[100px]"></div>
            </div>
            <div class="flex justify-between items-start relative z-10">
                <div>
                    <div class="w-12 h-12 mb-4 bg-[#FEF9C3]/20 rounded-lg flex items-center justify-center">
                        <i class="fa-solid fa-clipboard-check fa-xl text-[#FACC15]"></i>
                    </div>
                    <h3 class="text-2xl lg:text-3xl font-bold text-gray-700">{{ $totalPresences }}</h3>
                    <p class="text-sm lg:text-base text-gray-500">Pertemuan Bulan Ini</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts --}}
    <div class="grid lg:gap-4 gap-3 grid-cols-1 lg:grid-cols-2 mt-4">

        {{-- Bar Chart: Anggota per Ekstrakurikuler --}}
        <div class="relative group border-2 border-gray-200 hover:border-[#1779FC] hover:shadow-sm transition-all duration-300 backdrop-blur-lg rounded-xl p-6 overflow-hidden">
            <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-all duration-500">
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-72 h-72 bg-[#1779FC]/20 blur-[100px]"></div>
            </div>
            <div class="flex flex-col relative z-10">
                <h3 class="lg:text-lg font-semibold text-gray-700 mb-4">Anggota per Ekstrakurikuler</h3>
                <div id="member-per-extracurricular-chart"></div>
            </div>
        </div>

        {{-- List: Ekstrakurikuler yang Diampu --}}
        <div class="relative group border-2 border-gray-200 hover:border-[#6366F1] hover:shadow-sm transition-all duration-300 backdrop-blur-lg rounded-xl p-6 overflow-hidden">
            <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-all duration-500">
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-72 h-72 bg-[#6366F1]/20 blur-[100px]"></div>
            </div>
            <div class="flex flex-col relative z-10">
                <h3 class="lg:text-lg font-semibold text-gray-700 mb-4">Ekstrakurikuler Saya</h3>
                <div class="space-y-3">
                    @forelse($myExtracurriculars as $extracurricular)
                        <div class="flex items-center justify-between p-3 bg-gray-50/75 rounded-md border-gray-200 backdrop-blur-sm border-2 hover:border-gray-200 transition-all duration-300">
                            <div>
                                <p class="text-sm font-semibold text-gray-700">{{ $extracurricular->name }}</p>
                                <p class="text-xs text-gray-400">{{ $extracurricular->category?->name ?? '-' }}</p>
                            </div>
                            <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-700 rounded-md">
                                {{ $memberPerExtracurricular->firstWhere('name', $extracurricular->name)['count'] ?? 0 }} anggota
                            </span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400 italic">Belum ada ekstrakurikuler yang diampu.</p>
                    @endforelse
                </div>
            </div>
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

    const memberPerExtracurricularNames   = @json($memberPerExtracurricular->pluck('name'));
    const memberPerExtracurricularCounts  = @json($memberPerExtracurricular->pluck('count'));

    new ApexCharts(document.querySelector("#member-per-extracurricular-chart"), {
        chart: { type: 'bar', height: 320, toolbar: { show: false } },
        series: [{ name: 'Anggota Aktif', data: memberPerExtracurricularCounts }],
        xaxis: { categories: memberPerExtracurricularNames },
        colors: ['#1779FC'],
        plotOptions: { bar: { borderRadius: 6, columnWidth: '55%' } },
        dataLabels: { enabled: false },
        grid: { borderColor: '#eee' },
        yaxis: { title: { text: 'Anggota' } }
    }).render();
</script>
@endpush

@endsection