<div class="font-outfit space-y-6 pb-24 md:pb-12"
    x-data="{
        activeTab: '{{ $activeTab }}',
        init() {
            this.$watch('activeTab', value => {
                if (value === 'dashboard') {
                    setTimeout(() => { if (typeof window.initChart === 'function') window.initChart(); }, 50);
                }
            });
        }
    }"
    x-cloak>
    <!-- Header Area & Tab Navigation -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-800 tracking-tight italic">Ketua Tim Dashboard</h2>
            <p class="text-slate-400 text-[11px] font-medium">Monitoring progres penilaian dan capaian performa anggota tim.</p>
        </div>
        {{-- Tab di header: hanya tampil di desktop (md+) --}}
        <div class="hidden md:flex gap-1.5 p-1 bg-white rounded-xl shadow-sm border border-slate-100">
            <button @click="activeTab = 'dashboard'" class="px-5 py-2.5 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all" :class="activeTab === 'dashboard' ? 'bg-minimal-indigo text-white shadow-md' : 'text-slate-400 hover:bg-slate-50'">
                Dashboard
            </button>
            <button @click="activeTab = 'input'" class="px-5 py-2.5 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all" :class="activeTab === 'input' ? 'bg-minimal-indigo text-white shadow-md' : 'text-slate-400 hover:bg-slate-50'">
                Input Nilai
            </button>
            <button @click="activeTab = 'kabkot'" class="px-5 py-2.5 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all" :class="activeTab === 'kabkot' ? 'bg-minimal-indigo text-white shadow-md' : 'text-slate-400 hover:bg-slate-50'">
                Penilaian Kabkot
            </button>
        </div>
    </div>

    <!-- Global Period Filter -->
    <div class="bg-white p-4 rounded-[1.5rem] shadow-sm border border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-minimal-indigo/5 text-minimal-indigo flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
            </div>
            <div>
                <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">Periode Acuan</p>
                <p class="text-xs font-bold text-slate-700 leading-none mt-0.5">Bulan & Tahun Penilaian</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <select wire:model.live="month" class="h-10 px-4 rounded-xl border border-slate-100 bg-slate-50 text-[10px] font-black uppercase tracking-wider text-slate-700 focus:ring-4 focus:ring-minimal-indigo/10 transition-all cursor-pointer">
                @foreach($monthNames as $num => $name) <option value="{{ $num }}">{{ $name }}</option> @endforeach
            </select>
            <select wire:model.live="year" class="h-10 px-4 rounded-xl border border-slate-100 bg-slate-50 text-[10px] font-black uppercase tracking-wider text-slate-700 focus:ring-4 focus:ring-minimal-indigo/10 transition-all cursor-pointer">
                @foreach(range(date('Y')-2, date('Y')) as $y) <option value="{{ $y }}">{{ $y }}</option> @endforeach
            </select>
        </div>
    </div>

    <div x-show="activeTab === 'dashboard'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
        <div class="space-y-6">
            <!-- Metric Cards -->
            @php
                $ketuaCards = [
                    ['label' => 'Tim Dipimpin', 'sub' => 'Scope Kendali', 'val' => $stats['teamCount'], 'icon' => 'layers', 'color' => 'blue', 'dialog' => 'showTeamsDialog'],
                    ['label' => 'Total Anggota', 'sub' => 'SDM Managed', 'val' => $stats['uniqueMemberCount'], 'icon' => 'users', 'color' => 'emerald', 'dialog' => 'showMembersDialog'],
                ];
                $progressDashboard = $stats['uniqueMemberCount'] > 0 ? round(($stats['ratedCount'] / $stats['uniqueMemberCount']) * 100, 1) : 0;
            @endphp
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($ketuaCards as $c)
                    <div 
                        wire:click="$set('{{ $c['dialog'] }}', true)"
                        class="bg-white p-5 rounded-[1.5rem] shadow-sm border border-slate-100 flex items-center justify-between group hover:border-minimal-indigo/30 hover:shadow-md transition-all cursor-pointer relative overflow-hidden h-full"
                    >
                        <div class="absolute top-0 right-0 w-12 h-12 bg-[var(--tw-colors-{{ $c['color'] }}-500)]/5 rounded-bl-[1.5rem]"></div>
                        <div class="flex items-center gap-3 relative z-10">
                            <div class="w-11 h-11 rounded-xl bg-slate-50 text-{{ $c['color'] === 'blue' ? 'minimal-indigo' : 'emerald-600' }} flex items-center justify-center shadow-inner">
                                @if($c['icon'] === 'layers') <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>
                                @else <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg> @endif
                            </div>
                            <div>
                                <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 group-hover:text-minimal-indigo transition-colors">{{ $c['label'] }}</p>
                                <p class="text-[10px] font-bold text-slate-500 leading-none mt-0.5">{{ $c['sub'] }}</p>
                            </div>
                        </div>
                        <p class="text-3xl font-black text-slate-800 relative z-10 tracking-tighter">{{ $c['val'] }}</p>
                    </div>
                @endforeach

                <!-- Assessment Progress Card -->
                <div
                    @if($stats['unratedCount'] > 0)
                        wire:click="$set('showUnratedDialog', true)"
                    @else
                        @click="activeTab = 'input'"
                    @endif
                    class="bg-white p-5 rounded-[1.5rem] shadow-sm border border-slate-100 flex items-center justify-between group hover:border-minimal-indigo/30 hover:shadow-md transition-all cursor-pointer relative overflow-hidden h-full"
                >
                    <div class="absolute top-0 right-0 w-12 h-12 bg-amber-500/5 rounded-bl-[1.5rem]"></div>
                    <div class="flex items-center gap-3 relative z-10">
                        <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shadow-inner">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        </div>
                        <div>
                            <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">Progres Penilaian</p>
                            <p class="text-[10px] font-bold text-slate-500 leading-none mt-0.5">{{ $stats['ratedCount'] }}/{{ $stats['uniqueMemberCount'] }} Done</p>
                        </div>
                    </div>
                    <p class="text-3xl font-black text-minimal-indigo italic leading-none relative z-10">{{ $progressDashboard }}%</p>
                </div>
            </div>

            <!-- Charts & Analytics Board -->
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                <!-- Average Score Bar Chart -->
                <div class="xl:col-span-2 bg-white rounded-[2rem] p-8 shadow-sm border border-slate-100">
                    @if(count($stats['teamChartData']) > 0)
                        <div class="mb-6 px-1">
                            <h3 class="text-lg font-black text-slate-800 tracking-tight">Performa Rerata Tim</h3>
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">Capaian Nilai Anggota Per Tim ({{ $monthNames[$month] }})</p>
                        </div>
                        <div id="ketuaBarChart" class="h-80 w-full" wire:ignore></div>
                        <div class="flex items-center justify-center gap-6 mt-6 text-[8px] font-black uppercase tracking-widest text-slate-400">
                            <div class="flex items-center gap-1.5"><div class="w-2 h-2 rounded-full bg-emerald-500"></div> ≥ 80 (Baik)</div>
                            <div class="flex items-center gap-1.5"><div class="w-2 h-2 rounded-full bg-amber-500"></div> 60 - 79 (Cukup)</div>
                            <div class="flex items-center gap-1.5"><div class="w-2 h-2 rounded-full bg-rose-500"></div> < 60 (Kurang)</div>
                        </div>
                    @else
                        <div class="h-full flex flex-col items-center justify-center text-center py-12">
                            <div class="w-16 h-16 bg-slate-50 text-slate-300 rounded-2xl flex items-center justify-center mb-5 shadow-inner">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                            </div>
                            <h3 class="text-lg font-black text-slate-800 tracking-tight">Belum Ada Data Penilaian</h3>
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-2 leading-relaxed">Harap lakukan Input Nilai untuk anggota tim Anda<br>pada periode terpilih terlebih dahulu.</p>
                        </div>
                    @endif
                </div>

                <!-- Pending Assignment / Unrated Action Card -->
                <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-100 relative overflow-hidden group flex flex-col">
                    <div class="absolute -right-12 -top-12 w-48 h-48 bg-minimal-indigo/5 rounded-full blur-3xl group-hover:bg-minimal-indigo/10 transition-all"></div>
                    
                    <div class="mb-6 relative z-10">
                        <h3 class="text-sm font-black text-slate-800 tracking-tight">Status Penilaian</h3>
                        <p class="text-[9px] font-black uppercase tracking-widest text-minimal-indigo opacity-60">Pending Assessment</p>
                    </div>

                    <div class="flex-1 space-y-3 relative z-10 custom-scrollbar overflow-y-auto max-h-64 pr-1">
                        @forelse($stats['unratedMembers'] as $m)
                            <div class="p-4 rounded-2xl bg-slate-50/50 border border-slate-50 hover:bg-white hover:shadow-sm transition-all border-l-4 border-l-amber-200">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="text-[11px] font-black text-slate-700 leading-tight">{{ $m->name }}</p>
                                        <p class="text-[8px] font-mono font-bold text-slate-400 uppercase">NIP: {{ $m->nip }}</p>
                                    </div>
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                                </div>
                            </div>
                        @empty
                            <div class="py-12 text-center">
                                <div class="w-16 h-16 bg-emerald-50 text-emerald-500 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-inner">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M20 6 9 17l-5-5"/></svg>
                                </div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic leading-relaxed">Semua anggota tim telah<br>dinilai bulan ini. ✓</p>
                            </div>
                        @endforelse
                    </div>

                    <button @click="activeTab = 'input'" class="mt-6 w-full py-4 rounded-2xl bg-slate-900 shadow-xl text-white text-[10px] font-black uppercase tracking-widest hover:bg-minimal-indigo transition-all active:scale-95 flex items-center justify-center gap-3 relative z-10">
                        Assess Performance
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12h14l-4-4m0 8l4-4"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div x-show="activeTab === 'input'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
        <div class="space-y-6">
            @php
                $totalTargets = count($formState);
                $ratedTargets = collect($formState)->where('is_rated', true)->count();
                $progress = $totalTargets > 0 ? ($ratedTargets / $totalTargets) * 100 : 0;
            @endphp
            <div class="bg-white p-6 rounded-[2.5rem] border border-slate-100 shadow-sm relative overflow-hidden group">
                <div class="flex items-center justify-between mb-4">
                    <p class="text-xs font-black text-slate-400 uppercase tracking-widest">
                        Progres Penilaian Tim <span class="text-minimal-indigo italic underline decoration-amber-400 decoration-2 underline-offset-4">{{ $monthNames[$month] }} {{ $year }}</span>
                    </p>
                    <p class="text-sm font-black text-minimal-indigo italic">
                        {{ $ratedTargets }} / {{ $totalTargets }} <span class="text-[10px] text-slate-400 not-italic font-bold uppercase ml-1">target telah dinilai</span>
                    </p>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden border border-slate-200/50">
                    <div
                        class="bg-minimal-indigo h-full rounded-full transition-all duration-1000 ease-out shadow-inner"
                        style="width: {{ $progress }}%"
                    ></div>
                </div>
            </div>

            @if(count($membersData) === 0)
                <div class="bg-white py-20 rounded-[3rem] border-2 border-dashed border-slate-200 text-center">
                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                    </div>
                    <p class="text-xl font-black text-slate-400 uppercase italic">Tidak Ada Anggota Tim</p>
                    <p class="text-xs text-slate-300 font-bold uppercase tracking-widest mt-1">Anda belum memimpin tim atau belum ada anggota yang terdaftar.</p>
                </div>
            @else
                @php $sc = $scoringConfig; @endphp

                {{-- ===== TAMPILAN MOBILE (kartu, < md) ===== --}}
                <div class="block md:hidden space-y-3">
                    @foreach($membersData as $mId => $member)
                        @foreach($member['teams'] as $index => $team)
                            @php $key = "{$mId}_{$team->id}"; @endphp
                            <div
                                x-data="{
                                    hasWork: @entangle('formState.' . $key . '.has_work'),
                                    score: @entangle('formState.' . $key . '.score'),
                                    volume: @entangle('formState.' . $key . '.volume_work'),
                                    quality: @entangle('formState.' . $key . '.quality_work'),
                                    isDirty: @entangle('formState.' . $key . '.is_dirty'),
                                    isRated: @entangle('formState.' . $key . '.is_rated'),
                                    overridden: @entangle('formState.' . $key . '.overridden'),
                                    cfg: @js($sc),
                                    get finalScore() {
                                        if (!this.hasWork) return 'N/A';
                                        if (!this.score) return '-';
                                        let v = this.volume === 'Berat' ? this.cfg.volume_berat : (this.volume === 'Ringan' ? this.cfg.volume_ringan : this.cfg.volume_sedang);
                                        let q = this.quality === 'Sangat Baik' ? this.cfg.quality_sangat_baik : (this.quality === 'Baik' ? this.cfg.quality_baik : (this.quality === 'Kurang' ? this.cfg.quality_kurang : this.cfg.quality_cukup));
                                        return ((this.score * this.cfg.weight_score / 100) + (v * this.cfg.weight_volume / 100) + (q * this.cfg.weight_quality / 100)).toFixed(2);
                                    }
                                }"
                                class="bg-white rounded-2xl shadow-sm overflow-hidden border-l-4 transition-all"
                                :class="overridden ? 'border border-amber-200 border-l-amber-400' : (!isRated ? 'border border-rose-200 border-l-rose-400' : 'border border-emerald-100 border-l-emerald-400')"
                            >
                                {{-- Header kartu: nama + nilai akhir --}}
                                <div class="flex items-stretch border-b border-slate-100">
                                    <div class="flex-1 px-4 py-3 bg-slate-50/50">
                                        <p class="text-sm font-black text-slate-800 uppercase tracking-tight leading-none">{{ $member['name'] }}</p>
                                        <p class="text-[9px] font-mono font-bold text-slate-400 mt-1">NIP: {{ $member['nip'] }}</p>
                                        <span class="inline-block mt-1.5 text-[9px] font-black text-minimal-indigo uppercase italic tracking-tight px-2 py-0.5 bg-indigo-50 rounded-md">{{ $team->team_name }}</span>
                                        <template x-if="overridden">
                                            <span class="inline-flex items-center gap-1 ml-1 mt-1.5 text-[8px] font-black uppercase px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                                Diubah Pimpinan
                                            </span>
                                        </template>
                                        <span
                                            x-show="!overridden"
                                            class="inline-block ml-1 mt-1.5 text-[9px] font-black uppercase px-2 py-0.5 rounded-full"
                                            :class="isRated ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-500'"
                                            x-text="isRated ? '✓ Dinilai' : '○ Belum'"
                                        ></span>
                                    </div>
                                    {{-- Nilai Akhir Badge --}}
                                    <div class="flex flex-col items-center justify-center px-5 bg-gradient-to-br from-slate-800 to-minimal-indigo min-w-[80px]">
                                        <span
                                            x-text="finalScore"
                                            class="text-2xl font-black italic tracking-tighter leading-none"
                                            :class="(score || !hasWork) ? 'text-white' : 'text-white/30'"
                                        ></span>
                                        <span class="text-[8px] font-black uppercase tracking-widest text-indigo-200 mt-1">Nilai Akhir</span>
                                    </div>
                                </div>

                                {{-- Ada Pekerjaan --}}
                                <div class="px-4 py-3 flex items-center justify-between border-b border-slate-50">
                                    <span class="text-xs font-black text-slate-600 uppercase tracking-wide">Ada Pekerjaan?</span>
                                    <input
                                        x-model="hasWork"
                                        @change="isDirty = true"
                                        type="checkbox"
                                        class="w-6 h-6 text-minimal-indigo bg-white border-slate-300 rounded focus:ring-minimal-indigo focus:ring-2 cursor-pointer transition-all"
                                    >
                                </div>

                                {{-- Nilai Kinerja --}}
                                <div class="px-4 py-3 flex items-center justify-between border-b border-slate-50 transition-opacity" :class="hasWork ? 'opacity-100' : 'opacity-30 pointer-events-none'">
                                    <span class="text-xs font-black text-slate-600 uppercase tracking-wide">Nilai Kinerja</span>
                                    <input
                                        x-model="score"
                                        @input="isDirty = true"
                                        type="number"
                                        class="w-20 bg-slate-50 border border-slate-200 rounded-lg px-2 py-1.5 text-center text-sm font-black text-minimal-indigo focus:ring-4 focus:ring-minimal-indigo/10 focus:border-minimal-indigo transition-all shadow-inner"
                                        placeholder="--"
                                    >
                                </div>

                                {{-- Volume/Kesulitan --}}
                                <div class="px-4 py-3 border-b border-slate-50 transition-opacity" :class="hasWork ? 'opacity-100' : 'opacity-30 pointer-events-none'">
                                    <p class="text-xs font-black text-slate-600 uppercase tracking-wide mb-2">Volume / Kesulitan</p>
                                    <div class="flex gap-2">
                                        @foreach(['Ringan', 'Sedang', 'Berat'] as $v)
                                            <button
                                                @click="volume = '{{ $v }}'; isDirty = true"
                                                class="flex-1 py-2 rounded-lg text-[10px] font-black uppercase tracking-tighter transition-all border"
                                                :class="volume === '{{ $v }}' ? 'bg-minimal-indigo border-minimal-indigo text-white shadow-sm' : 'bg-slate-50 border-slate-200 text-slate-400'"
                                            >{{ $v }}</button>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Kualitas Kerja --}}
                                <div class="px-4 py-3 border-b border-slate-50 transition-opacity" :class="hasWork ? 'opacity-100' : 'opacity-30 pointer-events-none'">
                                    <p class="text-xs font-black text-slate-600 uppercase tracking-wide mb-2">Kualitas Kerja</p>
                                    <div class="grid grid-cols-2 gap-2">
                                        @foreach(['Kurang', 'Cukup', 'Baik', 'Sangat Baik'] as $q)
                                            <button
                                                @click="quality = '{{ $q }}'; isDirty = true"
                                                class="py-2 rounded-lg text-[10px] font-black uppercase tracking-tighter transition-all border"
                                                :class="quality === '{{ $q }}' ? 'bg-minimal-indigo border-minimal-indigo text-white shadow-sm' : 'bg-slate-50 border-slate-200 text-slate-400'"
                                            >{{ $q }}</button>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Aksi --}}
                                <div class="px-4 py-3 flex gap-3">
                                    <button
                                        wire:click="saveRating('{{ $key }}')"
                                        wire:loading.attr="disabled"
                                        class="flex-1 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all active:scale-95 disabled:opacity-50"
                                        :class="isDirty ? 'bg-slate-900 text-white shadow-md' : (isRated ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-slate-100 text-slate-400 cursor-not-allowed')"
                                        :disabled="!isDirty && !isRated"
                                    >
                                        <span wire:loading.remove wire:target="saveRating('{{ $key }}')">
                                            <template x-if="isRated && !isDirty"><span>✓ Tersimpan</span></template>
                                            <template x-if="!isRated || isDirty"><span>Simpan Nilai</span></template>
                                        </span>
                                        <span wire:loading wire:target="saveRating('{{ $key }}')">Menyimpan...</span>
                                    </button>
                                    @if($formState[$key]['is_rated'])
                                        <button
                                            wire:click="confirmReset('{{ $key }}')"
                                            wire:loading.attr="disabled"
                                            class="px-5 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest bg-rose-50 text-rose-500 hover:bg-rose-100 border border-rose-100 transition-all active:scale-95"
                                        >
                                            <span wire:loading.remove wire:target="confirmReset('{{ $key }}')">Reset</span>
                                            <span wire:loading wire:target="confirmReset('{{ $key }}')">...</span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @endforeach
                </div>

                {{-- ===== TAMPILAN DESKTOP (tabel, md+) ===== --}}
                <div class="hidden md:block bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50/80 sticky top-0 z-10">
                                <tr class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                                    <th class="px-5 py-4 border-b border-slate-100 w-1/5">Pegawai</th>
                                    <th class="px-5 py-4 border-b border-slate-100 w-1/4">Tim Kerja</th>
                                    <th class="px-4 py-4 border-b border-slate-100 text-center">Ada<br>Pekerjaan?</th>
                                    <th class="px-4 py-4 border-b border-slate-100 text-center">Nilai Kinerja</th>
                                    <th class="px-4 py-4 border-b border-slate-100">Volume/Kesulitan</th>
                                    <th class="px-4 py-4 border-b border-slate-100">Kualitas Kerja</th>
                                    <th class="px-4 py-4 border-b border-slate-100 text-center text-minimal-indigo italic underline decoration-minimal-indigo/30 decoration-dashed underline-offset-4">Nilai Akhir</th>
                                    <th class="px-5 py-4 border-b border-slate-100 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach($membersData as $mId => $member)
                                    @foreach($member['teams'] as $index => $team)
                                        @php $key = "{$mId}_{$team->id}"; @endphp
                                        <tr
                                            x-data="{
                                                hasWork: @entangle('formState.' . $key . '.has_work'),
                                                score: @entangle('formState.' . $key . '.score'),
                                                volume: @entangle('formState.' . $key . '.volume_work'),
                                                quality: @entangle('formState.' . $key . '.quality_work'),
                                                isDirty: @entangle('formState.' . $key . '.is_dirty'),
                                                isRated: @entangle('formState.' . $key . '.is_rated'),
                                                overridden: @entangle('formState.' . $key . '.overridden'),
                                                cfg: @js($sc),
                                                get finalScore() {
                                                    if (!this.hasWork) return 'N/A';
                                                    if (!this.score) return '-';
                                                    let v = this.volume === 'Berat' ? this.cfg.volume_berat : (this.volume === 'Ringan' ? this.cfg.volume_ringan : this.cfg.volume_sedang);
                                                    let q = this.quality === 'Sangat Baik' ? this.cfg.quality_sangat_baik : (this.quality === 'Baik' ? this.cfg.quality_baik : (this.quality === 'Kurang' ? this.cfg.quality_kurang : this.cfg.quality_cukup));
                                                    return ((this.score * this.cfg.weight_score / 100) + (v * this.cfg.weight_volume / 100) + (q * this.cfg.weight_quality / 100)).toFixed(2);
                                                }
                                            }"
                                            class="group transition-colors"
                                            :class="overridden ? 'bg-amber-50/50' : (!isRated ? 'bg-rose-100/50' : 'hover:bg-slate-50/50')"
                                        >
                                            @if($index === 0)
                                                <td rowspan="{{ count($member['teams']) }}" class="px-4 py-3 align-top border-r border-slate-50 bg-white shadow-[1px_0_0_0_rgba(241,245,249,1)]">
                                                    <div class="flex flex-col gap-0.5">
                                                        <p class="text-[11px] font-black text-slate-800 uppercase tracking-tight leading-none">{{ $member['name'] }}</p>
                                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-1">NIP: {{ $member['nip'] }}</p>
                                                        @php
                                                            $allRated = true;
                                                            foreach($member['teams'] as $t) {
                                                                if (!$formState["{$mId}_{$t->id}"]['is_rated']) $allRated = false;
                                                            }
                                                        @endphp
                                                        @if(!$allRated)
                                                            <span class="mt-3 px-3 py-1 bg-rose-100 text-rose-700 text-[9px] font-black rounded-full uppercase tracking-tighter w-fit flex items-center gap-1">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                                                                Belum Lengkap
                                                            </span>
                                                        @endif
                                                    </div>
                                                </td>
                                            @endif

                                            <td class="px-5 py-4">
                                                <p class="font-black text-minimal-indigo uppercase text-[10px] italic tracking-tight">{{ $team->team_name }}</p>
                                                <template x-if="overridden">
                                                    <span class="inline-flex items-center gap-1 mt-1 text-[8px] font-black uppercase px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                                        Diubah Pimpinan
                                                    </span>
                                                </template>
                                            </td>

                                            <td class="px-4 py-3 text-center">
                                                <input
                                                    x-model="hasWork"
                                                    @change="isDirty = true"
                                                    type="checkbox"
                                                    class="w-5 h-5 text-minimal-indigo bg-white border-slate-300 rounded focus:ring-minimal-indigo focus:ring-2 cursor-pointer transition-all"
                                                >
                                            </td>

                                            <td class="px-3 py-3 text-center transition-opacity" :class="hasWork ? 'opacity-100' : 'opacity-30 pointer-events-none'">
                                                <input
                                                    x-model="score"
                                                    @input="isDirty = true"
                                                    type="number"
                                                    class="w-16 bg-slate-50 border border-slate-200 rounded-lg px-2 py-1.5 text-center text-sm font-black text-minimal-indigo focus:ring-4 focus:ring-minimal-indigo/10 focus:border-minimal-indigo transition-all shadow-inner"
                                                    placeholder="--"
                                                >
                                            </td>

                                            <td class="px-3 py-3 transition-opacity" :class="hasWork ? 'opacity-100' : 'opacity-30 pointer-events-none'">
                                                <div class="flex flex-wrap gap-1.5 items-center">
                                                    @foreach(['Ringan', 'Sedang', 'Berat'] as $v)
                                                        <button
                                                            @click="volume = '{{ $v }}'; isDirty = true"
                                                            class="px-2.5 py-1.5 rounded-md text-[9px] font-black uppercase tracking-tighter transition-all border"
                                                            :class="volume === '{{ $v }}' ? 'bg-minimal-indigo border-minimal-indigo text-white shadow-sm shrink-0' : 'bg-slate-50 border-slate-200 text-slate-400 hover:bg-slate-100 hover:text-slate-600 shrink-0'"
                                                        >{{ $v }}</button>
                                                    @endforeach
                                                </div>
                                            </td>

                                            <td class="px-3 py-3 transition-opacity" :class="hasWork ? 'opacity-100' : 'opacity-30 pointer-events-none'">
                                                <div class="flex flex-wrap gap-1.5 items-center">
                                                    @foreach(['Kurang', 'Cukup', 'Baik', 'Sangat Baik'] as $q)
                                                        <button
                                                            @click="quality = '{{ $q }}'; isDirty = true"
                                                            class="px-2.5 py-1.5 rounded-md text-[9px] font-black uppercase tracking-tighter transition-all border"
                                                            :class="quality === '{{ $q }}' ? 'bg-minimal-indigo border-minimal-indigo text-white shadow-sm shrink-0' : 'bg-slate-50 border-slate-200 text-slate-400 hover:bg-slate-100 hover:text-slate-600 shrink-0'"
                                                        >{{ $q }}</button>
                                                    @endforeach
                                                </div>
                                            </td>

                                            {{-- Nilai Akhir --}}
                                            <td class="px-4 py-3 text-center">
                                                <div class="flex flex-col items-center gap-0.5">
                                                    <span
                                                        x-text="finalScore"
                                                        class="text-xl font-black italic tracking-tighter"
                                                        :class="finalScore === '-' || finalScore === 'N/A' ? 'text-slate-300' : 'text-minimal-indigo'"
                                                    ></span>
                                                    <span class="text-[8px] font-black uppercase tracking-widest text-slate-300">nilai akhir</span>
                                                </div>
                                            </td>

                                            <td class="px-4 py-3 text-right">
                                                <div class="flex flex-col gap-1.5 items-end">
                                                    <button
                                                        wire:click="saveRating('{{ $key }}')"
                                                        wire:loading.attr="disabled"
                                                        class="w-full min-w-[80px] relative px-2 py-1.5 rounded-lg text-[9px] font-black uppercase tracking-widest transition-all active:scale-95 disabled:opacity-50"
                                                        :class="isDirty ? 'bg-slate-900 text-white shadow-md shadow-slate-900/20' : (isRated ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed')"
                                                        :disabled="!isDirty && !isRated"
                                                    >
                                                        <span wire:loading.remove wire:target="saveRating('{{ $key }}')">
                                                            <template x-if="isRated && !isDirty">
                                                                <div class="flex items-center justify-center gap-1.5">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17l-5-5"/></svg>
                                                                    Tersimpan
                                                                </div>
                                                            </template>
                                                            <template x-if="!isRated || isDirty">
                                                                <span>Simpan</span>
                                                            </template>
                                                        </span>
                                                        <span wire:loading wire:target="saveRating('{{ $key }}')">...</span>
                                                    </button>

                                                    @if($formState[$key]['is_rated'])
                                                        <button
                                                            wire:click="confirmReset('{{ $key }}')"
                                                            wire:loading.attr="disabled"
                                                            class="w-full min-w-[80px] relative px-2 py-1.5 rounded-lg text-[9px] font-black uppercase tracking-widest transition-all active:scale-95 bg-rose-50 text-rose-500 hover:bg-rose-100 border border-rose-100"
                                                        >
                                                            <span wire:loading.remove wire:target="confirmReset('{{ $key }}')">
                                                                <div class="flex items-center justify-center gap-1.5">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                                                                    Reset
                                                                </div>
                                                            </span>
                                                            <span wire:loading wire:target="confirmReset('{{ $key }}')">...</span>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>

        <!-- Validation Dialog -->
        @if($showValidationDialog)
            <div class="fixed inset-0 z-[100] flex items-center justify-center p-4 pointer-events-auto">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="$set('showValidationDialog', false)"></div>
                <div class="bg-white w-full max-w-sm rounded-[2.5rem] shadow-2xl relative overflow-hidden flex flex-col p-8 animate-in zoom-in-95 duration-200 border-t-8 border-amber-500">
                    <div class="w-16 h-16 bg-amber-50 rounded-2xl flex items-center justify-center mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-amber-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                    </div>
                    <h4 class="text-lg font-black text-slate-800 uppercase italic leading-none mb-2">Penilaian Belum Lengkap</h4>
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mb-6">Silakan lengkapi isian berikut:</p>
                    
                    <div class="space-y-2 mb-8">
                        @foreach($validationMessages as $msg)
                            <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl border border-slate-100">
                                <div class="w-2 h-2 bg-amber-500 rounded-full"></div>
                                <span class="text-xs font-black text-slate-700 italic">{{ $msg }}</span>
                            </div>
                        @endforeach
                    </div>

                    <button 
                        wire:click="$set('showValidationDialog', false)"
                        class="w-full py-4 bg-slate-900 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-xl active:scale-95 transition-all"
                    >
                        Saya Mengerti
                    </button>
                </div>
            </div>
        @endif

    {{-- ===== TAB: PENILAIAN KABKOT ===== --}}
    <div x-show="activeTab === 'kabkot'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
        <div class="space-y-4">

            {{-- Flash success --}}
            @if(session('kabkot_success'))
                <div class="flex items-center gap-4 p-4 bg-emerald-50 border border-emerald-100 rounded-2xl animate-in fade-in duration-300">
                    <div class="w-7 h-7 bg-emerald-500 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                    <p class="text-xs font-black text-emerald-700 uppercase tracking-tight">{{ session('kabkot_success') }}</p>
                </div>
            @endif

            @if(empty($kabkotData))
                <div class="bg-white py-20 rounded-[3rem] border-2 border-dashed border-slate-200 text-center">
                    <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    </div>
                    <p class="text-xl font-black text-slate-400 uppercase italic">Tidak Ada Kepala Kabkot</p>
                    <p class="text-xs text-slate-300 font-bold uppercase tracking-widest mt-1">Belum ada pengguna dengan role Kepala Kabkot.</p>
                </div>
            @else

            {{-- ===== MOBILE CARDS (< md) ===== --}}
            <div class="block md:hidden space-y-4">
                @foreach($kabkotData as $kabkotId => $kabkot)
                    {{-- Kabkot Header --}}
                    <div class="bg-gradient-to-br from-slate-800 to-minimal-indigo rounded-2xl px-5 py-4 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-black text-white uppercase tracking-tight leading-none">{{ $kabkot['kabkot_name'] }}</p>
                            <p class="text-[9px] font-mono font-bold text-indigo-300 mt-1">NIP: {{ $kabkot['kabkot_nip'] }}</p>
                        </div>
                        @php
                            $allRatedMobile = collect($kabkot['teams'])->every(fn($t) => $kabkotFormState[$t['key']]['is_rated']);
                        @endphp
                        <span class="text-[8px] font-black uppercase tracking-widest px-2.5 py-1 rounded-full {{ $allRatedMobile ? 'bg-emerald-500/20 text-emerald-300' : 'bg-rose-500/20 text-rose-300' }}">
                            {{ $allRatedMobile ? '✓ Lengkap' : 'Belum Lengkap' }}
                        </span>
                    </div>

                    {{-- Per-team cards --}}
                    <div class="space-y-2 ml-3">
                        @foreach($kabkot['teams'] as $teamRow)
                        @php $key = $teamRow['key']; @endphp
                        <div
                            x-data="{
                                hasWork: @entangle('kabkotFormState.' . $key . '.has_work'),
                                score: @entangle('kabkotFormState.' . $key . '.score'),
                                isDirty: @entangle('kabkotFormState.' . $key . '.is_dirty'),
                                isRated: @entangle('kabkotFormState.' . $key . '.is_rated'),
                                overridden: @entangle('kabkotFormState.' . $key . '.overridden'),
                            }"
                            class="bg-white rounded-xl shadow-sm overflow-hidden border-l-4 transition-all"
                            :class="overridden ? 'border border-amber-200 border-l-amber-400' : (isRated ? 'border border-emerald-100 border-l-emerald-400' : 'border border-rose-200 border-l-rose-400')"
                        >
                            <div class="flex items-center justify-between px-4 py-2.5 bg-slate-50/60 border-b border-slate-100">
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] font-black text-minimal-indigo uppercase italic tracking-tight">{{ $teamRow['team_name'] }}</span>
                                    <template x-if="overridden">
                                        <span class="px-1.5 py-0.5 bg-amber-100 text-amber-700 text-[8px] font-black rounded-full uppercase tracking-tight">Diubah Pimpinan</span>
                                    </template>
                                </div>
                                <span class="text-[9px] font-black uppercase px-2 py-0.5 rounded-full"
                                    :class="isRated ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-500'"
                                    x-text="isRated ? '✓ Dinilai' : '○ Belum'"></span>
                            </div>
                            {{-- Ada Pekerjaan? row --}}
                            <div class="flex items-center justify-between px-4 py-2.5 border-b border-slate-100">
                                <span class="text-[10px] font-black text-slate-500 uppercase tracking-wide">Ada Pekerjaan?</span>
                                <label class="flex items-center gap-2 cursor-pointer select-none">
                                    <input
                                        type="checkbox"
                                        x-model="hasWork"
                                        @change="isDirty = true; if(!hasWork) score = '';"
                                        class="w-4 h-4 rounded border-slate-300 text-minimal-indigo focus:ring-minimal-indigo cursor-pointer"
                                    >
                                    <span class="text-[10px] font-black uppercase tracking-wide" :class="hasWork ? 'text-emerald-600' : 'text-slate-400'" x-text="hasWork ? 'Ya' : 'Tidak'"></span>
                                </label>
                            </div>
                            {{-- Nilai + Aksi row --}}
                            <div class="px-4 py-3 flex items-center gap-3">
                                <template x-if="hasWork">
                                    <span class="text-xs font-black text-slate-500 uppercase tracking-wide flex-shrink-0">Nilai</span>
                                </template>
                                <template x-if="hasWork">
                                    <input
                                        x-model="score"
                                        @input="isDirty = true"
                                        type="number" min="1" max="100"
                                        class="w-20 bg-slate-50 border border-slate-200 rounded-lg px-2 py-1.5 text-center text-sm font-black text-minimal-indigo focus:ring-4 focus:ring-minimal-indigo/10 focus:border-minimal-indigo transition-all shadow-inner"
                                        placeholder="--"
                                    >
                                </template>
                                <template x-if="!hasWork">
                                    <span class="text-xs font-bold text-slate-300 uppercase tracking-wide flex-shrink-0 italic">Tidak ada pekerjaan</span>
                                </template>
                                <button
                                    wire:click="saveKabkotRating('{{ $key }}')"
                                    wire:loading.attr="disabled"
                                    class="flex-1 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all active:scale-95 disabled:opacity-50"
                                    :class="isDirty ? 'bg-slate-900 text-white' : (isRated ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-slate-100 text-slate-400 cursor-not-allowed')"
                                    :disabled="!isDirty && !isRated"
                                >
                                    <span wire:loading.remove wire:target="saveKabkotRating('{{ $key }}')">
                                        <template x-if="isRated && !isDirty"><span>✓ Tersimpan</span></template>
                                        <template x-if="!isRated || isDirty"><span>Simpan</span></template>
                                    </span>
                                    <span wire:loading wire:target="saveKabkotRating('{{ $key }}')">...</span>
                                </button>
                                @if($kabkotFormState[$key]['is_rated'])
                                    <button wire:click="confirmResetKabkot('{{ $key }}')" class="px-3 py-2 rounded-xl text-[9px] font-black uppercase bg-rose-50 text-rose-500 border border-rose-100 active:scale-95">Reset</button>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endforeach
            </div>

            {{-- ===== DESKTOP TABLE (md+) ===== --}}
            <div class="hidden md:block bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-8 py-6 border-b border-slate-50 flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-black text-slate-800 tracking-tight italic">Penilaian Kepala Kabupaten/Kota</h3>
                        <p class="text-xs text-slate-400 font-mono uppercase tracking-tighter mt-0.5">{{ $monthNames[$month] ?? '...' }} {{ $year }}</p>
                    </div>
                    @php
                        $totalKabkot = count($kabkotFormState);
                        $ratedKabkot = collect($kabkotFormState)->filter(fn($e) => $e['is_rated'])->count();
                    @endphp
                    <span class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest {{ $ratedKabkot === $totalKabkot && $totalKabkot > 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600' }}">
                        {{ $ratedKabkot }}/{{ $totalKabkot }} Dinilai
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50/80">
                            <tr class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">
                                <th class="px-6 py-4 border-b border-slate-100">Kepala Kabkot</th>
                                <th class="px-5 py-4 border-b border-slate-100">Tim Penilai</th>
                                <th class="px-4 py-4 border-b border-slate-100 text-center">Ada Pekerjaan?</th>
                                <th class="px-4 py-4 border-b border-slate-100 text-center">Nilai (1–100)</th>
                                <th class="px-5 py-4 border-b border-slate-100 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($kabkotData as $kabkotId => $kabkot)
                                @foreach($kabkot['teams'] as $tIdx => $teamRow)
                                @php $key = $teamRow['key']; @endphp
                                <tr
                                    x-data="{
                                        hasWork: @entangle('kabkotFormState.' . $key . '.has_work'),
                                        score: @entangle('kabkotFormState.' . $key . '.score'),
                                        isDirty: @entangle('kabkotFormState.' . $key . '.is_dirty'),
                                        isRated: @entangle('kabkotFormState.' . $key . '.is_rated'),
                                        overridden: @entangle('kabkotFormState.' . $key . '.overridden'),
                                    }"
                                    class="group border-t border-slate-50 transition-colors"
                                    :class="overridden ? 'bg-amber-50/30' : (!isRated ? 'bg-rose-50/30' : 'hover:bg-slate-50/50')"
                                >
                                    {{-- Kabkot name cell — rowspan only on first team row --}}
                                    @if($tIdx === 0)
                                    <td rowspan="{{ count($kabkot['teams']) }}" class="px-6 py-4 align-top border-r border-slate-50 bg-white">
                                        <p class="text-[11px] font-black text-slate-800 uppercase tracking-tight leading-none">{{ $kabkot['kabkot_name'] }}</p>
                                        <p class="text-[9px] font-mono font-bold text-slate-400 uppercase tracking-widest mt-1">{{ $kabkot['kabkot_nip'] }}</p>
                                        @php
                                            $allRated = collect($kabkot['teams'])->every(fn($t) => $kabkotFormState[$t['key']]['is_rated']);
                                        @endphp
                                        @if(!$allRated)
                                            <span class="mt-2 inline-flex items-center gap-1 px-2 py-0.5 bg-rose-100 text-rose-600 text-[8px] font-black rounded-full uppercase tracking-tight">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                                Belum Lengkap
                                            </span>
                                        @endif
                                    </td>
                                    @endif

                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-2">
                                            <span class="font-black text-minimal-indigo uppercase text-[10px] italic tracking-tight">{{ $teamRow['team_name'] }}</span>
                                            <template x-if="overridden">
                                                <span class="px-1.5 py-0.5 bg-amber-100 text-amber-700 text-[8px] font-black rounded-full uppercase tracking-tight">Diubah Pimpinan</span>
                                            </template>
                                        </div>
                                    </td>

                                    {{-- Ada Pekerjaan? checkbox --}}
                                    <td class="px-4 py-4 text-center">
                                        <label class="inline-flex items-center gap-1.5 cursor-pointer select-none">
                                            <input
                                                type="checkbox"
                                                x-model="hasWork"
                                                @change="isDirty = true; if(!hasWork) score = '';"
                                                class="w-4 h-4 rounded border-slate-300 text-minimal-indigo focus:ring-minimal-indigo cursor-pointer"
                                            >
                                            <span class="text-[9px] font-black uppercase tracking-wide hidden xl:inline" :class="hasWork ? 'text-emerald-600' : 'text-slate-400'" x-text="hasWork ? 'Ya' : 'Tidak'"></span>
                                        </label>
                                    </td>

                                    {{-- Nilai input (only when has_work) --}}
                                    <td class="px-4 py-4 text-center">
                                        <template x-if="hasWork">
                                            <input
                                                x-model="score"
                                                @input="isDirty = true"
                                                type="number" min="1" max="100"
                                                class="w-20 bg-slate-50 border border-slate-200 rounded-lg px-2 py-1.5 text-center text-sm font-black text-minimal-indigo focus:ring-4 focus:ring-minimal-indigo/10 focus:border-minimal-indigo transition-all shadow-inner"
                                                placeholder="--"
                                            >
                                        </template>
                                        <template x-if="!hasWork">
                                            <span class="text-slate-300 font-black text-sm">—</span>
                                        </template>
                                    </td>

                                    <td class="px-5 py-4 text-right">
                                        <div class="flex gap-2 items-center justify-end">
                                            <button
                                                wire:click="saveKabkotRating('{{ $key }}')"
                                                wire:loading.attr="disabled"
                                                class="px-4 py-2 rounded-xl text-[9px] font-black uppercase tracking-widest transition-all active:scale-95 disabled:opacity-50"
                                                :class="isDirty ? 'bg-slate-900 text-white shadow-md' : (isRated ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-slate-100 text-slate-300 cursor-not-allowed')"
                                                :disabled="!isDirty && !isRated"
                                            >
                                                <span wire:loading.remove wire:target="saveKabkotRating('{{ $key }}')">
                                                    <template x-if="isRated && !isDirty"><span>✓ Tersimpan</span></template>
                                                    <template x-if="!isRated || isDirty"><span>Simpan</span></template>
                                                </span>
                                                <span wire:loading wire:target="saveKabkotRating('{{ $key }}')">...</span>
                                            </button>
                                            @if($kabkotFormState[$key]['is_rated'])
                                                <button wire:click="confirmResetKabkot('{{ $key }}')" class="px-3 py-2 rounded-xl text-[9px] font-black uppercase tracking-widest bg-rose-50 text-rose-500 hover:bg-rose-100 border border-rose-100 transition-all active:scale-95">Reset</button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>

        <!-- Reset Confirmation Dialog -->
        @if($showResetDialog)
            <div class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-slate-900/60 backdrop-blur-sm pointer-events-auto">
                <div class="bg-white w-full max-w-sm rounded-[2.5rem] shadow-2xl relative overflow-hidden flex flex-col p-8 animate-in zoom-in-95 duration-200 border-t-8 border-rose-500">
                    <div class="w-16 h-16 bg-rose-50 rounded-2xl flex items-center justify-center mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-rose-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                    </div>
                    <h4 class="text-lg font-black text-slate-800 uppercase italic leading-none mb-2">Konfirmasi Reset</h4>
                    <p class="text-xs text-slate-500 font-medium mb-8">Apakah Anda yakin ingin menghapus/mereset penilaian untuk pegawai ini? Data yang sudah tersimpan akan hilang permanen.</p>
                    
                    <div class="flex gap-3">
                        <button type="button" wire:click="cancelReset" class="flex-1 py-4 bg-slate-50 text-slate-400 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-100 transition-all active:scale-95">Batal</button>
                        <button type="button" wire:click="executeReset" class="flex-1 py-4 bg-rose-500 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-xl shadow-rose-500/20 hover:bg-rose-600 transition-all active:scale-95 flex justify-center items-center gap-2">
                            <span wire:loading.remove wire:target="executeReset">Ya, Reset</span>
                            <span wire:loading wire:target="executeReset">Proses...</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Kabkot Validation Dialog -->
        @if($showKabkotValidationDialog)
            <div class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-slate-900/60 backdrop-blur-sm pointer-events-auto">
                <div class="bg-white w-full max-w-sm rounded-[2.5rem] shadow-2xl relative overflow-hidden flex flex-col p-8 animate-in zoom-in-95 duration-200 border-t-8 border-amber-400">
                    <div class="w-16 h-16 bg-amber-50 rounded-2xl flex items-center justify-center mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-amber-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    </div>
                    <h4 class="text-lg font-black text-slate-800 uppercase italic leading-none mb-2">Nilai Diperlukan</h4>
                    <p class="text-xs text-slate-500 font-medium mb-8">Kolom "Ada Pekerjaan?" dicentang — masukkan nilai antara 1 sampai 100, atau hilangkan centang jika tidak ada pekerjaan.</p>
                    <button wire:click="$set('showKabkotValidationDialog', false)" class="w-full py-4 bg-slate-900 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-xl active:scale-95 transition-all">
                        Saya Mengerti
                    </button>
                </div>
            </div>
        @endif

        <!-- Kabkot Reset Confirmation Dialog -->
        @if($showKabkotResetDialog)
            <div class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-slate-900/60 backdrop-blur-sm pointer-events-auto">
                <div class="bg-white w-full max-w-sm rounded-[2.5rem] shadow-2xl relative overflow-hidden flex flex-col p-8 animate-in zoom-in-95 duration-200 border-t-8 border-rose-500">
                    <div class="w-16 h-16 bg-rose-50 rounded-2xl flex items-center justify-center mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-rose-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                    </div>
                    <h4 class="text-lg font-black text-slate-800 uppercase italic leading-none mb-2">Konfirmasi Reset</h4>
                    <p class="text-xs text-slate-500 font-medium mb-8">Reset penilaian Kepala Kabkot ini? Data akan hilang permanen.</p>
                    <div class="flex gap-3">
                        <button type="button" wire:click="cancelResetKabkot" class="flex-1 py-4 bg-slate-50 text-slate-400 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-100 transition-all active:scale-95">Batal</button>
                        <button type="button" wire:click="executeResetKabkot" class="flex-1 py-4 bg-rose-500 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-xl shadow-rose-500/20 hover:bg-rose-600 transition-all active:scale-95 flex justify-center items-center gap-2">
                            <span wire:loading.remove wire:target="executeResetKabkot">Ya, Reset</span>
                            <span wire:loading wire:target="executeResetKabkot">Proses...</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif

    <!-- Management Dialogs (Modals) Dashboard -->
    @php $dialogs = [
        ['state' => 'showTeamsDialog', 'title' => 'Daftar Tim Managed', 'type' => 'teams'],
        ['state' => 'showMembersDialog', 'title' => 'Detail Anggota Tim', 'type' => 'members'],
        ['state' => 'showUnratedDialog', 'title' => 'Pending Assessment', 'type' => 'unrated'],
    ]; @endphp

    @foreach($dialogs as $d)
        @if(${$d['state']})
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-slate-900/60 backdrop-blur-sm animate-in fade-in duration-300 pointer-events-auto">
            <div class="bg-white w-full {{ $d['type'] === 'members' ? 'max-w-xl' : 'max-w-md' }} rounded-[3rem] shadow-2xl overflow-hidden border border-white/20 animate-in zoom-in-95 duration-300 relative flex flex-col max-h-[85vh]">
                <div class="p-10 shrink-0">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-5">
                            <div class="w-14 h-14 rounded-2xl bg-minimal-indigo text-white flex items-center justify-center shadow-xl">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                            </div>
                            <div>
                                <h4 class="text-xl font-black text-slate-800 tracking-tight">{{ $d['title'] }}</h4>
                                <p class="text-[9px] font-black uppercase tracking-[0.2em] text-minimal-indigo/60">Lead Management Context</p>
                            </div>
                        </div>
                        <button wire:click="$set('{{ $d['state'] }}', false)" class="w-10 h-10 rounded-xl bg-slate-50 text-slate-300 hover:bg-red-50 hover:text-red-500 transition-all flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                        </button>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto px-10 pb-10 custom-scrollbar">
                    @if($d['type'] === 'teams')
                        <div class="space-y-3">
                            @foreach($stats['teamDetails'] as $index => $t)
                                <div class="flex items-center justify-between p-5 rounded-[1.5rem] bg-slate-50/50 border border-slate-100 hover:bg-white hover:shadow-sm transition-all group">
                                    <div class="flex items-center gap-4">
                                        <span class="text-2xl font-black text-minimal-indigo/20 italic group-hover:text-minimal-indigo transition-all">{{ $index + 1 }}</span>
                                        <div>
                                            <p class="text-[12px] font-black text-slate-800 uppercase tracking-tight">{{ $t['teamName'] }}</p>
                                            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">{{ count($t['members']) }} Anggota Aktif</p>
                                        </div>
                                    </div>
                                    <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                                </div>
                            @endforeach
                        </div>
                    @elseif($d['type'] === 'members')
                        <table class="w-full text-left">
                            <thead class="sticky top-0 bg-white z-10">
                                <tr class="text-[9px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50">
                                    <th class="pb-4">Tim Kerja</th>
                                    <th class="pb-4 text-right">Data Anggota</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach($stats['teamDetails'] as $t)
                                    <tr>
                                        <td class="py-5 align-top">
                                            <p class="text-[11px] font-black text-minimal-indigo uppercase tracking-tight">{{ $t['teamName'] }}</p>
                                        </td>
                                        <td class="py-5 text-right">
                                            <div class="flex flex-wrap gap-1 justify-end">
                                                @foreach($t['members'] as $m)
                                                    <span class="px-2 py-0.5 bg-slate-50 border border-slate-100 text-slate-600 rounded-md text-[8px] font-black uppercase">{{ $m['name'] }}</span>
                                                @endforeach
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="space-y-2">
                            @foreach($stats['unratedMembers'] as $m)
                                <div class="flex items-center justify-between p-4 rounded-2xl bg-amber-50/50 border border-amber-100 group hover:bg-white hover:border-amber-400 transition-all">
                                    <div>
                                        <p class="text-[12px] font-black text-slate-800 uppercase tracking-tight">{{ $m->name }}</p>
                                        <p class="text-[9px] text-slate-400 font-mono font-bold tracking-widest">NIP: {{ $m->nip }}</p>
                                    </div>
                                    <span class="text-[8px] font-black bg-amber-100 text-amber-700 px-3 py-1 rounded-full uppercase italic">Attention</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="p-10 bg-slate-50 border-t border-slate-100 flex justify-end shrink-0">
                    <button wire:click="$set('{{ $d['state'] }}', false)" class="px-8 py-3.5 bg-slate-900 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest active:scale-95 transition-all shadow-xl">Dismiss Dialog</button>
                </div>
            </div>
        </div>
        @endif
    @endforeach

    <script type="application/json" id="ketuaStatsData">
        {!! json_encode($stats['teamChartData']) !!}
    </script>

    @script
    <script>
        window.initChart = (passedData = null) => {
            const chartDiv = document.querySelector("#ketuaBarChart");
            const dataScript = document.querySelector("#ketuaStatsData");
            if (!chartDiv) return;
            
            let chartData = [];
            if (passedData) {
                chartData = passedData;
            } else if (dataScript) {
                try {
                    chartData = JSON.parse(dataScript.textContent);
                } catch (e) { return; }
            }

            if (!chartData || chartData.length === 0) return;

            const labels = chartData.map(t => t.teamName);
            const scores = chartData.map(t => t.avgScore);

            const options = {
                series: [{ name: 'Rerata Nilai', data: scores }],
                chart: { 
                    type: 'bar', 
                    height: 380, 
                    fontFamily: 'Outfit, sans-serif',
                    toolbar: { show: false },
                    animations: { enabled: true, easing: 'easeinout', speed: 800 }
                },
                colors: [function({ value }) {
                    if (value >= 80) return '#10b981';
                    if (value >= 60) return '#f59e0b';
                    return '#ef4444';
                }],
                xaxis: { 
                    categories: labels,
                    labels: { style: { fontSize: '9px', fontWeight: 800, colors: '#64748b' }, rotate: -45, hideOverlappingLabels: true }
                },
                yaxis: { 
                    max: 100, 
                    labels: { style: { fontSize: '9px', fontWeight: 800, colors: '#64748b' } }
                },
                plotOptions: {
                    bar: {
                        borderRadius: 10,
                        columnWidth: '35%',
                        distributed: true,
                        dataLabels: { position: 'top' }
                    }
                },
                dataLabels: {
                    enabled: true,
                    offsetY: -20,
                    style: { fontSize: '11px', fontWeight: 900, colors: ['#1e293b'] }
                },
                tooltip: { theme: 'dark' },
                grid: { borderColor: '#f8fafc' },
                legend: { show: false }
            };
            
            if (window.ketuaBarChartInstance) {
                window.ketuaBarChartInstance.destroy();
            }
            
            window.ketuaBarChartInstance = new ApexCharts(chartDiv, options);
            window.ketuaBarChartInstance.render();
        };

        setTimeout(() => window.initChart(), 50);

        Livewire.on('refreshKetuaCharts', (event) => {
            const data = event.chartData || null;
            setTimeout(() => window.initChart(data), 50);
        });
    </script>
    @endscript

    {{-- ===== BOTTOM NAV BAR (mobile only, < md) ===== --}}
    <div class="fixed bottom-0 left-0 right-0 z-50 md:hidden">
        {{-- Safe area untuk iPhone --}}
        <div class="bg-white border-t border-slate-100 shadow-[0_-4px_24px_rgba(0,0,0,0.08)]" style="padding-bottom: env(safe-area-inset-bottom, 0px);">
            <div class="flex">
                <button
                    @click="activeTab = 'dashboard'"
                    class="flex-1 flex flex-col items-center justify-center gap-1 py-3 transition-all active:scale-95"
                    :class="activeTab === 'dashboard' ? 'text-minimal-indigo' : 'text-slate-400'"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 transition-transform" :class="activeTab === 'dashboard' ? 'scale-110' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" rx="2" ry="2" x="3" y="3"/><line x1="3" x2="21" y1="9" y2="9"/><line x1="9" x2="9" y1="21" y2="9"/></svg>
                    <span class="text-[9px] font-black uppercase tracking-widest">Dashboard</span>
                    <span class="h-0.5 w-5 rounded-full transition-all" :class="activeTab === 'dashboard' ? 'bg-minimal-indigo' : 'bg-transparent'"></span>
                </button>

                {{-- Divider --}}
                <div class="w-px bg-slate-100 my-2"></div>

                <button
                    @click="activeTab = 'input'"
                    class="flex-1 flex flex-col items-center justify-center gap-1 py-3 transition-all active:scale-95"
                    :class="activeTab === 'input' ? 'text-minimal-indigo' : 'text-slate-400'"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 transition-transform" :class="activeTab === 'input' ? 'scale-110' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    <span class="text-[9px] font-black uppercase tracking-widest">Input Nilai</span>
                    <span class="h-0.5 w-5 rounded-full transition-all" :class="activeTab === 'input' ? 'bg-minimal-indigo' : 'bg-transparent'"></span>
                </button>

                <div class="w-px bg-slate-100 my-2"></div>

                <button
                    @click="activeTab = 'kabkot'"
                    class="flex-1 flex flex-col items-center justify-center gap-1 py-3 transition-all active:scale-95"
                    :class="activeTab === 'kabkot' ? 'text-minimal-indigo' : 'text-slate-400'"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 transition-transform" :class="activeTab === 'kabkot' ? 'scale-110' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    <span class="text-[9px] font-black uppercase tracking-widest">Kabkot</span>
                    <span class="h-0.5 w-5 rounded-full transition-all" :class="activeTab === 'kabkot' ? 'bg-minimal-indigo' : 'bg-transparent'"></span>
                </button>
            </div>
        </div>
    </div>
</div>
