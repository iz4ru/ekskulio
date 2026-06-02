@extends('role.kesiswaan.layouts.app')
@section('title', 'Ekskulio | Laporan Kehadiran')
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
                <h1 class="text-xl lg:text-2xl font-semibold text-gray-600">Laporan Kehadiran</h1>
                <p class="text-sm lg:text-base text-gray-400">Cetak laporan kehadiran siswa per bulan.</p>
                @if ($activeAY)
                    <span class="inline-block mt-2 px-3 py-1 text-xs font-medium bg-blue-100 text-blue-700 rounded-full">
                        <i class="fa-solid fa-calendar mr-1"></i>
                        {{ $activeAY->display_name }}
                    </span>
                @endif
            </div>
        </div>

        <div class="my-4 border-t-2 border-dashed border-gray-300 w-full"></div>

        <div class="py-8 px-4 mx-auto max-w-2xl lg:py-16">
            <form action="{{ route('reports.preview') }}" method="GET">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tahun Ajaran <span class="text-red-500">*</span></label>
                        <select name="academic_year_id" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md" required>
                            @foreach($academicYears as $ay)
                                <option value="{{ $ay->id }}" {{ $activeAY?->id == $ay->id ? 'selected' : '' }}>{{ $ay->display_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Ekstrakurikuler <span class="text-red-500">*</span></label>
                        <select name="extracurricular_id" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md" required>
                            <option value="">-- Pilih Terlebih Dahulu --</option>
                            @foreach($extracurriculars as $ekskul)
                                <option value="{{ $ekskul->id }}">{{ $ekskul->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Bulan <span class="text-red-500">*</span></label>
                        <select name="month" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md" required>
                            <option value="1">Januari</option>
                            <option value="2">Februari</option>
                            <option value="3">Maret</option>
                            <option value="4">April</option>
                            <option value="5">Mei</option>
                            <option value="6">Juni</option>
                            <option value="7">Juli</option>
                            <option value="8">Agustus</option>
                            <option value="9">September</option>
                            <option value="10">Oktober</option>
                            <option value="11">November</option>
                            <option value="12">Desember</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Kelas (Opsional)</label>
                        <select name="class_id" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md">
                            <option value="">Semua Kelas</option>
                            @foreach($studentClasses as $class)
                                <option value="{{ $class->id }}"
                                    {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="submit" class="inline-flex cursor-pointer items-center px-5 py-2.5 gap-2 text-sm font-medium text-center text-white bg-[#0083E9] rounded-md focus:ring-4 focus:ring-blue-300 hover:bg-[#DEECFF] hover:text-[#0083E9] active:scale-[0.98] transition-all duration-300 ease-out">
                        <i class="fa-solid fa-eye text-sm"></i>
                        <span>Preview</span>
                    </button>
                </div>
            </form>
        </div>

    </div>

@endsection