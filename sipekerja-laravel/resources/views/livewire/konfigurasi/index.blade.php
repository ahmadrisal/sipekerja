<div class="space-y-8 animate-in fade-in zoom-in-95 duration-500">

    {{-- Header --}}
    <div>
        <h2 class="text-3xl font-black text-bps-blue italic tracking-tight flex items-center gap-3 underline decoration-amber-400 decoration-8 underline-offset-8">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-bps-blue" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 1.41 13.42"/><path d="M4.93 4.93A10 10 0 0 0 3.52 18.35"/><path d="M20 12h2"/><path d="M2 12h2"/><path d="M12 2v2"/><path d="M12 20v2"/></svg>
            Konfigurasi Bobot Penilaian
        </h2>
        <p class="text-slate-400 font-bold uppercase tracking-widest text-[10px] mt-4">Sesuaikan bobot dan skor tiap kategori penilaian kinerja pegawai.</p>
    </div>

    {{-- Alert Success --}}
    @if($showSuccess)
        <div class="flex items-center gap-4 p-5 bg-emerald-50 border border-emerald-100 rounded-2xl animate-in fade-in duration-300">
            <div class="w-8 h-8 bg-emerald-500 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <p class="text-sm font-black text-emerald-700 uppercase tracking-tight">Konfigurasi berhasil disimpan!</p>
        </div>
    @endif

    {{-- Alert Error --}}
    @if($errorMessage)
        <div class="flex items-center gap-4 p-5 bg-red-50 border border-red-100 rounded-2xl animate-in fade-in duration-300">
            <div class="w-8 h-8 bg-red-500 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </div>
            <p class="text-sm font-black text-red-700 uppercase tracking-tight">{{ $errorMessage }}</p>
        </div>
    @endif

    {{-- Formula Preview Card --}}
    <div class="bg-gradient-to-br from-bps-blue to-blue-700 rounded-[2.5rem] p-8 text-white shadow-xl shadow-blue-900/20">
        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-blue-200 mb-4">Formula Nilai Akhir</p>
        <div class="flex flex-wrap items-center gap-3 font-black text-lg">
            <span class="bg-white/10 px-4 py-2 rounded-xl border border-white/10">
                Nilai Dasar × <span class="text-amber-300">{{ $bobot['weight_score'] }}%</span>
            </span>
            <span class="text-blue-300">+</span>
            <span class="bg-white/10 px-4 py-2 rounded-xl border border-white/10">
                Skor Volume × <span class="text-amber-300">{{ $bobot['weight_volume'] }}%</span>
            </span>
            <span class="text-blue-300">+</span>
            <span class="bg-white/10 px-4 py-2 rounded-xl border border-white/10">
                Skor Kualitas × <span class="text-amber-300">{{ $bobot['weight_quality'] }}%</span>
            </span>
        </div>
        @php $total = array_sum($bobot); @endphp
        <div class="mt-5 flex items-center gap-2">
            <div class="flex-1 bg-white/10 rounded-full h-2">
                <div class="bg-amber-400 h-2 rounded-full transition-all duration-500" style="width: {{ min($total, 100) }}%"></div>
            </div>
            <span class="text-xs font-black {{ abs($total - 100) < 0.01 ? 'text-emerald-300' : 'text-red-300' }} uppercase tracking-widest">
                Total: {{ $total }}%
                @if(abs($total - 100) < 0.01) ✓ @else ✗ @endif
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Bobot Section --}}
        <div class="bg-white rounded-[2.5rem] border border-slate-200/60 shadow-sm overflow-hidden">
            <div class="bg-slate-50/80 px-8 py-5 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-bps-blue rounded-xl flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-black text-bps-blue uppercase tracking-widest">Bobot Nilai</p>
                        <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">Harus total 100%</p>
                    </div>
                </div>
            </div>
            <div class="p-8 space-y-6">
                @foreach([
                    'weight_score'   => 'Nilai Dasar',
                    'weight_volume'  => 'Volume/Kesulitan',
                    'weight_quality' => 'Kualitas Kerja',
                ] as $key => $label)
                    <div>
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest block mb-2">{{ $label }}</label>
                        <div class="flex items-center gap-3">
                            <input
                                wire:model.live="bobot.{{ $key }}"
                                type="number"
                                min="0"
                                max="100"
                                step="0.01"
                                class="flex-1 bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-black text-bps-blue focus:ring-4 focus:ring-bps-blue/10 focus:border-bps-blue transition-all text-center"
                            >
                            <span class="text-sm font-black text-slate-400">%</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Volume Section --}}
        <div class="bg-white rounded-[2.5rem] border border-slate-200/60 shadow-sm overflow-hidden">
            <div class="bg-slate-50/80 px-8 py-5 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-amber-500 rounded-xl flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-black text-amber-600 uppercase tracking-widest">Skor Volume/Kesulitan</p>
                        <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">Nilai per kategori (0–100)</p>
                    </div>
                </div>
            </div>
            <div class="p-8 space-y-6">
                @foreach([
                    'volume_ringan' => 'Ringan',
                    'volume_sedang' => 'Sedang',
                    'volume_berat'  => 'Berat',
                ] as $key => $label)
                    <div>
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest block mb-2">{{ $label }}</label>
                        <input
                            wire:model.live="volume.{{ $key }}"
                            type="number"
                            min="0"
                            max="100"
                            step="0.01"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-black text-amber-600 focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 transition-all text-center"
                        >
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Kualitas Section --}}
        <div class="bg-white rounded-[2.5rem] border border-slate-200/60 shadow-sm overflow-hidden">
            <div class="bg-slate-50/80 px-8 py-5 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-emerald-500 rounded-xl flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-black text-emerald-600 uppercase tracking-widest">Skor Kualitas Kerja</p>
                        <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">Nilai per kategori (0–100)</p>
                    </div>
                </div>
            </div>
            <div class="p-8 space-y-6">
                @foreach([
                    'quality_kurang'      => 'Kurang',
                    'quality_cukup'       => 'Cukup',
                    'quality_baik'        => 'Baik',
                    'quality_sangat_baik' => 'Sangat Baik',
                ] as $key => $label)
                    <div>
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest block mb-2">{{ $label }}</label>
                        <input
                            wire:model.live="kualitas.{{ $key }}"
                            type="number"
                            min="0"
                            max="100"
                            step="0.01"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-black text-emerald-600 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all text-center"
                        >
                    </div>
                @endforeach
            </div>
        </div>

    </div>

    {{-- Action Buttons --}}
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-2">
        <div class="flex items-center gap-3">
            @if($hasLocalOverride)
            <span class="inline-flex px-2.5 py-1 rounded-full text-[8px] font-black uppercase tracking-widest bg-amber-50 text-amber-700 border border-amber-200">Override Lokal Aktif</span>
            <button
                wire:click="resetToGlobal"
                wire:confirm="Reset ke bobot global (dari Super Admin)? Override lokal akan dihapus."
                class="px-5 py-2.5 rounded-2xl text-xs font-black uppercase tracking-widest border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 hover:text-slate-700 transition-all active:scale-95"
            >
                Reset ke Global
            </button>
            @else
            <span class="inline-flex px-2.5 py-1 rounded-full text-[8px] font-black uppercase tracking-widest bg-emerald-50 text-emerald-700 border border-emerald-200">Pakai Bobot Global</span>
            @endif
        </div>
        <button
            wire:click="save"
            wire:loading.attr="disabled"
            class="px-12 py-3.5 rounded-2xl text-xs font-black uppercase tracking-widest bg-bps-blue text-white shadow-xl shadow-blue-900/20 hover:bg-blue-900 transition-all active:scale-95 disabled:opacity-50"
        >
            <span wire:loading.remove wire:target="save">Simpan Konfigurasi</span>
            <span wire:loading wire:target="save">Menyimpan...</span>
        </button>
    </div>

    {{-- Info Box --}}
    <div class="bg-blue-50 border border-blue-100 rounded-2xl p-6 flex gap-4">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-bps-blue flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
        <div>
            <p class="text-xs font-black text-bps-blue uppercase tracking-widest mb-1">Informasi</p>
            <p class="text-xs text-blue-700 leading-relaxed">Setiap kali konfigurasi disimpan, <span class="font-black">seluruh nilai akhir penilaian yang sudah ada</span> akan otomatis dihitung ulang menggunakan bobot terbaru — termasuk penilaian bulan-bulan sebelumnya.</p>
        </div>
    </div>

</div>
