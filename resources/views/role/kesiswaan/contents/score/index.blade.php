@extends('role.kesiswaan.layouts.app')
@section('title', 'Ekskulio | Penilaian')
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
                <h1 class="text-xl lg:text-2xl font-semibold text-gray-600">Penilaian Siswa</h1>
                <p class="text-sm lg:text-base text-gray-400">Kelola nilai akhir semester siswa.</p>
                @if ($activeAY)
                    <span class="inline-block mt-2 px-3 py-1 text-xs font-medium bg-blue-100 text-blue-700 rounded-full">
                        <i class="fa-solid fa-calendar mr-1"></i>
                        {{ $activeAY->display_name }}
                    </span>
                @endif
            </div>
        </div>

        <a href="{{ route('scores.input') }}"
            class="inline-flex cursor-pointer items-center px-5 py-2.5 gap-2 text-sm font-medium text-center text-white bg-[#0083E9] rounded-md focus:ring-4 focus:ring-blue-300 hover:bg-[#DEECFF] hover:text-[#0083E9] active:scale-[0.98] transition-all duration-300 ease-out">
            <i class="fa-solid fa-plus text-sm"></i>
            <span>Input Nilai</span>
        </a>

        <div class="my-4 border-t-2 border-dashed border-gray-300 w-full"></div>

        <div class="relative border-2 border-dashed border-gray-200 rounded-md p-4">

            <form method="GET" action="{{ route('scores.export') }}" class="mb-4 flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label for="academic_year_id" class="block text-xs font-medium text-gray-600 mb-1">Tahun Ajaran</label>
                    <select name="academic_year_id" id="academic_year_id"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md">
                        <option value="">Semua</option>
                        @foreach ($academicYears as $ay)
                            <option value="{{ $ay->id }}" {{ $selectedAY?->id == $ay->id ? 'selected' : '' }}>
                                {{ $ay->display_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label for="extracurricular_id"
                        class="block text-xs font-medium text-gray-600 mb-1">Ekstrakurikuler</label>
                    <select name="extracurricular_id" id="extracurricular_id"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md">
                        <option value="">Semua</option>
                        @foreach ($extracurriculars as $ekskul)
                            <option value="{{ $ekskul->id }}">{{ $ekskul->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1 min-w-[150px]">
                    <label for="class_id" class="block text-xs font-medium text-gray-600 mb-1">Kelas</label>
                    <select name="class_id" id="class_id"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md">
                        <option value="">Semua Kelas</option>
                        @foreach ($studentClasses ?? [] as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1 min-w-[150px]">
                    <label for="search" class="block text-xs font-medium text-gray-600 mb-1">Cari</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                        placeholder="Cari Nama Siswa / NIS / Nama Ekstrakurikuler..."
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md">
                </div>
                <div class="flex gap-2">
                    <button type="submit" formaction="{{ route('scores.index') }}"
                        class="px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded-md hover:bg-gray-700">Filter</button>
                    <a href="{{ route('scores.index') }}"
                        class="px-4 py-2 text-sm font-medium text-gray-600 border border-gray-300 rounded-md hover:bg-gray-100">Reset</a>
                    <div x-data="{ open: false }" class="relative inline-block">

                        <button type="button" @click="open = !open"
                            class="cursor-pointer px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700 flex items-center gap-1">
                            <i class="fa-solid fa-download"></i> Export
                        </button>

                        <div x-show="open" @click.outside="open = false" x-transition
                            class="absolute right-0 mt-2 w-40 bg-white border border-gray-200 rounded-md shadow-lg z-10">

                            <button type="submit" name="type" value="excel"
                                class="cursor-pointer w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fa-solid fa-file-excel mr-2"></i> Excel
                            </button>

                            <button type="submit" name="type" value="pdf"
                                class="cursor-pointer w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fa-solid fa-file-pdf mr-2"></i> PDF
                            </button>

                        </div>
                    </div>
                </div>
            </form>

            <div class="overflow-x-auto rounded-lg border border-gray-200">
                <table id="pagination-table" class="min-w-full text-sm text-left text-gray-600">
                    <thead class="border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-xs uppercase">No</th>
                            <th class="px-4 py-3 text-xs uppercase">NIS</th>
                            <th class="px-4 py-3 text-xs uppercase">Nama Siswa</th>
                            <th class="px-4 py-3 text-xs uppercase">Kelas</th>
                            <th class="px-4 py-3 text-xs uppercase">Ekstrakurikuler</th>
                            <th class="px-4 py-3 text-xs uppercase">Tahun Ajaran</th>
                            <th class="px-4 py-3 text-xs uppercase">Nilai</th>
                            <th class="px-4 py-3 text-xs uppercase">Predikat</th>
                            <th class="px-4 py-3 text-xs uppercase">
                                <span class="flex items-center w-32">Catatan</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($scores as $score)
                            <tr
                                class="hover:bg-gray-100 transition-colors transition-duration-300 border-b border-gray-200">
                                <td class="px-4 py-3 border-gray-200">{{ $loop->iteration }}</td>
                                <td class="px-4 py-3 font-medium whitespace-nowrap border-gray-200">
                                    {{ $score->membership->student->id_number ?? '-' }}</td>
                                <td class="px-4 py-3 font-medium whitespace-nowrap border-gray-200">
                                    {{ $score->membership->student->name ?? '-' }}</td>
                                <td class="px-4 py-3 font-medium whitespace-nowrap border-gray-200">
                                    {{ $score->membership->student->studentClass?->name ?? '-' }}</td>
                                <td class="px-4 py-3 font-medium whitespace-nowrap border-gray-200">
                                    {{ $score->membership->extracurricular->name ?? '-' }}</td>
                                <td class="px-4 py-3 font-medium whitespace-nowrap border-gray-200">
                                    {{ $score->academicYear->display_name ?? '-' }}
                                </td>
                                <td class="px-4 py-3 font-bold whitespace-nowrap border-gray-200">
                                    {{ $score->score ?? '-' }}
                                </td>
                                <td class="px-4 py-3 font-medium whitespace-nowrap border-gray-200">
                                    @if ($score->predicate)
                                        <span
                                            class="px-2 py-1 text-xs font-medium rounded-md 
                                        @if ($score->predicate === 'A') bg-green-100 text-green-700
                                        @elseif($score->predicate === 'B') bg-blue-100 text-blue-700
                                        @elseif($score->predicate === 'C') bg-yellow-100 text-yellow-700
                                        @else bg-red-100 text-red-700 @endif">
                                            {{ $score->predicate }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-medium whitespace-nowrap border-gray-200 text-gray-500 max-w-xs truncate"
                                    title="{{ $score->notes ?? '' }}">{{ $score->notes ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-8 text-gray-500">
                                    <div class="flex flex-col items-center justify-center gap-2 py-4">
                                        <i class="fa-solid fa-clipboard text-4xl mb-2"></i>
                                        <p>Belum ada data penilaian.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($scores->hasPages())
                <div class="mt-4">
                    {{ $scores->withQueryString()->links() }}
                </div>
            @endif

        </div>
    </div>

@endsection