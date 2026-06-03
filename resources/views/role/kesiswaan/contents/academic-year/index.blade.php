@extends('role.kesiswaan.layouts.app')
@section('title', 'Ekskulio | Tahun Ajaran')
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
                <h1 class="text-xl lg:text-2xl font-semibold text-gray-600">Kelola Tahun Ajaran</h1>
                <p class="text-sm lg:text-base text-gray-400"> Tambah, ubah, dan hapus data tahun ajaran sekolah.</p>

            </div>
        </div>

        <div class="inline-flex flex-col lg:flex-row gap-2">
            <a href="{{ route('academic-years.create') }}"
                class="inline-flex cursor-pointer items-center px-5 py-2.5 gap-2 text-sm font-medium text-center text-white bg-[#0083E9] rounded-md focus:ring-4 focus:ring-blue-300 hover:bg-[#DEECFF] hover:text-[#0083E9] active:scale-[0.98]
            transition-all duration-300 ease-out">
                <i class="fa-solid fa-plus text-sm"></i>
                <span>Tambah Tahun Ajaran</span>
            </a>
            <a href="{{ route('academic-years.close.form') }}"
                class="inline-flex cursor-pointer items-center px-5 py-2.5 gap-2 text-sm font-medium text-center text-white bg-[#0083E9] rounded-md focus:ring-4 focus:ring-blue-300 hover:bg-[#DEECFF] hover:text-[#0083E9] active:scale-[0.98]
            transition-all duration-300 ease-out">
                <i class="fa-solid fa-lock text-sm"></i>
                <span>Tutup Periode Saat Ini</span>
            </a>
            <a href="{{ route('academic-years.export') }}"
                class="inline-flex cursor-pointer items-center px-5 py-2.5 gap-2 text-sm font-medium text-center text-white bg-green-600 rounded-md focus:ring-4 focus:ring-green-300 hover:bg-[#E5FFDE] hover:text-green-600 active:scale-[0.98]
            transition-all duration-300 ease-out">
                <i class="fa-solid fa-file-excel mr-2"></i> Download Data Siswa Aktif
            </a>
        </div>

        <div class="my-4 border-t-2 border-dashed border-gray-300 w-full"></div>

        <div class="relative border-2 border-dashed border-gray-200 rounded-md p-4">

            {{-- Filter Section --}}
            <form method="GET" class="mb-4 flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[150px]">
                    <label for="search" class="block text-xs font-medium text-gray-600 mb-1">Cari</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                        placeholder="Cari Tahun Ajaran / Semester..."
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md">
                </div>
                <div class="flex gap-2">
                    <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded-md hover:bg-gray-700 transition-all">
                        Filter
                    </button>
                    <a href="{{ route('academic-years.index') }}"
                        class="px-4 py-2 text-sm font-medium text-gray-600 border border-gray-300 rounded-md hover:bg-gray-100 transition-all">
                        Reset
                    </a>
                </div>
            </form>

            <div class="overflow-x-auto rounded-lg border border-gray-200">
                <table id="pagination-table" class="min-w-full text-sm text-left text-gray-600">
                    <thead class="border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-xs uppercase">No</th>
                            <th class="px-4 py-3 text-xs uppercase">Tahun Ajaran</th>
                            <th class="px-4 py-3 text-xs uppercase">Semester</th>
                            <th class="px-4 py-3 text-xs uppercase">Status</th>
                            <th class="px-4 py-3 text-xs uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($academicYears as $academicYear)
                            <tr
                                class="hover:bg-gray-100 transition-colors transition-duration-300 border-b border-gray-200">
                                <td class="px-4 py-3 border-gray-200">{{ $loop->iteration }}</td>
                                <td class="px-4 py-3 font-medium whitespace-nowrap border-gray-200">
                                    {{ $academicYear->year }}
                                </td>
                                <td class="px-4 py-3 font-medium whitespace-nowrap border-gray-200">
                                    {{ ucfirst($academicYear->semester) }}
                                </td>
                                <td class="px-4 py-3 font-medium whitespace-nowrap border-gray-200">
                                    @php
                                        $isActive = $academicYear->is_active;

                                        // Tentukan apakah periode ini lebih baru dari yang sedang aktif
                                        $isNewerThanActive = false;
                                        if (!$isActive && $activeAY) {
                                            if ($academicYear->year > $activeAY->year) {
                                                $isNewerThanActive = true;
                                            } elseif (
                                                $academicYear->year === $activeAY->year &&
                                                $academicYear->semester === 'genap' &&
                                                $activeAY->semester === 'ganjil'
                                            ) {
                                                $isNewerThanActive = true;
                                            }
                                        }

                                        // Jika tidak ada yang aktif sama sekali dan ini yang terbaru, anggap siap diaktifkan
                                        if (!$isActive && !$activeAY) {
                                            $isNewerThanActive = true;
                                        }
                                    @endphp

                                    @if ($isActive)
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fa-solid fa-circle-check mr-1"></i> Aktif
                                        </span>
                                    @elseif ($isNewerThanActive)
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fa-solid fa-clock mr-1"></i> Siap Diaktifkan
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <i class="fa-solid fa-archive mr-1"></i> Diarsipkan
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-medium whitespace-nowrap border-gray-200">
                                    <div class="flex gap-3 items-center justify-start">
                                        <a href="{{ route('academic-years.edit', $academicYear->id) }}"
                                            class="text-[#0083E9] hover:underline font-medium focus:outline-none flex flex-col lg:flex-row items-center gap-1">
                                            <i class="fa-solid fa-edit text-sm"></i>
                                            <span class="text-sm">Edit</span>
                                        </a>
                                        <p class="font-bold text-gray-300">|</p>
                                        <form action="{{ route('academic-years.destroy', $academicYear->id) }}"
                                            method="POST" id="delete-form-{{ $academicYear->id }}"
                                            style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="password"
                                                id="delete-password-{{ $academicYear->id }}" value="">
                                            <button type="button"
                                                class="delete-btn text-[#EF4444] hover:underline font-medium focus:outline-none cursor-pointer flex flex-col lg:flex-row items-center gap-1"
                                                data-id="{{ $academicYear->id }}"
                                                data-name="{{ $academicYear->year }} - {{ ucfirst($academicYear->semester) }}">
                                                <i class="fa-solid fa-trash text-sm"></i>
                                                <span class="text-sm">Hapus</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($academicYears->hasPages())
                <div class="mt-4">
                    {{ $academicYears->withQueryString()->links() }}
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

                        const academicYearId = deleteBtn.getAttribute('data-id');
                        const academicYearName = deleteBtn.getAttribute('data-name');

                        // Show sweetalert dengan password input
                        const {
                            value: password
                        } = await Swal.fire({
                            title: 'Hapus Tahun Ajaran',
                            html: `<p>Anda akan menghapus tahun ajaran <strong>${academicYearName}</strong>.</p>
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
                            // Set password ke hidden input
                            document.getElementById('delete-password-' + academicYearId).value = password;

                            // Submit form
                            document.getElementById('delete-form-' + academicYearId).submit();
                        }
                    }
                });

            });
        </script>
    @endpush


@endsection
