<div class="min-h-screen flex items-center justify-center bg-[#003366] relative overflow-hidden">
    <!-- Background accents -->
    <div class="absolute -top-24 -left-24 w-96 h-96 bg-blue-500/20 rounded-full blur-3xl opacity-50"></div>
    <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-amber-400/10 rounded-full blur-3xl opacity-50"></div>

    <div class="w-full max-w-md p-8 bg-white/10 backdrop-blur-xl border border-white/20 rounded-3xl shadow-2xl z-10 mx-4">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-black text-white tracking-tight mb-1 uppercase italic">PAKAR</h1>
            <p class="text-blue-100/70 text-sm font-medium">Penilaian Kinerja Aparatur</p>
            <p class="text-amber-400/80 text-[11px] font-bold uppercase tracking-widest mt-1">BPS Provinsi Sulawesi Tengah</p>
        </div>

        <form wire:submit.prevent="login" class="space-y-6">
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
                <label for="password" class="block text-sm font-bold text-white mb-2 uppercase tracking-wide">Password</label>
                <div class="relative group">
                    <input
                        wire:model="password"
                        wire:loading.attr="disabled"
                        type="password"
                        id="password"
                        class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-white placeholder-white/30 focus:outline-none focus:ring-2 focus:ring-amber-500/50 transition-all group-hover:border-white/30 disabled:opacity-60 disabled:cursor-wait"
                        placeholder="••••••••"
                        autocomplete="current-password"
                    >
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
    </div>
</div>
