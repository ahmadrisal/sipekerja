<div class="font-outfit space-y-6 pb-12">
    <!-- Header Area -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-800 tracking-tight italic">Administrator Console</h2>
            <p class="text-slate-400 text-[11px] font-medium">Pengelolaan sumber daya, tim kerja, dan pemetaan SDM.</p>
        </div>
        <div class="flex gap-2 p-1 bg-white rounded-xl shadow-sm border border-slate-100">
            <div class="px-4 py-2 text-[10px] font-black uppercase tracking-widest text-minimal-indigo bg-minimal-indigo/5 rounded-lg border border-minimal-indigo/10 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-minimal-indigo animate-pulse"></span>
                System Administrator
            </div>
        </div>
    </div>

    <!-- Top Horizontal Row: Filters & Stats Summary -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        <!-- Filter Card -->
        <div class="bg-white rounded-[1.5rem] p-5 shadow-sm border border-slate-100 flex flex-col justify-center gap-2 h-full">
            <div class="flex items-center gap-2 mb-1 px-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-minimal-indigo" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                <span class="text-[9px] font-black uppercase tracking-widest text-slate-400">Periode Acuan</span>
            </div>
            
            <div class="grid grid-cols-2 gap-2">
                <div class="relative group">
                    <select wire:model.live="month" class="w-full h-9 pl-3 pr-8 rounded-lg border border-slate-100 bg-slate-50/50 text-[10px] font-black uppercase tracking-wider text-slate-700 appearance-none focus:ring-4 focus:ring-minimal-indigo/10 transition-all cursor-pointer">
                        @foreach($monthNames as $num => $name)
                            <option value="{{ $num }}">{{ substr($name, 0, 3) }}</option>
                        @endforeach
                    </select>
                    <div class="absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none text-slate-300 group-hover:text-minimal-indigo transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                    </div>
                </div>

                <div class="relative group">
                    <select wire:model.live="year" class="w-full h-9 pl-3 pr-8 rounded-lg border border-slate-100 bg-slate-50/50 text-[10px] font-black uppercase tracking-wider text-slate-700 appearance-none focus:ring-4 focus:ring-minimal-indigo/10 transition-all cursor-pointer">
                        @foreach(range(date('Y')-2, date('Y')) as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                    <div class="absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none text-slate-300 group-hover:text-minimal-indigo transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Metric Cards -->
        @php
            $adminCards = [
                ['label' => 'Total Pegawai', 'sub' => 'Registry SDM', 'val' => $stats['stats']['totalUsers'], 'icon' => 'user', 'color' => 'blue', 'dialog' => 'users'],
                ['label' => 'Total Tim', 'sub' => 'Aktif/Terdaftar', 'val' => $stats['stats']['totalTeams'], 'icon' => 'layers', 'color' => 'emerald', 'dialog' => 'teams'],
                ['label' => 'Belum Diplot', 'sub' => 'Need Assignment', 'val' => $stats['stats']['unassignedUsersCount'], 'icon' => 'alert', 'color' => 'rose', 'dialog' => 'unassigned'],
            ];
        @endphp

        @foreach($adminCards as $c)
            <div 
                wire:click="setAdminDialog('{{ $c['dialog'] }}')"
                class="bg-white p-5 rounded-[1.5rem] shadow-sm border border-slate-100 flex items-center justify-between group hover:border-minimal-indigo/30 hover:shadow-md transition-all cursor-pointer relative overflow-hidden h-full"
            >
                <div class="absolute top-0 right-0 w-12 h-12 bg-{{ $c['color'] }}-500/5 rounded-bl-[1.5rem]"></div>
                <div class="flex items-center gap-3 relative z-10">
                    <div class="w-11 h-11 rounded-xl bg-{{ $c['color'] }}-50 text-{{ $c['color'] === 'blue' ? 'minimal-indigo' : ($c['color'] === 'emerald' ? 'emerald-600' : 'rose-600') }} flex items-center justify-center shadow-inner">
                        @if($c['icon'] === 'user') <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                        @elseif($c['icon'] === 'layers') <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>
                        @else <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg> @endif
                    </div>
                    <div>
                        <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 group-hover:text-minimal-indigo transition-colors">{{ $c['label'] }}</p>
                        <p class="text-[10px] font-bold text-slate-500 leading-none mt-0.5">{{ $c['sub'] }}</p>
                    </div>
                </div>
                <p class="text-3xl font-black text-slate-800 relative z-10 tracking-tighter">{{ $c['val'] }}</p>
            </div>
        @endforeach
    </div>

    <!-- Secondary Management & Stats Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Structural Breakdown -->
        <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm relative overflow-hidden group">
            <div class="absolute -right-12 -top-12 w-48 h-48 bg-minimal-indigo/5 rounded-full blur-3xl group-hover:bg-minimal-indigo/10 transition-all"></div>
            
            <div class="flex items-center gap-3 mb-8 relative z-10">
                <div class="w-10 h-10 rounded-2xl bg-slate-900 shadow-xl flex items-center justify-center text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="m11 13-4-4-4 4"/><path d="m15 5 4 4-4 4"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-black text-slate-800 tracking-tight">Statistik Struktur Tim</h3>
                    <p class="text-[9px] font-black uppercase tracking-widest text-minimal-indigo opacity-60">Metrik Organisasi</p>
                </div>
            </div>

            <div class="space-y-3 relative z-10">
                <div class="flex justify-between items-center p-5 rounded-2xl bg-slate-50/50 border border-slate-50 group/item hover:bg-white hover:shadow-sm transition-all border-l-4 border-l-slate-200 hover:border-l-minimal-indigo">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none">Tim Terbesar</span>
                    <div class="text-right">
                        <p class="text-xs font-black text-slate-700 leading-none mb-1">{{ $stats['stats']['largestTeam']['teamName'] }}</p>
                        <p class="text-[8px] font-black text-minimal-indigo uppercase tracking-widest">{{ $stats['stats']['largestTeam']['count'] }} Anggota Tetap</p>
                    </div>
                </div>
                <div class="flex justify-between items-center p-5 rounded-2xl border border-slate-50 border-l-4 border-l-slate-200">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none">Rerata Anggota / Tim</span>
                    <span class="text-xl font-black text-slate-800 italic">{{ $stats['stats']['avgMembersPerTeam'] }}</span>
                </div>
            </div>
        </div>

        <!-- Human Asset Insight -->
        <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm relative overflow-hidden group">
            <div class="absolute -right-12 -top-12 w-48 h-48 bg-emerald-500/5 rounded-full blur-3xl group-hover:bg-emerald-500/10 transition-all"></div>
            
            <div class="flex items-center gap-3 mb-8 relative z-10">
                <div class="w-10 h-10 rounded-2xl bg-slate-900 shadow-xl flex items-center justify-center text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-black text-slate-800 tracking-tight">SDM Insight</h3>
                    <p class="text-[9px] font-black uppercase tracking-widest text-emerald-600 opacity-60">Penyebaran Beban Kerja</p>
                </div>
            </div>

            <div class="space-y-3 relative z-10">
                <div class="flex justify-between items-center p-5 rounded-2xl bg-slate-50/50 border border-slate-50 group/item hover:bg-white hover:shadow-sm transition-all border-l-4 border-l-slate-200 hover:border-l-emerald-500">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none">Tim Terbanyak / Pegawai</span>
                    <div class="text-right">
                        <p class="text-xs font-black text-slate-700 leading-none mb-1">{{ $stats['stats']['mostTeamsEmployee']['name'] }}</p>
                        <p class="text-[8px] font-black text-emerald-600 uppercase tracking-widest">{{ $stats['stats']['mostTeamsEmployee']['count'] }} Penempatan Tim</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="p-5 rounded-2xl border border-slate-50 border-l-4 border-l-slate-200">
                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Avg Tim/Sdm</span>
                        <span class="text-xl font-black text-slate-800 italic">{{ $stats['stats']['avgTeamsPerEmployee'] }}</span>
                    </div>
                    <div class="p-5 rounded-2xl border border-slate-50 border-l-4 border-l-slate-200">
                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Min Tim/Sdm</span>
                        <span class="text-xl font-black text-slate-800 italic">{{ $stats['stats']['minTeamsPerEmployee'] }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Management Dialogs -->
    @if($adminDialogType)
    <div class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-slate-900/60 backdrop-blur-sm animate-in fade-in duration-300 pointer-events-auto">
        <div class="bg-white w-full max-w-2xl rounded-[3rem] shadow-2xl overflow-hidden border border-white/20 animate-in zoom-in-95 duration-300 relative flex flex-col max-h-[90vh]">
            <div class="p-10 shrink-0">
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center gap-5">
                        <div class="w-16 h-16 rounded-2xl bg-minimal-indigo text-white flex items-center justify-center shadow-xl">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="m11 13-4-4-4 4"/></svg>
                        </div>
                        <div>
                            <h4 class="text-xl font-black text-slate-800 tracking-tight">
                                @if($adminDialogType === 'users') Management Pegawai
                                @elseif($adminDialogType === 'teams') Management Tim Kerja
                                @else Pending Assignment @endif
                            </h4>
                            <p class="text-[9px] font-black uppercase tracking-[0.2em] text-minimal-indigo/60">Administrator Management Console</p>
                        </div>
                    </div>
                    <button wire:click="setAdminDialog(null)" class="w-11 h-11 rounded-xl bg-slate-50 text-slate-300 hover:bg-red-50 hover:text-red-500 transition-all flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>

                <div class="relative group">
                    <input 
                        wire:model.live="searchQuery"
                        type="text" 
                        placeholder="Search resource..." 
                        class="w-full h-12 pl-12 pr-6 rounded-2xl bg-slate-50 border border-slate-100 text-[11px] font-bold focus:ring-8 focus:ring-minimal-indigo/5 transition-all outline-none"
                    >
                    <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-minimal-indigo transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    </div>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto px-10 pb-10 custom-scrollbar">
                @if($adminDialogType === 'users')
                    <table class="w-full text-left">
                        <thead class="sticky top-0 bg-white z-10">
                            <tr class="text-[9px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50">
                                <th class="pb-4">User Info</th>
                                <th class="pb-4 text-right">Team Deployment</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach(collect($stats['userDetails'])->filter(fn($u) => str_contains(strtolower($u['name']), strtolower($searchQuery)) || str_contains($u['nip'], $searchQuery)) as $u)
                                <tr class="group hover:bg-slate-50 transition-colors">
                                    <td class="py-4">
                                        <p class="text-[11px] font-black text-slate-700 leading-tight">{{ $u['name'] }}</p>
                                        <p class="text-[9px] font-mono font-bold text-slate-400 tracking-tighter">{{ $u['nip'] }}</p>
                                    </td>
                                    <td class="py-4 text-right">
                                        @if(count($u['teamNames']) > 0)
                                            <div class="flex flex-wrap gap-1 justify-end">
                                                @foreach($u['teamNames'] as $t)
                                                    <span class="px-2 py-0.5 bg-minimal-indigo/5 text-minimal-indigo rounded-md text-[8px] font-black uppercase border border-minimal-indigo/10">{{ $t }}</span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="px-2 py-0.5 bg-rose-50 text-rose-500 rounded-md text-[8px] font-black uppercase border border-rose-100 italic">No Assignment</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @elseif($adminDialogType === 'teams')
                    <table class="w-full text-left">
                        <thead class="sticky top-0 bg-white z-10">
                            <tr class="text-[9px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50">
                                <th class="pb-4">Team Designation</th>
                                <th class="pb-4 text-right">Capacity</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach(collect($stats['teamDetails'])->filter(fn($t) => str_contains(strtolower($t['teamName']), strtolower($searchQuery))) as $t)
                                <tr class="group hover:bg-slate-50 transition-colors">
                                    <td class="py-5">
                                        <p class="text-[12px] font-black text-minimal-indigo uppercase tracking-tight">{{ $t['teamName'] }}</p>
                                        <p class="text-[9px] text-slate-400 font-medium leading-relaxed max-w-sm truncate">{{ implode(', ', $t['members']) }}</p>
                                    </td>
                                    <td class="py-5 text-right">
                                        <span class="inline-flex px-3 py-1 bg-slate-900 text-white rounded-lg text-[10px] font-black italic">{{ $t['memberCount'] }} SDM</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="space-y-3">
                        @foreach(collect($stats['unassignedUsers'])->filter(fn($u) => str_contains(strtolower($u->name), strtolower($searchQuery)) || str_contains($u->nip, $searchQuery)) as $u)
                            <div class="flex items-center justify-between p-5 rounded-[1.5rem] bg-slate-50/50 border border-slate-100 hover:border-rose-400 hover:bg-white transition-all group">
                                <div class="flex items-center gap-4">
                                    <div class="w-1.5 h-10 rounded-full bg-rose-200 group-hover:bg-rose-500 transition-all"></div>
                                    <div>
                                        <p class="text-[12px] font-black text-slate-800 uppercase tracking-tight">{{ $u->name }}</p>
                                        <p class="text-[9px] text-slate-400 font-mono font-bold tracking-widest">NIP: {{ $u->nip }}</p>
                                    </div>
                                </div>
                                <div class="px-4 py-1.5 bg-rose-50 text-rose-600 rounded-xl text-[9px] font-black uppercase border border-rose-100 flex items-center gap-2 shadow-inner">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                                    Urgent Plot
                                </div>
                            </div>
                        @endforeach
                        @if(collect($stats['unassignedUsers'])->count() === 0)
                            <div class="py-16 text-center">
                                <div class="w-20 h-20 bg-emerald-50 text-emerald-500 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-inner scale-110">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M20 6 9 17l-5-5"/></svg>
                                </div>
                                <h5 class="text-sm font-black text-slate-800 uppercase tracking-widest mb-1 italic">Optimal Capacity</h5>
                                <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-[0.2em] italic opacity-60">Seluruh Pegawai Telah Terplot Ke Dalam Tim.</p>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <div class="p-10 bg-slate-50/80 border-t border-slate-100 flex justify-end gap-3 shrink-0">
                <button wire:click="setAdminDialog(null)" class="px-8 py-3.5 bg-white border border-slate-200 rounded-2xl text-[10px] font-black uppercase tracking-widest text-slate-400 shadow-sm active:scale-95 transition-all">Dismiss</button>
                <button class="px-8 py-3.5 bg-slate-900 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-xl shadow-slate-900/10 active:scale-95 transition-all flex items-center gap-3">
                    Resource Management Console
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12h14l-4-4m0 8l4-4"/></svg>
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
