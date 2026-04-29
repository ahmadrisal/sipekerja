<nav class="sticky top-0 z-40 bg-minimal-indigo text-white shadow-lg border-b border-white/10 px-6 py-3 flex items-center justify-between">
    <div class="flex items-center gap-4">
        @if(!in_array(session('active_role'), ['Ketua Tim', 'Pimpinan', 'Pegawai']))
        <button class="lg:hidden text-white/80 hover:text-white transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" /></svg>
        </button>
        @endif
        <div class="hidden md:block">
            <h1 class="text-sm font-black uppercase tracking-[0.2em] text-white/90">{{ $title ?? 'Dashboard' }}</h1>
            <p class="text-[9px] font-medium text-white/50 uppercase tracking-widest">Penilaian Kinerja Aparatur</p>
        </div>
    </div>

    <div class="flex items-center gap-6">
        <!-- Role Switcher -->
        @auth
            @if(Auth::user()->roles->count() > 1)
                <div class="hidden lg:block">
                    <livewire:auth.role-switcher />
                </div>
            @endif
        @endauth

        <!-- Notification & Profile Cluster -->
        <div class="flex items-center gap-3 pl-6 border-l border-white/10">
            <!-- User Info + Profile Dropdown -->
            <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                <button @click="open = !open" class="flex items-center gap-3 group cursor-pointer">
                    <div class="text-right">
                        <p class="text-[10px] font-black uppercase tracking-tight text-white">{{ Auth::user()->name }}</p>
                        <p class="text-[8px] font-black uppercase tracking-widest text-white/50">{{ session('active_role') }}</p>
                    </div>
                    <div class="w-9 h-9 rounded-xl bg-white/20 border border-white/20 overflow-hidden flex items-center justify-center shadow-lg group-hover:scale-105 transition-transform group-hover:border-white/40">
                        @if(Auth::user()->avatar_url)
                            <img src="{{ Auth::user()->avatar_url }}" alt="Avatar" class="w-full h-full object-cover">
                        @else
                            <span class="text-sm font-black text-white italic">{{ substr(Auth::user()->name, 0, 1) }}</span>
                        @endif
                    </div>
                </button>

                <!-- Dropdown -->
                <div
                    x-show="open"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                    class="absolute right-0 top-full mt-2 w-52 bg-white rounded-2xl shadow-2xl border border-slate-100 overflow-hidden z-50"
                    style="display: none;"
                >
                    <div class="px-4 py-3 border-b border-slate-50">
                        <p class="text-[10px] font-black text-slate-700 truncate">{{ Auth::user()->name }}</p>
                        <p class="text-[9px] font-mono text-slate-400 truncate">{{ Auth::user()->nip }}</p>
                    </div>
                    <div class="p-1.5">
                        <button
                            @click="open = false; $dispatch('open-change-password')"
                            class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-left hover:bg-indigo-50 transition-colors group"
                        >
                            <div class="w-7 h-7 rounded-lg bg-indigo-50 group-hover:bg-indigo-100 flex items-center justify-center transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-minimal-indigo" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            </div>
                            <span class="text-[10px] font-black text-slate-600 uppercase tracking-widest group-hover:text-minimal-indigo transition-colors">Ganti Password</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Logout Button + Dialog -->
            <div x-data="{ open: false, loading: false }">
                <button
                    @click="open = true"
                    class="w-9 h-9 flex items-center justify-center rounded-xl bg-red-500/20 text-red-100 hover:bg-red-500 hover:text-white transition-all shadow-lg active:scale-95 border border-red-500/20"
                    title="Keluar Sistem"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                </button>

                <!-- Logout Confirmation Dialog -->
                <div
                    x-show="open"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 z-[200] flex items-center justify-center p-4"
                    style="display: none;"
                >
                    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="if(!loading) open = false"></div>
                    <div
                        x-show="open"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="relative bg-white w-full max-w-sm rounded-3xl shadow-2xl p-8 border-t-4 border-red-500"
                    >
                        <div class="w-14 h-14 bg-red-50 rounded-2xl flex items-center justify-center mb-5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                        </div>
                        <h4 class="text-lg font-black text-slate-800 uppercase italic leading-none mb-2">Keluar Sistem?</h4>
                        <p class="text-sm text-slate-500 font-medium mb-8">Sesi Anda akan diakhiri dan Anda perlu login kembali untuk mengakses sistem.</p>
                        <div class="flex gap-3">
                            <button
                                type="button"
                                @click="open = false"
                                :disabled="loading"
                                class="flex-1 py-3.5 bg-slate-100 text-slate-500 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-200 transition-all active:scale-95 disabled:opacity-50"
                            >Batal</button>
                            <button
                                type="button"
                                @click="loading = true; document.getElementById('navbar-logout-form').submit();"
                                :disabled="loading"
                                class="flex-1 py-3.5 bg-red-500 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-red-600 transition-all active:scale-95 shadow-lg shadow-red-500/20 disabled:opacity-75 disabled:cursor-wait flex items-center justify-center gap-2"
                            >
                                <svg x-show="loading" class="animate-spin w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                </svg>
                                <span x-text="loading ? 'Keluar...' : 'Ya, Keluar'"></span>
                            </button>
                        </div>
                    </div>
                </div>

                <form id="navbar-logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                    @csrf
                </form>
            </div>
        </div>
    </div>
</nav>
