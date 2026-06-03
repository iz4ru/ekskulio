<aside id="logo-sidebar"
    class="fixed top-0 left-0 z-40 w-64 h-screen pt-20 transition-transform -translate-x-full bg-[#FAFAFA] border-r border-gray-200 sm:translate-x-0"
    aria-label="Sidebar">
    <div class="h-full px-3 pb-4 overflow-y-auto bg-[#FAFAFA]">
        <ul class="space-y-2 font-medium">

            <!-- Beranda -->
            <li>
                <x-nav-link href="{{ route('pembina.dashboard') }}" :active="request()->routeIs('pembina.dashboard')">
                    <i class="fa-solid fa-home text-md"></i>
                    <span class="ml-3">Beranda</span>
                </x-nav-link>
            </li>

            <!-- Presensi Ekstrakurikuler -->
            <li>
                <x-nav-link href="{{ route('presence.index') }}" :active="request()->routeIs(['presence.index', 'presence.show', 'presence.create', 'presence.edit'])">
                    <i class="fa-solid fa-calendar-check text-md"></i>
                    <span class="ml-3">Presensi Ekstrakurikuler</span>
                </x-nav-link>
            </li>

            <!-- Penilaian Siswa -->
            <li>
                <x-nav-link href="{{ route('scores.index') }}" :active="request()->routeIs(['scores.index', 'scores.input'])">
                    <i class="fa-solid fa-clipboard-list text-md"></i>
                    <span class="ml-3">Penilaian Siswa</span>
                </x-nav-link>
            </li>

        </ul>
    </div>
</aside>
