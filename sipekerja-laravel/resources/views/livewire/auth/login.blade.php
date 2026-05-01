<div class="min-h-screen flex items-center justify-center bg-[#003366] relative overflow-hidden">
    <!-- Background accents -->
    <div class="absolute -top-24 -left-24 w-96 h-96 bg-blue-500/20 rounded-full blur-3xl opacity-50"></div>
    <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-amber-400/10 rounded-full blur-3xl opacity-50"></div>

    <div class="w-full max-w-md p-8 bg-white/10 backdrop-blur-xl border border-white/20 rounded-3xl shadow-2xl z-10 mx-4">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-black text-white tracking-tight mb-1 uppercase italic">SIPAKAR</h1>
            <p class="text-blue-100/70 text-sm font-medium">Sistem Informasi Penilaian Kinerja Aparatur</p>
            <p class="text-amber-400/80 text-[11px] font-bold uppercase tracking-widest mt-1">BPS Provinsi Sulawesi Tengah</p>
        </div>

        <form wire:submit.prevent="login" class="space-y-6" x-data="{ showPw: false }">
            <div>
                <label for="username" class="block text-sm font-bold text-white mb-2 uppercase tracking-wide">NIP atau Username</label>
                <div class="relative group">
                    <input
                        wire:model="username"
                        wire:loading.attr="disabled"
                        type="text"
                        id="username"
                        class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-white placeholder-white/30 focus:outline-none focus:ring-2 focus:ring-amber-500/50 transition-all group-hover:border-white/30 disabled:opacity-60 disabled:cursor-wait"
                        placeholder="Masukkan NIP atau Username"
                        autocomplete="username"
                    >
                </div>
                @error('username') <span class="text-red-300 text-xs mt-2 block font-medium italic">{{ $message }}</span> @enderror
            </div>

            <div>
                <div class="flex items-center justify-between mb-2">
                    <label for="password" class="block text-sm font-bold text-white uppercase tracking-wide">Password</label>
                    <button
                        type="button"
                        x-on:click="$dispatch('open-lupa-password')"
                        class="text-[10px] font-black text-amber-400/80 hover:text-amber-400 uppercase tracking-widest transition-colors"
                    >Lupa Password?</button>
                </div>
                <div class="relative group">
                    <input
                        wire:model="password"
                        wire:loading.attr="disabled"
                        :type="showPw ? 'text' : 'password'"
                        id="password"
                        class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 pr-14 text-white placeholder-white/30 focus:outline-none focus:ring-2 focus:ring-amber-500/50 transition-all group-hover:border-white/30 disabled:opacity-60 disabled:cursor-wait"
                        placeholder="••••••••"
                        autocomplete="current-password"
                    >
                    <button type="button" @click="showPw = !showPw" class="absolute right-4 top-1/2 -translate-y-1/2 w-7 h-7 flex items-center justify-center text-white/30 hover:text-white/60 transition-colors">
                        <svg x-show="!showPw" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg x-show="showPw" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" x2="23" y1="1" y2="23"/></svg>
                    </button>
                </div>
                @error('password') <span class="text-red-300 text-xs mt-2 block font-medium italic">{{ $message }}</span> @enderror
            </div>

            <button
                type="submit"
                wire:loading.attr="disabled"
                class="w-full bg-amber-500 hover:bg-amber-400 text-[#003366] font-black py-4 px-6 rounded-2xl shadow-lg transform transition-all hover:scale-[1.02] active:scale-[0.98] uppercase tracking-widest text-sm disabled:opacity-75 disabled:cursor-wait disabled:scale-100 disabled:hover:bg-amber-500"
            >
                <span wire:loading.remove class="flex items-center justify-center gap-2">Masuk</span>
                <span wire:loading class="flex items-center justify-center gap-2">
                    <svg class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    Memproses...
                </span>
            </button>
        </form>

        <div class="mt-8 pt-8 border-t border-white/10 text-center">
            <p class="text-blue-100/60 text-[11px] font-bold uppercase tracking-widest">BPS Provinsi Sulawesi Tengah</p>
        </div>

        <!-- Lupa Password Modal -->
        <div
            x-data="{ show: false }"
            x-on:open-lupa-password.window="show = true"
            x-show="show"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            class="fixed inset-0 z-[300] flex items-center justify-center p-4"
            style="display: none;"
        >
            <div class="absolute inset-0 bg-slate-900/70 backdrop-blur-sm" @click="show = false"></div>
            <div
                x-show="show"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                class="relative bg-white w-full max-w-sm rounded-3xl shadow-2xl p-8 border-t-4 border-amber-400"
            >
                <div class="w-14 h-14 bg-amber-50 rounded-2xl flex items-center justify-center mb-5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-amber-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                </div>
                <h4 class="text-lg font-black text-slate-800 uppercase italic mb-2">Lupa Password?</h4>
                <p class="text-sm text-slate-500 font-medium mb-3">Sistem tidak mendukung reset via email secara otomatis.</p>
                <p class="text-sm text-slate-600 font-bold mb-6">Hubungi <span class="text-minimal-indigo">Administrator</span> untuk mereset password Anda. Admin dapat mereset password melalui menu <span class="font-black">Manajemen Pegawai</span>.</p>
                <button @click="show = false" class="w-full py-3 bg-slate-900 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-800 transition-all active:scale-95">Mengerti</button>
            </div>
        </div>
    </div>
</div>
