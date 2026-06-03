@extends('role.admin.layouts.app')
@section('title', 'Ekskulio | Presensi Ekstrakurikuler')
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
                <h1 class="text-xl lg:text-2xl font-semibold text-gray-600">Presensi Ekstrakurikuler</h1>
                <p class="text-sm lg:text-base text-gray-400"> Lihat data presensi ekstrakurikuler siswa.</p>

            </div>
        </div>

        <div class="my-4 border-t-2 border-dashed border-gray-300 w-full"></div>

        <div class="relative border-2 border-dashed border-gray-200 rounded-md p-4">

            {{-- Filter Section --}}
            <form method="GET" class="mb-4 flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[150px]">
                    <label for="search" class="block text-xs font-medium text-gray-600 mb-1">Cari</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                        placeholder="Cari Nama Ekstrakurikuler / Kode Ekstrakurikuler..."
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md">
                </div>
                <div class="flex gap-2">
                    <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded-md hover:bg-gray-700 transition-all">
                        Filter
                    </button>
                    <a href="{{ route('presence.index') }}"
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
                            <th class="px-4 py-3 text-xs uppercase">Nama Ekstrakurikuler</th>
                            <th class="px-4 py-3 text-xs uppercase">Kode Ekstrakurikuler</th>
                            <th class="px-4 py-3 text-xs uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($extracurriculars as $extracurricular)
                            <tr class="hover:bg-gray-100 transition-colors transition-duration-300 border-b border-gray-200">
                                <td class="px-4 py-3 border-gray-200">{{ $loop->iteration }}</td>
                                <td class="px-4 py-3 font-medium whitespace-nowrap border-gray-200">
                                    {{ $extracurricular->name }}
                                </td>
                                <td class="px-4 py-3 font-medium whitespace-nowrap border-gray-200">
                                    {{ $extracurricular->code }}
                                </td>
                                <td lcass="px-4 py-3 font-medium whitespace-nowrap border-gray-200">
                                    <div class="flex gap-3 items-center justify-start">
                                        <a href="{{ route('presence.show', $extracurricular->id) }}"
                                            class="text-[#0083E9] hover:underline font-medium focus:outline-none flex flex-col lg:flex-row items-center justify-center gap-1">
                                            <i class="fa-solid fa-info-circle text-sm"></i>
                                            <span class="text-sm justify-center">Lihat Presensi</span>
                                        </a>
                                    </div>
                                </td>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($extracurriculars->hasPages())
                <div class="mt-4">
                    {{ $extracurriculars->withQueryString()->links() }}
                </div>
            @endif

        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {

                document.addEventListener('change', async function(e) {
                    if (e.target.classList.contains('toggle-checkbox')) {
                        e.preventDefault();

                        const extracurricularId = e.target.getAttribute('data-id');
                        const extracurricularName = e.target.getAttribute('data-name');
                        const status = e.target.checked ? 'mengaktifkan' : 'menonaktifkan';

                        // Show sweetalert dengan password input
                        const {
                            value: password
                        } = await Swal.fire({
                            title: 'Konfirmasi Perubahan Status',
                            html: `<p>Anda akan <strong>${status}</strong> ekstrakurikuler <strong>${extracurricularName}</strong>.</p>
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
                            document.getElementById('password-' + extracurricularId).value = password;

                            // Submit form
                            document.getElementById('toggle-form-' + extracurricularId).submit();
                        } else {
                            // Reset checkbox jika user cancel
                            e.target.checked = !e.target.checked;
                        }
                    }
                });

                // Handle delete button dengan sweetalert
                document.addEventListener('click', async function(e) {
                    // Check if clicked element or its parent is delete button
                    const deleteBtn = e.target.closest('.delete-btn');

                    if (deleteBtn) {
                        e.preventDefault();

                        const extracurricularId = deleteBtn.getAttribute('data-id');
                        const extracurricularName = deleteBtn.getAttribute('data-name');

                        // Show sweetalert dengan password input
                        const {
                            value: password
                        } = await Swal.fire({
                            title: 'Hapus Ekstrakurikuler',
                            html: `<p>Anda akan menghapus ekstrakurikuler <strong>${extracurricularName}</strong>.</p>
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
                            document.getElementById('delete-password-' + extracurricularId).value =
                                password;

                            // Submit form
                            document.getElementById('delete-form-' + extracurricularId).submit();
                        }
                    }
                });

            });
        </script>
    @endpush

@endsection
