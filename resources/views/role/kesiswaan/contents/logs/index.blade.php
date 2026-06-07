@extends('role.kesiswaan.layouts.app')
@section('title', 'Ekskulio | Log Aktivitas')
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
                <h1 class="text-xl lg:text-2xl font-semibold text-gray-600">Lihat Log Aktivitas</h1>
                <p class="text-sm lg:text-base text-gray-400">Lihat log aktivitas global semua role.</p>

            </div>
        </div>


        <div class="my-4 border-t-2 border-dashed border-gray-300 w-full"></div>

        <div class="relative border-2 border-dashed border-gray-200 rounded-md p-4">

            {{-- Filter Section --}}
            <form method="GET" class="mb-4 flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[150px]">
                    <label for="search" class="block text-xs font-medium text-gray-600 mb-1">Cari</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                        placeholder="Cari Log Aktivitas..."
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md">
                </div>
                <div class="flex gap-2">
                    <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded-md hover:bg-gray-700 transition-all">
                        Filter
                    </button>
                    <a href="{{ route('logs.index') }}"
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
                            <th class="px-4 py-3 text-xs uppercase">Dibuat Pada</th>
                            <th class="px-4 py-3 text-xs uppercase">Nama Pengguna</th>
                            <th class="px-4 py-3 text-xs uppercase">Role</th>
                            <th class="px-4 py-3 text-xs uppercase">Aktivitas</th>
                            <th class="px-4 py-3 text-xs uppercase">Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($logs as $log)
                            <tr
                                class="hover:bg-gray-100 transition-colors transition-duration-300 border-b border-gray-200">
                                <td class="px-4 py-3 border-gray-200">{{ $loop->iteration }}</td>
                                <td class="px-4 py-3 font-semibold whitespace-nowrap border-gray-200">
                                    {{ \Carbon\Carbon::parse($log->created_at)->locale('id')->translatedFormat('l, d/m/y, H:i:s') }}
                                </td>
                                <td class="px-4 py-3 font-medium whitespace-nowrap border-gray-200">
                                    <div class="flex flex-row items-center justify-center gap-2">
                                        @if ($log->user->role === 'admin')
                                            <img class="w-6 h-6 rounded-full"
                                                src="https://ui-avatars.com/api/?name={{ urlencode($log->user->name) }}&background=008236&color=fff"
                                                alt="admin avatar">
                                        @elseif ($log->user->role === 'pembina')
                                            <img class="w-6 h-6 rounded-full"
                                                src="https://ui-avatars.com/api/?name={{ urlencode($log->user->name) }}&background=0083E9&color=fff"
                                                alt="advisor avatar">
                                        @else
                                            <img class="w-6 h-6 rounded-full"
                                                src="https://ui-avatars.com/api/?name={{ urlencode($log->user->name) }}&background=8200DB&color=fff"
                                                alt="student affairs avatar">
                                        @endif
                                        <span class="text-gray-200">
                                            |
                                        </span>
                                        {{ $log->user->name }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 font-medium whitespace-nowrap border-gray-200 flex-wrap">
                                    @if ($log->user->role === 'admin')
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Admin
                                        </span>
                                    @elseif($log->user->role === 'pembina')
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-[#0083E9]/10 text-[#0083E9]">
                                            Pembina
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-700">
                                            {{ ucfirst($log->user->role) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-medium whitespace-nowrap border-gray-200">
                                    {{ $log->activity }}
                                </td>
                                <td class="px-4 py-3 border-gray-200">
                                    <button
                                        class="detail-btn text-[#0083E9] hover:text-[#0066b3] text-xs font-medium flex items-center gap-1 hover:underline cursor-pointer"
                                        data-detail="{{ addslashes($log->detail) }}"
                                        data-created="{{ \Carbon\Carbon::parse($log->created_at)->locale('id')->translatedFormat('l, d/m/y, H:i:s') }}">
                                        Lihat selengkapnya
                                        <i class="fa-solid fa-arrow-right text-xs"></i>
                                    </button>
                                </td>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($logs->hasPages())
                <div class="mt-4">
                    {{ $logs->withQueryString()->links() }}
                </div>
            @endif

        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {

                document.addEventListener('click', function(e) {
                    const detailBtn = e.target.closest('.detail-btn');

                    if (detailBtn) {
                        e.preventDefault();

                        const detail = detailBtn.getAttribute('data-detail');
                        const createdAt = detailBtn.getAttribute('data-created');

                        Swal.fire({
                            title: 'Detail Log',
                            html: `
                <textarea
                    readonly
                    class="w-full h-60 text-sm text-gray-700 leading-relaxed border border-gray-200 rounded-lg p-3 resize-none focus:outline-none bg-gray-50"
                >[${createdAt}] ${detail}</textarea>
            `,
                            showConfirmButton: false,
                            showCloseButton: true,
                            width: '36rem',
                        });
                    }
                });

            });
        </script>
    @endpush

@endsection
