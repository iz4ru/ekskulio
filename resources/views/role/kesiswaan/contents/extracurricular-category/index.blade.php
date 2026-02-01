@extends('role.kesiswaan.layouts.app')
@section('title', 'Ekskulio | Kategori Ekstrakurikuler')
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
                <h1 class="text-xl lg:text-2xl font-semibold text-gray-600">Kelola Kategori Ekstrakurikuler</h1>
                <p class="text-sm lg:text-base text-gray-400"> Tambah, ubah, dan hapus data kategori ekstrakurikuler
                    sekolah.</p>

            </div>
        </div>

        <div class="inline-flex flex-col lg:flex-row gap-2">
            <a href="{{ route('extracurricular-category.create') }}"
                class="inline-flex cursor-pointer items-center px-5 py-2.5 gap-2 text-sm font-medium text-center text-white bg-[#0083E9] rounded-md focus:ring-4 focus:ring-blue-300 hover:bg-[#DEECFF] hover:text-[#0083E9] active:scale-[0.98] transition-all duration-300 ease-out">
                <i class="fa-solid fa-plus text-sm"></i>
                <span>Tambah Kategori Ekstrakurikuler</span>
            </a>
            <a href="{{ route('extracurricular-category.import') }}"
                class="inline-flex cursor-pointer items-center px-5 py-2.5 gap-2 text-sm font-medium text-center text-white bg-[#0083E9] rounded-md focus:ring-4 focus:ring-blue-300 hover:bg-[#DEECFF] hover:text-[#0083E9] active:scale-[0.98] transition-all duration-300 ease-out">
                <i class="fa-solid fa-file-import text-sm"></i>
                <span>Impor Kategori Ekstrakurikuler</span>
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
                                Nama Kategori
                                <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                    width="24" height="24" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4" />
                                </svg>
                            </span>
                        </th>
                        <th>
                            <span class="flex items-center">
                                Jumlah Ekstrakurikuler
                                <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                    width="24" height="24" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4" />
                                </svg>
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
                    @foreach ($categories as $category)
                        <tr class="hover:bg-gray-100 transition-colors transition-duration-300">
                            <td>{{ $loop->iteration }}</td>
                            <td class="font-medium text-gray-800 whitespace-nowrap">
                                {{ $category->name }}
                            </td>
                            <td class="font-medium text-gray-800 whitespace-nowrap">
                                <button onclick="toggleList(this)"
                                    class="cursor-pointer text-[#0083E9] hover:underline font-medium focus:outline-none">
                                    {{ count($category->extracurriculars) }} Ekstrakurikuler
                                </button>

                                <ul
                                    class="hidden opacity-0 max-h-0 overflow-hidden transition-all duration-300 ease-in-out">
                                    @foreach ($category->extracurriculars as $extracurricular)
                                        <li class="my-2">{{ $extracurricular->name }}</li>
                                    @endforeach
                                </ul>
                            </td>
                            <td>
                                <div class="flex gap-3 items-center justify-start">
                                    <a href="{{ route('extracurricular-category.edit', $category->id) }}"
                                        class="text-[#0083E9] hover:underline font-medium focus:outline-none flex flex-col lg:flex-row items-center gap-1">
                                        <i class="fa-solid fa-edit text-sm"></i>
                                        <span class="text-sm">Edit</span>
                                    </a>
                                    <p class="font-bold text-gray-300">|</p>
                                    <form action="{{ route('extracurricular-category.destroy', $category->id) }}"
                                        method="POST" id="delete-form-{{ $category->id }}" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="password" id="delete-password-{{ $category->id }}"
                                            value="">
                                        <button type="button"
                                            class="delete-btn text-[#EF4444] hover:underline font-medium focus:outline-none cursor-pointer flex flex-col lg:flex-row items-center gap-1"
                                            data-id="{{ $category->id }}" data-name="{{ $category->name }}">
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
            function toggleList(button) {
                const list = button.nextElementSibling;
                const isHidden = list.classList.contains('hidden');

                if (isHidden) {
                    list.classList.remove('hidden');
                    setTimeout(() => {
                        list.classList.remove('opacity-0', 'max-h-0');
                        list.classList.add('opacity-100', 'max-h-96');
                    }, 10);
                } else {
                    list.classList.remove('opacity-100', 'max-h-96');
                    list.classList.add('opacity-0', 'max-h-0');
                    setTimeout(() => {
                        list.classList.add('hidden');
                    }, 300);
                }
            }

            document.addEventListener('DOMContentLoaded', function() {

                let dataTable = null;

                if (document.getElementById("pagination-table") && typeof simpleDatatables.DataTable !== 'undefined') {
                    dataTable = new simpleDatatables.DataTable("#pagination-table", {
                        paging: true,
                        perPage: 10,
                        perPageSelect: [10, 15, 20, 25],
                        sortable: true
                    });
                }

                document.addEventListener('click', async function(e) {
                    // Check if clicked element or its parent is delete button
                    const deleteBtn = e.target.closest('.delete-btn');

                    if (deleteBtn) {
                        e.preventDefault();

                        const categoryId = deleteBtn.getAttribute('data-id');
                        const categoryName = deleteBtn.getAttribute('data-name');

                        // Show sweetalert dengan password input
                        const {
                            value: password
                        } = await Swal.fire({
                            title: 'Hapus Kategori Ekstrakurikuler',
                            html: `<p>Anda akan menghapus kategori ekstrakurikuler <strong>${categoryName}</strong>.</p>
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
                            document.getElementById('delete-password-' + categoryId).value = password;

                            // Submit form
                            document.getElementById('delete-form-' + categoryId).submit();
                        }
                    }
                });

            });
        </script>
    @endpush

@endsection
