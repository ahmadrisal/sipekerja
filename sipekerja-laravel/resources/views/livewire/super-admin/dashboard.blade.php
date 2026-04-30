<div class="space-y-6">

    {{-- Flash --}}
    @if(session('success'))
    <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 rounded-2xl px-5 py-3">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-emerald-500 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
        <p class="text-sm font-bold text-emerald-700">{{ session('success') }}</p>
    </div>
    @endif

    {{-- Header + Tabs --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-800 uppercase italic tracking-tight">Super Admin</h2>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-0.5">Panel Pusat — PAKAR</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            @foreach([['dashboard','Dashboard'],['satker','Kelola Satker'],['pegawai','Kelola Pegawai'],['laporan','Laporan'],['konfigurasi','Konfigurasi']] as [$key,$label])
            <button wire:click="setTab('{{ $key }}')"
                class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all {{ $activeTab === $key ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }}">
                {{ $label }}
            </button>
            @endforeach
        </div>
    </div>

    {{-- Period filter (Dashboard + Laporan tabs) --}}
    @if(in_array($activeTab, ['dashboard','laporan']))
    <div class="flex items-center gap-3">
        <select wire:model.live="month"
            class="bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-black text-slate-700 focus:ring-4 focus:ring-slate-100 focus:border-slate-400 transition-all">
            @foreach($monthNames as $n => $name)
            <option value="{{ $n }}">{{ $name }}</option>
            @endforeach
        </select>
        <select wire:model.live="year"
            class="bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-black text-slate-700 focus:ring-4 focus:ring-slate-100 focus:border-slate-400 transition-all">
            @foreach(range(date('Y'), date('Y')-2) as $y)
            <option value="{{ $y }}">{{ $y }}</option>
            @endforeach
        </select>
    </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════════
         TAB: DASHBOARD
    ══════════════════════════════════════════════════════════════════ --}}
    @if($activeTab === 'dashboard')

    {{-- Global aggregate stats --}}
    @if(!empty($globalStats))
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        @php
        $gStatCards = [
            ['label'=>'Total Satker','value'=>$globalStats['satker_count'],'color'=>'text-slate-800'],
            ['label'=>'Total Pegawai','value'=>$globalStats['total_pegawai'],'color'=>'text-slate-800'],
            ['label'=>'Total Tim','value'=>$globalStats['total_tim'],'color'=>'text-slate-800'],
            ['label'=>'Total Admin','value'=>$globalStats['total_admin'],'color'=>$globalStats['total_admin']>0?'text-emerald-600':'text-rose-500'],
            ['label'=>'Sudah Dinilai','value'=>$globalStats['total_rated'],'color'=>'text-minimal-indigo'],
            ['label'=>'Avg Nilai Global','value'=>$globalStats['avg_score']??'—','color'=>($globalStats['avg_score']>=80?'text-emerald-600':($globalStats['avg_score']>=60?'text-amber-500':'text-slate-400'))],
        ];
        @endphp
        @foreach($gStatCards as $card)
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-4 py-4 text-center">
            <p class="text-[8px] font-black uppercase tracking-widest text-slate-400 mb-1">{{ $card['label'] }}</p>
            <p class="text-2xl font-black italic {{ $card['color'] }}">{{ $card['value'] }}</p>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Per-satker rekap cards --}}
    <div class="grid grid-cols-1 gap-3">
        @forelse($rekap as $row)
        @php $satker = $row['satker']; @endphp
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-6 py-3 border-b border-slate-50"
                style="{{ $satker->type === 'provinsi' ? 'background:linear-gradient(90deg,#1e3a5f,#003366);' : 'background:#f8fafc;' }}">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center text-xs font-black {{ $satker->type === 'provinsi' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-600' }}">
                        {{ substr($satker->name, 0, 2) }}
                    </div>
                    <div>
                        <p class="text-sm font-black {{ $satker->type === 'provinsi' ? 'text-white' : 'text-slate-800' }} uppercase italic">{{ $satker->name }}</p>
                        <p class="text-[9px] font-bold {{ $satker->type === 'provinsi' ? 'text-white/60' : 'text-slate-400' }} uppercase tracking-widest">{{ $satker->type === 'provinsi' ? 'Provinsi' : 'Kabkot' }} {{ $satker->kode ? "· {$satker->kode}" : '' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    {{-- Progress bar --}}
                    <div class="hidden sm:block w-32">
                        <div class="flex justify-between mb-1">
                            <span class="text-[8px] font-black {{ $satker->type === 'provinsi' ? 'text-white/60' : 'text-slate-400' }} uppercase">Progress</span>
                            <span class="text-[8px] font-black {{ $satker->type === 'provinsi' ? 'text-white' : 'text-slate-700' }}">{{ $row['pct_rated'] }}%</span>
                        </div>
                        <div class="h-1.5 rounded-full {{ $satker->type === 'provinsi' ? 'bg-white/20' : 'bg-slate-100' }}">
                            <div class="h-full rounded-full {{ $row['pct_rated'] >= 80 ? 'bg-emerald-400' : ($row['pct_rated'] >= 40 ? 'bg-amber-400' : 'bg-rose-400') }}"
                                style="width:{{ $row['pct_rated'] }}%"></div>
                        </div>
                    </div>
                    @if(!$satker->is_active)
                    <span class="px-2 py-1 bg-rose-100 text-rose-600 text-[8px] font-black uppercase rounded-full">Nonaktif</span>
                    @endif
                </div>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-5 divide-x divide-slate-50">
                @foreach([['Total Pegawai',$row['total_pegawai'],'text-slate-800'],['Total Tim',$row['total_tim'],'text-slate-800'],['Admin',$row['total_admin'],$row['total_admin']>0?'text-emerald-600':'text-rose-500'],['Dinilai',$row['rated_count'].' ('.$row['pct_rated'].'%)','text-minimal-indigo'],['Avg Nilai',$row['avg_score']??'—',($row['avg_score']>=80?'text-emerald-600':($row['avg_score']>=60?'text-amber-500':($row['avg_score']>0?'text-red-500':'text-slate-300')))]] as [$lbl,$val,$col])
                <div class="px-4 py-3 text-center">
                    <p class="text-[8px] font-black uppercase tracking-widest text-slate-400 mb-1">{{ $lbl }}</p>
                    <p class="text-xl font-black italic {{ $col }}">{{ $val }}</p>
                </div>
                @endforeach
            </div>
        </div>
        @empty
        <div class="text-center py-16 text-slate-400 font-bold text-sm">Belum ada satker terdaftar.</div>
        @endforelse
    </div>

    {{-- Leaderboard --}}
    @if(!empty($leaderboard))
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-50">
            <h3 class="text-xs font-black text-slate-700 uppercase italic tracking-tight">Leaderboard Nilai Kinerja</h3>
            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">{{ $monthNames[$month] }} {{ $year }} — Rata-rata final score</p>
        </div>
        <div class="divide-y divide-slate-50">
            @foreach($leaderboard as $i => $row)
            <div class="flex items-center px-6 py-3 hover:bg-slate-50/50">
                <div class="w-8 h-8 rounded-xl flex items-center justify-center font-black text-sm shrink-0
                    {{ $i === 0 ? 'bg-amber-400 text-white' : ($i === 1 ? 'bg-slate-300 text-white' : ($i === 2 ? 'bg-orange-300 text-white' : 'bg-slate-100 text-slate-500')) }}">
                    {{ $i + 1 }}
                </div>
                <div class="ml-4 flex-1">
                    <p class="text-xs font-black text-slate-800 uppercase italic">{{ $row['satker']->name }}</p>
                    <p class="text-[9px] font-bold text-slate-400">{{ $row['rated_count'] }} dinilai · {{ $row['pct_rated'] }}% selesai</p>
                </div>
                <div class="text-right">
                    <p class="text-xl font-black italic {{ $row['avg_score'] >= 80 ? 'text-emerald-600' : ($row['avg_score'] >= 60 ? 'text-amber-500' : 'text-red-500') }}">
                        {{ $row['avg_score'] }}
                    </p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Distribution --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h3 class="text-xs font-black text-slate-700 uppercase italic tracking-tight">Distribusi Penugasan</h3>
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Tim per pegawai & pegawai per tim</p>
            </div>
            <select wire:model.live="distSatkerId"
                class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-black text-slate-700 focus:ring-4 focus:ring-slate-100">
                <option value="">Semua Satker</option>
                @foreach($satkers as $s)
                <option value="{{ $s->id }}">{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-slate-50">
            {{-- Tim per pegawai --}}
            <div class="p-5">
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-3">Tim per Pegawai (Top 10)</p>
                @forelse($distribution['timPerPegawai'] as $row)
                <div class="flex items-center gap-3 mb-2">
                    <p class="text-[10px] font-bold text-slate-700 flex-1 truncate">{{ $row->name }}</p>
                    <div class="flex items-center gap-2">
                        <div class="h-2 rounded-full bg-minimal-indigo/20 w-24">
                            <div class="h-full rounded-full bg-minimal-indigo" style="width:{{ min(100, $row->jumlah * 25) }}%"></div>
                        </div>
                        <span class="text-[10px] font-black text-minimal-indigo w-4 text-right">{{ $row->jumlah }}</span>
                    </div>
                </div>
                @empty
                <p class="text-[10px] font-bold text-slate-300 text-center py-4">Tidak ada data.</p>
                @endforelse
            </div>
            {{-- Pegawai per tim --}}
            <div class="p-5">
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-3">Pegawai per Tim (Top 10)</p>
                @forelse($distribution['pegawaiPerTim'] as $row)
                <div class="flex items-center gap-3 mb-2">
                    <p class="text-[10px] font-bold text-slate-700 flex-1 truncate">{{ $row->name }}</p>
                    <div class="flex items-center gap-2">
                        <div class="h-2 rounded-full bg-amber-100 w-24">
                            <div class="h-full rounded-full bg-amber-400" style="width:{{ min(100, $row->jumlah * 20) }}%"></div>
                        </div>
                        <span class="text-[10px] font-black text-amber-600 w-4 text-right">{{ $row->jumlah }}</span>
                    </div>
                </div>
                @empty
                <p class="text-[10px] font-bold text-slate-300 text-center py-4">Tidak ada data.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════
         TAB: KELOLA SATKER
    ══════════════════════════════════════════════════════════════════ --}}
    @elseif($activeTab === 'satker')
    <div class="space-y-4">
        <div class="flex justify-between items-center flex-wrap gap-3">
            <button wire:click="openMoveUser"
                class="flex items-center gap-2 px-4 py-2.5 bg-amber-50 text-amber-700 border border-amber-200 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-amber-100 transition-all active:scale-95">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                Pindah User Antar Satker
            </button>
            <button wire:click="openCreateSatker"
                class="flex items-center gap-2 px-4 py-2.5 bg-slate-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-700 transition-all active:scale-95">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Tambah Satker Kabkot
            </button>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-[9px] font-black text-slate-400 uppercase tracking-[0.15em]">
                        <th class="px-6 py-4 border-b border-slate-100">Nama Satker</th>
                        <th class="px-4 py-4 border-b border-slate-100 text-center">Tipe</th>
                        <th class="px-4 py-4 border-b border-slate-100 text-center">Kode</th>
                        <th class="px-4 py-4 border-b border-slate-100 text-center">Pegawai</th>
                        <th class="px-4 py-4 border-b border-slate-100 text-center">Tim</th>
                        <th class="px-4 py-4 border-b border-slate-100 text-center">Admin</th>
                        <th class="px-4 py-4 border-b border-slate-100 text-center">Status</th>
                        <th class="px-6 py-4 border-b border-slate-100 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($satkers as $satker)
                    @php
                        $pegawaiCount = $satker->users()->whereHas('roles', fn($q) => $q->where('name', 'Pegawai'))->count();
                        $teamCount    = $satker->teams()->count();
                        $adminCount   = $satker->users()->whereHas('roles', fn($q) => $q->where('name', 'Admin'))->count();
                    @endphp
                    <tr class="border-t border-slate-50 hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center text-[11px] font-black italic shrink-0
                                    {{ $satker->type === 'provinsi' ? 'bg-[#003366] text-white' : 'bg-slate-100 text-slate-600' }}">
                                    {{ substr($satker->name, 0, 2) }}
                                </div>
                                <p class="text-[12px] font-black text-slate-800 uppercase italic tracking-tight">{{ $satker->name }}</p>
                            </div>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <span class="inline-flex px-2.5 py-1 rounded-full text-[8px] font-black uppercase tracking-widest
                                {{ $satker->type === 'provinsi' ? 'bg-[#003366]/10 text-[#003366]' : 'bg-amber-50 text-amber-700' }}">
                                {{ $satker->type_label }}
                            </span>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <span class="text-[10px] font-mono font-bold text-slate-500">{{ $satker->kode ?? '—' }}</span>
                        </td>
                        <td class="px-4 py-4 text-center"><span class="text-sm font-black text-slate-700">{{ $pegawaiCount }}</span></td>
                        <td class="px-4 py-4 text-center"><span class="text-sm font-black text-slate-700">{{ $teamCount }}</span></td>
                        <td class="px-4 py-4 text-center">
                            <span class="text-sm font-black {{ $adminCount > 0 ? 'text-emerald-600' : 'text-rose-500' }}">{{ $adminCount }}</span>
                        </td>
                        <td class="px-4 py-4 text-center">
                            @if($satker->type === 'provinsi')
                                <span class="inline-flex px-2.5 py-1 rounded-full text-[8px] font-black uppercase tracking-widest bg-emerald-50 text-emerald-700">Aktif</span>
                            @else
                                <button wire:click="toggleSatkerActive('{{ $satker->id }}')"
                                    class="inline-flex px-2.5 py-1 rounded-full text-[8px] font-black uppercase tracking-widest transition-all
                                    {{ $satker->is_active ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' : 'bg-rose-50 text-rose-600 hover:bg-rose-100' }}">
                                    {{ $satker->is_active ? 'Aktif' : 'Nonaktif' }}
                                </button>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-1.5">
                                <button wire:click="openAddUser('{{ $satker->id }}')"
                                    class="px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 text-[9px] font-black uppercase tracking-widest hover:bg-emerald-100 transition-all active:scale-95">
                                    + User
                                </button>
                                @if($satker->type === 'kabkot')
                                <button wire:click="openAssignAdmin('{{ $satker->id }}')"
                                    class="px-3 py-1.5 rounded-lg bg-minimal-indigo/10 text-minimal-indigo text-[9px] font-black uppercase tracking-widest hover:bg-minimal-indigo hover:text-white transition-all active:scale-95">
                                    Assign Admin
                                </button>
                                <button wire:click="openEditSatker('{{ $satker->id }}')"
                                    class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 text-[9px] font-black uppercase tracking-widest hover:bg-slate-200 transition-all active:scale-95">
                                    Edit
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════
         TAB: LAPORAN
    ══════════════════════════════════════════════════════════════════ --}}
    @elseif($activeTab === 'laporan')
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-50">
            <h3 class="text-xs font-black text-slate-700 uppercase italic">Laporan Konsolidasi — {{ $monthNames[$month] }} {{ $year }}</h3>
            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Unduh laporan per satker · berdasarkan nilai pimpinan masing-masing</p>
        </div>
        <table class="w-full text-left">
            <thead>
                <tr class="text-[9px] font-black text-slate-400 uppercase tracking-[0.15em]">
                    <th class="px-6 py-3 border-b border-slate-100">Satker</th>
                    <th class="px-4 py-3 border-b border-slate-100 text-center">Progress</th>
                    <th class="px-4 py-3 border-b border-slate-100 text-center">Nilai Pegawai</th>
                    <th class="px-4 py-3 border-b border-slate-100 text-center">Nilai Ketua Tim</th>
                    <th class="px-4 py-3 border-b border-slate-100 text-center">Nilai Kepala Kabkot</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rekap as $row)
                @php $satker = $row['satker']; @endphp
                <tr class="border-t border-slate-50 hover:bg-slate-50/50">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center text-[10px] font-black italic shrink-0
                                {{ $satker->type === 'provinsi' ? 'bg-[#003366] text-white' : 'bg-slate-100 text-slate-600' }}">
                                {{ substr($satker->name, 0, 2) }}
                            </div>
                            <div>
                                <p class="text-[11px] font-black text-slate-800 uppercase italic">{{ $satker->name }}</p>
                                <p class="text-[8px] font-bold text-slate-400">{{ $row['total_pegawai'] }} pegawai · {{ $row['total_tim'] }} tim</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-4 text-center">
                        <span class="inline-flex px-2.5 py-1 rounded-full text-[9px] font-black
                            {{ $row['pct_rated'] >= 80 ? 'bg-emerald-50 text-emerald-700' : ($row['pct_rated'] >= 40 ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-600') }}">
                            {{ $row['pct_rated'] }}%
                        </span>
                    </td>
                    <td class="px-4 py-4 text-center">
                        <a href="{{ route('export.super.pegawai', ['month'=>$month,'year'=>$year,'satker_id'=>$satker->id]) }}"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-minimal-indigo/10 text-minimal-indigo text-[9px] font-black uppercase rounded-lg hover:bg-minimal-indigo hover:text-white transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                            Export
                        </a>
                    </td>
                    <td class="px-4 py-4 text-center">
                        <a href="{{ route('export.super.ketuaTim', ['month'=>$month,'year'=>$year,'satker_id'=>$satker->id]) }}"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-minimal-indigo/10 text-minimal-indigo text-[9px] font-black uppercase rounded-lg hover:bg-minimal-indigo hover:text-white transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                            Export
                        </a>
                    </td>
                    <td class="px-4 py-4 text-center">
                        @if($satker->type === 'provinsi')
                        <a href="{{ route('export.super.kabkot', ['month'=>$month,'year'=>$year,'satker_id'=>$satker->id]) }}"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-minimal-indigo/10 text-minimal-indigo text-[9px] font-black uppercase rounded-lg hover:bg-minimal-indigo hover:text-white transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                            Export
                        </a>
                        @else
                        <span class="text-[9px] font-bold text-slate-300">Hanya Provinsi</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════
         TAB: KONFIGURASI
    ══════════════════════════════════════════════════════════════════ --}}
    @elseif($activeTab === 'konfigurasi')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Scoring config --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-50">
                <h3 class="text-xs font-black text-slate-700 uppercase italic">Bobot Penilaian Global</h3>
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Default untuk seluruh satker · dapat di-override per satker oleh Admin lokal</p>
            </div>
            <form wire:submit="saveGlobalConfig" class="p-6 space-y-6">
                {{-- Bobot --}}
                <div>
                    <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-3">Bobot <span class="text-slate-300">(total harus 100)</span></p>
                    <div class="grid grid-cols-3 gap-3">
                        @foreach([['weight_score','Nilai Kinerja'],['weight_volume','Volume/Kesulitan'],['weight_quality','Kualitas Kerja']] as [$k,$label])
                        <div>
                            <label class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1 block">{{ $label }}</label>
                            <div class="relative">
                                <input wire:model="configValues.{{ $k }}" type="number" min="0" max="100" step="1"
                                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 pr-8 text-sm font-black text-slate-700 focus:ring-4 focus:ring-slate-100 focus:border-slate-400">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-black text-slate-300">%</span>
                            </div>
                            @error("configValues.{$k}") <p class="text-[8px] font-black text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        @endforeach
                    </div>
                </div>
                {{-- Volume --}}
                <div>
                    <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-3">Skor Volume / Tingkat Kesulitan</p>
                    <div class="grid grid-cols-3 gap-3">
                        @foreach([['volume_ringan','Ringan'],['volume_sedang','Sedang'],['volume_berat','Berat']] as [$k,$label])
                        <div>
                            <label class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1 block">{{ $label }}</label>
                            <input wire:model="configValues.{{ $k }}" type="number" min="0" max="100"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-black text-slate-700 focus:ring-4 focus:ring-slate-100">
                        </div>
                        @endforeach
                    </div>
                </div>
                {{-- Kualitas --}}
                <div>
                    <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-3">Skor Kualitas Kerja</p>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        @foreach([['quality_kurang','Kurang'],['quality_cukup','Cukup'],['quality_baik','Baik'],['quality_sangat_baik','Sangat Baik']] as [$k,$label])
                        <div>
                            <label class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1 block">{{ $label }}</label>
                            <input wire:model="configValues.{{ $k }}" type="number" min="0" max="100"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-black text-slate-700 focus:ring-4 focus:ring-slate-100">
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="pt-2">
                    <button type="submit" wire:loading.attr="disabled"
                        class="px-6 py-3 bg-slate-900 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-700 transition-all active:scale-95 disabled:opacity-70">
                        <span wire:loading.remove>Simpan Konfigurasi Global</span>
                        <span wire:loading>Menyimpan...</span>
                    </button>
                </div>
            </form>
        </div>

        {{-- Maintenance mode --}}
        <div class="space-y-4">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <h3 class="text-xs font-black text-slate-700 uppercase italic mb-1">Maintenance Mode</h3>
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-5">Kunci akses seluruh user kecuali Super Admin</p>
                <div class="flex items-center justify-between p-4 rounded-xl {{ $maintenanceMode ? 'bg-red-50 border border-red-200' : 'bg-slate-50 border border-slate-200' }}">
                    <div>
                        <p class="text-xs font-black {{ $maintenanceMode ? 'text-red-700' : 'text-slate-600' }} uppercase">
                            {{ $maintenanceMode ? 'Maintenance AKTIF' : 'Sistem Normal' }}
                        </p>
                        <p class="text-[9px] font-bold {{ $maintenanceMode ? 'text-red-400' : 'text-slate-400' }}">
                            {{ $maintenanceMode ? 'User tidak bisa login' : 'Semua user bisa login' }}
                        </p>
                    </div>
                    <button wire:click="toggleMaintenance" wire:confirm="{{ $maintenanceMode ? 'Matikan maintenance mode?' : 'Aktifkan maintenance mode? User tidak bisa login.' }}"
                        class="relative w-12 h-6 rounded-full transition-all {{ $maintenanceMode ? 'bg-red-500' : 'bg-slate-300' }}">
                        <span class="absolute top-0.5 w-5 h-5 bg-white rounded-full shadow transition-all {{ $maintenanceMode ? 'left-6' : 'left-0.5' }}"></span>
                    </button>
                </div>
            </div>

            {{-- Info card --}}
            <div class="bg-amber-50 border border-amber-100 rounded-2xl p-5">
                <p class="text-[9px] font-black text-amber-700 uppercase tracking-widest mb-2">Hierarki Konfigurasi</p>
                <ul class="space-y-1.5">
                    @foreach(['Super Admin: bobot global (acuan awal satker baru)','Admin Satker: override bobot untuk satkernya sendiri','Jika tidak di-override, satker pakai bobot global'] as $item)
                    <li class="flex items-start gap-2">
                        <span class="w-1 h-1 rounded-full bg-amber-500 mt-1.5 shrink-0"></span>
                        <p class="text-[9px] font-bold text-amber-700">{{ $item }}</p>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════════
         TAB: KELOLA PEGAWAI
    ══════════════════════════════════════════════════════════════════ --}}
    @if($activeTab === 'pegawai')
    <div class="space-y-4">

        {{-- Filter bar --}}
        <div class="flex flex-col sm:flex-row gap-3">
            <select wire:model.live="pgFilterSatker"
                class="bg-white border border-slate-200 rounded-xl px-3 py-2.5 text-xs font-black text-slate-700 focus:ring-4 focus:ring-slate-100 focus:border-slate-400 transition-all min-w-[200px]">
                <option value="">Semua Satker</option>
                @foreach($satkers as $s)
                <option value="{{ $s->id }}">{{ $s->name }}</option>
                @endforeach
            </select>
            <div class="relative flex-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input wire:model.live.debounce.300ms="pgSearch" type="text"
                    placeholder="Cari nama, NIP, email..."
                    class="w-full pl-9 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:ring-4 focus:ring-slate-100 focus:border-slate-400 transition-all">
            </div>
            <select wire:model.live="pgFilterRole"
                class="bg-white border border-slate-200 rounded-xl px-3 py-2.5 text-xs font-black text-slate-700 focus:ring-4 focus:ring-slate-100 transition-all">
                <option value="ALL">Semua Role</option>
                @foreach($allRoles as $r)
                <option value="{{ $r->name }}">{{ $r->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-[9px] font-black text-slate-400 uppercase tracking-[0.15em]">
                        <th class="px-5 py-4 border-b border-slate-100">Pegawai</th>
                        <th class="px-4 py-4 border-b border-slate-100">Kontak</th>
                        <th class="px-4 py-4 border-b border-slate-100">Peran & Penugasan</th>
                        <th class="px-4 py-4 border-b border-slate-100 text-center">Satker</th>
                        <th class="px-5 py-4 border-b border-slate-100 text-right">Manajemen</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pegawaiList as $u)
                    <tr class="border-t border-slate-50 hover:bg-slate-50/50 transition-colors group">
                        {{-- Pegawai --}}
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center text-slate-400 font-black italic text-[11px] group-hover:bg-minimal-indigo group-hover:text-white transition-all shrink-0">
                                    {{ substr($u->name, 0, 1) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[12px] font-black text-slate-700 truncate group-hover:text-minimal-indigo transition-colors">{{ $u->name }}</p>
                                    <p class="text-[9px] font-mono font-bold text-slate-400 uppercase tracking-tighter">{{ $u->nip }}</p>
                                </div>
                            </div>
                        </td>
                        {{-- Kontak --}}
                        <td class="px-4 py-3">
                            <p class="text-[11px] font-bold text-slate-600 truncate max-w-[160px]">{{ $u->email }}</p>
                            @if($u->username)
                            <p class="text-[9px] font-bold text-slate-300 italic">{{ $u->username }}</p>
                            @endif
                        </td>
                        {{-- Peran & Penugasan --}}
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-1 max-w-[200px]">
                                @foreach($u->roles as $role)
                                <span class="px-2 py-0.5 bg-minimal-indigo/5 text-minimal-indigo text-[8px] font-black rounded-md uppercase tracking-tighter border border-minimal-indigo/10">
                                    {{ $role->name }}
                                </span>
                                @endforeach
                                @foreach($u->teams as $team)
                                <span class="px-2 py-0.5 bg-amber-50 text-amber-600 text-[8px] font-black rounded-md uppercase tracking-tighter border border-amber-100">
                                    {{ $team->team_name }}
                                </span>
                                @endforeach
                                @if($u->roles->isEmpty() && $u->teams->isEmpty())
                                <span class="text-[9px] text-slate-300 font-black uppercase italic">Belum Ditugaskan</span>
                                @endif
                            </div>
                        </td>
                        {{-- Satker --}}
                        <td class="px-4 py-3 text-center">
                            @if($u->satker)
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[8px] font-black uppercase tracking-widest
                                {{ $u->satker->type === 'provinsi' ? 'bg-[#003366]/10 text-[#003366]' : 'bg-amber-50 text-amber-700' }}">
                                {{ Str::limit($u->satker->name, 20) }}
                            </span>
                            @else
                            <span class="text-[9px] text-slate-300 font-black">—</span>
                            @endif
                        </td>
                        {{-- Manajemen --}}
                        <td class="px-5 py-3 text-right">
                            <div class="flex justify-end gap-1">
                                <button wire:click="openEditUser('{{ $u->id }}')"
                                    class="w-8 h-8 bg-slate-50 text-slate-400 rounded-lg flex items-center justify-center hover:bg-minimal-indigo hover:text-white transition-all"
                                    title="Edit Data">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                                </button>
                                <button wire:click="openResetPw('{{ $u->id }}')"
                                    class="w-8 h-8 bg-slate-50 text-slate-400 rounded-lg flex items-center justify-center hover:bg-amber-100 hover:text-amber-600 transition-all"
                                    title="Reset Password">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M21.5 2v6h-6"/><path d="M2.5 22v-6h6"/><path d="M21.34 15.57a10 10 0 0 1-17.17 2.35"/><path d="M2.66 8.43a10 10 0 0 1 17.17-2.35"/></svg>
                                </button>
                                <button wire:click="confirmDeleteUser('{{ $u->id }}')"
                                    class="w-8 h-8 bg-slate-50 text-slate-400 rounded-lg flex items-center justify-center hover:bg-red-50 hover:text-red-500 transition-all"
                                    title="Hapus">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-14 text-slate-300 font-bold text-sm">
                            @if($pgSearch || $pgFilterSatker || $pgFilterRole !== 'ALL')
                                Tidak ada pegawai yang cocok dengan filter.
                            @else
                                Belum ada pegawai terdaftar.
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            @if($pegawaiList instanceof \Illuminate\Pagination\LengthAwarePaginator && $pegawaiList->hasPages())
            <div class="px-5 py-4 border-t border-slate-50">
                {{ $pegawaiList->links() }}
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════════
         MODAL: Edit User (Super Admin)
    ══════════════════════════════════════════════════════════════════ --}}
    @if($showEditUserModal)
    <div class="fixed inset-0 z-[200] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="$set('showEditUserModal', false)"></div>
        <div class="relative bg-white w-full max-w-lg rounded-3xl shadow-2xl border-t-4 border-minimal-indigo p-8">
            <h4 class="text-lg font-black text-slate-800 uppercase italic mb-6">Edit Data Pegawai</h4>
            <form wire:submit="saveEditUser" class="space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div class="col-span-2">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Nama Lengkap</label>
                        <input wire:model="editUserName" type="text"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-100 focus:border-minimal-indigo transition-all">
                        @error('editUserName') <p class="text-[9px] font-black text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 block">NIP</label>
                        <input wire:model="editUserNip" type="text"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-100 transition-all">
                        @error('editUserNip') <p class="text-[9px] font-black text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Username <span class="text-slate-300">(opsional)</span></label>
                        <input wire:model="editUserUsername" type="text"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-100 transition-all">
                        @error('editUserUsername') <p class="text-[9px] font-black text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="col-span-2">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Email</label>
                        <input wire:model="editUserEmail" type="email"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-100 transition-all">
                        @error('editUserEmail') <p class="text-[9px] font-black text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="col-span-2">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Satker</label>
                        <select wire:model="editUserSatkerId"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-100 transition-all">
                            <option value="">— Tidak ada satker —</option>
                            @foreach($satkers as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Role</label>
                        @error('editUserRoles') <p class="text-[9px] font-black text-red-500 mb-2">{{ $message }}</p> @enderror
                        <div class="flex flex-wrap gap-2">
                            @foreach(\Spatie\Permission\Models\Role::orderBy('name')->get() as $r)
                            <label class="flex items-center gap-1.5 cursor-pointer">
                                <input type="checkbox" wire:model="editUserRoles" value="{{ $r->name }}"
                                    class="w-3.5 h-3.5 rounded border-slate-300 text-minimal-indigo focus:ring-minimal-indigo">
                                <span class="text-[10px] font-black text-slate-600 uppercase tracking-widest">{{ $r->name }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" wire:click="$set('showEditUserModal', false)"
                        class="flex-1 py-3 bg-slate-100 text-slate-500 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-200 transition-all">Batal</button>
                    <button type="submit" wire:loading.attr="disabled"
                        class="flex-[2] py-3 bg-minimal-indigo text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-700 transition-all active:scale-95">
                        <span wire:loading.remove>Simpan Perubahan</span>
                        <span wire:loading>Menyimpan...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════════
         MODAL: Reset Password (Super Admin)
    ══════════════════════════════════════════════════════════════════ --}}
    @if($showResetPwModal)
    <div class="fixed inset-0 z-[200] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="$set('showResetPwModal', false)"></div>
        <div class="relative bg-white w-full max-w-sm rounded-3xl shadow-2xl border-t-4 border-amber-500 p-8">
            <div class="w-12 h-12 bg-amber-50 rounded-2xl flex items-center justify-center mb-5">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-amber-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </div>
            <h4 class="text-lg font-black text-slate-800 uppercase italic mb-1">Reset Password</h4>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-6">Password baru untuk user ini</p>
            <form wire:submit="saveResetPw" class="space-y-4">
                <div>
                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Password Baru</label>
                    <input wire:model="resetPwNew" type="password"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-amber-100 focus:border-amber-400 transition-all"
                        placeholder="Min. 6 karakter">
                    @error('resetPwNew') <p class="text-[9px] font-black text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" wire:click="$set('showResetPwModal', false)"
                        class="flex-1 py-3 bg-slate-100 text-slate-500 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-200 transition-all">Batal</button>
                    <button type="submit" wire:loading.attr="disabled"
                        class="flex-[2] py-3 bg-amber-500 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-amber-600 transition-all active:scale-95">
                        <span wire:loading.remove>Reset Password</span>
                        <span wire:loading>Mereset...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════════
         MODAL: Konfirmasi Hapus User
    ══════════════════════════════════════════════════════════════════ --}}
    @if($showDeleteUserModal)
    <div class="fixed inset-0 z-[200] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="$set('showDeleteUserModal', false)"></div>
        <div class="relative bg-white w-full max-w-sm rounded-3xl shadow-2xl border-t-4 border-red-500 p-8">
            <div class="w-12 h-12 bg-red-50 rounded-2xl flex items-center justify-center mb-5">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
            </div>
            <h4 class="text-lg font-black text-slate-800 uppercase italic mb-2">Hapus Pegawai?</h4>
            <p class="text-sm font-bold text-slate-600 mb-1">{{ $deleteUserName }}</p>
            <p class="text-xs text-slate-400 mb-8">Data ini tidak bisa dikembalikan. Semua rating terkait juga akan terhapus.</p>
            <div class="flex gap-3">
                <button wire:click="$set('showDeleteUserModal', false)"
                    class="flex-1 py-3 bg-slate-100 text-slate-500 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-200 transition-all">Batal</button>
                <button wire:click="executeDeleteUser" wire:loading.attr="disabled"
                    class="flex-[2] py-3 bg-red-500 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-red-600 transition-all active:scale-95">
                    <span wire:loading.remove>Ya, Hapus</span>
                    <span wire:loading>Menghapus...</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════════
         MODAL: Tambah/Edit Satker
    ══════════════════════════════════════════════════════════════════ --}}
    @if($showSatkerModal)
    <div class="fixed inset-0 z-[200] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="$set('showSatkerModal', false)"></div>
        <div class="relative bg-white w-full max-w-md rounded-3xl shadow-2xl border-t-4 border-slate-900 p-8">
            <h4 class="text-lg font-black text-slate-800 uppercase italic mb-6">
                {{ $editingSatkerId ? 'Edit Satker' : 'Tambah Satker Kabkot' }}
            </h4>
            <form wire:submit="saveSatker" class="space-y-4">
                <div>
                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Nama Satker</label>
                    <input wire:model="satkerName" type="text"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-slate-200 focus:border-slate-400 transition-all"
                        placeholder="Contoh: BPS Kabupaten Donggala">
                    @error('satkerName') <p class="text-[9px] font-black text-red-500 mt-1 uppercase">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Kode <span class="text-slate-300">(opsional)</span></label>
                    <input wire:model="satkerKode" type="text"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-slate-200 focus:border-slate-400 transition-all"
                        placeholder="Contoh: 7201">
                    @error('satkerKode') <p class="text-[9px] font-black text-red-500 mt-1 uppercase">{{ $message }}</p> @enderror
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" wire:click="$set('showSatkerModal', false)"
                        class="flex-1 py-3 bg-slate-100 text-slate-500 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-200 transition-all">
                        Batal
                    </button>
                    <button type="submit" wire:loading.attr="disabled"
                        class="flex-[2] py-3 bg-slate-900 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-700 transition-all active:scale-95">
                        <span wire:loading.remove>Simpan</span>
                        <span wire:loading>Menyimpan...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════════
         MODAL: Assign Admin Kabkot
    ══════════════════════════════════════════════════════════════════ --}}
    @if($showAssignAdminModal)
    <div class="fixed inset-0 z-[200] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="$set('showAssignAdminModal', false)"></div>
        <div class="relative bg-white w-full max-w-lg rounded-3xl shadow-2xl border-t-4 border-minimal-indigo p-8">
            <h4 class="text-lg font-black text-slate-800 uppercase italic mb-1">Assign Admin Kabkot</h4>
            <p class="text-[10px] font-bold text-minimal-indigo uppercase tracking-widest mb-6">{{ $assignSatkerName }}</p>
            <div class="mb-4">
                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Cari User (Nama / NIP)</label>
                <input wire:model.live.debounce.300ms="adminSearch" type="text"
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-100 focus:border-minimal-indigo transition-all"
                    placeholder="Ketik minimal 2 karakter...">
            </div>
            @if($searchableUsers->isNotEmpty())
            <div class="space-y-2 max-h-52 overflow-y-auto mb-5 pr-1">
                @foreach($searchableUsers as $u)
                <button wire:click="selectUser('{{ $u->id }}')"
                    class="w-full flex items-center gap-3 px-4 py-3 rounded-xl border transition-all text-left
                    {{ $selectedUserId === $u->id ? 'border-minimal-indigo bg-indigo-50' : 'border-slate-100 hover:border-minimal-indigo/30 hover:bg-slate-50' }}">
                    <div class="w-8 h-8 rounded-xl bg-slate-100 flex items-center justify-center text-[11px] font-black text-slate-500 italic shrink-0">{{ substr($u->name,0,1) }}</div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[11px] font-black text-slate-800 uppercase truncate">{{ $u->name }}</p>
                        <p class="text-[9px] font-mono font-bold text-slate-400">{{ $u->nip }}</p>
                    </div>
                    <div class="flex flex-wrap gap-1 justify-end shrink-0">
                        @foreach($u->getRoleNames() as $role)
                        <span class="px-1.5 py-0.5 bg-slate-100 text-slate-500 text-[7px] font-black rounded-full uppercase">{{ $role }}</span>
                        @endforeach
                    </div>
                    @if($selectedUserId === $u->id)
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-minimal-indigo shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    @endif
                </button>
                @endforeach
            </div>
            @elseif(strlen($adminSearch) >= 2)
            <div class="text-center py-5 text-slate-400 text-xs font-bold mb-5">Tidak ada user ditemukan.</div>
            @else
            <div class="text-center py-5 text-slate-300 text-xs font-bold mb-5">Ketik nama atau NIP untuk mencari.</div>
            @endif
            <div class="bg-amber-50 border border-amber-100 rounded-xl px-4 py-3 mb-4">
                <p class="text-[9px] font-black text-amber-700 uppercase">⚠ User dipindahkan ke satker ini + diberikan role Admin.</p>
            </div>
            <div class="flex gap-3">
                <button type="button" wire:click="$set('showAssignAdminModal', false)"
                    class="flex-1 py-3 bg-slate-100 text-slate-500 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-200 transition-all">Batal</button>
                <button wire:click="assignAdmin" wire:loading.attr="disabled"
                    @if(!$selectedUserId) disabled @endif
                    class="flex-[2] py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all active:scale-95 disabled:opacity-40
                    {{ $selectedUserId ? 'bg-minimal-indigo text-white hover:bg-indigo-700' : 'bg-slate-200 text-slate-400' }}">
                    <span wire:loading.remove>Assign Admin</span>
                    <span wire:loading>Memproses...</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════════
         MODAL: Tambah User ke Satker
    ══════════════════════════════════════════════════════════════════ --}}
    @if($showAddUserModal)
    <div class="fixed inset-0 z-[200] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="$set('showAddUserModal', false)"></div>
        <div class="relative bg-white w-full max-w-lg rounded-3xl shadow-2xl border-t-4 border-emerald-500 p-8">
            <h4 class="text-lg font-black text-slate-800 uppercase italic mb-1">Tambah User</h4>
            <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest mb-6">{{ $addUserSatkerName }}</p>
            <form wire:submit="addUser" class="space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div class="col-span-2">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Nama Lengkap</label>
                        <input wire:model="addUserName" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-slate-100 transition-all" placeholder="Nama lengkap">
                        @error('addUserName') <p class="text-[9px] font-black text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 block">NIP</label>
                        <input wire:model="addUserNip" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-slate-100 transition-all" placeholder="18 digit NIP">
                        @error('addUserNip') <p class="text-[9px] font-black text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Role</label>
                        <select wire:model="addUserRole" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-slate-100 transition-all">
                            @foreach(['Pegawai','Ketua Tim','Pimpinan','Admin','Kepala Kabkot'] as $r)
                            <option value="{{ $r }}">{{ $r }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Email</label>
                        <input wire:model="addUserEmail" type="email" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-slate-100 transition-all" placeholder="email@bps.go.id">
                        @error('addUserEmail') <p class="text-[9px] font-black text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Password</label>
                        <input wire:model="addUserPassword" type="password" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-slate-100 transition-all" placeholder="Min 6 karakter">
                        @error('addUserPassword') <p class="text-[9px] font-black text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" wire:click="$set('showAddUserModal', false)"
                        class="flex-1 py-3 bg-slate-100 text-slate-500 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-200 transition-all">Batal</button>
                    <button type="submit" wire:loading.attr="disabled"
                        class="flex-[2] py-3 bg-emerald-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-emerald-700 transition-all active:scale-95">
                        <span wire:loading.remove>Tambah User</span>
                        <span wire:loading>Menyimpan...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════════
         MODAL: Pindah User Antar Satker
    ══════════════════════════════════════════════════════════════════ --}}
    @if($showMoveUserModal)
    <div class="fixed inset-0 z-[200] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="$set('showMoveUserModal', false)"></div>
        <div class="relative bg-white w-full max-w-lg rounded-3xl shadow-2xl border-t-4 border-amber-500 p-8">
            <h4 class="text-lg font-black text-slate-800 uppercase italic mb-1">Pindah User Antar Satker</h4>
            <p class="text-[10px] font-bold text-amber-600 uppercase tracking-widest mb-6">Mutasi / Pindah Tugas</p>

            <div class="mb-4">
                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Cari User (Nama / NIP)</label>
                <input wire:model.live.debounce.300ms="moveUserSearch" type="text"
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-amber-100 focus:border-amber-400 transition-all"
                    placeholder="Ketik minimal 2 karakter...">
            </div>

            @if($moveUserResults->isNotEmpty())
            <div class="space-y-2 max-h-44 overflow-y-auto mb-4 pr-1">
                @foreach($moveUserResults as $u)
                <button wire:click="selectMoveUser('{{ $u->id }}')"
                    class="w-full flex items-center gap-3 px-4 py-3 rounded-xl border transition-all text-left
                    {{ $moveUserId === $u->id ? 'border-amber-400 bg-amber-50' : 'border-slate-100 hover:border-amber-200 hover:bg-slate-50' }}">
                    <div class="w-8 h-8 rounded-xl bg-slate-100 flex items-center justify-center text-[11px] font-black text-slate-500 italic shrink-0">{{ substr($u->name,0,1) }}</div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[11px] font-black text-slate-800 uppercase truncate">{{ $u->name }}</p>
                        <p class="text-[8px] font-bold text-slate-400">{{ $u->nip }} · {{ $u->satker?->name ?? '—' }}</p>
                    </div>
                    @if($moveUserId === $u->id)
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-amber-500 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    @endif
                </button>
                @endforeach
            </div>
            @elseif(strlen($moveUserSearch) >= 2)
            <div class="text-center py-4 text-slate-400 text-xs font-bold mb-4">Tidak ada user ditemukan.</div>
            @else
            <div class="text-center py-4 text-slate-300 text-xs font-bold mb-4">Ketik nama atau NIP untuk mencari.</div>
            @endif

            @if($moveUserId)
            <div class="mb-4">
                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Pindahkan ke Satker</label>
                <select wire:model="moveTargetSatkerId"
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-slate-100 transition-all">
                    <option value="">— Pilih satker tujuan —</option>
                    @foreach($satkers as $s)
                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="flex gap-3">
                <button type="button" wire:click="$set('showMoveUserModal', false)"
                    class="flex-1 py-3 bg-slate-100 text-slate-500 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-200 transition-all">Batal</button>
                <button wire:click="moveUser" wire:loading.attr="disabled"
                    @if(!$moveUserId || !$moveTargetSatkerId) disabled @endif
                    class="flex-[2] py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all active:scale-95 disabled:opacity-40
                    {{ ($moveUserId && $moveTargetSatkerId) ? 'bg-amber-500 text-white hover:bg-amber-600' : 'bg-slate-200 text-slate-400' }}">
                    <span wire:loading.remove>Pindahkan User</span>
                    <span wire:loading>Memproses...</span>
                </button>
            </div>
        </div>
    </div>
    @endif

</div>
