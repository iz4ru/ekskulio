@extends('role.kesiswaan.layouts.app')
@section('title', 'Ekskulio | Siswa')
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
                <h1 class="text-xl lg:text-2xl font-semibold text-gray-600">Kelola Siswa</h1>
                <p class="text-sm lg:text-base text-gray-400">Tambah, ubah, dan hapus data siswa.</p>
            </div>
        </div>

        <div class="inline-flex flex-col lg:flex-row gap-2">
            <a href="{{ route('student.create') }}"
                class="inline-flex cursor-pointer items-center px-5 py-2.5 gap-2 text-sm font-medium text-center text-white bg-[#0083E9] rounded-md focus:ring-4 focus:ring-blue-300 hover:bg-[#DEECFF] hover:text-[#0083E9] active:scale-[0.98] transition-all duration-300 ease-out">
                <i class="fa-solid fa-plus text-sm"></i>
                <span>Tambah Siswa</span>
            </a>
            <a href="{{ route('student.import') }}"
                class="inline-flex cursor-pointer items-center px-5 py-2.5 gap-2 text-sm font-medium text-center text-white bg-[#0083E9] rounded-md focus:ring-4 focus:ring-blue-300 hover:bg-[#DEECFF] hover:text-[#0083E9] active:scale-[0.98] transition-all duration-300 ease-out">
                <i class="fa-solid fa-file-import text-sm"></i>
                <span>Impor Data Siswa</span>
            </a>
        </div>

        <div class="my-4 border-t-2 border-dashed border-gray-300 w-full"></div>

        <div class="relative border-2 border-dashed border-gray-200 rounded-md p-4">

            {{-- Filter Section --}}
            <form method="GET" class="mb-4 flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[150px]">
                    <label for="grade" class="block text-xs font-medium text-gray-600 mb-1">Tingkat</label>
                    <select name="grade" id="grade"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md">
                        <option value="">Semua Tingkat</option>
                        <option value="X" {{ request('grade') == 'X' ? 'selected' : '' }}>Kelas X</option>
                        <option value="XI" {{ request('grade') == 'XI' ? 'selected' : '' }}>Kelas XI</option>
                        <option value="XII" {{ request('grade') == 'XII' ? 'selected' : '' }}>Kelas XII</option>
                    </select>
                </div>
                <div class="flex-1 min-w-[150px]">
                    <label for="status" class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                    <select name="status" id="status"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md">
                        <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="lulus" {{ request('status') == 'lulus' ? 'selected' : '' }}>Lulus</option>
                        <option value="mutasi" {{ request('status') == 'mutasi' ? 'selected' : '' }}>Mutasi</option>
                        <option value="">Semua Status</option>
                    </select>
                </div>
                <div class="flex-1 min-w-[150px]">
                    <label for="class_id" class="block text-xs font-medium text-gray-600 mb-1">Kelas</label>
                    <select name="class_id" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md">
                        <option value="">Semua Kelas</option>
                        @foreach ($studentClasses as $class)
                            <option value="{{ $class->id }}"
                                {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1 min-w-[150px]">
                    <label for="search" class="block text-xs font-medium text-gray-600 mb-1">Cari</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                        placeholder="Cari Nama / NIS..." class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md">
                </div>
                <div class="flex gap-2">
                    <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded-md hover:bg-gray-700 transition-all">
                        Filter
                    </button>
                    <a href="{{ route('student.index') }}"
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
                            <th class="px-4 py-3 text-xs uppercase">NIS</th>
                            <th class="px-4 py-3 text-xs uppercase">Nama</th>
                            <th class="px-4 py-3 text-xs uppercase">Kelas</th>
                            <th class="px-4 py-3 text-xs uppercase">Tingkat</th>
                            <th class="px-4 py-3 text-xs uppercase">Status</th>
                            <th class="px-4 py-3 text-xs uppercase">Tahun Masuk</th>
                            <th class="px-4 py-3 text-xs uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($students as $student)
                            <tr class="hover:bg-gray-100 transition-colors transition-duration-300 border-b border-gray-200">
                                <td class="px-4 py-3 border-gray-200">{{ $loop->iteration }}</td>
                                <td class="px-4 py-3 font-medium whitespace-nowrap border-gray-200">
                                    {{ $student->id_number }}
                                </td>
                                <td class="px-4 py-3 font-medium whitespace-nowrap border-gray-200">
                                    {{ $student->name }}
                                </td>
                                <td class="px-4 py-3 font-medium whitespace-nowrap border-gray-200">
                                    {{ $student->studentClass?->name ?? '-' }}
                                </td>
                                <td class="px-4 py-3 font-medium whitespace-nowrap border-gray-200">
                                    <span
                                        class="px-2 py-1 text-xs font-medium rounded-md 
                                    @if ($student->grade->value === 'X') bg-blue-100 text-blue-700
                                    @elseif($student->grade->value === 'XI') bg-indigo-100 text-indigo-700
                                    @else bg-gray-100 text-gray-700 @endif">
                                        Kelas {{ $student->grade_value }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-medium whitespace-nowrap border-gray-200">
                                    <span
                                        class="px-2 py-1 text-xs font-medium rounded-md 
                                    @if ($student->status_label === 'aktif') bg-green-100 text-green-700
                                    @elseif($student->status_label === 'lulus') bg-purple-100 text-purple-700
                                    @else bg-orange-100 text-orange-700 @endif">
                                        {{ ucfirst($student->status_label) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-medium whitespace-nowrap border-gray-200">
                                    {{ $student->enrollment_year }}
                                </td>
                                <td class="px-4 py-3 font-medium whitespace-nowrap border-gray-200">
                                    <div class="flex gap-3 items-center justify-start">
                                        <a href="{{ route('student.edit', $student->uuid) }}"
                                            class="text-[#0083E9] hover:underline font-medium focus:outline-none flex flex-col lg:flex-row items-center gap-1">
                                            <i class="fa-solid fa-edit text-sm"></i>
                                            <span class="text-sm">Edit</span>
                                        </a>
                                        <p class="font-bold text-gray-300">|</p>
                                        <form action="{{ route('student.destroy', $student->uuid) }}" method="POST"
                                            id="delete-form-{{ $student->uuid }}" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="password"
                                                id="delete-password-{{ $student->uuid }}" value="">
                                            <button type="button"
                                                class="delete-btn text-[#EF4444] hover:underline font-medium focus:outline-none cursor-pointer flex flex-col lg:flex-row items-center gap-1"
                                                data-id="{{ $student->uuid }}" data-name="{{ $student->name }}">
                                                <i class="fa-solid fa-trash text-sm"></i>
                                                <span class="text-sm">Hapus</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-8 text-gray-500">
                                    <i class="fa-solid fa-users text-4xl mb-2"></i>
                                    <p>Belum ada data siswa.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($students->hasPages())
                <div class="mt-4">
                    {{ $students->withQueryString()->links() }}
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
                            title: 'Hapus Siswa',
                            html: `<p>Anda akan menghapus siswa <strong>${studentName}</strong>.</p>
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
