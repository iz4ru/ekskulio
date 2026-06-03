@extends('role.admin.layouts.app')
@section('title', 'Ekskulio | Presensi Ekstrakurikuler - ' . ucwords(strtolower($extracurricular->name)))
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

        <a href="{{ route('presence.index') }}"
            class="text-sm inline-flex items-center gap-1 font-semibold text-gray-500 hover:text-[#0083E9] active:scale-[0.98] transition-all duration-300 ease-out">
            <i class="fa-solid fa-chevron-left"></i>
            <span>Kembali</span>
        </a>

        <div class="flex gap-4 my-4 justify-between">
            <div>
                <h1 class="text-xl lg:text-2xl font-semibold text-gray-600">Presensi Ekstrakurikuler
                    {{ ucwords(strtolower($extracurricular->name)) }}</h1>
                <p class="text-sm lg:text-base text-gray-400"> Lihat data presensi siswa di ekstrakurikuler
                    {{ ucwords(strtolower($extracurricular->name)) }}.</p>

            </div>
        </div>

        <a href="{{ route('presence.create', ['extracurricular_id' => $extracurricular->id]) }}"
            class="inline-flex cursor-pointer items-center px-5 py-2.5 gap-2 text-sm font-medium text-center text-white bg-[#0083E9] rounded-md focus:ring-4 focus:ring-blue-300 hover:bg-[#DEECFF] hover:text-[#0083E9] active:scale-[0.98]
        transition-all duration-300 ease-out">
            <i class="fa-solid fa-plus text-sm"></i>
            <span>Tambahkan Data Presensi</span>
        </a>

        <div class="my-4 border-t-2 border-dashed border-gray-300 w-full"></div>

        <div class="relative border-2 border-dashed border-gray-200 rounded-md p-4">

            <form method="GET" class="my-4 flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[150px]">
                    <label for="academic_year_id" class="block text-xs font-medium text-gray-600 mb-1">Tahun Ajaran</label>
                    <select name="academic_year_id" id="academic_year_id"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md">
                        <option value="">Semua Tahun Ajaran</option>
                        @foreach ($academicYears as $ay)
                            <option value="{{ $ay->id }}" {{ $selectedAY?->id == $ay->id ? 'selected' : '' }}>
                                {{ $ay->display_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1 min-w-[150px]">
                    <label for="search" class="block text-xs font-medium text-gray-600 mb-1">Cari</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                        placeholder="Cari Tanggal (cont. 2026-01-01)..."
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md">
                </div>
                <button type="submit"
                    class="px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded-md hover:bg-gray-700 transition-all">
                    Filter
                </button>
                @if (request('academic_year_id'))
                    <a href="{{ route('presence.show', $extracurricular->id) }}"
                        class="px-4 py-2 text-sm font-medium text-gray-600 border border-gray-300 rounded-md hover:bg-gray-100 transition-all">
                        Reset
                    </a>
                @endif
            </form>

            <div class="overflow-x-auto rounded-lg border border-gray-200">
                <table id="pagination-table" class="min-w-full text-sm text-left text-gray-600">
                    <thead class="border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-xs uppercase">No</th>
                            <th class="px-4 py-3 text-xs uppercase">Tahun Ajaran</th>
                            <th class="px-4 py-3 text-xs uppercase">Tanggal</th>
                            <th class="px-4 py-3 text-xs uppercase">Hari</th>
                            <th class="px-4 py-3 text-xs uppercase">Hadir</th>
                            <th class="px-4 py-3 text-xs uppercase">Izin</th>
                            <th class="px-4 py-3 text-xs uppercase">Sakit</th>
                            <th class="px-4 py-3 text-xs uppercase">Alfa</th>
                            <th class="px-4 py-3 text-xs uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($presences as $presence)
                            <tr class="hover:bg-gray-100 transition-colors transition-duration-300 border-b border-gray-200">
                                <td class="px-4 py-3 border-gray-200">{{ $loop->iteration }}</td>
                                <td class="px-4 py-3 font-medium whitespace-nowrap border-gray-200">
                                    <span
                                        class="inline-block mt-2 px-3 py-1 text-xs font-medium bg-blue-100 text-blue-700 rounded-full">
                                        <i class="fa-solid fa-calendar mr-1"></i>
                                        {{ $presence->academicYear->year }} -
                                        {{ ucfirst($presence->academicYear->semester) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-medium whitespace-nowrap border-gray-200">
                                    {{ $presence->date->isoFormat('D MMMM Y') }}
                                </td>
                                <td class="px-4 py-3 font-medium whitespace-nowrap border-gray-200">
                                    {{ $presence->day }}
                                </td>
                                <td class="px-4 py-3 font-medium whitespace-nowrap border-gray-200">
                                    <div
                                        class="flex items-center gap-2 text-green-600 bg-green-50 px-2 py-1 rounded-md w-max">
                                        {{ $presence->present_count }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 font-medium whitespace-nowrap border-gray-200">
                                    <div
                                        class="flex items-center gap-2 text-yellow-600 bg-yellow-50 px-2 py-1 rounded-md w-max">
                                        {{ $presence->sick_count }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 font-medium whitespace-nowrap border-gray-200">
                                    <div
                                        class="flex items-center gap-2 text-blue-600 bg-blue-50 px-2 py-1 rounded-md w-max">
                                        {{ $presence->permission_count }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 font-medium whitespace-nowrap border-gray-200">
                                    <div class="flex items-center gap-2 text-red-600 bg-red-50 px-2 py-1 rounded-md w-max">
                                        {{ $presence->absent_count }}
                                    </div>
                                </td>
                                <td>
                                    <div class="flex gap-3 items-center justify-start">
                                        <a href="{{ route('presence.edit', $presence->id) }}"
                                            class="text-[#0083E9] hover:underline font-medium focus:outline-none flex flex-col lg:flex-row items-center gap-1">
                                            <i class="fa-solid fa-edit text-sm"></i>
                                            <span class="text-sm">Edit</span>
                                        </a>
                                        <p class="font-bold text-gray-300">|</p>
                                        <form action="{{ route('presence.destroy', $presence->id) }}" method="POST"
                                            id="delete-form-{{ $presence->id }}" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="password"
                                                id="delete-password-{{ $presence->id }}" value="">
                                            <button type="button"
                                                class="delete-btn text-[#EF4444] hover:underline font-medium focus:outline-none cursor-pointer flex flex-col lg:flex-row items-center gap-1"
                                                data-id="{{ $presence->id }}"
                                                data-name="{{ $presence->date->format('d M Y') }}">
                                                <i class="fa-solid fa-trash text-sm"></i>
                                                <span class="text-sm">Hapus</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($presences->hasPages())
                <div class="mt-4">
                    {{ $presences->withQueryString()->links() }}
                </div>
            @endif

        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {

                document.addEventListener('click', async function(e) {
                    const deleteBtn = e.target.closest('.delete-btn');

                    if (deleteBtn) {
                        e.preventDefault();

                        const studentId = deleteBtn.getAttribute('data-id');
                        const studentName = deleteBtn.getAttribute('data-name');

                        const {
                            value: password
                        } = await Swal.fire({
                            title: 'Hapus Data Presensi',
                            html: `<p>Anda akan menghapus data presensi <strong>${studentName}</strong>.</p>
                                   <p class="mt-3 text-sm text-gray-600">Aksi ini tidak dapat dibatalkan. Masukkan password untuk melanjutkan:</p>`,
                            input: 'password',
                            inputPlaceholder: 'Masukkan password Anda',
                            inputAttributes: {
                                autocapitalize: 'off',
                                autocorrect: 'off'
                            },
                            showCancelButton: true,
                            cancelButtonText: 'Batal',
                            confirmButtonText: 'Hapus',
                            confirmButtonColor: '#EF4444',
                            cancelButtonColor: '#6B7280',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            didOpen: (modal) => {
                                modal.querySelector('input').focus();
                            }
                        });

                        if (password) {
                            document.getElementById('delete-password-' + studentId).value = password;
                            document.getElementById('delete-form-' + studentId).submit();
                        }
                    }
                });

            });
        </script>
    @endpush

@endsection
