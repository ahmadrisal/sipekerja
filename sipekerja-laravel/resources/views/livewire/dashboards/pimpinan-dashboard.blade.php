<div class="font-outfit space-y-6 pb-24 md:pb-12">
    <!-- Header Area -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-800 tracking-tight italic">Dashboard Overview</h2>
            <p class="text-slate-400 text-[11px] font-medium">Monitoring performa dan beban kerja pegawai secara real-time.</p>
        </div>
        <div class="hidden md:flex flex-wrap gap-1.5 p-1 bg-white rounded-xl shadow-sm border border-slate-100">
            <button wire:click="setActiveTab('overview')" class="px-5 py-2 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all {{ $activeTab === 'overview' ? 'bg-minimal-indigo text-white shadow-md' : 'text-slate-400 hover:bg-slate-50' }}">
                Nilai Pegawai
            </button>
            <button wire:click="setActiveTab('input-kt')" class="px-5 py-2 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all {{ $activeTab === 'input-kt' ? 'bg-minimal-indigo text-white shadow-md' : 'text-slate-400 hover:bg-slate-50' }}">
                Nilai Ketua Tim
            </button>
            <button wire:click="setActiveTab('rekap-kabkot')" class="px-5 py-2 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all {{ $activeTab === 'rekap-kabkot' ? 'bg-minimal-indigo text-white shadow-md' : 'text-slate-400 hover:bg-slate-50' }}">
                Nilai Kepala Kabkot
            </button>
            <button wire:click="setActiveTab('report')" class="px-5 py-2 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all {{ $activeTab === 'report' ? 'bg-minimal-indigo text-white shadow-md' : 'text-slate-400 hover:bg-slate-50' }}">
                Report
            </button>
        </div>
    </div>

    <!-- Shared Top Horizontal Row: Reorganized Filters ONLY -->
    <div class="bg-white rounded-[1.5rem] p-6 shadow-sm border border-slate-100 flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-minimal-indigo/5 text-minimal-indigo flex items-center justify-center shadow-inner">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
            </div>
            <div>
                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 block">Periode Acuan</span>
                <span class="text-sm font-black text-slate-700 uppercase italic">{{ $monthNames[$month] ?? '...' }} {{ $year }}</span>
            </div>
        </div>
        
        <div class="flex items-center gap-3 w-full md:w-auto">
            <div class="relative group w-full md:w-48">
                <select wire:model.live="month" class="w-full h-11 pl-4 pr-10 rounded-xl border border-slate-100 bg-slate-50/50 text-[11px] font-black uppercase tracking-wider text-slate-700 appearance-none focus:ring-8 focus:ring-minimal-indigo/5 transition-all cursor-pointer">
                    @foreach($monthNames as $num => $name)
                        <option value="{{ $num }}">{{ $name }}</option>
                    @endforeach
                </select>
                <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </div>
            </div>

            <div class="relative group w-full md:w-32">
                <select wire:model.live="year" class="w-full h-11 pl-4 pr-10 rounded-xl border border-slate-100 bg-slate-50/50 text-[11px] font-black uppercase tracking-wider text-slate-700 appearance-none focus:ring-8 focus:ring-minimal-indigo/5 transition-all cursor-pointer">
                    @foreach(range(2026, max(2026, date('Y') + 1)) as $y)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endforeach
                </select>
                <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </div>
            </div>
        </div>
    </div>

    <div wire:loading.class="opacity-50 pointer-events-none" wire:loading.class.remove="transition-opacity" wire:target="setActiveTab" class="transition-opacity duration-150">
    @if($activeTab === 'overview')
    {{-- Flash success nilai pimpinan --}}
    @if(session('pegawai_pimpinan_success'))
        <div class="flex items-center gap-4 p-4 bg-emerald-50 border border-emerald-100 rounded-2xl animate-in fade-in duration-300">
            <div class="w-7 h-7 bg-emerald-500 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <p class="text-xs font-black text-emerald-700 uppercase tracking-tight">{{ session('pegawai_pimpinan_success') }}</p>
        </div>
    @endif
    <!-- Compliance Action Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Incomplete Teams Card -->
        <div wire:click="$set('showIncompleteTeamsDialog', true)" class="bg-white p-6 rounded-[1.5rem] shadow-sm border border-slate-100 flex items-center justify-between group hover:border-minimal-indigo/30 hover:shadow-md transition-all cursor-pointer relative overflow-hidden">
            <div class="absolute top-0 right-0 w-16 h-16 bg-amber-500/5 rounded-bl-[1.5rem] group-hover:bg-amber-500/10 transition-colors"></div>
            <div class="flex items-center gap-4 relative z-10">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <div>
                    <h4 class="text-[10px] font-black uppercase tracking-widest text-slate-400">Tim Belum Selesai Menilai</h4>
                    <p class="text-xs font-bold text-slate-500 mt-0.5">{{ $stats['compliance']['teamsCount'] }} Tim memerlukan perhatian</p>
                </div>
            </div>
            <div class="text-2xl font-black text-amber-500 italic relative z-10">{{ $stats['compliance']['teamsCount'] }}</div>
        </div>

        <!-- Incomplete Employees Card -->
        <div wire:click="$set('showIncompleteEmployeesDialog', true)" class="bg-white p-6 rounded-[1.5rem] shadow-sm border border-slate-100 flex items-center justify-between group hover:border-minimal-indigo/30 hover:shadow-md transition-all cursor-pointer relative overflow-hidden">
            <div class="absolute top-0 right-0 w-16 h-16 bg-rose-500/5 rounded-bl-[1.5rem] group-hover:bg-rose-500/10 transition-colors"></div>
            <div class="flex items-center gap-4 relative z-10">
                <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                </div>
                <div>
                    <h4 class="text-[10px] font-black uppercase tracking-widest text-slate-400">Pegawai Belum Dinilai Lengkap</h4>
                    <p class="text-xs font-bold text-slate-500 mt-0.5">{{ $stats['compliance']['employeesCount'] }} Pegawai belum tuntas</p>
                </div>
            </div>
            <div class="text-2xl font-black text-rose-500 italic relative z-10">{{ $stats['compliance']['employeesCount'] }}</div>
        </div>
    </div>

    <div class="space-y-6">

        <!-- Charts Board -->
        <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-slate-100">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
                <!-- Scatter Chart -->
                <div>
                    <div class="flex items-center justify-between mb-6 px-1">
                        <div>
                            <h3 class="text-lg font-black text-slate-800 tracking-tight">Employee Quad</h3>
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">Beban Kerja vs Kinerja ({{ $monthNames[$month] ?? '...' }})</p>
                        </div>
                        <div class="flex gap-3 text-[8px] font-black uppercase tracking-widest text-slate-400">
                            <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-minimal-indigo"></span> Pegawai</div>
                            <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-slate-200"></span> Average</div>
                        </div>
                    </div>
                    <div id="scatterChart" class="h-56 sm:h-80 w-full" wire:ignore></div>
                </div>

                <!-- Team Performance Bar Chart -->
                <div class="border-t lg:border-t-0 lg:border-l border-slate-50 pt-8 lg:pt-0 lg:pl-10">
                    <div class="px-1 mb-6">
                        <h3 class="text-lg font-black text-slate-800 tracking-tight">Rata-rata Capaian Per Tim</h3>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">Nilai Rata-rata Anggota Tim (0-100)</p>
                    </div>
                    <div id="teamBarChart" class="h-56 sm:h-80 w-full" wire:ignore></div>
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-4 sm:p-8 border-b border-slate-50 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h3 class="text-xl font-black text-slate-800 tracking-tight italic">Rekapitulasi Penilaian</h3>
                    <p class="text-xs text-slate-400 font-medium font-mono uppercase tracking-tighter">{{ $monthNames[$month] ?? '...' }} {{ $year }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <div class="relative group">
                        <input wire:model.live.debounce.300ms="search" placeholder="Cari Pegawai..." class="h-10 pl-10 pr-4 rounded-xl bg-slate-50/80 border border-slate-100 text-[11px] font-bold w-full md:w-56 focus:ring-4 focus:ring-minimal-indigo/10 transition-all shadow-inner">
                        <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-300 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    </div>
                    <select wire:model.live="statusFilter" class="h-10 px-4 rounded-xl bg-slate-50 border border-slate-100 text-[9px] font-black uppercase tracking-widest focus:ring-4 focus:ring-minimal-indigo/10 transition-all cursor-pointer">
                        <option value="All">Semua Status</option>
                        <option value="HasTeam">Punya Tim</option>
                        <option value="NoTeam">Tidak Punya Tim</option>
                    </select>
                    <a href="{{ route('export.pegawai', ['month' => $month, 'year' => $year]) }}"
                       class="inline-flex items-center gap-2 h-10 px-4 rounded-xl bg-emerald-500 hover:bg-emerald-600 active:scale-95 text-white text-[9px] font-black uppercase tracking-widest transition-all shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Export .xlsx
                    </a>
                </div>
            </div>
            {{-- ===== TAMPILAN DESKTOP (tabel, md+) ===== --}}
            <div class="hidden md:block overflow-x-auto px-4">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50/30">
                            <th class="px-4 py-4 text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 text-center w-10">No</th>
                            <th class="px-6 py-4 text-[9px] font-black uppercase tracking-[0.2em] text-slate-400">Pegawai</th>
                            <th class="px-4 py-4 text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 text-center">Plotting</th>
                            <th class="px-4 py-4 text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 text-center text-rose-400">Min</th>
                            <th class="px-4 py-4 text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 text-center">Avg Tim</th>
                            <th class="px-4 py-4 text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 text-center text-emerald-400">Max</th>
                            <th class="px-4 py-4 text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 text-center">Nilai Pimpinan</th>
                            <th class="px-4 py-4 text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 text-center">Score Akhir</th>
                            <th class="px-6 py-4 text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rekap as $u)
                        @php $isOpen = $detailUserId === $u->id; @endphp
                        <tr class="{{ $isOpen ? '' : 'hover:bg-slate-50' }} group transition-all duration-200 border-t border-slate-50" {{ $isOpen ? 'style=background:#eef2ff' : '' }}>
                            <td class="px-4 py-4 text-center">
                                <span class="text-xs font-black text-slate-800 tabular-nums">{{ $loop->iteration }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl flex items-center justify-center font-black italic shadow-inner text-[11px] transition-all flex-shrink-0" style="{{ $isOpen ? 'background:#6366f1;color:#fff' : 'background:linear-gradient(135deg,#f1f5f9,#e2e8f0);color:#94a3b8' }}">
                                        {{ substr($u->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="text-[12px] font-black leading-tight transition-colors {{ $isOpen ? 'text-minimal-indigo' : 'text-slate-700 group-hover:text-minimal-indigo' }}">{{ $u->name }}</p>
                                        <p class="text-[9px] font-mono font-bold text-slate-400 uppercase tracking-tighter">{{ $u->nip }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="inline-flex px-2.5 py-1 rounded-full text-[8px] font-black uppercase tracking-widest {{ $u->totalTeams > 0 && $u->ratedTeams === $u->totalTeams ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600' }}">
                                    {{ $u->ratedTeams }}/{{ $u->totalTeams }} DONE
                                </span>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="text-sm font-black italic text-rose-400">{{ $u->min_score ?? '—' }}</span>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="text-sm font-black italic {{ $u->averageScore >= 80 ? 'text-emerald-600' : ($u->averageScore >= 60 ? 'text-amber-500' : ($u->averageScore > 0 ? 'text-red-500' : 'text-slate-200')) }}">
                                    {{ $u->averageScore > 0 ? number_format($u->averageScore, 2) : '—' }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="text-sm font-black italic text-emerald-500">{{ $u->max_score ?? '—' }}</span>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <input wire:model="pegawaiPimpinanFormState.{{ $u->id }}.score"
                                    type="number" min="1" max="100"
                                    class="w-20 bg-slate-50 border rounded-lg px-2 py-1.5 text-center text-sm font-black text-minimal-indigo focus:ring-4 focus:ring-minimal-indigo/10 focus:border-minimal-indigo transition-all shadow-inner {{ $u->pimpinan_score !== null ? 'border-emerald-300' : 'border-slate-200' }}"
                                    placeholder="—">
                            </td>
                            <td class="px-4 py-4 text-center">
                                @if($u->pimpinan_score !== null)
                                    <span class="text-lg font-black italic {{ $u->nilai_akhir >= 80 ? 'text-emerald-600' : ($u->nilai_akhir >= 60 ? 'text-amber-500' : 'text-red-500') }}">{{ number_format($u->nilai_akhir, 2) }}</span>
                                    <p class="text-[7px] font-black uppercase tracking-widest text-minimal-indigo mt-0.5">Pimpinan</p>
                                @elseif($u->nilai_akhir !== null)
                                    <span class="text-lg font-black italic {{ $u->nilai_akhir >= 80 ? 'text-emerald-600' : ($u->nilai_akhir >= 60 ? 'text-amber-500' : 'text-red-500') }}">{{ number_format($u->nilai_akhir, 2) }}</span>
                                    <p class="text-[7px] font-black uppercase tracking-widest text-slate-300 mt-0.5">Avg Tim</p>
                                @else
                                    <span class="text-lg font-black italic text-slate-200">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button wire:click="savePimpinanPegawaiScore('{{ $u->id }}')" wire:loading.attr="disabled"
                                        wire:target="savePimpinanPegawaiScore('{{ $u->id }}')"
                                        class="px-3 py-1.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-[9px] font-black uppercase tracking-widest hover:bg-emerald-100 transition-all active:scale-95 disabled:opacity-50">
                                        <span wire:loading.remove wire:target="savePimpinanPegawaiScore('{{ $u->id }}')">✓ Simpan</span>
                                        <span wire:loading wire:target="savePimpinanPegawaiScore('{{ $u->id }}')">...</span>
                                    </button>
                                    @if($isOpen)
                                    <button wire:click="$set('detailUserId', null)"
                                        class="px-3 py-1.5 rounded-2xl bg-amber-50 border border-amber-200 text-amber-700 text-[9px] font-black uppercase tracking-widest hover:bg-amber-100 transition-all active:scale-95">Tutup ×</button>
                                    @else
                                    <button wire:click="setDetailUser('{{ $u->id }}')"
                                        class="px-3 py-1.5 rounded-2xl bg-slate-50 border border-slate-200 text-slate-500 text-[9px] font-black uppercase tracking-widest hover:border-minimal-indigo hover:text-minimal-indigo transition-all active:scale-95">Detail</button>
                                    @endif
                                    @if($u->pimpinan_score !== null)
                                    <button wire:click="resetPimpinanPegawaiScore('{{ $u->id }}')" wire:loading.attr="disabled"
                                        wire:target="resetPimpinanPegawaiScore('{{ $u->id }}')"
                                        class="px-3 py-1.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-600 text-[9px] font-black uppercase tracking-widest hover:bg-rose-100 transition-all active:scale-95 disabled:opacity-50">
                                        <span wire:loading.remove wire:target="resetPimpinanPegawaiScore('{{ $u->id }}')">Reset</span>
                                        <span wire:loading wire:target="resetPimpinanPegawaiScore('{{ $u->id }}')">...</span>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @if($isOpen)
                        @php $sc = $scoringConfig; @endphp
                        <tr style="background: linear-gradient(135deg, #eef2ff 0%, #f5f3ff 100%); border-top: 2px solid #6366f1;">
                            <td colspan="9" class="px-6 py-5" style="border-left: 4px solid #6366f1;">
                                <div class="space-y-3 animate-in fade-in slide-in-from-top-1 duration-200">
                                    {{-- Warning strip --}}
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-lg bg-minimal-indigo/10 flex items-center justify-center flex-shrink-0">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-minimal-indigo" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                            </div>
                                            <p class="text-[10px] font-black text-minimal-indigo uppercase tracking-widest">Edit Nilai Pegawai — Hak Prerogatif Pimpinan</p>
                                        </div>
                                        <span class="text-[8px] font-black text-minimal-indigo/50 uppercase tracking-widest">Perubahan menimpa nilai ketua tim</span>
                                    </div>

                                    @if(empty($overrideFormState))
                                        <p class="text-center text-slate-400 text-xs py-6 italic font-bold">Belum ada penilaian dari Ketua Tim untuk periode ini.</p>
                                    @else
                                    <div class="bg-white rounded-2xl border border-minimal-indigo/20 overflow-hidden shadow-md">
                                        <table class="w-full text-left">
                                            <thead style="background:#eef2ff">
                                                <tr class="text-[9px] font-black text-slate-400 uppercase tracking-[0.15em]">
                                                    <th class="px-4 py-3 border-b border-slate-100">Tim / Ketua</th>
                                                    <th class="px-4 py-3 border-b border-slate-100 text-center">Nilai Dasar</th>
                                                    <th class="px-4 py-3 border-b border-slate-100">Volume</th>
                                                    <th class="px-4 py-3 border-b border-slate-100">Kualitas</th>
                                                    <th class="px-4 py-3 border-b border-slate-100 text-center">Nilai Akhir</th>
                                                    <th class="px-4 py-3 border-b border-slate-100 text-right">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-50">
                                                @foreach($overrideFormState as $ratingId => $entry)
                                                <tr
                                                    x-data="{
                                                        hasWork: {{ $entry['has_work'] ? 'true' : 'false' }},
                                                        score: '{{ $entry['score'] }}',
                                                        volume: '{{ $entry['volume_work'] ?? 'Sedang' }}',
                                                        quality: '{{ $entry['quality_work'] ?? 'Cukup' }}',
                                                        isDirty: false,
                                                        overridden: {{ $entry['overridden'] ? 'true' : 'false' }},
                                                        saving: false,
                                                        cfg: @js($sc),
                                                        get finalScore() {
                                                            if (!this.hasWork) return 'N/A';
                                                            if (!this.score) return '-';
                                                            let v = this.volume === 'Berat' ? this.cfg.volume_berat : (this.volume === 'Ringan' ? this.cfg.volume_ringan : this.cfg.volume_sedang);
                                                            let q = this.quality === 'Sangat Baik' ? this.cfg.quality_sangat_baik : (this.quality === 'Baik' ? this.cfg.quality_baik : (this.quality === 'Kurang' ? this.cfg.quality_kurang : this.cfg.quality_cukup));
                                                            return ((this.score * this.cfg.weight_score / 100) + (v * this.cfg.weight_volume / 100) + (q * this.cfg.weight_quality / 100)).toFixed(2);
                                                        },
                                                        async save() {
                                                            this.saving = true;
                                                            await $wire.saveOverride('{{ $ratingId }}', this.score, this.volume, this.quality);
                                                            this.isDirty = false;
                                                            this.overridden = true;
                                                            this.saving = false;
                                                        }
                                                    }"
                                                    class="transition-colors"
                                                    :class="overridden ? 'bg-amber-50/50' : ''"
                                                >
                                                    <td class="px-4 py-3">
                                                        <div class="flex items-center gap-2">
                                                            <div class="w-1 h-8 rounded-full flex-shrink-0" :class="overridden ? 'bg-amber-400' : 'bg-slate-200'"></div>
                                                            <div>
                                                                <p class="text-[10px] font-black text-minimal-indigo uppercase italic tracking-tight">{{ $entry['team_name'] }}</p>
                                                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">KT: {{ $entry['leader_name'] }}</p>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-3 text-center">
                                                        @if($entry['has_work'])
                                                        <input
                                                            x-model="score"
                                                            @input="isDirty = true"
                                                            type="number" min="1" max="100"
                                                            class="w-16 bg-slate-50 border border-slate-200 rounded-lg px-2 py-1.5 text-center text-sm font-black text-minimal-indigo focus:ring-4 focus:ring-minimal-indigo/10 focus:border-minimal-indigo transition-all shadow-inner"
                                                            placeholder="--"
                                                        >
                                                        @else
                                                        <span class="text-xs font-black text-slate-300 italic">N/A</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        @if($entry['has_work'])
                                                        <div class="flex gap-1">
                                                            @foreach(['Ringan', 'Sedang', 'Berat'] as $v)
                                                            <button
                                                                @click="volume = '{{ $v }}'; isDirty = true"
                                                                class="px-2.5 py-1.5 rounded-lg text-[8px] font-black uppercase tracking-tight transition-all border"
                                                                :class="volume === '{{ $v }}' ? 'bg-minimal-indigo border-minimal-indigo text-white shadow-sm' : 'bg-slate-50 border-slate-200 text-slate-400 hover:border-minimal-indigo/30 hover:text-minimal-indigo'"
                                                            >{{ $v }}</button>
                                                            @endforeach
                                                        </div>
                                                        @else
                                                        <span class="text-xs text-slate-300 font-bold italic">—</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        @if($entry['has_work'])
                                                        <div class="flex gap-1 flex-wrap">
                                                            @foreach(['Kurang', 'Cukup', 'Baik', 'Sangat Baik'] as $q)
                                                            <button
                                                                @click="quality = '{{ $q }}'; isDirty = true"
                                                                class="px-2.5 py-1.5 rounded-lg text-[8px] font-black uppercase tracking-tight transition-all border"
                                                                :class="quality === '{{ $q }}' ? 'bg-minimal-indigo border-minimal-indigo text-white shadow-sm' : 'bg-slate-50 border-slate-200 text-slate-400 hover:border-minimal-indigo/30 hover:text-minimal-indigo'"
                                                            >{{ $q }}</button>
                                                            @endforeach
                                                        </div>
                                                        @else
                                                        <span class="text-xs text-slate-300 font-bold italic">—</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-3 text-center">
                                                        <template x-if="overridden">
                                                            <span class="flex items-center justify-center gap-1 mb-1 px-2 py-0.5 bg-amber-100 text-amber-700 text-[8px] font-black rounded-full uppercase tracking-tight mx-auto w-fit">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                                                Diubah
                                                            </span>
                                                        </template>
                                                        <span x-text="finalScore" class="text-lg font-black italic" :class="finalScore === '-' || finalScore === 'N/A' ? 'text-slate-300' : 'text-minimal-indigo'"></span>
                                                    </td>
                                                    <td class="px-4 py-3 text-right">
                                                        <div class="flex gap-1.5 items-center justify-end">
                                                            <button
                                                                @click="save()"
                                                                :disabled="!isDirty || saving"
                                                                class="px-3 py-1.5 rounded-lg text-[9px] font-black uppercase tracking-widest transition-all active:scale-95 disabled:opacity-50"
                                                                :class="isDirty && !saving ? 'bg-slate-900 text-white shadow-md' : 'bg-slate-100 text-slate-300 cursor-not-allowed'"
                                                            >
                                                                <span x-text="saving ? '...' : 'Simpan'"></span>
                                                            </button>
                                                            <template x-if="overridden">
                                                                <button
                                                                    wire:click="resetOverride('{{ $ratingId }}')"
                                                                    class="px-3 py-1.5 rounded-lg text-[9px] font-black uppercase bg-white text-amber-600 border border-amber-200 hover:bg-amber-50 transition-all active:scale-95"
                                                                >Hapus Flag</button>
                                                            </template>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- ===== TAMPILAN MOBILE (kartu, < md) ===== --}}
            <div class="block md:hidden divide-y divide-slate-50">
                @forelse($rekap as $u)
                @php $allDone = $u->totalTeams > 0 && $u->ratedTeams === $u->totalTeams; $isOpen = $detailUserId === $u->id; @endphp
                <div class="transition-colors" style="{{ $isOpen ? 'background:linear-gradient(135deg,#eef2ff 0%,#f5f3ff 100%);border-left:4px solid #6366f1' : '' }}">
                    <div class="px-4 pt-3 pb-3 flex flex-col gap-2.5">
                        {{-- Baris atas: avatar + nama + badge plotting + score akhir --}}
                        <div class="flex items-center gap-3">
                            <div class="shrink-0 w-9 h-9 rounded-xl flex items-center justify-center font-black italic shadow-inner text-[11px]" style="{{ $isOpen ? 'background:#6366f1;color:#fff' : 'background:linear-gradient(135deg,#f1f5f9,#e2e8f0);color:#94a3b8' }}">
                                {{ substr($u->name, 0, 1) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-[12px] font-black {{ $isOpen ? 'text-minimal-indigo' : 'text-slate-800' }} uppercase tracking-tight leading-none truncate">{{ $u->name }}</p>
                                <p class="text-[9px] font-mono font-bold text-slate-400 uppercase mt-0.5">{{ $u->nip }}</p>
                            </div>
                            <div class="flex-shrink-0 text-right">
                                @if($u->nilai_akhir !== null)
                                    <p class="text-xl font-black italic {{ $u->nilai_akhir >= 80 ? 'text-emerald-600' : ($u->nilai_akhir >= 60 ? 'text-amber-500' : 'text-red-500') }} leading-none">{{ number_format($u->nilai_akhir, 2) }}</p>
                                    <p class="text-[7px] font-black uppercase tracking-widest mt-0.5 {{ $u->pimpinan_score !== null ? 'text-minimal-indigo' : 'text-slate-300' }}">{{ $u->pimpinan_score !== null ? 'Pimpinan' : 'Avg Tim' }}</p>
                                @else
                                    <p class="text-xl font-black italic text-slate-200 leading-none">—</p>
                                    <p class="text-[7px] font-black uppercase tracking-widest text-slate-200 mt-0.5">Belum</p>
                                @endif
                            </div>
                        </div>

                        {{-- Stats row: Min / Avg / Max --}}
                        <div class="grid grid-cols-3 gap-2">
                            <div class="rounded-xl px-2 py-1.5 text-center bg-rose-50">
                                <p class="text-[7px] font-black uppercase tracking-widest text-rose-400">Min Tim</p>
                                <p class="text-sm font-black italic text-rose-500">{{ $u->min_score ?? '—' }}</p>
                            </div>
                            <div class="rounded-xl px-2 py-1.5 text-center bg-slate-50">
                                <p class="text-[7px] font-black uppercase tracking-widest text-slate-400">Avg Tim</p>
                                <p class="text-sm font-black italic {{ $u->averageScore >= 80 ? 'text-emerald-600' : ($u->averageScore >= 60 ? 'text-amber-500' : ($u->averageScore > 0 ? 'text-red-500' : 'text-slate-300')) }}">
                                    {{ $u->averageScore > 0 ? number_format($u->averageScore, 2) : '—' }}
                                </p>
                            </div>
                            <div class="rounded-xl px-2 py-1.5 text-center bg-emerald-50">
                                <p class="text-[7px] font-black uppercase tracking-widest text-emerald-500">Max Tim</p>
                                <p class="text-sm font-black italic text-emerald-600">{{ $u->max_score ?? '—' }}</p>
                            </div>
                        </div>

                        {{-- Nilai Pimpinan input + actions --}}
                        <div class="flex items-center gap-2 h-10 px-3 rounded-xl bg-slate-50 border {{ $u->pimpinan_score !== null ? 'border-emerald-200' : 'border-slate-200' }} focus-within:ring-4 focus-within:ring-minimal-indigo/10 focus-within:border-minimal-indigo transition-all">
                            <span class="text-[9px] font-black uppercase tracking-widest text-slate-400 flex-shrink-0">Nilai Pimpinan</span>
                            <input wire:model="pegawaiPimpinanFormState.{{ $u->id }}.score"
                                type="number" min="1" max="100"
                                class="flex-1 h-full bg-transparent text-sm font-black text-center text-minimal-indigo outline-none border-none focus:ring-0"
                                placeholder="—">
                        </div>
                        <div class="flex items-center gap-2">
                            <button wire:click="savePimpinanPegawaiScore('{{ $u->id }}')" wire:loading.attr="disabled"
                                wire:target="savePimpinanPegawaiScore('{{ $u->id }}')"
                                class="flex-1 py-2.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-[9px] font-black uppercase tracking-widest active:scale-95 disabled:opacity-50 transition-all">
                                <span wire:loading.remove wire:target="savePimpinanPegawaiScore('{{ $u->id }}')">✓ Simpan</span>
                                <span wire:loading wire:target="savePimpinanPegawaiScore('{{ $u->id }}')">...</span>
                            </button>
                            @if($isOpen)
                            <button wire:click="$set('detailUserId', null)"
                                class="flex-1 py-2.5 rounded-2xl bg-amber-50 border border-amber-200 text-amber-700 text-[9px] font-black uppercase tracking-widest active:scale-95 transition-all">Tutup ×</button>
                            @else
                            <button wire:click="setDetailUser('{{ $u->id }}')"
                                class="flex-1 py-2.5 rounded-2xl bg-slate-50 border border-slate-200 text-slate-500 text-[9px] font-black uppercase tracking-widest hover:border-minimal-indigo hover:text-minimal-indigo active:scale-95 transition-all">Detail</button>
                            @endif
                            @if($u->pimpinan_score !== null)
                            <button wire:click="resetPimpinanPegawaiScore('{{ $u->id }}')" wire:loading.attr="disabled"
                                wire:target="resetPimpinanPegawaiScore('{{ $u->id }}')"
                                class="flex-1 py-2.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-600 text-[9px] font-black uppercase tracking-widest active:scale-95 disabled:opacity-50 transition-all">
                                <span wire:loading.remove wire:target="resetPimpinanPegawaiScore('{{ $u->id }}')">Reset</span>
                                <span wire:loading wire:target="resetPimpinanPegawaiScore('{{ $u->id }}')">...</span>
                            </button>
                            @endif
                        </div>
                    </div>

                    {{-- Expanded override panel (mobile) --}}
                    @if($isOpen)
                    @php $sc = $scoringConfig; @endphp
                    <div class="px-4 pb-4 space-y-2 animate-in fade-in slide-in-from-top-1 duration-200">
                        {{-- Header strip --}}
                        <div class="flex items-center gap-2 px-3 py-2.5 rounded-xl" style="background:#e0e7ff">
                            <div class="w-5 h-5 rounded-md flex items-center justify-center flex-shrink-0" style="background:#6366f1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </div>
                            <p class="text-[8px] font-black uppercase tracking-widest" style="color:#4338ca">Edit Nilai — Hak Prerogatif Pimpinan</p>
                        </div>

                        @if(empty($overrideFormState))
                            <p class="text-center text-slate-400 text-xs py-4 italic font-bold">Belum ada penilaian dari Ketua Tim.</p>
                        @else
                        @foreach($overrideFormState as $ratingId => $entry)
                        <div
                            x-data="{
                                hasWork: {{ $entry['has_work'] ? 'true' : 'false' }},
                                score: '{{ $entry['score'] }}',
                                volume: '{{ $entry['volume_work'] ?? 'Sedang' }}',
                                quality: '{{ $entry['quality_work'] ?? 'Cukup' }}',
                                isDirty: false,
                                overridden: {{ $entry['overridden'] ? 'true' : 'false' }},
                                saving: false,
                                cfg: @js($sc),
                                get finalScore() {
                                    if (!this.hasWork) return 'N/A';
                                    if (!this.score) return '-';
                                    let v = this.volume === 'Berat' ? this.cfg.volume_berat : (this.volume === 'Ringan' ? this.cfg.volume_ringan : this.cfg.volume_sedang);
                                    let q = this.quality === 'Sangat Baik' ? this.cfg.quality_sangat_baik : (this.quality === 'Baik' ? this.cfg.quality_baik : (this.quality === 'Kurang' ? this.cfg.quality_kurang : this.cfg.quality_cukup));
                                    return ((this.score * this.cfg.weight_score / 100) + (v * this.cfg.weight_volume / 100) + (q * this.cfg.weight_quality / 100)).toFixed(2);
                                },
                                async save() {
                                    this.saving = true;
                                    await $wire.saveOverride('{{ $ratingId }}', this.score, this.volume, this.quality);
                                    this.isDirty = false;
                                    this.overridden = true;
                                    this.saving = false;
                                }
                            }"
                            class="rounded-2xl border overflow-hidden transition-all"
                            :class="overridden ? 'border-amber-200' : 'border-slate-100'"
                        >
                            {{-- Card header --}}
                            <div class="flex items-center justify-between px-4 py-2.5" :class="overridden ? 'bg-amber-50' : 'bg-slate-50'">
                                <div class="flex items-center gap-2">
                                    <div class="w-1 h-7 rounded-full flex-shrink-0" :class="overridden ? 'bg-amber-400' : 'bg-slate-200'"></div>
                                    <div>
                                        <p class="text-[10px] font-black text-minimal-indigo uppercase italic tracking-tight">{{ $entry['team_name'] }}</p>
                                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">KT: {{ $entry['leader_name'] }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <template x-if="overridden">
                                        <span class="px-2 py-0.5 bg-amber-100 text-amber-700 text-[8px] font-black rounded-full uppercase">Diubah</span>
                                    </template>
                                    <div class="text-right">
                                        <span x-text="finalScore" class="text-xl font-black italic leading-none" :class="finalScore === '-' || finalScore === 'N/A' ? 'text-slate-300' : 'text-minimal-indigo'"></span>
                                        <p class="text-[8px] text-slate-300 font-bold uppercase tracking-widest">akhir</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Card body --}}
                            <div class="px-4 py-3 bg-white space-y-2.5">
                                @if(!$entry['has_work'])
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest italic">Tidak Ada Pekerjaan — skor 0</p>
                                @else
                                <div class="flex items-center gap-3">
                                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest w-24 flex-shrink-0">Nilai Dasar</label>
                                    <div class="flex items-center gap-2">
                                        <input
                                            x-model="score"
                                            @input="isDirty = true"
                                            type="number" min="1" max="100"
                                            class="w-16 bg-slate-50 border border-slate-200 rounded-lg px-2 py-1.5 text-center text-sm font-black text-minimal-indigo focus:ring-4 focus:ring-minimal-indigo/10 transition-all shadow-inner"
                                            placeholder="--"
                                        >
                                        <span class="text-[9px] text-slate-300 font-bold">/ 100</span>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest w-24 flex-shrink-0 mt-2">Volume</label>
                                    <div class="flex gap-1 flex-wrap">
                                        @foreach(['Ringan', 'Sedang', 'Berat'] as $v)
                                        <button
                                            @click="volume = '{{ $v }}'; isDirty = true"
                                            class="px-3 py-1.5 rounded-lg text-[8px] font-black uppercase tracking-tight transition-all border"
                                            :class="volume === '{{ $v }}' ? 'bg-minimal-indigo border-minimal-indigo text-white' : 'bg-slate-50 border-slate-200 text-slate-400 hover:border-minimal-indigo/30'"
                                        >{{ $v }}</button>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest w-24 flex-shrink-0 mt-2">Kualitas</label>
                                    <div class="flex gap-1 flex-wrap">
                                        @foreach(['Kurang', 'Cukup', 'Baik', 'Sangat Baik'] as $q)
                                        <button
                                            @click="quality = '{{ $q }}'; isDirty = true"
                                            class="px-3 py-1.5 rounded-lg text-[8px] font-black uppercase tracking-tight transition-all border"
                                            :class="quality === '{{ $q }}' ? 'bg-minimal-indigo border-minimal-indigo text-white' : 'bg-slate-50 border-slate-200 text-slate-400 hover:border-minimal-indigo/30'"
                                        >{{ $q }}</button>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                            </div>

                            {{-- Card actions --}}
                            <div class="px-4 py-2.5 border-t border-slate-100 bg-white flex gap-2">
                                <button
                                    @click="save()"
                                    :disabled="!isDirty || saving"
                                    class="flex-1 py-2 rounded-xl text-[9px] font-black uppercase tracking-widest transition-all active:scale-95 disabled:opacity-50"
                                    :class="isDirty && !saving ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-300 cursor-not-allowed'"
                                >
                                    <span x-text="saving ? '...' : 'Simpan'"></span>
                                </button>
                                <template x-if="overridden">
                                    <button
                                        wire:click="resetOverride('{{ $ratingId }}')"
                                        class="px-4 py-2 rounded-xl text-[9px] font-black uppercase bg-white text-amber-600 border border-amber-200 hover:bg-amber-50 transition-all active:scale-95 flex-shrink-0"
                                    >Hapus Flag</button>
                                </template>
                            </div>
                        </div>
                        @endforeach
                        @endif
                    </div>
                    @endif
                </div>
                @empty
                <div class="px-4 py-10 text-center text-slate-300 font-bold italic text-xs">Tidak ada data.</div>
                @endforelse
            </div>
        </div>
    </div>
    @elseif($activeTab === 'input-kt')
        {{-- ===== INPUT NILAI KETUA TIM ===== --}}
        <div class="space-y-6 animate-in fade-in duration-500">

            {{-- Flash success --}}
            @if(session('kt_success'))
                <div class="flex items-center gap-4 p-4 bg-emerald-50 border border-emerald-100 rounded-2xl animate-in fade-in duration-300">
                    <div class="w-7 h-7 bg-emerald-500 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                    <p class="text-xs font-black text-emerald-700 uppercase tracking-tight">{{ session('kt_success') }}</p>
                </div>
            @endif

            {{-- Search bar + Export --}}
            <div class="flex items-center gap-3">
                <div class="flex-1 flex items-center gap-3 h-12 px-4 rounded-2xl bg-slate-50/80 border border-slate-100 shadow-inner focus-within:ring-4 focus-within:ring-minimal-indigo/10 transition-all group">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 flex-shrink-0 text-slate-300 group-focus-within:text-minimal-indigo transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input wire:model.live.debounce.300ms="ktSearch" type="text" placeholder="Cari nama atau NIP ketua tim..."
                        class="flex-1 h-full bg-transparent text-sm font-bold text-slate-700 placeholder:text-slate-300 placeholder:font-medium outline-none border-none focus:ring-0">
                    @if($ktSearch)
                    <button wire:click="$set('ktSearch','')" class="flex-shrink-0 text-slate-300 hover:text-slate-500 transition-colors text-sm font-black">✕</button>
                    @endif
                </div>
                <a href="{{ route('export.ketuaTim', ['month' => $month, 'year' => $year]) }}"
                   class="inline-flex items-center gap-2 h-12 px-4 rounded-2xl bg-emerald-500 hover:bg-emerald-600 active:scale-95 text-white text-[9px] font-black uppercase tracking-widest transition-all shadow-sm flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Export .xlsx
                </a>
            </div>

            @if(empty($ktData))
                <div class="bg-white py-20 rounded-[3rem] border-2 border-dashed border-slate-200 text-center">
                    <p class="text-xl font-black text-slate-400 uppercase italic">{{ $ktSearch ? 'Tidak Ada Hasil' : 'Tidak Ada Ketua Tim' }}</p>
                    <p class="text-xs text-slate-300 font-bold uppercase tracking-widest mt-1">{{ $ktSearch ? 'Coba kata kunci lain.' : 'Belum ada pengguna dengan role Ketua Tim yang terdaftar.' }}</p>
                </div>
            @else

                {{-- ===== MOBILE CARDS (< md) ===== --}}
                <div class="block md:hidden space-y-4">
                    @foreach($ktData as $ktId => $kt)
                        {{-- KT Header --}}
                        <div class="bg-white rounded-2xl px-5 py-4 flex items-center justify-between border border-slate-100 shadow-sm">
                            <div>
                                <p class="text-sm font-black text-slate-800 uppercase tracking-tight leading-none">{{ $kt['ketua_name'] }}</p>
                                <p class="text-[9px] font-mono font-bold text-slate-400 mt-1">NIP: {{ $kt['ketua_nip'] }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[8px] font-black uppercase tracking-widest text-slate-400">Nilai Akhir</p>
                                <p class="text-2xl font-black italic text-minimal-indigo leading-none mt-0.5">{{ $kt['nilai_akhir'] ?? '-' }}</p>
                            </div>
                        </div>

                        {{-- Per-team sub-cards --}}
                        <div class="space-y-2 ml-3">
                            @foreach($kt['teams'] as $team)
                                @php $key = $team['key']; @endphp
                                <div
                                    x-data="{
                                        score: @entangle('ktFormState.' . $key . '.score'),
                                        isDirty: @entangle('ktFormState.' . $key . '.is_dirty'),
                                        isRated: @entangle('ktFormState.' . $key . '.is_rated'),
                                    }"
                                    class="bg-white rounded-xl shadow-sm overflow-hidden border-l-4 transition-all"
                                    :class="!isRated ? 'border border-rose-200 border-l-rose-400' : 'border border-emerald-100 border-l-emerald-400'"
                                >
                                    {{-- Tim header --}}
                                    <div class="flex items-center justify-between px-4 py-2.5 bg-slate-50/60 border-b border-slate-100">
                                        <span class="text-[10px] font-black text-minimal-indigo uppercase italic tracking-tight">{{ $team['team_name'] }}</span>
                                        <span
                                            class="text-[9px] font-black uppercase px-2 py-0.5 rounded-full"
                                            :class="isRated ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-500'"
                                            x-text="isRated ? '✓ Dinilai' : '○ Belum'"
                                        ></span>
                                    </div>

                                    {{-- Rekomendasi --}}
                                    <div class="px-4 py-2.5 border-b border-slate-50 grid grid-cols-3 gap-2">
                                        <div class="text-center">
                                            <p class="text-[8px] font-black uppercase tracking-widest text-slate-400">Rata-rata</p>
                                            <p class="text-sm font-black {{ $team['avg'] ? 'text-minimal-indigo' : 'text-slate-300' }} italic">{{ $team['avg'] ?? '-' }}</p>
                                        </div>
                                        <div class="text-center border-x border-slate-100">
                                            <p class="text-[8px] font-black uppercase tracking-widest text-slate-400">Kuartil 3</p>
                                            <p class="text-sm font-black {{ $team['q3'] ? 'text-amber-500' : 'text-slate-300' }} italic">{{ $team['q3'] ?? '-' }}</p>
                                        </div>
                                        <div class="text-center">
                                            <p class="text-[8px] font-black uppercase tracking-widest text-slate-400">Tertinggi</p>
                                            <p class="text-sm font-black {{ $team['max'] ? 'text-emerald-600' : 'text-slate-300' }} italic">{{ $team['max'] ?? '-' }}</p>
                                        </div>
                                    </div>

                                    {{-- Input + Aksi --}}
                                    <div class="px-4 py-3 flex items-center gap-3">
                                        <span class="text-xs font-black text-slate-500 uppercase tracking-wide flex-shrink-0">Nilai</span>
                                        <input
                                            x-model="score"
                                            @input="isDirty = true"
                                            type="number" min="1" max="100"
                                            class="w-20 bg-slate-50 border border-slate-200 rounded-lg px-2 py-1.5 text-center text-sm font-black text-minimal-indigo focus:ring-4 focus:ring-minimal-indigo/10 focus:border-minimal-indigo transition-all shadow-inner"
                                            placeholder="--"
                                        >
                                        <button
                                            wire:click="saveKetuaTimRating('{{ $key }}')"
                                            wire:loading.attr="disabled"
                                            class="flex-1 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all active:scale-95 disabled:opacity-50"
                                            :class="isDirty ? 'bg-slate-900 text-white' : (isRated ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-slate-100 text-slate-400 cursor-not-allowed')"
                                            :disabled="!isDirty && !isRated"
                                        >
                                            <span wire:loading.remove wire:target="saveKetuaTimRating('{{ $key }}')">
                                                <template x-if="isRated && !isDirty"><span>✓ Simpan</span></template>
                                                <template x-if="!isRated || isDirty"><span>Simpan</span></template>
                                            </span>
                                            <span wire:loading wire:target="saveKetuaTimRating('{{ $key }}')">...</span>
                                        </button>
                                        @if($ktFormState[$key]['is_rated'])
                                            <button wire:click="confirmResetKt('{{ $key }}')" class="px-3 py-2 rounded-xl text-[9px] font-black uppercase bg-rose-50 text-rose-500 border border-rose-100 active:scale-95">Reset</button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>

                {{-- ===== DESKTOP TABLE (md+) ===== --}}
                <div class="hidden md:block bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden">
                    @php
                        $ktSortIcon = function($col) use ($ktSortKey, $ktSortDir) {
                            if ($col !== $ktSortKey) return '<svg xmlns="http://www.w3.org/2000/svg" class="inline w-3 h-3 text-slate-300 ml-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M7 15l5 5 5-5"/><path d="M7 9l5-5 5 5"/></svg>';
                            return $ktSortDir === 'asc'
                                ? '<svg xmlns="http://www.w3.org/2000/svg" class="inline w-3 h-3 text-minimal-indigo ml-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 15l7-7 7 7"/></svg>'
                                : '<svg xmlns="http://www.w3.org/2000/svg" class="inline w-3 h-3 text-minimal-indigo ml-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M19 9l-7 7-7-7"/></svg>';
                        };
                    @endphp
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50/80 sticky top-0 z-10">
                                <tr class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                                    <th class="px-5 py-4 border-b border-slate-100">
                                        <button wire:click="sortKt('ketua_name')" class="flex items-center gap-0.5 hover:text-minimal-indigo transition-colors {{ $ktSortKey === 'ketua_name' ? 'text-minimal-indigo' : '' }}">
                                            Ketua Tim {!! $ktSortIcon('ketua_name') !!}
                                        </button>
                                    </th>
                                    <th class="px-4 py-4 border-b border-slate-100 text-center">
                                        <button wire:click="sortKt('nilai_akhir')" class="flex items-center gap-0.5 mx-auto hover:text-minimal-indigo transition-colors {{ $ktSortKey === 'nilai_akhir' ? 'text-minimal-indigo' : '' }}">
                                            Nilai Akhir KT {!! $ktSortIcon('nilai_akhir') !!}
                                        </button>
                                    </th>
                                    <th class="px-5 py-4 border-b border-slate-100">Tim Kerja</th>
                                    <th class="px-4 py-4 border-b border-slate-100 text-center">Rata-rata Tim</th>
                                    <th class="px-4 py-4 border-b border-slate-100 text-center text-amber-500">Kuartil 3</th>
                                    <th class="px-4 py-4 border-b border-slate-100 text-center text-emerald-600">Tertinggi</th>
                                    <th class="px-4 py-4 border-b border-slate-100 text-center">Nilai</th>
                                    <th class="px-5 py-4 border-b border-slate-100 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ktData as $ktId => $kt)
                                    @foreach($kt['teams'] as $tIdx => $team)
                                        @php $key = $team['key']; @endphp
                                        <tr
                                            x-data="{
                                                score: @entangle('ktFormState.' . $key . '.score'),
                                                isDirty: @entangle('ktFormState.' . $key . '.is_dirty'),
                                                isRated: @entangle('ktFormState.' . $key . '.is_rated'),
                                            }"
                                            class="group border-t border-slate-50 transition-colors"
                                            :class="!isRated ? 'bg-rose-50/30' : 'hover:bg-slate-50/50'"
                                        >
                                            {{-- Ketua Tim cell: rowspan, only first team --}}
                                            @if($tIdx === 0)
                                                <td rowspan="{{ count($kt['teams']) }}" class="px-5 py-4 align-top border-r border-slate-50 bg-white">
                                                    <p class="text-[11px] font-black text-slate-800 uppercase tracking-tight leading-none">{{ $kt['ketua_name'] }}</p>
                                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-1">NIP: {{ $kt['ketua_nip'] }}</p>
                                                    @php
                                                        $allRated = collect($kt['teams'])->every(fn($t) => $ktFormState[$t['key']]['is_rated']);
                                                    @endphp
                                                    @if(!$allRated)
                                                        <span class="mt-2 inline-flex items-center gap-1 px-2 py-0.5 bg-rose-100 text-rose-600 text-[8px] font-black rounded-full uppercase tracking-tight">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                                            Belum Lengkap
                                                        </span>
                                                    @endif
                                                </td>
                                                {{-- Nilai Akhir KT: rowspan --}}
                                                <td rowspan="{{ count($kt['teams']) }}" class="px-4 py-4 align-middle text-center border-r border-slate-50 bg-white">
                                                    <span class="text-2xl font-black italic {{ $kt['nilai_akhir'] ? 'text-minimal-indigo' : 'text-slate-200' }}">
                                                        {{ $kt['nilai_akhir'] ?? '-' }}
                                                    </span>
                                                    @if($kt['nilai_akhir'])
                                                        <p class="text-[8px] text-slate-300 font-bold uppercase tracking-widest mt-0.5">avg</p>
                                                    @endif
                                                </td>
                                            @endif

                                            <td class="px-5 py-4 font-black text-minimal-indigo uppercase text-[10px] italic tracking-tight">{{ $team['team_name'] }}</td>

                                            <td class="px-4 py-4 text-center">
                                                <span class="text-base font-black italic {{ $team['avg'] ? 'text-slate-700' : 'text-slate-200' }}">{{ $team['avg'] ?? '-' }}</span>
                                                @if($team['rated_count'] > 0)
                                                    <p class="text-[8px] text-slate-300 font-bold uppercase tracking-widest">{{ $team['rated_count'] }} peg</p>
                                                @endif
                                            </td>
                                            <td class="px-4 py-4 text-center">
                                                <span class="text-base font-black italic {{ $team['q3'] ? 'text-amber-500' : 'text-slate-200' }}">{{ $team['q3'] ?? '-' }}</span>
                                            </td>
                                            <td class="px-4 py-4 text-center">
                                                <span class="text-base font-black italic {{ $team['max'] ? 'text-emerald-600' : 'text-slate-200' }}">{{ $team['max'] ?? '-' }}</span>
                                            </td>

                                            <td class="px-3 py-4 text-center">
                                                <input
                                                    x-model="score"
                                                    @input="isDirty = true"
                                                    type="number" min="1" max="100"
                                                    class="w-16 bg-slate-50 border border-slate-200 rounded-lg px-2 py-1.5 text-center text-sm font-black text-minimal-indigo focus:ring-4 focus:ring-minimal-indigo/10 focus:border-minimal-indigo transition-all shadow-inner"
                                                    placeholder="--"
                                                >
                                            </td>
                                            <td class="px-4 py-4 text-right">
                                                <div class="flex gap-2 items-center justify-end">
                                                    <button
                                                        wire:click="saveKetuaTimRating('{{ $key }}')"
                                                        wire:loading.attr="disabled"
                                                        class="px-4 py-2 rounded-xl text-[9px] font-black uppercase tracking-widest transition-all active:scale-95 disabled:opacity-50"
                                                        :class="isDirty ? 'bg-slate-900 text-white shadow-md' : (isRated ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-slate-100 text-slate-300 cursor-not-allowed')"
                                                        :disabled="!isDirty && !isRated"
                                                    >
                                                        <span wire:loading.remove wire:target="saveKetuaTimRating('{{ $key }}')">
                                                            <template x-if="isRated && !isDirty"><span>✓ Simpan</span></template>
                                                            <template x-if="!isRated || isDirty"><span>Simpan</span></template>
                                                        </span>
                                                        <span wire:loading wire:target="saveKetuaTimRating('{{ $key }}')">...</span>
                                                    </button>
                                                    @if($ktFormState[$key]['is_rated'])
                                                        <button wire:click="confirmResetKt('{{ $key }}')" class="px-3 py-2 rounded-xl text-[9px] font-black uppercase tracking-widest bg-rose-50 text-rose-500 hover:bg-rose-100 border border-rose-100 transition-all active:scale-95">Reset</button>
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

            {{-- ── KT Charts ──────────────────────────────────────────── --}}
            <div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden">
                <div class="p-6 sm:p-8 border-b border-slate-50 flex items-center justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-black text-slate-800 tracking-tight italic">Analisis Ketua Tim</h3>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">{{ $monthNames[$month] ?? '' }} {{ $year }}</p>
                    </div>
                    <button wire:click="loadKtCharts"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-minimal-indigo text-white text-[9px] font-black uppercase tracking-widest hover:bg-minimal-violet active:scale-95 transition-all shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                        <span wire:loading.remove wire:target="loadKtCharts">Update Graph</span>
                        <span wire:loading wire:target="loadKtCharts">Memuat...</span>
                    </button>
                </div>
                <div id="ktChartsArea" class="p-6 sm:p-8">
                    <div id="ktChartsPlaceholder" class="py-16 text-center">
                        <div class="w-14 h-14 rounded-2xl bg-slate-50 flex items-center justify-center mx-auto mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                        </div>
                        <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest">Klik "Update Graph" untuk memuat grafik</p>
                    </div>
                    <div id="ktChartsContent" class="hidden grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-slate-50/50 rounded-2xl p-4">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Scatter Plot</p>
                            <p class="text-[9px] text-slate-300 font-bold uppercase tracking-widest mb-4">Nilai Akhir vs Jumlah Tim Dipimpin</p>
                            <div id="ktScatterChart" class="h-72 w-full" wire:ignore></div>
                        </div>
                        <div class="bg-slate-50/50 rounded-2xl p-4">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Perbandingan Nilai</p>
                            <p class="text-[9px] text-slate-300 font-bold uppercase tracking-widest mb-4">Nilai Akhir Seluruh Ketua Tim</p>
                            <div id="ktBarChart" class="h-72 w-full" wire:ignore></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @elseif($activeTab === 'rekap-kabkot')
        {{-- ===== NILAI KEPALA KABKOT ===== --}}
        <div class="space-y-6 animate-in fade-in duration-500">

            {{-- Flash success --}}
            @if(session('kabkot_pimpinan_success'))
                <div class="flex items-center gap-4 p-4 bg-emerald-50 border border-emerald-100 rounded-2xl animate-in fade-in duration-300">
                    <div class="w-7 h-7 bg-emerald-500 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                    <p class="text-xs font-black text-emerald-700 uppercase tracking-tight">{{ session('kabkot_pimpinan_success') }}</p>
                </div>
            @endif

            {{-- Stats Cards --}}
            @if($kabkotStats['total_teams'] > 0 && $kabkotStats['total_kabkots'] > 0)
            <div class="grid grid-cols-2 gap-3">
                <button wire:click="openIncompleteTeamsDialog" class="group relative bg-white rounded-2xl border border-slate-100 shadow-sm p-4 text-left hover:border-rose-200 hover:shadow-md transition-all active:scale-95 overflow-hidden">
                    <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity" style="background:linear-gradient(135deg,#fff5f5,#fff)"></div>
                    <div class="relative flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 {{ $kabkotStats['incomplete_teams'] > 0 ? 'bg-rose-50' : 'bg-emerald-50' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 {{ $kabkotStats['incomplete_teams'] > 0 ? 'text-rose-500' : 'text-emerald-500' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-2xl font-black italic {{ $kabkotStats['incomplete_teams'] > 0 ? 'text-rose-600' : 'text-emerald-600' }} leading-none">{{ $kabkotStats['incomplete_teams'] }}</p>
                            <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mt-0.5 leading-tight">Tim Belum Selesai Menilai</p>
                            <p class="text-[8px] text-slate-300 font-mono mt-0.5">dari {{ $kabkotStats['total_teams'] }} tim</p>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-slate-200 group-hover:text-slate-400 transition-colors ml-auto flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                    </div>
                </button>
                <button wire:click="openIncompleteKabkotDialog" class="group relative bg-white rounded-2xl border border-slate-100 shadow-sm p-4 text-left hover:border-amber-200 hover:shadow-md transition-all active:scale-95 overflow-hidden">
                    <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity" style="background:linear-gradient(135deg,#fffbeb,#fff)"></div>
                    <div class="relative flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 {{ $kabkotStats['incomplete_kabkots'] > 0 ? 'bg-amber-50' : 'bg-emerald-50' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 {{ $kabkotStats['incomplete_kabkots'] > 0 ? 'text-amber-500' : 'text-emerald-500' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-2xl font-black italic {{ $kabkotStats['incomplete_kabkots'] > 0 ? 'text-amber-600' : 'text-emerald-600' }} leading-none">{{ $kabkotStats['incomplete_kabkots'] }}</p>
                            <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mt-0.5 leading-tight">Kabkot Belum Dinilai Penuh</p>
                            <p class="text-[8px] text-slate-300 font-mono mt-0.5">dari {{ $kabkotStats['total_kabkots'] }} kabkot</p>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-slate-200 group-hover:text-slate-400 transition-colors ml-auto flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                    </div>
                </button>
            </div>
            @endif

            {{-- Search bar + Export --}}
            <div class="flex items-center gap-3">
                <div class="flex-1 flex items-center gap-3 h-12 px-4 rounded-2xl bg-slate-50/80 border border-slate-100 shadow-inner focus-within:ring-4 focus-within:ring-minimal-indigo/10 transition-all group">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 flex-shrink-0 text-slate-300 group-focus-within:text-minimal-indigo transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input wire:model.live.debounce.300ms="kabkotSearch" type="text" placeholder="Cari nama atau NIP kepala kabkot..."
                        class="flex-1 h-full bg-transparent text-sm font-bold text-slate-700 placeholder:text-slate-300 placeholder:font-medium outline-none border-none focus:ring-0">
                    @if($kabkotSearch)
                    <button wire:click="$set('kabkotSearch','')" class="flex-shrink-0 text-slate-300 hover:text-slate-500 transition-colors text-sm font-black">✕</button>
                    @endif
                </div>
                <a href="{{ route('export.kabkot', ['month' => $month, 'year' => $year]) }}"
                   class="inline-flex items-center gap-2 h-12 px-4 rounded-2xl bg-emerald-500 hover:bg-emerald-600 active:scale-95 text-white text-[9px] font-black uppercase tracking-widest transition-all shadow-sm flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Export .xlsx
                </a>
            </div>

            @if(empty($kabkotRekap))
                <div class="bg-white py-20 rounded-[3rem] border-2 border-dashed border-slate-200 text-center">
                    <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    </div>
                    <p class="text-xl font-black text-slate-400 uppercase italic">{{ $kabkotSearch ? 'Tidak Ada Hasil' : 'Tidak Ada Kepala Kabkot' }}</p>
                    <p class="text-xs text-slate-300 font-bold uppercase tracking-widest mt-1">{{ $kabkotSearch ? 'Coba kata kunci lain.' : 'Belum ada pengguna dengan role Kepala Kabkot.' }}</p>
                </div>
            @else

            {{-- ===== MOBILE CARDS (< md) ===== --}}
            <div class="block md:hidden space-y-3">
                @foreach($kabkotRekap as $kabkot)
                @php $isOpen = $detailKabkotId === $kabkot->id; @endphp
                <div class="rounded-2xl overflow-hidden border transition-all" style="{{ $isOpen ? 'border-color:#6366f1' : 'border-color:#f1f5f9' }};background:#fff">
                    {{-- Card header --}}
                    <div class="px-4 pt-3 pb-2 flex items-center gap-3">
                        <div class="shrink-0 w-9 h-9 rounded-xl flex items-center justify-center font-black italic text-[11px] shadow-inner" style="{{ $isOpen ? 'background:#6366f1;color:#fff' : 'background:#f1f5f9;color:#94a3b8' }}">
                            {{ substr($kabkot->name, 0, 1) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-[12px] font-black uppercase tracking-tight leading-none truncate {{ $isOpen ? 'text-minimal-indigo' : 'text-slate-800' }}">{{ $kabkot->name }}</p>
                            <p class="text-[9px] font-mono font-bold text-slate-400 uppercase mt-0.5">{{ $kabkot->nip }}</p>
                        </div>
                        @if($kabkot->pimpinan_score !== null)
                            <span class="flex-shrink-0 px-2.5 py-1 rounded-full text-[9px] font-black bg-minimal-indigo/10 text-minimal-indigo">{{ $kabkot->pimpinan_score }}</span>
                        @endif
                    </div>

                    {{-- Recommendation badges --}}
                    <div class="px-4 pb-3 grid grid-cols-3 gap-2">
                        <div class="rounded-xl px-2 py-1.5 text-center" style="background:#f0fdf4">
                            <p class="text-[8px] font-black uppercase tracking-widest text-emerald-500">Avg Tim</p>
                            <p class="text-sm font-black italic {{ $kabkot->avg_score >= 80 ? 'text-emerald-600' : ($kabkot->avg_score >= 60 ? 'text-amber-500' : ($kabkot->avg_score > 0 ? 'text-red-500' : 'text-slate-300')) }}">
                                {{ $kabkot->avg_score ?? '—' }}
                            </p>
                        </div>
                        <div class="rounded-xl px-2 py-1.5 text-center" style="background:#faf5ff">
                            <p class="text-[8px] font-black uppercase tracking-widest text-purple-500">Q3 Tim</p>
                            <p class="text-sm font-black italic text-purple-600">{{ $kabkot->q3_score ?? '—' }}</p>
                        </div>
                        <div class="rounded-xl px-2 py-1.5 text-center" style="background:#eff6ff">
                            <p class="text-[8px] font-black uppercase tracking-widest text-blue-500">Maks Tim</p>
                            <p class="text-sm font-black italic text-blue-600">{{ $kabkot->max_score ?? '—' }}</p>
                        </div>
                    </div>

                    {{-- Score input + action buttons --}}
                    <div class="px-4 pb-4 space-y-2">
                        <div class="flex items-center gap-2 h-10 px-3 rounded-xl bg-slate-50 border border-slate-200 focus-within:ring-4 focus-within:ring-minimal-indigo/10 focus-within:border-minimal-indigo transition-all">
                            <span class="text-[9px] font-black uppercase tracking-widest text-slate-400 flex-shrink-0">Nilai Pimpinan</span>
                            <input wire:model="kabkotPimpinanFormState.{{ $kabkot->id }}.score"
                                type="number" min="1" max="100"
                                class="flex-1 h-full bg-transparent text-sm font-black text-center text-minimal-indigo outline-none border-none focus:ring-0"
                                placeholder="—">
                        </div>
                        <div class="flex items-center gap-2">
                            <button wire:click="savePimpinanKabkotScore('{{ $kabkot->id }}')" wire:loading.attr="disabled"
                                wire:target="savePimpinanKabkotScore('{{ $kabkot->id }}')"
                                class="flex-1 py-2.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-[9px] font-black uppercase tracking-widest active:scale-95 disabled:opacity-50 transition-all">
                                <span wire:loading.remove wire:target="savePimpinanKabkotScore('{{ $kabkot->id }}')">✓ Simpan</span>
                                <span wire:loading wire:target="savePimpinanKabkotScore('{{ $kabkot->id }}')">...</span>
                            </button>
                            @if($isOpen)
                            <button wire:click="setDetailKabkot('{{ $kabkot->id }}')"
                                class="flex-1 py-2.5 rounded-2xl bg-amber-50 border border-amber-200 text-amber-700 text-[9px] font-black uppercase tracking-widest active:scale-95 transition-all">Tutup ×</button>
                            @else
                            <button wire:click="setDetailKabkot('{{ $kabkot->id }}')"
                                class="flex-1 py-2.5 rounded-2xl bg-slate-50 border border-slate-200 text-slate-500 text-[9px] font-black uppercase tracking-widest hover:border-minimal-indigo hover:text-minimal-indigo active:scale-95 transition-all">Detail</button>
                            @endif
                            @if($kabkot->pimpinan_score !== null)
                            <button wire:click="resetPimpinanKabkotScore('{{ $kabkot->id }}')" wire:loading.attr="disabled"
                                wire:target="resetPimpinanKabkotScore('{{ $kabkot->id }}')"
                                class="flex-1 py-2.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-600 text-[9px] font-black uppercase tracking-widest active:scale-95 disabled:opacity-50 transition-all">
                                <span wire:loading.remove wire:target="resetPimpinanKabkotScore('{{ $kabkot->id }}')">Reset</span>
                                <span wire:loading wire:target="resetPimpinanKabkotScore('{{ $kabkot->id }}')">...</span>
                            </button>
                            @endif
                        </div>
                    </div>

                    {{-- Read-only detail panel --}}
                    @if($isOpen)
                    <div class="border-t px-4 pb-4 pt-3 space-y-2 animate-in fade-in slide-in-from-top-1 duration-200" style="border-color:#e0e7ff;background:#fafbff">
                        <p class="text-[8px] font-black uppercase tracking-widest text-slate-400 mb-2">Nilai dari Ketua Tim</p>
                        @if(empty($kabkotOverrideFormState))
                            <p class="text-center text-slate-400 text-xs py-4 italic font-bold">Belum ada penilaian dari Ketua Tim untuk periode ini.</p>
                        @else
                        @foreach($kabkotOverrideFormState as $entry)
                        <div class="bg-white rounded-xl border border-slate-100 flex items-center justify-between px-3 py-2.5">
                            <div>
                                <p class="text-[10px] font-black text-minimal-indigo uppercase italic tracking-tight">{{ $entry['team_name'] }}</p>
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">KT: {{ $entry['evaluator_name'] }}</p>
                            </div>
                            @if($entry['has_work'])
                                <span class="text-lg font-black italic {{ ($entry['score'] ?? 0) >= 80 ? 'text-emerald-600' : (($entry['score'] ?? 0) >= 60 ? 'text-amber-500' : (($entry['score'] ?? 0) > 0 ? 'text-red-500' : 'text-slate-300')) }}">
                                    {{ $entry['score'] ?? '—' }}
                                </span>
                            @else
                                <span class="text-[8px] font-black uppercase tracking-widest px-2 py-0.5 bg-slate-100 text-slate-400 rounded-full">Tidak Ada Pekerjaan</span>
                            @endif
                        </div>
                        @endforeach
                        @endif
                    </div>
                    @endif
                </div>
                @endforeach
            </div>

            {{-- ===== DESKTOP TABLE (md+) ===== --}}
            <div class="hidden md:block bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden">
                @php
                    $kSortIcon = function($col) use ($kabkotSortKey, $kabkotSortDir) {
                        if ($col !== $kabkotSortKey) return '<svg xmlns="http://www.w3.org/2000/svg" class="inline w-3 h-3 text-slate-300 ml-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M7 15l5 5 5-5"/><path d="M7 9l5-5 5 5"/></svg>';
                        return $kabkotSortDir === 'asc'
                            ? '<svg xmlns="http://www.w3.org/2000/svg" class="inline w-3 h-3 text-minimal-indigo ml-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 15l7-7 7 7"/></svg>'
                            : '<svg xmlns="http://www.w3.org/2000/svg" class="inline w-3 h-3 text-minimal-indigo ml-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M19 9l-7 7-7-7"/></svg>';
                    };
                @endphp
                <div class="p-6 sm:p-8 border-b border-slate-50 flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-black text-slate-800 tracking-tight italic">Nilai Kepala Kabkot</h3>
                        <p class="text-xs text-slate-400 font-mono uppercase tracking-tighter mt-0.5">{{ $monthNames[$month] ?? '...' }} {{ $year }} — Nilai diberikan langsung oleh Pimpinan</p>
                    </div>
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ count($kabkotRekap) }} Kepala Kabkot</span>
                </div>
                <div class="overflow-x-auto px-4">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50/30 text-[9px] font-black uppercase tracking-[0.2em] text-slate-400">
                                <th class="px-4 py-4 text-center w-10">No</th>
                                <th class="px-6 py-4">
                                    <button wire:click="sortKabkot('name')" class="flex items-center gap-0.5 hover:text-minimal-indigo transition-colors {{ $kabkotSortKey === 'name' ? 'text-minimal-indigo' : '' }}">
                                        Kepala Kabkot {!! $kSortIcon('name') !!}
                                    </button>
                                </th>
                                <th class="px-4 py-4 text-center">
                                    <button wire:click="sortKabkot('rated_count')" class="flex items-center gap-0.5 mx-auto hover:text-minimal-indigo transition-colors {{ $kabkotSortKey === 'rated_count' ? 'text-minimal-indigo' : '' }}">
                                        Dinilai {!! $kSortIcon('rated_count') !!}
                                    </button>
                                </th>
                                <th class="px-4 py-4 text-center">
                                    <button wire:click="sortKabkot('avg_score')" class="flex items-center gap-0.5 mx-auto hover:text-minimal-indigo transition-colors {{ $kabkotSortKey === 'avg_score' ? 'text-minimal-indigo' : '' }}">
                                        Avg Tim {!! $kSortIcon('avg_score') !!}
                                    </button>
                                </th>
                                <th class="px-4 py-4 text-center text-purple-400">Q3 Tim</th>
                                <th class="px-4 py-4 text-center text-blue-400">Maks Tim</th>
                                <th class="px-4 py-4 text-center">
                                    <button wire:click="sortKabkot('pimpinan_score')" class="flex items-center gap-0.5 mx-auto hover:text-minimal-indigo transition-colors {{ $kabkotSortKey === 'pimpinan_score' ? 'text-minimal-indigo' : '' }}">
                                        Nilai Pimpinan {!! $kSortIcon('pimpinan_score') !!}
                                    </button>
                                </th>
                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($kabkotRekap as $idx => $kabkot)
                            @php $isOpen = $detailKabkotId === $kabkot->id; @endphp
                            <tr class="{{ $isOpen ? '' : 'hover:bg-slate-50' }} group transition-all duration-200 border-t border-slate-50" {{ $isOpen ? 'style=background:#eef2ff' : '' }}>
                                <td class="px-4 py-4 text-center">
                                    <span class="text-xs font-black text-slate-800 tabular-nums">{{ $idx + 1 }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl flex items-center justify-center text-[11px] font-black italic shadow-inner transition-all flex-shrink-0" style="{{ $isOpen ? 'background:#6366f1;color:#fff' : 'background:linear-gradient(135deg,#f1f5f9,#e2e8f0);color:#94a3b8' }}">
                                            {{ substr($kabkot->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="text-[12px] font-black leading-tight transition-colors {{ $isOpen ? 'text-minimal-indigo' : 'text-slate-700 group-hover:text-minimal-indigo' }}">{{ $kabkot->name }}</p>
                                            <p class="text-[9px] font-mono font-bold text-slate-400 uppercase tracking-tighter">{{ $kabkot->nip }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-[8px] font-black uppercase tracking-widest {{ $kabkot->rated_count > 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-400' }}">
                                        {{ $kabkot->rated_count }} Tim
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <span class="text-base font-black italic {{ $kabkot->avg_score >= 80 ? 'text-emerald-600' : ($kabkot->avg_score >= 60 ? 'text-amber-500' : ($kabkot->avg_score > 0 ? 'text-red-500' : 'text-slate-200')) }}">
                                        {{ $kabkot->avg_score ?? '—' }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <span class="text-base font-black italic text-purple-500">{{ $kabkot->q3_score ?? '—' }}</span>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <span class="text-base font-black italic text-blue-500">{{ $kabkot->max_score ?? '—' }}</span>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <input wire:model="kabkotPimpinanFormState.{{ $kabkot->id }}.score"
                                        type="number" min="1" max="100"
                                        class="w-20 bg-slate-50 border rounded-lg px-2 py-1.5 text-center text-sm font-black text-minimal-indigo focus:ring-4 focus:ring-minimal-indigo/10 focus:border-minimal-indigo transition-all shadow-inner {{ $kabkot->pimpinan_score !== null ? 'border-emerald-300' : 'border-slate-200' }}"
                                        placeholder="—">
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button wire:click="savePimpinanKabkotScore('{{ $kabkot->id }}')" wire:loading.attr="disabled"
                                            wire:target="savePimpinanKabkotScore('{{ $kabkot->id }}')"
                                            class="px-3 py-1.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-[9px] font-black uppercase tracking-widest hover:bg-emerald-100 transition-all active:scale-95 disabled:opacity-50">
                                            <span wire:loading.remove wire:target="savePimpinanKabkotScore('{{ $kabkot->id }}')">✓ Simpan</span>
                                            <span wire:loading wire:target="savePimpinanKabkotScore('{{ $kabkot->id }}')">...</span>
                                        </button>
                                        @if($isOpen)
                                        <button wire:click="setDetailKabkot('{{ $kabkot->id }}')"
                                            class="px-3 py-1.5 rounded-2xl bg-amber-50 border border-amber-200 text-amber-700 text-[9px] font-black uppercase tracking-widest hover:bg-amber-100 transition-all active:scale-95">Tutup ×</button>
                                        @else
                                        <button wire:click="setDetailKabkot('{{ $kabkot->id }}')"
                                            class="px-3 py-1.5 rounded-2xl bg-slate-50 border border-slate-200 text-slate-500 text-[9px] font-black uppercase tracking-widest hover:border-minimal-indigo hover:text-minimal-indigo transition-all active:scale-95">Detail</button>
                                        @endif
                                        @if($kabkot->pimpinan_score !== null)
                                        <button wire:click="resetPimpinanKabkotScore('{{ $kabkot->id }}')" wire:loading.attr="disabled"
                                            wire:target="resetPimpinanKabkotScore('{{ $kabkot->id }}')"
                                            class="px-3 py-1.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-600 text-[9px] font-black uppercase tracking-widest hover:bg-rose-100 transition-all active:scale-95 disabled:opacity-50">
                                            <span wire:loading.remove wire:target="resetPimpinanKabkotScore('{{ $kabkot->id }}')">Reset</span>
                                            <span wire:loading wire:target="resetPimpinanKabkotScore('{{ $kabkot->id }}')">...</span>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            {{-- Read-only detail row --}}
                            @if($isOpen)
                            <tr style="background:linear-gradient(135deg,#eef2ff 0%,#f5f3ff 100%);border-top:2px solid #6366f1">
                                <td colspan="8" class="px-6 py-5" style="border-left:4px solid #6366f1">
                                    <div class="space-y-3 animate-in fade-in slide-in-from-top-1 duration-200">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-lg flex items-center justify-center flex-shrink-0" style="background:#e0e7ff">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" style="color:#6366f1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/></svg>
                                            </div>
                                            <p class="text-[10px] font-black uppercase tracking-widest" style="color:#4f46e5">Nilai dari Ketua Tim — Hanya untuk referensi</p>
                                        </div>
                                        @if(empty($kabkotOverrideFormState))
                                            <p class="text-center text-slate-400 text-xs py-6 italic font-bold">Belum ada penilaian dari Ketua Tim untuk periode ini.</p>
                                        @else
                                        <div class="bg-white rounded-2xl border overflow-hidden shadow-sm" style="border-color:rgba(99,102,241,0.15)">
                                            <table class="w-full text-left">
                                                <thead style="background:#eef2ff">
                                                    <tr class="text-[9px] font-black text-slate-500 uppercase tracking-[0.15em]">
                                                        <th class="px-4 py-3 border-b border-slate-100">Tim</th>
                                                        <th class="px-4 py-3 border-b border-slate-100">Ketua Tim</th>
                                                        <th class="px-4 py-3 border-b border-slate-100 text-center">Nilai Diberikan</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-slate-50">
                                                    @foreach($kabkotOverrideFormState as $entry)
                                                    <tr>
                                                        <td class="px-4 py-3">
                                                            <p class="text-[10px] font-black text-minimal-indigo uppercase italic tracking-tight">{{ $entry['team_name'] }}</p>
                                                        </td>
                                                        <td class="px-4 py-3">
                                                            <p class="text-[10px] font-bold text-slate-600">{{ $entry['evaluator_name'] }}</p>
                                                        </td>
                                                        <td class="px-4 py-3 text-center">
                                                            @if($entry['has_work'])
                                                                <span class="text-lg font-black italic {{ ($entry['score'] ?? 0) >= 80 ? 'text-emerald-600' : (($entry['score'] ?? 0) >= 60 ? 'text-amber-500' : (($entry['score'] ?? 0) > 0 ? 'text-red-500' : 'text-slate-300')) }}">
                                                                    {{ $entry['score'] ?? '—' }}
                                                                </span>
                                                            @else
                                                                <span class="text-[8px] font-black uppercase tracking-widest px-2 py-0.5 bg-slate-100 text-slate-400 rounded-full">Tidak Ada Pekerjaan</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>

    @elseif($activeTab === 'report')
        <!-- Report Individu Tab -->
        <div class="w-full space-y-6 animate-in fade-in duration-500">
            <div class="bg-white rounded-[2rem] p-6 sm:p-10 shadow-sm border border-slate-100 text-center space-y-4">
                <div class="w-16 h-16 bg-minimal-indigo/5 text-minimal-indigo rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-inner">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                </div>
                <div>
                    <h3 class="text-2xl font-black text-slate-800 tracking-tight italic">Report Individu</h3>
                    <p class="text-slate-400 text-xs font-medium">Monitoring performa mendalam tiap pegawai.</p>
                </div>
                <div class="relative max-w-lg mx-auto">
                    <div class="flex items-center gap-3 h-12 px-4 rounded-2xl bg-slate-50/80 border border-slate-100 shadow-inner focus-within:ring-4 focus-within:ring-minimal-indigo/10 transition-all group">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 flex-shrink-0 text-slate-300 group-focus-within:text-minimal-indigo transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        <input wire:model.live.debounce.300ms="reportSearch" placeholder="Cari nama atau NIP..." class="flex-1 h-full bg-transparent text-sm font-bold text-slate-700 placeholder:text-slate-300 placeholder:font-medium outline-none border-none focus:ring-0">
                    </div>
                    @if($reportSearch)
                        <div class="absolute z-50 w-full mt-2 bg-white rounded-2xl shadow-2xl border border-slate-50 overflow-hidden max-h-64 overflow-y-auto">
                            @php $suggestions = $allUsers->filter(fn($u) => str_contains(strtolower($u->name), strtolower($reportSearch)) || str_contains($u->nip, $reportSearch))->take(5); @endphp
                            @forelse($suggestions as $u)
                                <button wire:click="setReportUserId('{{ $u->id }}')" class="w-full p-4 text-left hover:bg-slate-50 transition-all flex items-center justify-between group border-b border-slate-50 last:border-none">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-minimal-indigo/5 text-minimal-indigo flex items-center justify-center font-black italic shadow-inner text-[10px]">{{ substr($u->name, 0, 1) }}</div>
                                        <div>
                                            <p class="text-xs font-black text-slate-700 leading-tight group-hover:text-minimal-indigo transition-colors">{{ $u->name }}</p>
                                            <p class="text-[9px] font-mono font-bold text-slate-400 mt-0.5 uppercase tracking-tighter">{{ $u->nip }}</p>
                                        </div>
                                    </div>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-200 group-hover:text-minimal-indigo transition-all group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                                </button>
                            @empty
                                <div class="p-8 text-slate-300 font-bold italic text-[10px]">No data found...</div>
                            @endforelse
                        </div>
                    @endif
                </div>

                @if($reportUser)
                <div class="pt-6 border-t border-slate-50 mt-4">
                    <p class="text-[11px] font-black text-slate-500 uppercase tracking-widest leading-relaxed">
                        Report Pegawai <span class="text-minimal-indigo mx-1 px-2 py-1 bg-minimal-indigo/5 rounded-md border border-minimal-indigo/10 break-words">{{ $reportUser->name }}</span> <br class="md:hidden">Pada <span class="text-slate-700 ml-1 px-2 py-1 bg-slate-50 rounded-md border border-slate-100">{{ $monthNames[$month] ?? '' }} {{ $year }}</span>
                    </p>
                </div>
                @endif
            </div>

            @if($reportUserId)
                <div class="bg-white rounded-[2rem] p-4 sm:p-8 shadow-sm border border-slate-100">
                    <livewire:dashboards.pegawai-dashboard :userId="$reportUserId" :month="$month" :year="$year" :isFromPimpinan="true" :key="'report-'.$reportUserId.'-'.$month.'-'.$year" />
                </div>
            @endif
        </div>
    @endif
    </div>{{-- end wire:loading wrapper --}}

    <!-- Incomplete Teams Dialog -->
    @if($showIncompleteTeamsDialog)
    <div class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-slate-900/60 backdrop-blur-sm animate-in fade-in duration-300 pointer-events-auto">
        <div class="bg-white w-full max-w-2xl rounded-[2.5rem] shadow-2xl overflow-hidden animate-in zoom-in-95 duration-300">
            <div class="p-6 sm:p-10">
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center shadow-inner">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-slate-800 tracking-tight leading-tight">Tim Belum Selesai Menilai</h3>
                            <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">Periode {{ $monthNames[$month] }} {{ $year }}</p>
                        </div>
                    </div>
                    <button wire:click="$set('showIncompleteTeamsDialog', false)" class="w-10 h-10 rounded-xl bg-slate-50 text-slate-300 hover:bg-red-50 hover:text-red-500 transition-all flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="space-y-4 max-h-[50vh] overflow-y-auto pr-2 custom-scrollbar">
                    @forelse($stats['compliance']['teams'] as $team)
                    <div class="p-5 rounded-2xl border border-slate-50 bg-slate-50/50 hover:bg-white hover:border-amber-100 hover:shadow-md transition-all group">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <h4 class="text-sm font-black text-slate-800 tracking-tight group-hover:text-minimal-indigo transition-colors">{{ $team['team_name'] }}</h4>
                                <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mt-1">Ketua: {{ $team['leader_name'] }}</p>
                            </div>
                            <span class="px-3 py-1 bg-amber-100 text-amber-700 text-[9px] font-black rounded-full uppercase tracking-widest">{{ $team['pending_count'] }} PENDING</span>
                        </div>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($team['pending_members'] as $mName)
                            <span class="px-2 py-0.5 bg-white text-slate-500 text-[8px] font-black border border-slate-100 rounded-lg uppercase tracking-tight">{{ $mName }}</span>
                            @endforeach
                        </div>
                    </div>
                    @empty
                    <div class="py-12 text-center text-slate-300 italic text-sm font-bold">Semua tim telah tuntas menilai. ✓</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Incomplete Employees Dialog -->
    @if($showIncompleteEmployeesDialog)
    <div class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-slate-900/60 backdrop-blur-sm animate-in fade-in duration-300 pointer-events-auto">
        <div class="bg-white w-full max-w-2xl rounded-[2.5rem] shadow-2xl overflow-hidden animate-in zoom-in-95 duration-300">
            <div class="p-6 sm:p-10">
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-500 flex items-center justify-center shadow-inner">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-slate-800 tracking-tight leading-tight">Pegawai Belum Dinilai Lengkap</h3>
                            <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">Periode {{ $monthNames[$month] }} {{ $year }}</p>
                        </div>
                    </div>
                    <button wire:click="$set('showIncompleteEmployeesDialog', false)" class="w-10 h-10 rounded-xl bg-slate-50 text-slate-300 hover:bg-red-50 hover:text-red-500 transition-all flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="space-y-4 max-h-[50vh] overflow-y-auto pr-2 custom-scrollbar">
                    @forelse($stats['compliance']['employees'] as $employee)
                    <div class="p-5 rounded-2xl border border-slate-50 bg-slate-50/50 hover:bg-white hover:border-rose-100 hover:shadow-md transition-all group">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <h4 class="text-sm font-black text-slate-800 tracking-tight group-hover:text-minimal-indigo transition-colors">{{ $employee['name'] }}</h4>
                                <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mt-1">NIP: {{ $employee['nip'] }}</p>
                            </div>
                            <span class="px-3 py-1 bg-rose-100 text-rose-700 text-[9px] font-black rounded-full uppercase tracking-widest">{{ $employee['missing_count'] }} MISSING RATINGS</span>
                        </div>
                        <div class="space-y-1.5">
                            @foreach($employee['missing_details'] as $detail)
                            <div class="flex items-center gap-2 text-[9px] font-bold text-slate-500">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                                <span class="text-slate-700">{{ $detail['team_name'] }}</span>
                                <span class="opacity-40 italic">Ketua: {{ $detail['leader_name'] }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @empty
                    <div class="py-12 text-center text-slate-300 italic text-sm font-bold">Seluruh pegawai telah dinilai lengkap. ✓</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ===== BOTTOM NAV BAR (mobile only, < md) ===== --}}
    <div class="fixed bottom-0 left-0 right-0 z-50 md:hidden">
        <div class="bg-white border-t border-slate-100 shadow-[0_-4px_24px_rgba(0,0,0,0.08)]" style="padding-bottom: env(safe-area-inset-bottom, 0px);">
            <div class="flex">
                <button
                    wire:click="setActiveTab('overview')"
                    class="flex-1 flex flex-col items-center justify-center gap-1 py-3 transition-all active:scale-95 {{ $activeTab === 'overview' ? 'text-minimal-indigo' : 'text-slate-400' }}"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 transition-transform {{ $activeTab === 'overview' ? 'scale-110' : '' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" rx="2" x="3" y="3"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                    <span class="text-[9px] font-black uppercase tracking-widest">Ringkasan</span>
                    <span class="h-0.5 w-5 rounded-full transition-all {{ $activeTab === 'overview' ? 'bg-minimal-indigo' : 'bg-transparent' }}"></span>
                </button>

                <div class="w-px bg-slate-100 my-2"></div>

                <button
                    wire:click="setActiveTab('input-kt')"
                    class="flex-1 flex flex-col items-center justify-center gap-1 py-3 transition-all active:scale-95 {{ $activeTab === 'input-kt' ? 'text-minimal-indigo' : 'text-slate-400' }}"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 transition-transform {{ $activeTab === 'input-kt' ? 'scale-110' : '' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    <span class="text-[9px] font-black uppercase tracking-widest">Input KT</span>
                    <span class="h-0.5 w-5 rounded-full transition-all {{ $activeTab === 'input-kt' ? 'bg-minimal-indigo' : 'bg-transparent' }}"></span>
                </button>

                <div class="w-px bg-slate-100 my-2"></div>

                <button
                    wire:click="setActiveTab('rekap-kabkot')"
                    class="flex-1 flex flex-col items-center justify-center gap-1 py-3 transition-all active:scale-95 {{ $activeTab === 'rekap-kabkot' ? 'text-minimal-indigo' : 'text-slate-400' }}"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 transition-transform {{ $activeTab === 'rekap-kabkot' ? 'scale-110' : '' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    <span class="text-[9px] font-black uppercase tracking-widest">Kabkot</span>
                    <span class="h-0.5 w-5 rounded-full transition-all {{ $activeTab === 'rekap-kabkot' ? 'bg-minimal-indigo' : 'bg-transparent' }}"></span>
                </button>

                <div class="w-px bg-slate-100 my-2"></div>

                <button
                    wire:click="setActiveTab('report')"
                    class="flex-1 flex flex-col items-center justify-center gap-1 py-3 transition-all active:scale-95 {{ $activeTab === 'report' ? 'text-minimal-indigo' : 'text-slate-400' }}"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 transition-transform {{ $activeTab === 'report' ? 'scale-110' : '' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <span class="text-[9px] font-black uppercase tracking-widest">Report</span>
                    <span class="h-0.5 w-5 rounded-full transition-all {{ $activeTab === 'report' ? 'bg-minimal-indigo' : 'bg-transparent' }}"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- Override Validation Dialog --}}
    @if($showOverrideValidationDialog)
    <div class="fixed inset-0 z-[110] flex items-center justify-center p-6 bg-slate-900/60 backdrop-blur-sm animate-in fade-in duration-200 pointer-events-auto">
        <div class="bg-white w-full max-w-sm rounded-[2rem] shadow-2xl p-8 space-y-6 animate-in zoom-in-95 duration-200">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                </div>
                <div>
                    <h4 class="text-base font-black text-slate-800">Nilai Tidak Valid</h4>
                    <p class="text-xs text-slate-500 mt-1">Masukkan nilai antara 1 hingga 100.</p>
                </div>
            </div>
            <button wire:click="$set('showOverrideValidationDialog', false)" class="w-full py-3 rounded-xl text-[10px] font-black uppercase tracking-widest bg-slate-900 text-white hover:bg-minimal-indigo transition-all active:scale-95">Tutup</button>
        </div>
    </div>
    @endif

    {{-- KT Reset Dialog --}}
    @if($showKtResetDialog)
    <div class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-slate-900/60 backdrop-blur-sm animate-in fade-in duration-200 pointer-events-auto">
        <div class="bg-white w-full max-w-sm rounded-[2rem] shadow-2xl overflow-hidden animate-in zoom-in-95 duration-200 p-8 space-y-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-500 flex items-center justify-center flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.85"/></svg>
                </div>
                <div>
                    <h4 class="text-base font-black text-slate-800 tracking-tight">Reset Penilaian?</h4>
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">Tindakan tidak bisa dibatalkan.</p>
                </div>
            </div>
            <div class="flex gap-3">
                <button wire:click="cancelResetKt" class="flex-1 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 transition-all active:scale-95">Batal</button>
                <button wire:click="executeResetKt" class="flex-1 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest bg-rose-500 text-white hover:bg-rose-600 transition-all active:scale-95">Ya, Reset</button>
            </div>
        </div>
    </div>
    @endif

    {{-- KT Validation Dialog --}}
    @if($showKtValidationDialog)
    <div class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-slate-900/60 backdrop-blur-sm animate-in fade-in duration-200 pointer-events-auto">
        <div class="bg-white w-full max-w-sm rounded-[2rem] shadow-2xl overflow-hidden animate-in zoom-in-95 duration-200 p-8 space-y-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                </div>
                <div>
                    <h4 class="text-base font-black text-slate-800 tracking-tight">Nilai Tidak Valid</h4>
                    <p class="text-xs text-slate-500 mt-1">Masukkan nilai antara 1 hingga 100.</p>
                </div>
            </div>
            <button wire:click="$set('showKtValidationDialog', false)" class="w-full py-3 rounded-xl text-[10px] font-black uppercase tracking-widest bg-slate-900 text-white hover:bg-minimal-indigo transition-all active:scale-95">Tutup</button>
        </div>
    </div>
    @endif

    {{-- Kabkot Override Validation Dialog --}}
    @if($showKabkotOverrideValidationDialog)
    <div class="fixed inset-0 z-[110] flex items-center justify-center p-6 bg-slate-900/60 backdrop-blur-sm animate-in fade-in duration-200 pointer-events-auto">
        <div class="bg-white w-full max-w-sm rounded-[2rem] shadow-2xl overflow-hidden animate-in zoom-in-95 duration-200 p-8 space-y-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                </div>
                <div>
                    <h4 class="text-base font-black text-slate-800 tracking-tight">Nilai Tidak Valid</h4>
                    <p class="text-xs text-slate-500 mt-1">Masukkan nilai antara 1 hingga 100.</p>
                </div>
            </div>
            <button wire:click="$set('showKabkotOverrideValidationDialog', false)" class="w-full py-3 rounded-xl text-[10px] font-black uppercase tracking-widest bg-slate-900 text-white hover:bg-minimal-indigo transition-all active:scale-95">Tutup</button>
        </div>
    </div>
    @endif

    {{-- Incomplete Teams Dialog (Kabkot) --}}
    @if($showKabkotIncompleteTeamsDialog)
    <div class="fixed inset-0 z-[110] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-in fade-in duration-200 pointer-events-auto">
        <div class="bg-white w-full max-w-md rounded-[2rem] shadow-2xl overflow-hidden animate-in zoom-in-95 duration-200 flex flex-col max-h-[85vh]">
            <div class="px-8 pt-8 pb-5 border-b border-slate-100 flex-shrink-0">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-500 flex items-center justify-center flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <div>
                        <h4 class="text-base font-black text-slate-800 tracking-tight">Tim Belum Selesai Menilai</h4>
                        <p class="text-xs text-slate-400 font-mono uppercase tracking-widest mt-0.5">{{ count($kabkotIncompleteTeamsData) }} tim</p>
                    </div>
                </div>
            </div>
            <div class="flex-1 overflow-y-auto px-8 py-5 space-y-3">
                @if(empty($kabkotIncompleteTeamsData))
                    <p class="text-center text-slate-400 text-sm py-8 italic font-bold">Semua tim sudah menilai semua Kepala Kabkot.</p>
                @else
                    @foreach($kabkotIncompleteTeamsData as $teamRow)
                    <div class="bg-slate-50 rounded-2xl p-4 space-y-2">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full bg-rose-400 flex-shrink-0"></div>
                            <p class="text-[11px] font-black text-slate-800 uppercase tracking-tight">{{ $teamRow['team_name'] }}</p>
                            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">— KT: {{ $teamRow['leader_name'] }}</p>
                        </div>
                        <div class="ml-4 space-y-1">
                            <p class="text-[8px] font-black uppercase tracking-widest text-slate-400 mb-1">Belum menilai:</p>
                            @foreach($teamRow['unrated_kabkots'] as $kabkotName)
                            <div class="flex items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-2.5 h-2.5 text-rose-400 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                                <span class="text-[10px] font-bold text-slate-600">{{ $kabkotName }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                @endif
            </div>
            <div class="px-8 pb-8 pt-4 flex-shrink-0 border-t border-slate-50">
                <button wire:click="$set('showKabkotIncompleteTeamsDialog', false)" class="w-full py-3 rounded-xl text-[10px] font-black uppercase tracking-widest bg-slate-900 text-white hover:bg-minimal-indigo transition-all active:scale-95">Tutup</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Incomplete Kabkot Dialog --}}
    @if($showKabkotIncompleteKabkotDialog)
    <div class="fixed inset-0 z-[110] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-in fade-in duration-200 pointer-events-auto">
        <div class="bg-white w-full max-w-md rounded-[2rem] shadow-2xl overflow-hidden animate-in zoom-in-95 duration-200 flex flex-col max-h-[85vh]">
            <div class="px-8 pt-8 pb-5 border-b border-slate-100 flex-shrink-0">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    </div>
                    <div>
                        <h4 class="text-base font-black text-slate-800 tracking-tight">Kepala Kabkot Belum Dinilai Penuh</h4>
                        <p class="text-xs text-slate-400 font-mono uppercase tracking-widest mt-0.5">{{ count($kabkotIncompleteKabkotData) }} kabkot</p>
                    </div>
                </div>
            </div>
            <div class="flex-1 overflow-y-auto px-8 py-5 space-y-3">
                @if(empty($kabkotIncompleteKabkotData))
                    <p class="text-center text-slate-400 text-sm py-8 italic font-bold">Semua Kepala Kabkot sudah dinilai oleh semua tim.</p>
                @else
                    @foreach($kabkotIncompleteKabkotData as $kabkotRow)
                    <div class="bg-slate-50 rounded-2xl p-4 space-y-2">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full bg-amber-400 flex-shrink-0"></div>
                            <p class="text-[11px] font-black text-slate-800 uppercase tracking-tight">{{ $kabkotRow['kabkot_name'] }}</p>
                            <p class="text-[9px] font-mono text-slate-400 font-bold">{{ $kabkotRow['kabkot_nip'] }}</p>
                        </div>
                        <div class="ml-4 space-y-1">
                            <p class="text-[8px] font-black uppercase tracking-widest text-slate-400 mb-1">Belum dinilai oleh:</p>
                            @foreach($kabkotRow['unrated_teams'] as $teamName)
                            <div class="flex items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-2.5 h-2.5 text-amber-400 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                                <span class="text-[10px] font-bold text-slate-600">{{ $teamName }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                @endif
            </div>
            <div class="px-8 pb-8 pt-4 flex-shrink-0 border-t border-slate-50">
                <button wire:click="$set('showKabkotIncompleteKabkotDialog', false)" class="w-full py-3 rounded-xl text-[10px] font-black uppercase tracking-widest bg-slate-900 text-white hover:bg-minimal-indigo transition-all active:scale-95">Tutup</button>
            </div>
        </div>
    </div>
    @endif

    @script
    <script>
        let scatter, teamBar;
        let currentChartsData = @json($charts);

        const renderCharts = (chartsData) => {
            // Wait for DOM elements to be available
            const scatterEl = document.querySelector("#scatterChart");
            const barEl = document.querySelector("#teamBarChart");
            if (!scatterEl || !barEl) return;

            // Scatter Chart
            const scatterOptions = {
                series: [{
                    name: 'Pegawai',
                    data: chartsData.scatter.map(i => [i.x, i.y])
                }],
                chart: { 
                    type: 'scatter', 
                    height: 380, 
                    fontFamily: 'Outfit, sans-serif',
                    toolbar: { show: false },
                    zoom: { enabled: false },
                    selection: { enabled: false },
                    animations: { enabled: true, easing: 'easeinout', speed: 800 }
                },
                colors: ['#6366f1'],
                xaxis: { 
                    title: { text: 'BEBAN KERJA (TIM)', style: { fontSize: '9px', color: '#94a3b8', fontWeight: 900 } },
                    labels: { style: { fontSize: '9px', fontWeight: 800, colors: '#64748b' } }
                },
                yaxis: { 
                    max: 100, 
                    title: { text: 'CAPAIAN KINERJA', style: { fontSize: '9px', color: '#94a3b8', fontWeight: 900 } },
                    labels: { style: { fontSize: '9px', fontWeight: 800, colors: '#64748b' } }
                },
                grid: { borderColor: '#f8fafc' },
                tooltip: {
                    theme: 'dark',
                    custom: function({series, seriesIndex, dataPointIndex, w}) {
                        const d = chartsData.scatter[dataPointIndex];
                        return `<div class="p-4 bg-slate-900 text-white rounded-xl shadow-2xl border border-white/10">
                                    <p class="font-black text-[10px] uppercase tracking-widest text-minimal-indigo mb-1.5">${d.name}</p>
                                    <p class="text-[9px] text-white/40 font-bold uppercase tracking-widest">Beban: ${d.x} | Capaian: ${d.y}</p>
                                </div>`;
                    }
                },
                annotations: {
                    xaxis: [{ x: chartsData.avgX, borderColor: '#cad4e0', strokeDashArray: 4, label: { text: 'AVG WORKLOAD', style: { color: '#64748b', background: '#ffffff', fontSize: '8px', fontWeight: 900 } } }],
                    yaxis: [{ y: chartsData.avgY, borderColor: '#cad4e0', strokeDashArray: 4, label: { text: 'AVG PERF', style: { color: '#64748b', background: '#ffffff', fontSize: '8px', fontWeight: 900 } } }]
                }
            };
            
            if(scatter) scatter.destroy();
            scatter = new ApexCharts(scatterEl, scatterOptions);
            scatter.render();

            // Team Performance Distribution Bar Chart
            const barOptions = {
                series: [{
                    name: 'Rata-rata Skor',
                    data: chartsData.teamSize.series
                }],
                chart: {
                    type: 'bar',
                    height: 350,
                    fontFamily: 'Outfit, sans-serif',
                    toolbar: { show: false }
                },
                colors: ['#6366f1'],
                plotOptions: {
                    bar: {
                        borderRadius: 6,
                        columnWidth: '45%',
                        dataLabels: { position: 'top' }
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: function(val) { return val.toFixed(1) },
                    offsetY: -20,
                    style: { fontSize: '10px', colors: ["#6366f1"], fontWeight: 900 }
                },
                xaxis: {
                    categories: chartsData.teamSize.labels,
                    labels: {
                        style: { fontSize: '9px', fontWeight: 800, colors: '#64748b' },
                        rotate: -45,
                        hideOverlappingLabels: true
                    }
                },
                yaxis: {
                    max: 100,
                    labels: { style: { fontSize: '9px', fontWeight: 800, colors: '#64748b' } }
                },
                grid: { borderColor: '#f8fafc' },
                tooltip: {
                    custom: function({ series, seriesIndex, dataPointIndex }) {
                        const teamName = chartsData.teamSize.labels[dataPointIndex] || '-';
                        const leader  = chartsData.teamSize.leaders[dataPointIndex] || '-';
                        const score   = (series[seriesIndex][dataPointIndex] || 0).toFixed(1);
                        const maxScore = (chartsData.teamSize.maxScores?.[dataPointIndex] ?? 0).toFixed(1);
                        const q3Score  = (chartsData.teamSize.q3Scores?.[dataPointIndex] ?? 0).toFixed(1);
                        return `<div style="padding:12px 16px;background:#0f172a;color:#fff;border-radius:12px;border:1px solid rgba(255,255,255,0.08);font-family:Outfit,sans-serif;min-width:180px">
                                    <p style="font-size:10px;font-weight:900;text-transform:uppercase;letter-spacing:0.1em;color:#818cf8;margin-bottom:4px">${teamName}</p>
                                    <p style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:rgba(255,255,255,0.4);margin-bottom:10px">Ketua: ${leader}</p>
                                    <div style="display:flex;flex-direction:column;gap:5px">
                                        <div style="display:flex;justify-content:space-between;align-items:center;gap:16px">
                                            <span style="font-size:9px;font-weight:800;text-transform:uppercase;letter-spacing:0.08em;color:rgba(255,255,255,0.4)">Rata-rata</span>
                                            <span style="font-size:15px;font-weight:900;font-style:italic;color:#fff">${score} <span style="font-size:9px;font-weight:700;color:rgba(255,255,255,0.35)">pts</span></span>
                                        </div>
                                        <div style="display:flex;justify-content:space-between;align-items:center;gap:16px">
                                            <span style="font-size:9px;font-weight:800;text-transform:uppercase;letter-spacing:0.08em;color:rgba(255,255,255,0.4)">Kuartil 3</span>
                                            <span style="font-size:13px;font-weight:900;font-style:italic;color:#a5b4fc">${q3Score} <span style="font-size:9px;font-weight:700;color:rgba(255,255,255,0.35)">pts</span></span>
                                        </div>
                                        <div style="display:flex;justify-content:space-between;align-items:center;gap:16px">
                                            <span style="font-size:9px;font-weight:800;text-transform:uppercase;letter-spacing:0.08em;color:rgba(255,255,255,0.4)">Tertinggi</span>
                                            <span style="font-size:13px;font-weight:900;font-style:italic;color:#34d399">${maxScore} <span style="font-size:9px;font-weight:700;color:rgba(255,255,255,0.35)">pts</span></span>
                                        </div>
                                    </div>
                                </div>`;
                    }
                }
            };

            if(teamBar) teamBar.destroy();
            teamBar = new ApexCharts(barEl, barOptions);
            teamBar.render();
        };

        // Initial render
        setTimeout(() => renderCharts(currentChartsData), 100);

        // Listen for updates with fresh data from Livewire
        $wire.on('refreshCharts', (event) => {
            currentChartsData = event.charts;
            setTimeout(() => renderCharts(currentChartsData), 100);

        });

        // ── KT Charts ─────────────────────────────────────────────────
        let ktScatter, ktBar;

        const renderKtCharts = (d) => {
            const scatterEl = document.querySelector('#ktScatterChart');
            const barEl     = document.querySelector('#ktBarChart');
            if (!scatterEl || !barEl) return;

            // Show content, hide placeholder
            document.getElementById('ktChartsPlaceholder').classList.add('hidden');
            const content = document.getElementById('ktChartsContent');
            content.classList.remove('hidden');
            content.classList.add('grid');

            // ── Scatter ─────────────────────────────────────────────
            const scatterPoints = d.scatter.filter(p => p.y !== null && p.y > 0);
            if (ktScatter) { ktScatter.destroy(); ktScatter = null; }
            ktScatter = new ApexCharts(scatterEl, {
                series: [{ name: 'Ketua Tim', data: scatterPoints.map(p => ({ x: p.x, y: p.y })) }],
                chart: {
                    type: 'scatter', height: 270,
                    fontFamily: 'Outfit, sans-serif',
                    toolbar: { show: false }, zoom: { enabled: false }, animations: { enabled: true, speed: 600 }
                },
                colors: ['#6366f1'],
                markers: { size: 8, strokeColors: '#fff', strokeWidth: 2, hover: { sizeOffset: 3 } },
                xaxis: {
                    tickAmount: Math.max(...scatterPoints.map(p => p.x), 1),
                    title: { text: 'JUMLAH TIM DIPIMPIN', style: { fontSize: '8px', color: '#94a3b8', fontWeight: 900 } },
                    labels: { style: { fontSize: '9px', fontWeight: 800, colors: '#64748b' } }
                },
                yaxis: {
                    min: 0, max: 100,
                    title: { text: 'NILAI AKHIR', style: { fontSize: '8px', color: '#94a3b8', fontWeight: 900 } },
                    labels: { style: { fontSize: '9px', fontWeight: 800, colors: '#64748b' } }
                },
                grid: { borderColor: '#f1f5f9' },
                tooltip: {
                    theme: 'dark',
                    custom: function({ dataPointIndex }) {
                        const p = scatterPoints[dataPointIndex];
                        if (!p) return '';
                        return `<div style="padding:10px 14px;background:#0f172a;color:#fff;border-radius:10px;font-family:Outfit,sans-serif">
                            <p style="font-size:10px;font-weight:900;text-transform:uppercase;letter-spacing:0.1em;color:#818cf8;margin-bottom:4px">${p.name}</p>
                            <p style="font-size:9px;font-weight:700;color:rgba(255,255,255,0.4)">${p.nip}</p>
                            <div style="margin-top:8px;display:flex;gap:16px">
                                <div><p style="font-size:8px;font-weight:900;text-transform:uppercase;color:rgba(255,255,255,0.35)">Tim</p><p style="font-size:18px;font-weight:900;font-style:italic;color:#fff">${p.x}</p></div>
                                <div><p style="font-size:8px;font-weight:900;text-transform:uppercase;color:rgba(255,255,255,0.35)">Nilai</p><p style="font-size:18px;font-weight:900;font-style:italic;color:#a5b4fc">${p.y}</p></div>
                            </div>
                        </div>`;
                    }
                }
            });
            ktScatter.render();

            // ── Bar ──────────────────────────────────────────────────
            if (ktBar) { ktBar.destroy(); ktBar = null; }
            ktBar = new ApexCharts(barEl, {
                series: [{ name: 'Nilai Akhir', data: d.barSeries.map(v => v ?? 0) }],
                chart: {
                    type: 'bar', height: 270,
                    fontFamily: 'Outfit, sans-serif',
                    toolbar: { show: false }, animations: { enabled: true, speed: 600 }
                },
                colors: ['#6366f1'],
                plotOptions: {
                    bar: { borderRadius: 6, columnWidth: '55%', dataLabels: { position: 'top' } }
                },
                dataLabels: {
                    enabled: true,
                    formatter: v => v > 0 ? v.toFixed(1) : '-',
                    offsetY: -18,
                    style: { fontSize: '9px', colors: ['#6366f1'], fontWeight: 900 }
                },
                xaxis: {
                    categories: d.barLabels,
                    labels: {
                        style: { fontSize: '9px', fontWeight: 800, colors: '#64748b' },
                        rotate: -35, hideOverlappingLabels: true
                    }
                },
                yaxis: {
                    min: 0, max: 100,
                    labels: { style: { fontSize: '9px', fontWeight: 800, colors: '#64748b' } }
                },
                grid: { borderColor: '#f1f5f9' },
                tooltip: {
                    theme: 'dark',
                    custom: function({ dataPointIndex }) {
                        const name  = d.barLabels[dataPointIndex] || '-';
                        const nip   = d.barNips[dataPointIndex]   || '-';
                        const score = d.barSeries[dataPointIndex];
                        return `<div style="padding:10px 14px;background:#0f172a;color:#fff;border-radius:10px;font-family:Outfit,sans-serif">
                            <p style="font-size:10px;font-weight:900;text-transform:uppercase;letter-spacing:0.1em;color:#818cf8;margin-bottom:2px">${name}</p>
                            <p style="font-size:9px;font-weight:700;color:rgba(255,255,255,0.4);margin-bottom:8px">${nip}</p>
                            <p style="font-size:22px;font-weight:900;font-style:italic;color:#fff">${score !== null ? score : '—'} <span style="font-size:10px;font-weight:700;color:rgba(255,255,255,0.35)">pts</span></p>
                        </div>`;
                    }
                }
            });
            ktBar.render();
        };

        $wire.on('refreshKtCharts', (event) => {
            setTimeout(() => renderKtCharts(event.ktChartData), 100);
        });
    </script>
    @endscript
</div>
