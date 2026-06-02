@extends('role.kesiswaan.layouts.app')
@section('title', 'Ekskulio | Akun Pengguna & Pembina')
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
                <h1 class="text-xl lg:text-2xl font-semibold text-gray-600">Manajemen Akun Pengguna & Pembina</h1>
                <p class="text-sm lg:text-base text-gray-400">Tambah, ubah, dan hapus data akun pengguna.</p>
            </div>
        </div>

        <a href="{{ route('user-management.create') }}"
            class="inline-flex cursor-zpointer items-center px-5 py-2.5 gap-2 text-sm font-medium text-center text-white bg-[#0083E9] rounded-md focus:ring-4 focus:ring-blue-300 hover:bg-[#DEECFF] hover:text-[#0083E9] active:scale-[0.98] transition-all duration-300 ease-out">
            <i class="fa-solid fa-plus text-sm"></i>
            <span>Tambah Akun</span>
        </a>

        <div class="my-4 border-t-2 border-dashed border-gray-300 w-full"></div>

        <div class="relative border-2 border-dashed border-gray-200 rounded-md p-4">

            {{-- Filter Section --}}
            <form method="GET" class="mb-4 flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[150px]">
                    <label for="search" class="block text-xs font-medium text-gray-600 mb-1">Cari</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                        placeholder="Cari Nama / Email / Username..."
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md">
                </div>
                <div class="flex gap-2">
                    <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded-md hover:bg-gray-700 transition-all">
                        Filter
                    </button>
                    <a href="{{ route('user-management.index') }}"
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
                            <th class="px-4 py-3 text-xs uppercase">Avatar</th>
                            <th class="px-4 py-3 text-xs uppercase">Nama</th>
                            <th class="px-4 py-3 text-xs uppercase">Email</th>
                            <th class="px-4 py-3 text-xs uppercase">Username</th>
                            <th class="px-4 py-3 text-xs uppercase">Telepon</th>
                            <th class="px-4 py-3 text-xs uppercase">Role</th>
                            <th class="px-4 py-3 text-xs uppercase">Ekstrakurikuler</th>
                            <th class="px-4 py-3 text-xs uppercase">Status</th>
                            <th class="px-4 py-3 text-xs uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr class="hover:bg-gray-100 transition-colors transition-duration-300 border-b border-gray-200">
                                <td class="px-4 py-3 border-gray-200">{{ $loop->iteration }}</td>
                                <td class="px-4 py-3 font-medium whitespace-nowrap border-gray-200">
                                    <img class="w-8 h-8 rounded-full"
                                        src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=0083E9&color=fff"
                                        alt="user avatar">
                                </td>
                                <td class="px-4 py-3 font-medium whitespace-nowrap border-gray-200">
                                    {{ $user->name }}
                                </td>
                                <td class="px-4 py-3 font-medium whitespace-nowrap border-gray-200">
                                    {{ $user->email }}
                                </td>
                                <td class="px-4 py-3 font-medium whitespace-nowrap border-gray-200">
                                    {{ $user->username }}
                                </td>
                                <td class="px-4 py-3 font-medium whitespace-nowrap border-gray-200">
                                    {{ $user->phone }}
                                </td>
                                <td class="px-4 py-3 font-medium whitespace-nowrap border-gray-200">
                                    @if ($user->role === 'admin')
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Admin
                                        </span>
                                    @elseif($user->role === 'pembina')
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-[#0083E9]/10 text-[#0083E9]">
                                            Pembina
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ ucfirst($user->role) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-medium whitespace-nowrap border-gray-200">
                                    @if ($user->extracurriculars->count() > 0)
                                        @foreach ($user->extracurriculars as $item)
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-[#0083E9]/10 text-[#0083E9] mr-1 mb-1">
                                                {{ $item->extracurricular->name }}
                                            </span>
                                        @endforeach
                                    @else
                                        <span class="text-gray-400 italic">Belum mengampu</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-medium whitespace-nowrap border-gray-200">
                                    <form action="{{ route('user-management.toggle', $user) }}" method="POST"
                                        id="toggle-form-{{ $user->uuid }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="password" id="password-{{ $user->uuid }}"
                                            value="">
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" class="sr-only peer toggle-checkbox"
                                                data-id="{{ $user->uuid }}" data-name="{{ $user->name }}"
                                                {{ $user->is_active ? 'checked' : '' }}>
                                            <div
                                                class="relative w-14 h-8 bg-gray-300 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-6 after:content-[''] after:absolute after:top-1 after:start-1 after:bg-white after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-[#0083E9]">
                                            </div>
                                        </label>
                                    </form>
                                </td>
                                <td class="px-4 py-3 font-medium whitespace-nowrap border-gray-200">
                                    <div class="flex gap-3 items-center justify-start">
                                        <a href="{{ route('user-management.edit', $user->uuid) }}"
                                            class="text-[#0083E9] hover:underline font-medium focus:outline-none flex flex-col lg:flex-row items-center gap-1">
                                            <i class="fa-solid fa-edit text-sm"></i>
                                            <span class="text-sm">Edit</span>
                                        </a>
                                        <p class="font-bold text-gray-300">|</p>
                                        <form action="{{ route('user-management.destroy', $user->uuid) }}" method="POST"
                                            id="delete-form-{{ $user->uuid }}" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="password"
                                                id="delete-password-{{ $user->uuid }}" value="">
                                            <button type="button"
                                                class="delete-btn text-[#EF4444] hover:underline font-medium focus:outline-none cursor-pointer flex flex-col lg:flex-row items-center gap-1"
                                                data-id="{{ $user->uuid }}" data-name="{{ $user->name }}">
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

            @if ($users->hasPages())
                <div class="mt-4">
                    {{ $users->withQueryString()->links() }}
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

                        const classId = e.target.getAttribute('data-id');
                        const className = e.target.getAttribute('data-name');
                        const status = e.target.checked ? 'mengaktifkan' : 'menonaktifkan';

                        const {
                            value: password
                        } = await Swal.fire({
                            title: 'Konfirmasi Perubahan Status',
                            html: `<p>Anda akan <strong>${status}</strong> pengguna <strong>${className}</strong>.</p>
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
                            document.getElementById('password-' + classId).value = password;
                            document.getElementById('toggle-form-' + classId).submit();
                        } else {
                            e.target.checked = !e.target.checked;
                        }
                    }
                });

                document.addEventListener('click', async function(e) {
                    const deleteBtn = e.target.closest('.delete-btn');

                    if (deleteBtn) {
                        e.preventDefault();

                        const classId = deleteBtn.getAttribute('data-id');
                        const className = deleteBtn.getAttribute('data-name');

                        const {
                            value: password
                        } = await Swal.fire({
                            title: 'Hapus Pengguna',
                            html: `<p>Anda akan menghapus pengguna <strong>${className}</strong>.</p>
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
                            document.getElementById('delete-password-' + classId).value = password;
                            document.getElementById('delete-form-' + classId).submit();
                        }
                    }
                });

            });
        </script>
    @endpush

@endsection
