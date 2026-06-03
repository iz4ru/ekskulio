@extends('role.kesiswaan.layouts.app')
@section('title', 'Ekskulio | Preview Laporan')
@section('content')

    <div class="p-4 border-2 border-gray-200 border-dashed rounded-md w-full mt-14">

        <a href="{{ route('reports.index') }}"
            class="text-sm inline-flex items-center gap-1 font-semibold text-gray-500 hover:text-[#0083E9] active:scale-[0.98] transition-all duration-300 ease-out">
            <i class="fa-solid fa-chevron-left"></i>
            <span>Kembali</span>
        </a>

        <div class="my-4">
            <h1 class="text-xl lg:text-2xl font-semibold text-gray-600">Preview Laporan Kehadiran</h1>
            <p class="text-sm lg:text-base text-gray-400">
                {{ $extracurricular->name }} - {{ $monthName }} - {{ $academicYear->display_name }}
            </p>
        </div>

        <div class="my-4 border-t-2 border-dashed border-gray-300 w-full"></div>

        @if ($students->isEmpty())
            <div class="text-center py-8 text-gray-500">
                <i class="fa-solid fa-file-empty text-4xl mb-2"></i>
                <p>Tidak ada data kehadiran untuk bulan ini.</p>
            </div>
        @else
            <div class="relative border-2 border-dashed border-gray-200 rounded-md p-4">
                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table id="pagination-table" class="min-w-full text-xs text-left text-gray-600">
                        <thead class="border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-xs uppercase text-center">No</th>
                                <th class="px-4 py-3 text-xs uppercase">NIS</th>
                                <th class="px-4 py-3 text-xs uppercase">Nama</th>
                                <th class="px-4 py-3 text-xs uppercase">Kelas</th>
                                @foreach ($presences as $p)
                                    @php
                                        $dayName = \Carbon\Carbon::parse($p->date)->locale('id')->isoFormat('dddd');
                                    @endphp
                                    <th class="px-1 py-3 text-center relative cursor-default select-none bg-gray-200"
                                        x-data="{ show: false, x: 0, y: 0 }"
                                        @mouseenter="show = true; const r = $el.getBoundingClientRect(); x = r.left + r.width / 2; y = r.top"
                                        @mouseleave="show = false"
                                        @touchstart.passive="const r = $el.getBoundingClientRect(); x = r.left + r.width / 2; y = r.top; setTimeout(() => show = true, 300)"
                                        @touchend.passive="setTimeout(() => show = false, 1000)"
                                        @touchmove.passive="show = false">

                                        {{ \Carbon\Carbon::parse($p->date)->format('d') }}

                                        <template x-teleport="body">
                                            <div x-show="show" x-transition:enter="transition ease-out duration-150"
                                                x-transition:enter-start="opacity-0 -translate-y-1"
                                                x-transition:enter-end="opacity-100 translate-y-0"
                                                :style="`position: fixed; left: ${x}px; top: ${y}px; transform: translate(-50%, calc(-100% - 8px)); z-index: 9999;`"
                                                class="pointer-events-none bg-gray-800 text-white text-xs rounded px-2 py-1 whitespace-nowrap">
                                                <span x-text="`{{ $dayName }}`"></span>
                                                <div
                                                    class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-gray-800">
                                                </div>
                                            </div>
                                        </template>
                                    </th>
                                @endforeach
                                <th class="px-2 py-3 text-xs uppercase text-center bg-green-50">H</th>
                                <th class="px-2 py-3 text-xs uppercase text-center bg-yellow-50">S</th>
                                <th class="px-2 py-3 text-xs uppercase text-center bg-blue-50">I</th>
                                <th class="px-2 py-3 text-xs uppercase text-center bg-red-50">A</th>
                                <th class="px-2 py-3 text-xs uppercase text-center bg-gray-50">%</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $no = 1; @endphp
                            @foreach ($students as $student)
                                <tr
                                    class="hover:bg-gray-100 transition-colors transition-duration-300 border-b border-gray-200">
                                    <td class="px-4 py-3 border-gray-200 text-center">{{ $no++ }}</td>
                                    <td class="px-4 py-3 font-medium whitespace-nowrap border-gray-200">{{ $student->id_number ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 font-medium whitespace-nowrap border-gray-200">{{ $student->name }}</td>
                                    <td class="px-4 py-3 font-medium whitespace-nowrap border-gray-200">{{ $student->studentClass?->name ?? '-' }}</td>
                                    @foreach ($presences as $presence)
                                        @php $detail = $presence->details->firstWhere('student_id', $student->id); @endphp
                                        <td
                                            class="px-1 py-3 font-medium whitespace-nowrap border-gray-200 text-center
                                        @if ($detail?->status?->value === 'present') bg-green-100 text-green-700
                                        @elseif($detail?->status?->value === 'sick') bg-yellow-100 text-yellow-700
                                        @elseif($detail?->status?->value === 'permission') bg-blue-100 text-blue-700
                                        @elseif($detail?->status?->value === 'absent') bg-red-100 text-red-700 @endif">
                                            @if ($detail)
                                                @switch($detail->status?->value)
                                                    @case('present')
                                                        H
                                                    @break

                                                    @case('sick')
                                                        S
                                                    @break

                                                    @case('permission')
                                                        I
                                                    @break

                                                    @case('absent')
                                                        A
                                                    @break
                                                @endswitch
                                            @else
                                                -
                                            @endif
                                        </td>
                                    @endforeach
                                    @php $s = $stats[$student->id] ?? ['present'=>0,'sick'=>0,'permission'=>0,'absent'=>0,'percentage'=>0]; @endphp
                                    <td class="px-2 py-3 whitespace-nowrap border-gray-200 text-center bg-green-50 font-medium">{{ $s['present'] }}</td>
                                    <td class="px-2 py-3 whitespace-nowrap border-gray-200 text-center bg-yellow-50">{{ $s['sick'] }}</td>
                                    <td class="px-2 py-3 whitespace-nowrap border-gray-200 text-center bg-blue-50">{{ $s['permission'] }}</td>
                                    <td class="px-2 py-3 whitespace-nowrap border-gray-200 text-center bg-red-50">{{ $s['absent'] }}</td>
                                    <td class="px-2 py-3 whitespace-nowrap border-gray-200 text-center bg-gray-50 font-bold">
                                        {{ $s['percentage'] ?? 0 }}%
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4 flex justify-end gap-2">
                <form action="{{ route('reports.export.pdf') }}" method="POST" target="_blank">
                    @csrf
                    <input type="hidden" name="extracurricular_id" value="{{ $extracurricular->id }}">
                    <input type="hidden" name="academic_year_id" value="{{ $academicYear->id }}">
                    <input type="hidden" name="month" value="{{ $month }}">
                    <input type="hidden" name="class_id" value="{{ request('class_id') }}">
                    <button type="submit"
                        class="inline-flex cursor-zpointer items-center px-5 py-2.5 gap-2 text-sm font-medium text-center text-white bg-[#0083E9] rounded-md focus:ring-4 focus:ring-blue-300 hover:bg-[#DEECFF] hover:text-[#0083E9] active:scale-[0.98] transition-all duration-300 ease-out">
                        <i class="fa-solid fa-file-pdf text-sm"></i>
                        <span>Export PDF</span>
                    </button>
                </form>
                <form action="{{ route('reports.export.excel') }}" method="POST">
                    @csrf
                    <input type="hidden" name="extracurricular_id" value="{{ $extracurricular->id }}">
                    <input type="hidden" name="academic_year_id" value="{{ $academicYear->id }}">
                    <input type="hidden" name="month" value="{{ $month }}">
                    <input type="hidden" name="class_id" value="{{ request('class_id') }}">
                    <button type="submit"
                        class="inline-flex cursor-zpointer items-center px-5 py-2.5 gap-2 text-sm font-medium text-center text-white bg-[#0083E9] rounded-md focus:ring-4 focus:ring-blue-300 hover:bg-[#DEECFF] hover:text-[#0083E9] active:scale-[0.98] transition-all duration-300 ease-out">
                        <i class="fa-solid fa-file-excel text-sm"></i>
                        <span>Export Excel</span>
                    </button>
                </form>
            </div>
        @endif

    </div>

@endsection
