@extends('role.kesiswaan.layouts.app')
@section('title', 'Ekskulio | Keanggotaan Ekskul')
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
                <h1 class="text-xl lg:text-2xl font-semibold text-gray-600">Keanggotaan Ekskul</h1>
                <p class="text-sm lg:text-base text-gray-400">Kelola keanggotaan siswa pada ekstrakurikuler.</p>
                @if ($activeAY)
                    <span class="inline-block mt-4 px-3 py-1 text-xs font-medium bg-blue-100 text-blue-700 rounded-full">
                        <i class="fa-solid fa-calendar mr-1"></i>
                        Tahun Ajaran Aktif: {{ $activeAY->display_name }}
                    </span>
                @else
                    <span class="inline-block mt-4 px-3 py-1 text-xs font-medium bg-red-100 text-red-700 rounded-full">
                        <i class="fa-solid fa-exclamation-triangle mr-1"></i>
                        Tidak ada tahun ajaran aktif
                    </span>
                @endif
            </div>
        </div>
        
        @if ($activeAY)
            <a href="{{ route('memberships.create') }}"
                class="inline-flex cursor-zpointer items-center px-5 py-2.5 gap-2 text-sm font-medium text-center text-white bg-[#0083E9] rounded-md focus:ring-4 focus:ring-blue-300 hover:bg-[#DEECFF] hover:text-[#0083E9] active:scale-[0.98] transition-all duration-300 ease-out">
                <i class="fa-solid fa-plus text-sm"></i>
                <span>Daftarkan Siswa</span>
            </a>
        @endif

        <div class="my-4 border-t-2 border-dashed border-gray-300 w-full"></div>

        {{-- Filter Section --}}
        <form method="GET" class="mb-4 flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
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
            <div class="flex-1 min-w-[200px]">
                <label for="extracurricular_id" class="block text-xs font-medium text-gray-600 mb-1">Ekstrakurikuler</label>
                <select name="extracurricular_id" id="extracurricular_id"
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md">
                    <option value="">Semua Ekskul</option>
                    @foreach ($extracurriculars as $ekskul)
                        <option value="{{ $ekskul->id }}" {{ request('extracurricular_id') == $ekskul->id ? 'selected' : '' }}>
                            {{ $ekskul->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[150px]">
                <label for="status" class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select name="status" id="status" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md">
                    <option value="">Semua Status</option>
                    <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                    <option value="drop" {{ request('status') == 'drop' ? 'selected' : '' }}>Drop</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit"
                    class="px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded-md hover:bg-gray-700 transition-all">
                    Filter
                </button>
                <a href="{{ route('memberships.index') }}"
                    class="px-4 py-2 text-sm font-medium text-gray-600 border border-gray-300 rounded-md hover:bg-gray-100 transition-all">
                    Reset
                </a>
            </div>
        </form>

        <div class="relative overflow-x-auto border-2 border-dashed border-gray-200 rounded-md p-4">

            <table id="pagination-table" class="min-w-full text-sm text-left text-gray-600">
                <thead>
                    <tr>
                        <th class="px-4 py-3">No</th>
                        <th class="px-4 py-3">NIS</th>
                        <th class="px-4 py-3">Nama Siswa</th>
                        <th class="px-4 py-3">Kelas</th>
                        <th class="px-4 py-3">Ekstrakurikuler</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Tahun Ajaran</th>
                        <th class="px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($memberships as $membership)
                        @php $student = $membership->student; @endphp
                        <tr class="hover:bg-gray-100 transition-colors transition-duration-300{{ $student?->trashed() ? ' bg-red-50' : '' }}">
                            <td class="px-4 py-3">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3 font-medium whitespace-nowrap">
                                {{ $student?->id_number ?? '-' }}
                            </td>
                            <td class="px-4 py-3 font-medium whitespace-nowrap{{ $student?->trashed() ? ' text-red-500 line-through' : '' }}">
                                {{ $student?->name ?? '-' }}
                                @if($student?->trashed())
                                    <span class="ml-1 text-xs text-red-500">(Dihapus)</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                {{ $student?->studentClass?->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                {{ $membership->extracurricular->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-md 
                                    @if($membership->status_label === 'aktif') bg-green-100 text-green-700
                                    @elseif($membership->status_label === 'selesai') bg-blue-100 text-blue-700
                                    @else bg-red-100 text-red-700
                                    @endif">
                                    {{ ucfirst($membership->status_label) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                {{ $membership->academicYear->display_name ?? '-' }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2 items-center">
                                    @if (!$student?->trashed() && $membership->status_label === 'aktif')
                                        <form action="{{ route('memberships.update.status', $membership) }}" method="POST"
                                            class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <select name="status" onchange="this.form.submit()"
                                                class="text-xs px-2 py-1 border border-gray-300 rounded-md bg-white">
                                                <option value="aktif" selected>Aktif</option>
                                                <option value="selesai">Selesai</option>
                                                <option value="drop">Drop</option>
                                            </select>
                                        </form>
                                    @endif
                                    @if (!$student?->trashed())
                                        <form action="{{ route('memberships.destroy', $membership) }}" method="POST"
                                            id="delete-form-{{ $membership->id }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                            class="delete-btn text-red-500 hover:text-red-700 p-1"
                                            data-id="{{ $membership->id }}">
                                            <i class="fa-solid fa-trash text-sm"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 text-gray-500">
                                <i class="fa-solid fa-users-slash text-4xl mb-2"></i>
                                <p>Belum ada data keanggotaan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if ($memberships->hasPages())
                <div class="mt-4">
                    {{ $memberships->withQueryString()->links() }}
                </div>
            @endif

        </div>
    </div>

    @push('scripts')
        <script>
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
                    const deleteBtn = e.target.closest('.delete-btn');

                    if (deleteBtn) {
                        e.preventDefault();

                        const membershipId = deleteBtn.getAttribute('data-id');

                        const {
                            value: confirmDelete
                        } = await Swal.fire({
                            title: 'Hapus Keanggotaan',
                            text: 'Apakah Anda yakin ingin menghapus keanggotaan ini?',
                            icon: 'warning',
                            showCancelButton: true,
                            cancelButtonText: 'Batal',
                            confirmButtonText: 'Hapus',
                            confirmButtonColor: '#EF4444',
                            cancelButtonColor: '#6B7280',
                        });

                        if (confirmDelete) {
                            document.getElementById('delete-form-' + membershipId).submit();
                        }
                    }
                });
            });
        </script>
    @endpush

@endsection
