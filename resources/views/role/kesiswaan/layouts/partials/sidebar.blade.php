<aside id="logo-sidebar"
    class="fixed top-0 left-0 z-40 w-64 h-screen pt-20 transition-transform -translate-x-full bg-[#FAFAFA] border-r border-gray-200 sm:translate-x-0"
    aria-label="Sidebar">
    <div class="h-full px-3 pb-4 overflow-y-auto bg-[#FAFAFA]">
        <ul class="space-y-2 font-medium">

            <!-- Beranda -->
            <li>
                <x-nav-link href="{{ route('kesiswaan.dashboard') }}" :active="request()->routeIs('kesiswaan.dashboard')">
                    <i class="fa-solid fa-home text-md"></i>
                    <span class="ml-3">Beranda</span>
                </x-nav-link>
            </li>

            <!-- Tahun Ajaran -->
            <li>
                <x-nav-link href="{{ route('academic-years.index') }}" :active="request()->routeIs(['academic-years.index', 'academic-years.create', 'academic-years.edit'])">
                    <i class="fa-solid fa-calendar text-md"></i>
                    <span class="ml-3">Tahun Ajaran</span>
                </x-nav-link>
            </li>

            <!-- Kategori Ekstrakurikuler -->
            <li>
                <x-nav-link href="{{ route('extracurricular-category.index') }}" :active="request()->routeIs(['extracurricular-category.index', 'extracurricular-category.create', 'extracurricular-category.import', 'extracurricular-category.edit'])">
                    <i class="fa-solid fa-layer-group text-md"></i>
                    <span class="ml-3">Kategori Ekstrakurikuler</span>
                </x-nav-link>
            </li>

            <!-- Ekstrakurikuler -->
            <li>
                <x-nav-link href="{{ route('extracurricular.index') }}" :active="request()->routeIs(['extracurricular.index', 'extracurricular.create', 'extracurricular.import', 'extracurricular.edit', 'extracurricular.detail'])">
                    <i class="fa-solid fa-people-roof text-md"></i>
                    <span class="ml-3">Ekstrakurikuler</span>
                </x-nav-link>
            </li>

            <!-- Kelas -->
            <li>
                <x-nav-link href="{{ route('student-class.index') }}" :active="request()->routeIs(['student-class.index', 'student-class.create', 'student-class.import', 'student-class.edit'])">
                    <i class="fa-solid fa-school text-md"></i>
                    <span class="ml-3">Kelas</span>
                </x-nav-link>
            </li>

            <!-- Siswa -->
            <li>
                <x-nav-link href="{{ route('student.index') }}" :active="request()->routeIs(['student.index', 'student.create', 'student.import', 'student.edit'])">
                    <i class="fa-solid fa-id-badge text-md"></i>
                    <span class="ml-3">Siswa</span>
                </x-nav-link>
            </li>

            <!-- Akun -->
            <li>
                <x-nav-link href="{{ route('user-management.index') }}" :active="request()->routeIs(['user-management.index', 'user-management.create', 'user-management.edit'])">
                    <i class="fa-solid fa-user text-md"></i>
                    <span class="ml-3">Akun Pengguna & Pembina</span>
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
                <x-nav-link href="#">
                    <i class="fa-solid fa-clipboard-list text-md"></i>
                    <span class="ml-3">Penilaian Siswa</span>
                </x-nav-link>
            </li>

            <!-- Laporan -->
            <li>
                <x-nav-link href="#">
                    <i class="fa-solid fa-file-alt text-md"></i>
                    <span class="ml-3">Laporan</span>
                </x-nav-link>
            </li>

        </ul>
    </div>
</aside>
