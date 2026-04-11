<nav class="sticky top-0 z-40 bg-minimal-indigo text-white shadow-lg border-b border-white/10 px-6 py-3 flex items-center justify-between">
    <div class="flex items-center gap-4">
        <button class="lg:hidden text-white/80 hover:text-white transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" /></svg>
        </button>
        <div class="hidden md:block">
            <h1 class="text-sm font-black uppercase tracking-[0.2em] text-white/90">{{ $title ?? 'Dashboard' }}</h1>
            <p class="text-[9px] font-medium text-white/50 uppercase tracking-widest">Sistem Penilaian Kinerja</p>
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
            <!-- Simple Notification Badge -->
            <button class="relative w-8 h-8 flex items-center justify-center text-white/70 hover:text-white hover:bg-white/10 rounded-full transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                <span class="absolute top-1 right-1 w-3 h-3 bg-red-500 border-2 border-minimal-indigo rounded-full text-[7px] font-black flex items-center justify-center">1</span>
            </button>

            <!-- User Info -->
            <div class="flex items-center gap-3 group cursor-default">
                <div class="hidden sm:block text-right">
                    <p class="text-[10px] font-black uppercase tracking-tight text-white">{{ Auth::user()->name }}</p>
                    <p class="text-[8px] font-black uppercase tracking-widest text-white/50">{{ session('active_role') }}</p>
                </div>
                <div class="w-9 h-9 rounded-xl bg-white/20 border border-white/20 overflow-hidden flex items-center justify-center shadow-lg group-hover:scale-105 transition-transform">
                    @if(Auth::user()->avatar_url)
                        <img src="{{ Auth::user()->avatar_url }}" alt="Avatar" class="w-full h-full object-cover">
                    @else
                        <span class="text-sm font-black text-white italic">{{ substr(Auth::user()->name, 0, 1) }}</span>
                    @endif
                </div>
            </div>

            <!-- Logout Button (Icon Only for Minimalist) -->
            <button 
                wire:confirm="Yakin ingin keluar?"
                onclick="event.preventDefault(); document.getElementById('navbar-logout-form').submit();"
                class="w-9 h-9 flex items-center justify-center rounded-xl bg-red-500/20 text-red-100 hover:bg-red-500 hover:text-white transition-all shadow-lg active:scale-95 border border-red-500/20"
                title="Keluar Sistem"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
            </button>
            <form id="navbar-logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                @csrf
            </form>
        </div>
    </div>
</nav>
