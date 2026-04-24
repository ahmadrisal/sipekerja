<aside class="w-64 bg-[#012a52] text-white flex flex-col h-screen fixed inset-y-0 left-0 z-50 transform lg:translate-x-0 transition-transform duration-500 shadow-2xl border-r border-white/5">
    <div class="px-6 py-8 flex items-center gap-3">
        <div class="w-8 h-8 bg-white/10 rounded-lg flex items-center justify-center border border-white/10 backdrop-blur-sm">
            <span class="text-sm font-black text-white italic">S</span>
        </div>
        <span class="text-sm font-black uppercase italic tracking-widest text-white/90">SIPEKERJA</span>
    </div>

    <nav class="flex-1 px-3 space-y-1 overflow-y-auto">
        <div class="px-4 mb-2">
            <p class="text-[9px] font-black uppercase tracking-[0.2em] text-blue-300 opacity-40">Utama</p>
        </div>

        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl {{ request()->is('dashboard*') || request()->is('/') ? 'bg-white/10 text-white font-bold shadow-sm' : 'text-blue-100/70 hover:bg-white/5 hover:text-white' }} transition-all group">
            <div class="w-6 h-6 rounded-md {{ request()->is('dashboard*') || request()->is('/') ? 'text-white' : 'text-blue-300/60' }} flex items-center justify-center group-hover:scale-110 transition-transform">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" rx="2" ry="2" x="3" y="3"/><line x1="3" x2="21" y1="9" y2="9"/><line x1="9" x2="9" y1="21" y2="9"/></svg>
            </div>
            <span class="text-xs uppercase tracking-widest">Dashboard</span>
        </a>

        @if(session('active_role') === 'Admin')
            <div class="px-4 mt-6 mb-2">
                <p class="text-[9px] font-black uppercase tracking-[0.2em] text-blue-300 opacity-40">Admin</p>
            </div>
            <a href="{{ route('teams') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl {{ request()->is('teams*') ? 'bg-white/10 text-white font-bold' : 'text-blue-100/70 hover:bg-white/5 hover:text-white' }} transition-all group">
                <div class="w-6 h-6 rounded-md {{ request()->is('teams*') ? 'text-white' : 'text-blue-300/60' }} flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <span class="text-xs uppercase tracking-widest leading-none">Manajemen Tim</span>
            </a>
            <a href="{{ route('users') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl {{ request()->is('users*') ? 'bg-white/10 text-white font-bold' : 'text-blue-100/70 hover:bg-white/5 hover:text-white' }} transition-all group">
                <div class="w-6 h-6 rounded-md {{ request()->is('users*') ? 'text-white' : 'text-blue-300/60' }} flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 2a7 7 0 0 1 7 7c0 5.25-7 13-7 13S5 14.25 5 9a7 7 0 0 1 7-7z"/></svg>
                </div>
                <span class="text-xs uppercase tracking-widest leading-none">Data Pegawai</span>
            </a>
            <a href="{{ route('konfigurasi') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl {{ request()->is('konfigurasi*') ? 'bg-white/10 text-white font-bold' : 'text-blue-100/70 hover:bg-white/5 hover:text-white' }} transition-all group">
                <div class="w-6 h-6 rounded-md {{ request()->is('konfigurasi*') ? 'text-white' : 'text-blue-300/60' }} flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 1.41 13.42"/><path d="M4.93 4.93A10 10 0 0 0 3.52 18.35"/><path d="M20 12h2"/><path d="M2 12h2"/><path d="M12 2v2"/><path d="M12 20v2"/></svg>
                </div>
                <span class="text-xs uppercase tracking-widest leading-none">Bobot Penilaian</span>
            </a>
        @endif


        <div class="px-4 mt-8 mb-4">
            <p class="text-[9px] font-black uppercase tracking-[0.2em] text-blue-300 opacity-40">Info</p>
        </div>
        <div class="px-4 py-2 border border-white/5 rounded-xl bg-white/5 mx-2">
            <p class="text-[9px] text-blue-100/50 leading-relaxed italic">Gunakan Navbar bagian atas untuk beralih role atau keluar sistem.</p>
        </div>
    </nav>

    <div class="p-4 mx-2 my-4 rounded-3xl bg-white/5 border border-white/5">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center text-[#003366] font-black text-xs shadow-lg">
                {{ substr(Auth::user()->name, 0, 1) }}
            </div>
            <div class="overflow-hidden">
                <p class="text-[11px] font-black truncate uppercase tracking-tighter text-white/90">{{ Auth::user()->name }}</p>
                <p class="text-[8px] uppercase font-black tracking-[0.2em] text-amber-400 opacity-80 italic">{{ session('active_role') }}</p>
            </div>
        </div>
    </div>
</aside>
