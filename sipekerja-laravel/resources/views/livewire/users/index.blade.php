<div class="font-outfit space-y-6 pb-12 animate-in fade-in zoom-in-95 duration-500">
    <!-- Header Area -->
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-800 tracking-tight italic">Manajemen Pegawai</h2>
            <p class="text-slate-400 text-[11px] font-medium">Daftar seluruh pegawai, pengaturan hak akses, dan manajemen akun sistem.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <button wire:click="openImportModal" class="bg-white text-slate-600 border border-slate-200 px-5 py-2.5 rounded-xl font-black uppercase tracking-widest text-[10px] flex items-center gap-2 hover:bg-slate-50 hover:border-minimal-indigo/30 transition-all shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                Import Excel
            </button>
            <button 
                wire:click="openCreateModal"
                class="bg-minimal-indigo text-white px-6 py-2.5 rounded-xl font-black uppercase tracking-widest text-[10px] flex items-center gap-2 shadow-xl shadow-indigo-500/20 hover:scale-105 transition-all active:scale-95 group"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 group-hover:rotate-90 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Tambah Pengguna
            </button>
        </div>
    </div>

    <!-- Stats & Filters -->
    <div class="bg-white p-3 rounded-[1.5rem] border border-slate-100 shadow-sm flex flex-col md:flex-row gap-3 items-center">
        <div class="relative flex-1 group">
            <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 group-focus-within:text-bps-blue transition-colors" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input 
                wire:model.live.debounce.300ms="search"
                type="text" 
                placeholder="Cari NIP, Nama, Username, atau Email..."
                class="w-full bg-slate-50 border-none rounded-xl pl-12 pr-6 py-2.5 text-[11px] font-bold text-slate-700 placeholder:text-slate-300 focus:ring-4 focus:ring-minimal-indigo/5 transition-all"
            >
        </div>
        <div class="flex gap-2 w-full md:w-auto">
            <select wire:model.live="filterRole" class="bg-slate-50 border-none rounded-xl px-3 py-2.5 text-[10px] font-black text-slate-700 uppercase tracking-tighter focus:ring-0 cursor-pointer">
                <option value="ALL">Semua Peran</option>
                @foreach($allRoles as $role)
                    <option value="{{ $role->name }}">{{ $role->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="filterTeam" class="bg-slate-50 border-none rounded-xl px-3 py-2.5 text-[10px] font-black text-slate-700 uppercase tracking-tighter focus:ring-0 cursor-pointer">
                <option value="ALL">Semua Tim</option>
                @foreach($uniqueTeams as $team)
                    <option value="{{ $team }}">{{ $team }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="bg-white rounded-[2rem] border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50/30">
                        <th class="px-5 py-3 w-10">
                            <div class="flex items-center justify-center">
                                <input type="checkbox" wire:model.live="selectAll" class="w-4 h-4 rounded border-slate-300 text-minimal-indigo focus:ring-minimal-indigo transition-all cursor-pointer">
                            </div>
                        </th>
                        <th class="px-5 py-3 text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 border-b border-slate-50">Info Pegawai</th>
                        <th class="px-4 py-3 text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 border-b border-slate-50">Kontak</th>
                        <th class="px-4 py-3 text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 border-b border-slate-50">Peran & Penugasan</th>
                        <th class="px-5 py-3 text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 border-b border-slate-50 text-right">Manajemen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($users as $u)
                        <tr class="group hover:bg-slate-50/50 transition-colors {{ in_array($u->id, $selectedIds) ? 'bg-minimal-indigo/[0.02]' : '' }}">
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-center">
                                    <input type="checkbox" value="{{ $u->id }}" wire:model.live="selectedIds" class="w-4 h-4 rounded border-slate-300 text-minimal-indigo focus:ring-minimal-indigo transition-all cursor-pointer">
                                </div>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-slate-100 to-slate-200 border border-white flex items-center justify-center text-slate-400 font-black italic shadow-inner group-hover:bg-minimal-indigo group-hover:text-white transition-all text-[11px]">
                                        {{ substr($u->name, 0, 1) }}
                                    </div>
                                    <div class="overflow-hidden">
                                        <p class="text-[12px] font-black text-slate-700 leading-tight truncate group-hover:text-minimal-indigo transition-colors">{{ $u->name }}</p>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span class="text-[9px] font-mono font-bold text-slate-400 uppercase tracking-tighter">{{ $u->nip }}</span>
                                            <span class="text-[9px] font-bold text-slate-300 italic">{{ $u->username }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-[11px] font-bold text-slate-600 truncate">{{ $u->email }}</p>
                                <p class="text-[8px] font-black text-slate-300 uppercase tracking-widest mt-0.5 italic">Verified</p>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1">
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
                            <td class="px-5 py-3 text-right">
                                <div class="flex justify-end gap-1">
                                    <button wire:click="openEditModal('{{ $u->id }}')" class="w-8 h-8 bg-slate-50 text-slate-400 rounded-lg flex items-center justify-center hover:bg-minimal-indigo hover:text-white transition-all" title="Edit Data">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                                    </button>
                                    <button wire:click="openResetModal('{{ $u->id }}')" class="w-8 h-8 bg-slate-50 text-slate-400 rounded-lg flex items-center justify-center hover:bg-amber-100 hover:text-amber-600 transition-all" title="Reset Password">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M21.5 2v6h-6"/><path d="M2.5 22v-6h6"/><path d="M21.34 15.57a10 10 0 0 1-17.17 2.35"/><path d="M2.66 8.43a10 10 0 0 1 17.17-2.35"/></svg>
                                    </button>
                                    <button 
                                        wire:click="confirmDelete('{{ $u->id }}')" 
                                        class="w-8 h-8 bg-slate-50 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-xl flex items-center justify-center transition-all bg-white shadow-sm border border-slate-100"
                                        title="Hapus"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if($users->hasPages())
            <div class="px-5 py-3 bg-slate-50/50 border-t border-slate-50">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <!-- Modals (Create/Edit & Reset) -->
    
    <!-- User Form Modal -->
    @if($isModalOpen)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-6">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="closeModal"></div>
            <div class="bg-white w-full max-w-lg rounded-[2rem] shadow-2xl relative overflow-hidden flex flex-col animate-in zoom-in-95 duration-300">
                <!-- Header -->
                <div class="p-6 pb-4 border-b border-slate-50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-minimal-indigo text-white flex items-center justify-center shadow-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="16" y1="11" x2="22" y2="11"/></svg>
                            </div>
                            <div>
                                <h4 class="text-lg font-black text-slate-800 tracking-tight">{{ $userId ? 'Edit Pengguna' : 'Tambah Pegawai' }}</h4>
                                <p class="text-[9px] font-black uppercase tracking-[0.2em] text-minimal-indigo/60">Form Data Pegawai</p>
                            </div>
                        </div>
                        <button wire:click="closeModal" class="w-9 h-9 rounded-xl bg-slate-50 text-slate-300 hover:bg-red-50 hover:text-red-500 transition-all flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                        </button>
                    </div>
                </div>

                <!-- Form Body -->
                <form wire:submit="saveUser" class="p-6 space-y-4 max-h-[70vh] overflow-y-auto custom-scrollbar">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-3">NIP Pegawai</label>
                            <input wire:model="nip" type="text" class="w-full bg-slate-50 border border-slate-100 rounded-xl px-4 py-2.5 text-[12px] font-bold text-slate-700 focus:ring-4 focus:ring-minimal-indigo/10 focus:border-minimal-indigo/30 transition-all">
                            @error('nip') <span class="text-[8px] text-red-500 font-black uppercase ml-3">{{ $message }}</span> @enderror
                        </div>
                        <div class="space-y-1">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-3">Username</label>
                            <input wire:model="username" type="text" class="w-full bg-slate-50 border border-slate-100 rounded-xl px-4 py-2.5 text-[12px] font-bold text-slate-700 focus:ring-4 focus:ring-minimal-indigo/10 focus:border-minimal-indigo/30 transition-all">
                            @error('username') <span class="text-[8px] text-red-500 font-black uppercase ml-3">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-3">Nama Lengkap</label>
                        <input wire:model="name" type="text" class="w-full bg-slate-50 border border-slate-100 rounded-xl px-4 py-2.5 text-[12px] font-bold text-slate-700 focus:ring-4 focus:ring-minimal-indigo/10 focus:border-minimal-indigo/30 transition-all">
                        @error('name') <span class="text-[8px] text-red-500 font-black uppercase ml-3">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-3">Email Instansi</label>
                        <input wire:model="email" type="email" class="w-full bg-slate-50 border border-slate-100 rounded-xl px-4 py-2.5 text-[12px] font-bold text-slate-700 focus:ring-4 focus:ring-minimal-indigo/10 focus:border-minimal-indigo/30 transition-all">
                        @error('email') <span class="text-[8px] text-red-500 font-black uppercase ml-3">{{ $message }}</span> @enderror
                    </div>

                    @if(!$userId)
                        <div class="space-y-1">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-3">Password Sementara</label>
                            <input wire:model="password" type="password" class="w-full bg-slate-50 border border-slate-100 rounded-xl px-4 py-2.5 text-[12px] font-bold text-slate-700 focus:ring-4 focus:ring-minimal-indigo/10 focus:border-minimal-indigo/30 transition-all" placeholder="Paling sedikit 6 karakter">
                            @error('password') <span class="text-[8px] text-red-500 font-black uppercase ml-3">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    <div class="space-y-2 border-t border-slate-50 pt-4">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-3">Tugaskan Peran (Roles)</label>
                        <div class="flex flex-wrap gap-2 mt-1">
                            @foreach($allRoles as $role)
                                <label class="cursor-pointer">
                                    <input type="checkbox" value="{{ $role->name }}" wire:model.live="selectedRoles" class="hidden peer">
                                    <div class="px-4 py-2 rounded-xl border-2 font-black text-[9px] uppercase tracking-widest transition-all
                                        {{ in_array($role->name, $selectedRoles) ? 'bg-minimal-indigo border-minimal-indigo text-white shadow-lg shadow-indigo-500/20' : 'bg-white border-slate-100 text-slate-300 hover:border-slate-300' }}">
                                        {{ $role->name }}
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex gap-3 pt-4 border-t border-slate-50">
                        <button type="button" wire:click="closeModal" class="flex-1 py-3 bg-slate-50 text-slate-400 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-100 transition-all active:scale-95">Batal</button>
                        <button type="submit" class="flex-[2] py-3 bg-minimal-indigo text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-xl shadow-indigo-500/20 hover:scale-[1.02] transition-all active:scale-95">Simpan Data Pegawai</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Reset Password Modal -->
    @if($isResetModalOpen)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-6">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="closeResetModal"></div>
            <div class="bg-white w-full max-w-sm rounded-[2rem] shadow-2xl relative overflow-hidden flex flex-col animate-in zoom-in-95 duration-300">
                <div class="p-6 space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-amber-50 rounded-xl flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-amber-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21.5 2v6h-6"/><path d="M2.5 22v-6h6"/><path d="M21.34 15.57a10 10 0 0 1-17.17 2.35"/><path d="M2.66 8.43a10 10 0 0 1 17.17-2.35"/></svg>
                        </div>
                        <div>
                            <h4 class="text-lg font-black text-slate-800 tracking-tight">Reset Password</h4>
                            <p class="text-[9px] font-black uppercase tracking-[0.2em] text-amber-500/60">Tentukan password baru</p>
                        </div>
                    </div>

                    <form wire:submit="resetPassword" class="space-y-4">
                        <div class="space-y-1">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-3">Password Baru</label>
                            <input wire:model="newPassword" type="password" class="w-full bg-slate-50 border border-slate-100 rounded-xl px-4 py-2.5 text-[12px] font-bold text-slate-700 focus:ring-4 focus:ring-amber-400/10 focus:border-amber-300 transition-all" placeholder="Paling sedikit 6 karakter">
                            @error('newPassword') <span class="text-[8px] text-red-500 font-black uppercase ml-3">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button type="button" wire:click="closeResetModal" class="flex-1 py-3 bg-slate-50 text-slate-400 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-100 transition-all active:scale-95">Batal</button>
                            <button type="submit" class="flex-[2] py-3 bg-amber-400 text-slate-800 rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-amber-400/20 hover:scale-[1.02] transition-all active:scale-95">Setel Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Import Excel Modal -->
    @if($isImportModalOpen)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-6">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="closeImportModal"></div>
            <div class="bg-white w-full max-w-lg rounded-[2rem] shadow-2xl relative overflow-hidden flex flex-col animate-in zoom-in-95 duration-300">
                <!-- Header -->
                <div class="p-6 pb-4 border-b border-slate-50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center shadow-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                            </div>
                            <div>
                                <h4 class="text-lg font-black text-slate-800 tracking-tight">Import Pegawai</h4>
                                <p class="text-[9px] font-black uppercase tracking-[0.2em] text-emerald-600/60">Upload file Excel (.xlsx)</p>
                            </div>
                        </div>
                        <button wire:click="closeImportModal" class="w-9 h-9 rounded-xl bg-slate-50 text-slate-300 hover:bg-red-50 hover:text-red-500 transition-all flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                        </button>
                    </div>
                </div>

                <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto custom-scrollbar">
                    <!-- Download Template Link (Google Drive) -->
                    <button type="button" onclick="window.open('https://docs.google.com/spreadsheets/d/1PrLlcSnU2OHnQf6V-On2RtVsFTxNX3bp/edit?usp=sharing&ouid=103539980233346494499&rtpof=true&sd=true', '_blank')" class="w-full flex items-center justify-start text-left gap-3 px-4 py-3 bg-minimal-indigo/5 border border-minimal-indigo/10 rounded-xl hover:bg-minimal-indigo/10 transition-all group block">
                        <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center shadow-sm pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-minimal-indigo" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        </div>
                        <div class="pointer-events-none">
                            <p class="text-[11px] font-black text-minimal-indigo group-hover:underline">Download Template Excel</p>
                            <p class="text-[9px] text-slate-400 font-medium mt-0.5">Panduan xlsx (via Google Drive)</p>
                        </div>
                    </button>

                    <!-- File Upload Zone -->
                    @if(!$importParsed)
                        <div class="relative">
                            <label
                                for="importFileInput"
                                class="flex flex-col items-center justify-center gap-3 p-8 border-2 border-dashed border-slate-200 rounded-2xl bg-slate-50/50 hover:border-emerald-400 hover:bg-emerald-50/30 transition-all cursor-pointer group"
                            >
                                <div wire:loading.remove wire:target="importFile">
                                    <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center shadow-sm group-hover:shadow-md transition-all text-slate-300 group-hover:text-emerald-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                    </div>
                                    <p class="text-[11px] font-black text-slate-500 text-center mt-2">Klik untuk pilih file atau drag & drop</p>
                                    <p class="text-[9px] text-slate-300 font-bold uppercase tracking-widest text-center mt-1">Format: .xlsx • Max: 5MB</p>
                                </div>
                                <div wire:loading wire:target="importFile" class="flex flex-col items-center gap-2">
                                    <div class="w-8 h-8 border-3 border-emerald-500 border-t-transparent rounded-full animate-spin"></div>
                                    <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest">Memproses file...</p>
                                </div>
                            </label>
                            <input
                                wire:model="importFile"
                                type="file"
                                id="importFileInput"
                                accept=".xlsx,.xls"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                            >
                        </div>
                        @error('importFile')
                            <p class="text-[9px] text-red-500 font-black uppercase">{{ $message }}</p>
                        @enderror
                    @endif

                    <!-- Parse Results -->
                    @if($importParsed)
                        <div class="space-y-3">
                            <!-- Summary -->
                            <div class="grid grid-cols-2 gap-3">
                                <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-100">
                                    <p class="text-[9px] font-black uppercase tracking-widest text-emerald-600/60">Berhasil</p>
                                    <p class="text-2xl font-black text-emerald-600 italic">{{ count($importValid) }}</p>
                                    <p class="text-[9px] font-bold text-emerald-500 mt-0.5">pegawai siap ditambahkan</p>
                                </div>
                                <div class="p-4 bg-rose-50 rounded-xl border border-rose-100">
                                    <p class="text-[9px] font-black uppercase tracking-widest text-rose-500/60">Gagal</p>
                                    <p class="text-2xl font-black text-rose-500 italic">{{ count($importInvalid) }}</p>
                                    <p class="text-[9px] font-bold text-rose-400 mt-0.5">data tidak valid</p>
                                </div>
                            </div>

                            <!-- Valid Entries Preview -->
                            @if(count($importValid) > 0)
                                <div class="p-3 bg-slate-50/50 rounded-xl border border-slate-100">
                                    <p class="text-[9px] font-black uppercase tracking-widest text-emerald-600 mb-2 px-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block mr-1"></span>
                                        Data Valid ({{ count($importValid) }})
                                    </p>
                                    <div class="space-y-1 max-h-28 overflow-y-auto custom-scrollbar">
                                        @foreach($importValid as $v)
                                            <div class="flex items-center justify-between px-2 py-1.5 bg-white rounded-lg text-[10px]">
                                                <span class="font-bold text-slate-700 truncate flex-1">{{ $v['name'] }}</span>
                                                <span class="font-mono text-slate-400 text-[9px] ml-2">{{ $v['nip'] }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Invalid Entries Detail -->
                            @if(count($importInvalid) > 0)
                                <div class="p-3 bg-rose-50/50 rounded-xl border border-rose-100">
                                    <p class="text-[9px] font-black uppercase tracking-widest text-rose-500 mb-2 px-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 inline-block mr-1"></span>
                                        Data Tidak Valid ({{ count($importInvalid) }})
                                    </p>
                                    <div class="space-y-2 max-h-36 overflow-y-auto custom-scrollbar">
                                        @foreach($importInvalid as $inv)
                                            <div class="px-3 py-2 bg-white rounded-lg border border-rose-100">
                                                <div class="flex items-center justify-between">
                                                    <span class="font-black text-[10px] text-slate-700">
                                                        {{ $inv['name'] ?: '(nama kosong)' }}
                                                    </span>
                                                    <span class="text-[8px] font-mono text-slate-400">Baris {{ $inv['row'] }}</span>
                                                </div>
                                                <div class="flex flex-wrap gap-1 mt-1">
                                                    @foreach($inv['errors'] as $err)
                                                        <span class="px-1.5 py-0.5 bg-rose-50 text-rose-500 text-[8px] font-bold rounded border border-rose-100">{{ $err }}</span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="flex gap-3 pt-2 border-t border-slate-50">
                        <button type="button" wire:click="closeImportModal" class="flex-1 py-3 bg-slate-50 text-slate-400 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-100 transition-all active:scale-95">Tutup</button>
                        @if($importParsed && count($importValid) > 0)
                            <button
                                wire:click="confirmImport"
                                wire:loading.attr="disabled"
                                class="flex-[2] py-3 bg-emerald-500 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-xl shadow-emerald-500/20 hover:scale-[1.02] transition-all active:scale-95 flex items-center justify-center gap-2"
                            >
                                <span wire:loading.remove wire:target="confirmImport">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 inline -mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="16" y1="11" x2="22" y2="11"/></svg>
                                    Tambah {{ count($importValid) }} Pegawai
                                </span>
                                <span wire:loading wire:target="confirmImport" class="text-[10px]">Menambahkan...</span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($isDeleteModalOpen)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white w-full max-w-sm rounded-[2rem] shadow-2xl relative overflow-hidden flex flex-col p-6 animate-in zoom-in-95 duration-300">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-red-50 text-red-500 rounded-xl flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                    </div>
                    <div>
                        <h4 class="text-lg font-black text-slate-800 tracking-tight">Hapus Pengguna</h4>
                        <p class="text-[9px] font-black uppercase tracking-[0.2em] text-red-500/60">Tindakan ini permanen</p>
                    </div>
                </div>
                <p class="text-xs text-slate-500 font-medium mb-6">Apakah Anda yakin ingin menghapus pengguna ini? Semua data terkait yang dimilikinya mungkin akan hilang.</p>
                
                <div class="flex gap-3">
                    <button type="button" wire:click="cancelDelete" class="flex-1 py-3 bg-slate-50 text-slate-400 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-100 transition-all active:scale-95">Batal</button>
                    <button type="button" wire:click="executeDelete" class="flex-1 py-3 bg-red-500 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-red-500/20 hover:bg-red-600 transition-all active:scale-95">Ya, Hapus</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Bulk Action Bar -->
    @if(count($selectedIds) > 0)
        <div class="fixed bottom-8 left-1/2 -translate-x-1/2 z-[90] animate-in slide-in-from-bottom-10 duration-500">
            <div class="bg-slate-900/90 backdrop-blur-md text-white px-6 py-4 rounded-[2rem] shadow-2xl flex items-center gap-6 border border-white/10">
                <div class="flex items-center gap-3 pr-6 border-r border-white/10">
                    <div class="w-8 h-8 rounded-full bg-minimal-indigo flex items-center justify-center text-[11px] font-black shadow-lg shadow-indigo-500/40">
                        {{ count($selectedIds) }}
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-300">Pegawai Terpilih</span>
                </div>
                <div class="flex items-center gap-3">
                    <button 
                        wire:click="confirmBulkDelete"
                        class="px-5 py-2.5 bg-rose-500 hover:bg-rose-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition-all active:scale-95 flex items-center gap-2 shadow-lg shadow-rose-500/20"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                        Hapus Sekaligus
                    </button>
                    <button 
                        wire:click="$set('selectedIds', []); $set('selectAll', false)"
                        class="px-5 py-2.5 bg-white/5 hover:bg-white/10 text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition-all active:scale-95"
                    >
                        Batal
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Bulk Delete Confirmation Modal -->
    @if($isBulkDeleteModalOpen)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white w-full max-w-sm rounded-[2rem] shadow-2xl relative overflow-hidden flex flex-col p-6 animate-in zoom-in-95 duration-300">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-red-50 text-red-500 rounded-xl flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                    </div>
                    <div>
                        <h4 class="text-lg font-black text-slate-800 tracking-tight">Hapus Masal</h4>
                        <p class="text-[9px] font-black uppercase tracking-[0.2em] text-red-500/60">Hapus {{ count($selectedIds) }} Pegawai</p>
                    </div>
                </div>
                <p class="text-xs text-slate-500 font-medium mb-6">Apakah Anda yakin ingin menghapus <span class="font-black text-slate-800">{{ count($selectedIds) }} pegawa</span> sekaligus? Tindakan ini tidak dapat dibatalkan.</p>
                
                <div class="flex gap-3">
                    <button type="button" wire:click="cancelBulkDelete" class="flex-1 py-3 bg-slate-50 text-slate-400 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-100 transition-all active:scale-95">Batal</button>
                    <button type="button" wire:click="executeBulkDelete" class="flex-1 py-3 bg-red-500 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-red-500/20 hover:bg-red-600 transition-all active:scale-95">Ya, Hapus Semua</button>
                </div>
            </div>
        </div>
    @endif
</div>
