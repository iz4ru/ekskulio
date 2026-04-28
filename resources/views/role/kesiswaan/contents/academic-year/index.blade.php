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
            <a href="{{ route('academic-years.transition.form') }}"
                class="inline-flex cursor-pointer items-center px-5 py-2.5 gap-2 text-sm font-medium text-center text-white bg-[#0083E9] rounded-md focus:ring-4 focus:ring-blue-300 hover:bg-[#DEECFF] hover:text-[#0083E9] active:scale-[0.98]
            transition-all duration-300 ease-out">
                <i class="fa-solid fa-forward text-sm"></i>
                <span>Transisi Tahun Ajaran</span>
            </a>
        </div>

        <div class="my-4 border-t-2 border-dashed border-gray-300 w-full"></div>

        <div class="relative overflow-x-auto border-2 border-dashed border-gray-200 rounded-md p-4">

            <table id="pagination-table" class="min-w-full text-sm text-left text-gray-600">
                <thead>
                    <tr>
                        <th>
                            <span class="flex items-center">
                                No
                                <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                    width="24" height="24" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4" />
                                </svg>
                            </span>
                        </th>
                        <th>
                            <span class="flex items-center">
                                Tahun Ajaran
                                <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                    width="24" height="24" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4" />
                                </svg>
                            </span>
                        </th>
                        <th>
                            <span class="flex items-center">
                                Semester
                                <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                    width="24" height="24" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4" />
                                </svg>
                            </span>
                        </th>
                        <th>
                            <span class="flex items-center" data-sortable="false">
                                Status
                            </span>
                        </th>
                        <th data-sortable="false">
                            <span class="flex items-center">
                                Aksi
                            </span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($academicYears as $academicYear)
                        <tr class="hover:bg-gray-100 transition-colors transition-duration-300">
                            <td>{{ $loop->iteration }}</td>
                            <td class="font-medium text-gray-800 whitespace-nowrap">
                                {{ $academicYear->year }}
                            </td>
                            <td class="font-medium text-gray-800 whitespace-nowrap">
                                {{ ucfirst($academicYear->semester) }}
                            </td>
                            <td>
                                <form action="{{ route('academic-years.toggle', $academicYear) }}" method="POST"
                                    id="toggle-form-{{ $academicYear->id }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="password" id="password-{{ $academicYear->id }}"
                                        value="">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="sr-only peer toggle-checkbox"
                                            data-id="{{ $academicYear->id }}"
                                            data-name="{{ $academicYear->year }} - {{ ucfirst($academicYear->semester) }}"
                                            {{ $academicYear->is_active ? 'checked' : '' }}>
                                        <div
                                            class="relative w-14 h-8 bg-gray-300 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-6 after:content-[''] after:absolute after:top-1 after:start-1 after:bg-white after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-[#0083E9]">
                                        </div>
                                    </label>
                                </form>
                            </td>
                            <td>
                                <div class="flex gap-3 items-center justify-start">
                                    <a href="{{ route('academic-years.edit', $academicYear->id) }}"
                                        class="text-[#0083E9] hover:underline font-medium focus:outline-none flex flex-col lg:flex-row items-center gap-1">
                                        <i class="fa-solid fa-edit text-sm"></i>
                                        <span class="text-sm">Edit</span>
                                    </a>
                                    <p class="font-bold text-gray-300">|</p>
                                    <form action="{{ route('academic-years.destroy', $academicYear->id) }}" method="POST"
                                        id="delete-form-{{ $academicYear->id }}" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="password" id="delete-password-{{ $academicYear->id }}"
                                            value="">
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

    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {

                let dataTable = null;

                if (document.getElementById("pagination-table") && typeof simpleDatatables.DataTable !== 'undefined') {
                    dataTable = new simpleDatatables.DataTable("#pagination-table", {
                        paging: true,
                        perPage: 5,
                        perPageSelect: [5, 10, 15, 20, 25],
                        sortable: true
                    });
                }

                // ✅ GUNAKAN EVENT DELEGATION untuk toggle checkbox
                document.addEventListener('change', async function(e) {
                    if (e.target.classList.contains('toggle-checkbox')) {
                        e.preventDefault();

                        const academicYearId = e.target.getAttribute('data-id');
                        const academicYearName = e.target.getAttribute('data-name');
                        const status = e.target.checked ? 'mengaktifkan' : 'menonaktifkan';

                        // Show sweetalert dengan password input
                        const {
                            value: password
                        } = await Swal.fire({
                            title: 'Konfirmasi Perubahan Status',
                            html: `<p>Anda akan <strong>${status}</strong> tahun ajaran <strong>${academicYearName}</strong>.</p>
                               <p class="mt-3 text-sm text-gray-600">Masukkan password untuk melanjutkan:</p>`,
                            input: 'password',
                            inputPlaceholder: 'Masukkan password Anda',
                            inputAttributes: {
                                autocapitalize: 'off',
                                autocorrect: 'off'
                            },
                            showCancelButton: true,
                            cancelButtonText: 'Batal',
                            confirmButtonText: 'Konfirmasi',
                            confirmButtonColor: '#0083E9',
                            cancelButtonColor: '#EF4444',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            didOpen: (modal) => {
                                modal.querySelector('input').focus();
                            }
                        });

                        if (password) {
                            // Set password ke hidden input
                            document.getElementById('password-' + academicYearId).value = password;

                            // Submit form
                            document.getElementById('toggle-form-' + academicYearId).submit();
                        } else {
                            // Reset checkbox jika user cancel
                            e.target.checked = !e.target.checked;
                        }
                    }
                });

                // ✅ GUNAKAN EVENT DELEGATION untuk delete button
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
