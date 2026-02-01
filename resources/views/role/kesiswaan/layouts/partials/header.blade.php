<nav class="fixed top-0 z-50 w-full bg-[#FAFAFA] border-b border-gray-200">
    <div class="px-3 py-3 lg:px-5 lg:pl-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center justify-start rtl:justify-end">
                <button data-drawer-target="logo-sidebar" data-drawer-toggle="logo-sidebar" aria-controls="logo-sidebar"
                    type="button"
                    class="cursor-pointer inline-flex items-center p-2 text-sm text-gray-500 rounded-md sm:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 :text-gray-400 :hover:bg-gray-700 :focus:ring-gray-600">
                    <span class="sr-only">Open sidebar</span>
                    <i class="fa-solid fa-bars text-gray-500 hover:bg-gray-100 text-xl"></i>
                </button>
                <a href="#" class="flex ms-2 md:me-24">
                    <img src="{{ asset('images/ekskulio.png') }}" class="h-8 me-3 p-1.5" alt="Ekskulio Logo" />
                </a>
            </div>
            <div class="flex items-center">
                <div class="flex items-center ms-3">
                    <div>
                        <button type="button"
                            class="flex cursor-pointer text-sm bg-gray-800 rounded-full focus:ring-4 focus:ring-gray-300"
                            aria-expanded="false" data-dropdown-toggle="dropdown-user">
                            <span class="sr-only">Open user menu</span>
                            <img class="w-8 h-8 rounded-full"
                                src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=0083E9&color=fff"
                                alt="user photo">
                        </button>
                    </div>
                    <div class="z-50 hidden my-4 text-base list-none bg-white divide-y divide-gray-100 rounded-sm shadow-sm :bg-gray-700"
                        id="dropdown-user">
                        <div class="px-4 py-3" role="none">
                            <p class="text-sm text-gray-800 :text-white font-medium flex items-center gap-2"
                                role="none">
                                <i class="fa-solid fa-user-circle text-[#0083E9]"></i>
                                {{ Auth::user()->name }}
                            </p>

                            <div class="my-2 border-t border border-gray-100 w-full"></div>

                            <div class="flex flex-col gap-2">
                                <p class="text-sm text-gray-800 :text-white flex items-center gap-2" role="none">
                                    <i class="fa-solid fa-at text-[#0083E9]"></i>
                                    {{ Auth::user()->username }}
                                </p>

                                <p class="text-sm text-gray-800 truncate :text-gray-300 flex items-center gap-2"
                                    role="none">
                                    <i class="fa-solid fa-envelope text-[#0083E9]"></i>
                                    {{ Auth::user()->email }}
                                </p>
                            </div>

                        </div>
                        <ul class="py-1" role="none">
                            <li>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                    role="menuitem">
                                    <i class="fa-solid fa-gears mr-2 text-gray-700 hover:bg-gray-100 text-md"></i>
                                    Pengaturan</a>
                            </li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST"
                                    class="block cursor-pointer px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                    role="menuitem">
                                    @csrf
                                    <button type="submit" class="cursor-pointer"> <i
                                            class="fa-solid fa-right-from-bracket mr-2 text-gray-700 hover:bg-gray-100 text-md"></i>
                                        Keluar</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>
