<div class="min-h-screen flex items-center justify-center bg-[#003366] relative overflow-hidden">
    <!-- Background accents -->
    <div class="absolute -top-24 -left-24 w-96 h-96 bg-blue-500/20 rounded-full blur-3xl opacity-50"></div>
    <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-amber-400/10 rounded-full blur-3xl opacity-50"></div>
    
    <div class="w-full max-w-md p-8 bg-white/10 backdrop-blur-xl border border-white/20 rounded-3xl shadow-2xl z-10 mx-4">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-black text-white tracking-tight mb-2 uppercase italic">SIPEKERJA</h1>
            <p class="text-blue-100/70 text-sm font-medium">Sistem Penilaian Kinerja Bulanan BPS</p>
        </div>

        <form wire:submit.prevent="login" class="space-y-6">
            <div>
                <label for="username" class="block text-sm font-bold text-white mb-2 uppercase tracking-wide">NIP atau Username</label>
                <div class="relative group">
                    <input 
                        wire:model="username" 
                        type="text" 
                        id="username" 
                        class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-white placeholder-white/30 focus:outline-none focus:ring-2 focus:ring-amber-500/50 transition-all group-hover:border-white/30"
                        placeholder="Masukkan NIP atau Username"
                    >
                </div>
                @error('username') <span class="text-red-300 text-xs mt-2 block font-medium italic">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-bold text-white mb-2 uppercase tracking-wide">Password</label>
                <div class="relative group">
                    <input 
                        wire:model="password" 
                        type="password" 
                        id="password" 
                        class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-white placeholder-white/30 focus:outline-none focus:ring-2 focus:ring-amber-500/50 transition-all group-hover:border-white/30"
                        placeholder="••••••••"
                    >
                </div>
                @error('password') <span class="text-red-300 text-xs mt-2 block font-medium italic">{{ $message }}</span> @enderror
            </div>

            <button 
                type="submit" 
                class="w-full bg-amber-500 hover:bg-amber-400 text-[#003366] font-black py-4 px-6 rounded-2xl shadow-lg transform transition-all hover:scale-[1.02] active:scale-[0.98] uppercase tracking-widest text-sm"
            >
                Masuk ke Dashboard
            </button>
        </form>

        <div class="mt-8 pt-8 border-t border-white/10 text-center">
            <p class="text-blue-100/50 text-xs font-medium uppercase tracking-widest">Badan Pusat Statistik Provinsi</p>
        </div>
    </div>
</div>
