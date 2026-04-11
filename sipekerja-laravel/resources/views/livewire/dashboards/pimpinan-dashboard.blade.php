<div class="font-outfit space-y-6 pb-24 md:pb-12">
    <!-- Header Area -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-800 tracking-tight italic">Dashboard Overview</h2>
            <p class="text-slate-400 text-[11px] font-medium">Monitoring performa dan beban kerja pegawai secara real-time.</p>
        </div>
        <div class="hidden md:flex flex-wrap gap-1.5 p-1 bg-white rounded-xl shadow-sm border border-slate-100">
            <button wire:click="setActiveTab('overview')" class="px-5 py-2 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all {{ $activeTab === 'overview' ? 'bg-minimal-indigo text-white shadow-md' : 'text-slate-400 hover:bg-slate-50' }}">
                Ringkasan Tabular
            </button>
            <button wire:click="setActiveTab('report')" class="px-5 py-2 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all {{ $activeTab === 'report' ? 'bg-minimal-indigo text-white shadow-md' : 'text-slate-400 hover:bg-slate-50' }}">
                Report Individu
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
                </div>
            </div>
            {{-- ===== TAMPILAN DESKTOP (tabel, md+) ===== --}}
            <div class="hidden md:block overflow-x-auto px-4">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50/30">
                            <th class="px-4 py-4 text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 text-center w-12">No</th>
                            <th class="px-6 py-4 text-[9px] font-black uppercase tracking-[0.2em] text-slate-400">Pegawai</th>
                            <th class="px-4 py-4 text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 text-center">Plotting</th>
                            <th class="px-4 py-4 text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 text-center">Avg Score</th>
                            <th class="px-6 py-4 text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($rekap as $u)
                        <tr class="hover:bg-slate-50 group transition-all duration-300">
                            <td class="px-4 py-4 text-center">
                                <span class="text-xs font-black text-slate-800 tabular-nums">{{ $loop->iteration }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-slate-100 to-slate-200 border border-white flex items-center justify-center text-slate-400 font-black italic shadow-inner group-hover:bg-minimal-indigo group-hover:text-white transition-all text-[11px]">
                                        {{ substr($u->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="text-[12px] font-black text-slate-700 leading-tight group-hover:text-minimal-indigo transition-colors">{{ $u->name }}</p>
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
                                <span class="text-lg font-black italic {{ $u->averageScore >= 80 ? 'text-emerald-600' : ($u->averageScore >= 60 ? 'text-amber-500' : ($u->averageScore > 0 ? 'text-red-500' : 'text-slate-200')) }}">
                                    {{ $u->averageScore > 0 ? number_format($u->averageScore, 2) : '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button wire:click="setDetailUser('{{ $u->id }}')" class="px-4 py-2 rounded-lg border border-minimal-indigo/20 text-minimal-indigo text-[9px] font-black uppercase tracking-widest hover:bg-minimal-indigo hover:text-white transition-all shadow-sm">
                                    Detail
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- ===== TAMPILAN MOBILE (kartu, < md) ===== --}}
            <div class="block md:hidden divide-y divide-slate-50">
                @forelse($rekap as $u)
                @php $allDone = $u->totalTeams > 0 && $u->ratedTeams === $u->totalTeams; @endphp
                <div class="px-4 py-3 flex flex-col gap-3">
                    {{-- Baris atas: avatar + nama + badge --}}
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="shrink-0 w-9 h-9 rounded-xl bg-gradient-to-br from-slate-100 to-slate-200 border border-white flex items-center justify-center text-slate-400 font-black italic shadow-inner text-[11px]">
                                {{ substr($u->name, 0, 1) }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-[12px] font-black text-slate-800 uppercase tracking-tight leading-none truncate">{{ $u->name }}</p>
                                <p class="text-[9px] font-mono font-bold text-slate-400 uppercase mt-0.5">{{ $u->nip }}</p>
                            </div>
                        </div>
                        <span class="shrink-0 inline-flex px-2.5 py-1 rounded-full text-[8px] font-black uppercase tracking-widest {{ $allDone ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600' }}">
                            {{ $u->ratedTeams }}/{{ $u->totalTeams }} Done
                        </span>
                    </div>
                    {{-- Baris bawah: score + tombol detail --}}
                    <div class="flex items-center gap-3">
                        <div class="flex-1 flex items-center justify-between bg-slate-50 rounded-xl px-3 py-2 border border-slate-100">
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Avg Score</span>
                            <span class="text-base font-black italic {{ $u->averageScore >= 80 ? 'text-emerald-600' : ($u->averageScore >= 60 ? 'text-amber-500' : ($u->averageScore > 0 ? 'text-red-500' : 'text-slate-300')) }}">
                                {{ $u->averageScore > 0 ? number_format($u->averageScore, 2) : '—' }}
                            </span>
                        </div>
                        <button
                            wire:click="setDetailUser('{{ $u->id }}')"
                            class="shrink-0 px-4 py-2 rounded-xl border border-minimal-indigo/20 text-minimal-indigo text-[9px] font-black uppercase tracking-widest hover:bg-minimal-indigo hover:text-white transition-all active:scale-95"
                        >Detail</button>
                    </div>
                </div>
                @empty
                <div class="px-4 py-10 text-center text-slate-300 font-bold italic text-xs">Tidak ada data.</div>
                @endforelse
            </div>
        </div>
    </div>
    @else
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
                <div class="relative max-w-lg mx-auto group">
                    <input wire:model.live.debounce.300ms="reportSearch" placeholder="Cari nama atau NIP..." class="w-full h-12 pl-12 pr-6 rounded-2xl bg-slate-50/50 border border-slate-100 text-sm font-bold shadow-inner focus:ring-8 focus:ring-minimal-indigo/5 transition-all">
                    <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-minimal-indigo transition-colors hover:scale-110">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
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

    <!-- Detail Dialog -->
    @if($detailUser)
    <div class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-slate-900/60 backdrop-blur-sm animate-in fade-in duration-300 pointer-events-auto">
        <div class="bg-white w-full max-w-xl rounded-[2rem] shadow-2xl overflow-hidden animate-in zoom-in-95 duration-300 relative max-h-[90vh] overflow-y-auto">
            <div class="p-6 sm:p-10 relative z-10">
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center gap-5">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-minimal-indigo to-minimal-violet text-white flex items-center justify-center text-3xl font-black italic shadow-xl">
                            {{ substr($detailUser->name, 0, 1) }}
                        </div>
                        <div>
                            <h4 class="text-xl font-black text-slate-800 tracking-tight leading-tight">{{ $detailUser->name }}</h4>
                            <p class="text-[9px] font-black uppercase tracking-[0.2em] text-minimal-indigo/60">Plotting Period {{ $monthNames[$month] }}</p>
                        </div>
                    </div>
                    <button wire:click="$set('detailUserId', null)" class="w-9 h-9 rounded-xl bg-slate-50 text-slate-300 hover:bg-red-50 hover:text-red-500 transition-all flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-8">
                    <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 shadow-inner">
                        <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1">NIP Pegawai</p>
                        <p class="text-lg font-black text-slate-700 font-mono">{{ $detailUser->nip }}</p>
                    </div>
                    <div class="p-6 bg-minimal-indigo text-white rounded-2xl shadow-xl shadow-minimal-indigo/20">
                        <p class="text-[9px] font-black uppercase tracking-widest text-white/50 mb-1">Avg Score</p>
                        <p class="text-3xl font-black italic">{{ $detailUser->averageScore > 0 ? number_format($detailUser->averageScore, 2) : 'N/A' }}</p>
                    </div>
                </div>

                <div class="space-y-3 max-h-64 overflow-y-auto pr-2 custom-scrollbar">
                    @forelse($detailUser->details as $d)
                    <div class="flex items-center justify-between p-4 rounded-xl border border-slate-50 bg-white hover:bg-slate-50 transition-all group">
                        <div class="flex items-center gap-4">
                            <div class="w-0.5 h-8 rounded-full bg-slate-100 group-hover:bg-minimal-indigo transition-all"></div>
                            <div>
                                <p class="text-[12px] font-black text-slate-700 leading-tight group-hover:text-minimal-indigo transition-all">{{ $d['teamName'] }}</p>
                                <p class="text-[9px] text-slate-400 font-bold uppercase tracking-tight">Leader: {{ $d['leaderName'] }}</p>
                            </div>
                        </div>
                        <p class="text-xl font-black italic {{ $d['score'] ? 'text-minimal-indigo' : 'text-slate-200' }}">
                            {{ $d['score'] ?? '-' }}
                        </p>
                    </div>
                    @empty
                    <div class="p-8 text-center text-slate-300 font-bold italic text-xs">No plotting found...</div>
                    @endforelse
                </div>

                <div class="mt-10 flex justify-end">
                    <button wire:click="setReportUserId('{{ $detailUser->id }}')" class="px-8 py-3 rounded-xl bg-slate-900 text-white text-[10px] font-black uppercase tracking-widest hover:bg-minimal-indigo transition-all shadow-xl active:scale-95">
                        Dash Lengkap
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

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
                        return `<div style="padding:12px 16px;background:#0f172a;color:#fff;border-radius:12px;border:1px solid rgba(255,255,255,0.08);font-family:Outfit,sans-serif;min-width:160px">
                                    <p style="font-size:10px;font-weight:900;text-transform:uppercase;letter-spacing:0.1em;color:#818cf8;margin-bottom:6px">${teamName}</p>
                                    <p style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:rgba(255,255,255,0.45);margin-bottom:8px">Ketua: ${leader}</p>
                                    <p style="font-size:16px;font-weight:900;font-style:italic;color:#fff">${score} <span style="font-size:9px;font-weight:700;color:rgba(255,255,255,0.4)">pts</span></p>
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
    </script>
    @endscript
</div>
