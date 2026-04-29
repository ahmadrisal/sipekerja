<div
    x-data="{
        showCurrent: false,
        showNew: false,
        showConfirm: false,
    }"
    @if(!$isOpen) style="display:none;" @endif
    class="fixed inset-0 z-[300] flex items-center justify-center p-4"
>
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="close"></div>

    <div class="relative bg-white w-full max-w-md rounded-3xl shadow-2xl border-t-4 border-minimal-indigo animate-in zoom-in-95 duration-200">

        <!-- Header -->
        <div class="px-8 pt-8 pb-5 border-b border-slate-50">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-minimal-indigo/10 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-minimal-indigo" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </div>
                    <div>
                        <h4 class="text-base font-black text-slate-800 tracking-tight">Ganti Password</h4>
                        <p class="text-[9px] font-black uppercase tracking-widest text-minimal-indigo/60">{{ Auth::user()->name }}</p>
                    </div>
                </div>
                <button wire:click="close" class="w-8 h-8 rounded-xl bg-slate-50 text-slate-300 hover:bg-red-50 hover:text-red-500 transition-all flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>
            </div>
        </div>

        <!-- State: Success -->
        @if($state === 'success')
        <div class="px-8 py-10 text-center">
            <div class="w-16 h-16 bg-emerald-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <h5 class="text-lg font-black text-slate-800 uppercase italic mb-2">Password Berhasil Diganti</h5>
            <p class="text-sm text-slate-400 font-medium mb-6">Gunakan password baru Anda untuk login berikutnya.</p>
            <button wire:click="close" class="w-full py-3 bg-emerald-500 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-emerald-500/20 hover:scale-[1.02] transition-all active:scale-95">Tutup</button>
        </div>

        @else
        <!-- Form -->
        <form wire:submit="save" class="px-8 py-6 space-y-4">

            <!-- Error banner -->
            @if($state === 'error' && $errorMessage)
            <div class="flex items-center gap-3 bg-red-50 border border-red-100 rounded-2xl px-4 py-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-red-500 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                <p class="text-[10px] font-black text-red-600">{{ $errorMessage }}</p>
            </div>
            @endif

            <!-- Password Lama -->
            <div class="space-y-1">
                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Password Lama</label>
                <div class="relative">
                    <input
                        wire:model="currentPassword"
                        :type="showCurrent ? 'text' : 'password'"
                        class="w-full bg-slate-50 border border-slate-100 rounded-xl px-4 py-3 pr-12 text-[12px] font-bold text-slate-700 focus:ring-4 focus:ring-minimal-indigo/10 focus:border-minimal-indigo/30 transition-all"
                        placeholder="Masukkan password lama"
                        autocomplete="current-password"
                    >
                    <button type="button" @click="showCurrent = !showCurrent" class="absolute right-3 top-1/2 -translate-y-1/2 w-7 h-7 flex items-center justify-center text-slate-300 hover:text-slate-500 transition-colors">
                        <svg x-show="!showCurrent" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg x-show="showCurrent" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" x2="23" y1="1" y2="23"/></svg>
                    </button>
                </div>
                @error('currentPassword') <p class="text-[8px] font-black text-red-500 uppercase tracking-widest ml-1">{{ $message }}</p> @enderror
            </div>

            <!-- Password Baru -->
            <div class="space-y-1">
                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Password Baru <span class="text-slate-300">(min. 8 karakter)</span></label>
                <div class="relative">
                    <input
                        wire:model="newPassword"
                        :type="showNew ? 'text' : 'password'"
                        class="w-full bg-slate-50 border border-slate-100 rounded-xl px-4 py-3 pr-12 text-[12px] font-bold text-slate-700 focus:ring-4 focus:ring-minimal-indigo/10 focus:border-minimal-indigo/30 transition-all"
                        placeholder="Masukkan password baru"
                        autocomplete="new-password"
                    >
                    <button type="button" @click="showNew = !showNew" class="absolute right-3 top-1/2 -translate-y-1/2 w-7 h-7 flex items-center justify-center text-slate-300 hover:text-slate-500 transition-colors">
                        <svg x-show="!showNew" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg x-show="showNew" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" x2="23" y1="1" y2="23"/></svg>
                    </button>
                </div>
                @error('newPassword') <p class="text-[8px] font-black text-red-500 uppercase tracking-widest ml-1">{{ $message }}</p> @enderror
            </div>

            <!-- Konfirmasi Password Baru -->
            <div class="space-y-1">
                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Konfirmasi Password Baru</label>
                <div class="relative">
                    <input
                        wire:model="confirmPassword"
                        :type="showConfirm ? 'text' : 'password'"
                        class="w-full bg-slate-50 border border-slate-100 rounded-xl px-4 py-3 pr-12 text-[12px] font-bold text-slate-700 focus:ring-4 focus:ring-minimal-indigo/10 focus:border-minimal-indigo/30 transition-all"
                        placeholder="Ulangi password baru"
                        autocomplete="new-password"
                    >
                    <button type="button" @click="showConfirm = !showConfirm" class="absolute right-3 top-1/2 -translate-y-1/2 w-7 h-7 flex items-center justify-center text-slate-300 hover:text-slate-500 transition-colors">
                        <svg x-show="!showConfirm" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg x-show="showConfirm" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" x2="23" y1="1" y2="23"/></svg>
                    </button>
                </div>
                @error('confirmPassword') <p class="text-[8px] font-black text-red-500 uppercase tracking-widest ml-1">{{ $message }}</p> @enderror
            </div>

            <!-- Actions -->
            <div class="flex gap-3 pt-2">
                <button type="button" wire:click="close" class="flex-1 py-3 bg-slate-50 text-slate-400 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-100 transition-all active:scale-95">Batal</button>
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="flex-[2] py-3 bg-minimal-indigo text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-xl shadow-indigo-500/20 hover:scale-[1.02] transition-all active:scale-95 disabled:opacity-70 disabled:cursor-wait flex items-center justify-center gap-2"
                >
                    <svg wire:loading wire:target="save" class="animate-spin w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    <span wire:loading.remove wire:target="save">Simpan Password</span>
                    <span wire:loading wire:target="save">Menyimpan...</span>
                </button>
            </div>
        </form>
        @endif
    </div>
</div>
