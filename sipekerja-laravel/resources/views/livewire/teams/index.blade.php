<div class="font-outfit space-y-6 pb-12 animate-in fade-in zoom-in-95 duration-500">
    <!-- Header Area -->
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-800 tracking-tight italic">Manajemen Tim Kerja</h2>
            <p class="text-slate-400 text-[11px] font-medium">Kelola struktur tim, tentukan ketua, dan atur penugasan anggota.</p>
        </div>
        <div class="flex items-center gap-2">
            <button
                wire:click="openImportModal"
                class="bg-white border border-slate-200 text-slate-600 px-5 py-2.5 rounded-xl font-black uppercase tracking-widest text-[10px] flex items-center gap-2 hover:border-minimal-indigo/40 hover:text-minimal-indigo hover:shadow-sm transition-all active:scale-95"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                Import Tim
            </button>
            <button
                wire:click="openCreateModal"
                class="bg-minimal-indigo text-white px-6 py-2.5 rounded-xl font-black uppercase tracking-widest text-[10px] flex items-center gap-2 shadow-xl shadow-indigo-500/20 hover:scale-105 transition-all active:scale-95 group"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 group-hover:rotate-90 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Buat Tim Baru
            </button>
        </div>
    </div>

    <!-- Stats & Filters -->
    <div class="bg-white p-3 rounded-[1.5rem] border border-slate-100 shadow-sm flex flex-col md:flex-row gap-3 items-center">
        <div class="relative flex-1 group">
            <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-300 group-focus-within:text-minimal-indigo transition-colors" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input 
                wire:model.live.debounce.300ms="search"
                type="text" 
                placeholder="Cari Tim, Ketua, atau Anggota..."
                class="w-full bg-slate-50 border-none rounded-xl pl-10 pr-4 py-2.5 text-[11px] font-bold text-slate-700 placeholder:text-slate-400 focus:ring-4 focus:ring-minimal-indigo/10 transition-all"
            >
        </div>
        <div class="flex gap-2 w-full md:w-auto">
            <select wire:model.live="filterLeader" class="bg-slate-50 border-none rounded-xl px-4 py-2.5 text-[10px] font-black text-slate-600 uppercase tracking-widest focus:ring-0 cursor-pointer">
                <option value="ALL">Semua Kepemimpinan</option>
                <option value="HAS_LEADER">Ada Ketua</option>
                <option value="NO_LEADER">Tanpa Ketua</option>
            </select>
            <select wire:model.live="filterSize" class="bg-slate-50 border-none rounded-xl px-4 py-2.5 text-[10px] font-black text-slate-600 uppercase tracking-widest focus:ring-0 cursor-pointer">
                <option value="ALL">Semua Ukuran</option>
                <option value="EMPTY">Kosong (0)</option>
                <option value="SMALL">Kecil (1-5)</option>
                <option value="LARGE">Besar (>5)</option>
            </select>
            <div class="hidden md:flex ml-2 items-center gap-3 px-3 border-l border-slate-100">
                <div class="text-right">
                    <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest leading-none">Total</p>
                    <p class="text-sm font-black text-slate-800">{{ count($teams) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Team Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($teams as $team)
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm group hover:shadow-lg hover:border-minimal-indigo/30 transition-all duration-300 flex flex-col overflow-hidden relative">
                <div class="p-5 flex-1">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-lg font-black text-slate-800 italic tracking-tight uppercase leading-tight transition-all">
                                {{ $team->team_name }}
                            </h3>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-1">{{ count($team->members) }} Anggota</p>
                        </div>
                        <div class="flex gap-1">
                            <button wire:click="openEditModal('{{ $team->id }}')" class="w-8 h-8 rounded-lg text-slate-400 hover:bg-slate-50 hover:text-minimal-indigo flex items-center justify-center transition-all bg-white border border-transparent hover:border-slate-100 shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                            </button>
                            <button wire:click="confirmDelete('{{ $team->id }}')" class="w-8 h-8 rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-500 flex items-center justify-center transition-all bg-white border border-transparent hover:border-slate-100 shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Leader Snippet -->
                    <div class="mb-5 p-3 rounded-xl {{ $team->leader ? 'bg-indigo-50 border border-indigo-100/50' : 'bg-red-50 border border-red-100/50' }} flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg {{ $team->leader ? 'bg-minimal-indigo text-white shadow-sm shadow-indigo-500/20' : 'bg-red-400 text-white' }} flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m2 4 3 12h14l3-12-6 7-4-7-4 7-6-7zm3 16h14"/></svg>
                        </div>
                        <div class="overflow-hidden">
                            <p class="text-[8px] font-black {{ $team->leader ? 'text-minimal-indigo' : 'text-red-500' }} uppercase tracking-widest leading-none mb-0.5">Ketua Tim</p>
                            <p class="text-[11px] font-bold text-slate-800 truncate">
                                {{ $team->leader->name ?? 'Belum Ditugaskan' }}
                            </p>
                        </div>
                    </div>

                    <!-- Members Snippet -->
                    <div class="space-y-1.5">
                        @foreach($team->members->take(4) as $member)
                            <div class="flex items-center justify-between p-1.5 rounded-lg hover:bg-slate-50 transition-colors">
                                <div class="flex items-center gap-2">
                                    <div class="w-1.5 h-1.5 rounded-full bg-slate-200"></div>
                                    <span class="text-[10px] font-bold text-slate-600 truncate max-w-[120px]">{{ $member->name }}</span>
                                </div>
                                <span class="text-[8px] font-black text-slate-300 uppercase tracking-widest">{{ $member->nip }}</span>
                            </div>
                        @endforeach
                        @if($team->members->count() > 4)
                            <p class="text-center text-[9px] font-black text-slate-400 uppercase tracking-widest pt-2">
                                + {{ $team->members->count() - 4 }} Lainnya
                            </p>
                        @endif
                        @if($team->members->count() === 0)
                            <div class="py-6 text-center">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Belum ada anggota</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if(count($teams) === 0)
        <div class="py-20 text-center">
            <p class="text-xl font-black text-slate-300 uppercase italic tracking-tight">Tidak Ada Tim Ditemukan</p>
        </div>
    @endif

    <!-- Team Modal (Buat / Edit) -->
    @if($isModalOpen)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-6">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="closeModal"></div>
            <div class="bg-white w-full max-w-xl rounded-[2rem] shadow-2xl relative overflow-hidden flex flex-col animate-in zoom-in-95 duration-300">
                <!-- Header -->
                <div class="p-6 pb-4 border-b border-slate-50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-minimal-indigo text-white flex items-center justify-center shadow-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            </div>
                            <div>
                                <h4 class="text-lg font-black text-slate-800 tracking-tight">{{ $teamId ? 'Edit Tim Kerja' : 'Buat Tim Baru' }}</h4>
                                <p class="text-[9px] font-black uppercase tracking-[0.2em] text-minimal-indigo/60">Pengaturan Grup & Personel</p>
                            </div>
                        </div>
                        <button wire:click="closeModal" class="w-9 h-9 rounded-xl bg-slate-50 text-slate-300 hover:bg-red-50 hover:text-red-500 transition-all flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                        </button>
                    </div>
                </div>

                <!-- Form Body -->
                <div class="p-6 space-y-5 max-h-[70vh] overflow-y-auto custom-scrollbar">
                    <!-- Nama Tim -->
                    <div class="space-y-1">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-3">Nama Tim Kerja</label>
                        <input wire:model="teamName" type="text" class="w-full bg-slate-50 border border-slate-100 rounded-xl px-4 py-2.5 text-[12px] font-bold text-slate-700 focus:ring-4 focus:ring-minimal-indigo/10 focus:border-minimal-indigo/30 transition-all uppercase italic tracking-tight">
                        @error('teamName') <span class="text-[8px] text-red-500 font-black uppercase ml-3">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <!-- Pilihan Ketua -->
                        <div class="space-y-1">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-3">Ketua Tim</label>
                            
                            @if($leaderId)
                                <!-- Terpilih -->
                                @php
                                    $theLeader = collect($allUsers)->firstWhere('id', $leaderId);
                                @endphp
                                <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-3 flex justify-between items-center group">
                                    <div class="overflow-hidden pr-2">
                                        <p class="text-[10px] font-black text-slate-800 truncate">{{ $theLeader['name'] ?? 'Unknown' }}</p>
                                        <p class="text-[8px] font-black text-indigo-500 uppercase tracking-widest">{{ $theLeader['nip'] ?? '-' }}</p>
                                    </div>
                                    <button wire:click="removeLeader" class="text-[9px] w-6 h-6 shrink-0 bg-white rounded flex items-center justify-center text-slate-400 hover:text-red-500 shadow-sm" title="Ganti Ketua">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                    </button>
                                </div>
                            @else
                                <!-- Pencarian -->
                                <input wire:model.live.debounce.200ms="searchLeader" type="text" class="w-full bg-slate-50 border border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold text-slate-700 focus:ring-4 focus:ring-minimal-indigo/10 transition-all" placeholder="Ketik nama / nip ketua...">
                                @if(strlen($searchLeader) > 1)
                                    <div class="bg-white border border-slate-100 rounded-xl shadow-lg shadow-slate-200/50 absolute z-10 w-full md:w-auto md:min-w-[240px] max-h-48 overflow-y-auto mt-1 custom-scrollbar">
                                        @php
                                            $filteredLeaders = collect($allUsers)->filter(fn($u) => 
                                                str_contains(strtolower($u['name']), strtolower($searchLeader)) || 
                                                str_contains(strtolower($u['nip']), strtolower($searchLeader))
                                            )->take(5);
                                        @endphp
                                        @forelse($filteredLeaders as $fl)
                                            <button wire:click="setLeader('{{ $fl['id'] }}')" class="w-full text-left p-2.5 hover:bg-slate-50 transition-colors border-b border-slate-50 last:border-0">
                                                <p class="text-[10px] font-black text-slate-700">{{ $fl['name'] }}</p>
                                                <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest">{{ $fl['nip'] }}</p>
                                            </button>
                                        @empty
                                            <p class="p-3 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Tidak ditemukan</p>
                                        @endforelse
                                    </div>
                                @endif
                            @endif
                        </div>

                        <!-- Pilihan Anggota -->
                        <div class="space-y-1 relative">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-3">Pilih Anggota</label>
                            <input wire:model.live.debounce.200ms="searchMember" type="text" class="w-full bg-slate-50 border border-slate-100 rounded-xl px-4 py-2.5 text-[10px] font-bold text-slate-700 focus:ring-4 focus:ring-minimal-indigo/10 transition-all" placeholder="Cari untuk ditambahkan...">
                            
                            @if(strlen($searchMember) > 1)
                                <div class="bg-white border border-slate-100 rounded-xl shadow-lg shadow-slate-200/50 absolute z-10 w-full max-h-48 overflow-y-auto mt-1 custom-scrollbar">
                                    @php
                                        // PENTING: User yang sudah jadi Ketua Tim ini tidak boleh masuk anggota!
                                        $filteredMembers = collect($allUsers)->filter(fn($u) => 
                                            // bukan ketua saat ini
                                            $u['id'] !== $leaderId && 
                                            // bukan member saat ini
                                            !in_array($u['id'], $memberIds) && 
                                            // match search
                                            (str_contains(strtolower($u['name']), strtolower($searchMember)) || 
                                             str_contains(strtolower($u['nip']), strtolower($searchMember)))
                                        )->take(8);
                                    @endphp
                                    @forelse($filteredMembers as $fm)
                                        <button wire:click="toggleMember('{{ $fm['id'] }}')" class="w-full text-left p-2.5 flex items-center justify-between hover:bg-slate-50 transition-colors border-b border-slate-50 last:border-0 group">
                                            <div>
                                                <p class="text-[10px] font-black text-slate-700 group-hover:text-minimal-indigo transition-colors">{{ $fm['name'] }}</p>
                                                <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest">{{ $fm['nip'] }}</p>
                                            </div>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-300 group-hover:text-minimal-indigo transition-colors" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                        </button>
                                    @empty
                                        <p class="p-3 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Tidak ditemukan atau sudah dipilih</p>
                                    @endforelse
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Selected Members Box -->
                    <div class="space-y-1 pt-2">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-3 flex justify-between items-center">
                            <span>Anggota Terpilih ({{ count($memberIds) }})</span>
                        </label>
                        <div class="bg-slate-50 rounded-xl p-3 max-h-40 overflow-y-auto border border-slate-100">
                            @if(count($memberIds) === 0)
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest text-center py-4">Belum ada anggota dipilih</p>
                            @else
                                <div class="flex flex-wrap gap-2">
                                    @foreach($memberIds as $mid)
                                        @php
                                            $theMember = collect($allUsers)->firstWhere('id', $mid);
                                        @endphp
                                        @if($theMember)
                                            <div class="flex items-center bg-white border border-slate-200 rounded-lg pl-2 pr-1 py-1 text-[10px] font-bold text-slate-700 shadow-sm gap-1 group">
                                                <span>{{ explode(' ', trim($theMember['name']))[0] }}</span>
                                                <button wire:click="toggleMember('{{ $mid }}')" class="w-4 h-4 rounded hover:bg-red-50 hover:text-red-500 text-slate-300 flex items-center justify-center transition-colors">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                                </button>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-3 pt-4 border-t border-slate-50">
                        <button type="button" wire:click="closeModal" class="flex-1 py-3 bg-slate-50 text-slate-400 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-100 transition-all active:scale-95">Batal</button>
                        <button type="button" wire:click="saveTeam" class="flex-[2] py-3 bg-minimal-indigo text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-xl shadow-indigo-500/20 hover:scale-[1.02] transition-all active:scale-95">Simpan Tim Kerja</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Import Tim Modal -->
    @if($isImportModalOpen)
        <div class="fixed inset-0 z-[100] flex items-start justify-center p-4 pt-10 overflow-y-auto">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="closeImportModal"></div>
            <div class="bg-white w-full max-w-2xl rounded-[2rem] shadow-2xl relative flex flex-col animate-in zoom-in-95 duration-300 mb-10">

                <!-- Header -->
                <div class="p-6 pb-4 border-b border-slate-50 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-minimal-indigo/10 text-minimal-indigo flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        </div>
                        <div>
                            <h4 class="text-lg font-black text-slate-800 tracking-tight">Import Tim dari Excel</h4>
                            <p class="text-[9px] font-black uppercase tracking-[0.2em] text-minimal-indigo/60">Bulk Import · Assign Otomatis</p>
                        </div>
                    </div>
                    <button wire:click="closeImportModal" class="w-9 h-9 rounded-xl bg-slate-50 text-slate-300 hover:bg-red-50 hover:text-red-500 transition-all flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>

                <div class="p-6 space-y-5">

                    <!-- Download Template -->
                    <div class="flex items-center justify-between bg-indigo-50 border border-indigo-100 rounded-2xl px-4 py-3">
                        <div>
                            <p class="text-[10px] font-black text-minimal-indigo uppercase tracking-widest leading-none mb-0.5">Template Excel</p>
                            <p class="text-[10px] text-slate-500 font-medium">Unduh template, isi data, lalu upload kembali</p>
                        </div>
                        <button wire:click="downloadImportTemplate" class="flex items-center gap-2 bg-minimal-indigo text-white px-4 py-2 rounded-xl text-[9px] font-black uppercase tracking-widest hover:scale-105 transition-all shadow-lg shadow-indigo-500/20 active:scale-95 shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            Unduh Template
                        </button>
                    </div>

                    <!-- Format info -->
                    <div class="bg-slate-50 rounded-xl px-4 py-3 text-[10px] text-slate-500 font-medium space-y-1">
                        <p class="font-black text-slate-600 uppercase tracking-widest text-[9px] mb-1">Format Kolom Excel</p>
                        <p><span class="font-black text-slate-700">Kolom A</span> — Nama Tim (cukup diisi di baris pertama tiap tim)</p>
                        <p><span class="font-black text-slate-700">Kolom B</span> — NIP Ketua Tim (cukup di baris pertama, harus sudah ada di Data Pegawai)</p>
                        <p><span class="font-black text-slate-700">Kolom C</span> — NIP Anggota, satu per baris (harus sudah ada di Data Pegawai)</p>
                    </div>

                    <!-- File Upload -->
                    <div>
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-1.5">Upload File Excel (.xlsx)</label>
                        <label class="flex flex-col items-center justify-center gap-2 border-2 border-dashed border-slate-200 hover:border-minimal-indigo/40 rounded-2xl p-8 cursor-pointer transition-colors group">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-slate-300 group-hover:text-minimal-indigo/40 transition-colors" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest group-hover:text-minimal-indigo/60 transition-colors">
                                {{ $importFile ? $importFile->getClientOriginalName() : 'Klik untuk pilih file .xlsx' }}
                            </span>
                            <input type="file" wire:model="importFile" accept=".xlsx,.xls" class="hidden">
                        </label>
                        @error('importFile') <p class="text-[8px] font-black text-red-500 uppercase tracking-widest mt-1 ml-1">{{ $message }}</p> @enderror
                        <div wire:loading wire:target="importFile" class="mt-2 text-[9px] text-minimal-indigo font-black uppercase tracking-widest text-center">Memproses file...</div>
                    </div>

                    <!-- Preview Results -->
                    @if($importParsed)
                        <!-- Summary Stat Cards -->
                        <div class="grid grid-cols-3 gap-3">
                            <div class="bg-emerald-50 border border-emerald-100 rounded-2xl p-3 text-center">
                                <p class="text-2xl font-black text-emerald-600">{{ count($importTeams) }}</p>
                                <p class="text-[8px] font-black text-emerald-500 uppercase tracking-widest leading-tight mt-0.5">Tim Siap Import</p>
                            </div>
                            <div class="bg-slate-50 border border-slate-100 rounded-2xl p-3 text-center">
                                <p class="text-2xl font-black text-slate-700">{{ collect($importTeams)->sum(fn($t) => count($t['members'])) }}</p>
                                <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest leading-tight mt-0.5">Total Anggota</p>
                            </div>
                            <div class="bg-red-50 border border-red-100 rounded-2xl p-3 text-center">
                                <p class="text-2xl font-black text-red-500">{{ count($importErrors) }}</p>
                                <p class="text-[8px] font-black text-red-400 uppercase tracking-widest leading-tight mt-0.5">Tim Gagal</p>
                            </div>
                        </div>

                        <!-- Valid Teams Preview -->
                        @if(count($importTeams) > 0)
                            <div>
                                <p class="text-[9px] font-black text-emerald-600 uppercase tracking-widest mb-2 flex items-center gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                    Tim yang Akan Diimport
                                </p>
                                <div class="space-y-2 max-h-56 overflow-y-auto custom-scrollbar pr-1">
                                    @foreach($importTeams as $team)
                                        <div class="bg-white border border-slate-100 rounded-xl px-4 py-3 flex items-start justify-between gap-3">
                                            <div class="flex-1 min-w-0">
                                                <p class="text-[11px] font-black text-slate-800 uppercase italic tracking-tight truncate">{{ $team['team_name'] }}</p>
                                                <p class="text-[9px] font-medium text-slate-400 mt-0.5">
                                                    Ketua: <span class="font-black text-minimal-indigo">{{ $team['leader_name'] ?? '—' }}</span>
                                                    @if($team['leader_nip'])
                                                        <span class="text-slate-300"> · {{ $team['leader_nip'] }}</span>
                                                    @endif
                                                </p>
                                                @if(count($team['skipped_members']) > 0)
                                                    <p class="text-[8px] text-amber-500 font-black uppercase tracking-widest mt-0.5">
                                                        ⚠ {{ count($team['skipped_members']) }} anggota dilewati (NIP tidak ditemukan)
                                                    </p>
                                                @endif
                                            </div>
                                            <div class="shrink-0 text-right">
                                                <span class="inline-block bg-emerald-50 text-emerald-600 border border-emerald-100 px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-widest">
                                                    {{ count($team['members']) }} Anggota
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Invalid Teams / Errors -->
                        @if(count($importErrors) > 0)
                            <div>
                                <p class="text-[9px] font-black text-red-500 uppercase tracking-widest mb-2 flex items-center gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                                    Tim Gagal Diimport
                                </p>
                                <div class="space-y-2 max-h-44 overflow-y-auto custom-scrollbar pr-1">
                                    @foreach($importErrors as $err)
                                        <div class="bg-red-50 border border-red-100 rounded-xl px-4 py-3">
                                            <p class="text-[10px] font-black text-red-700 uppercase italic tracking-tight">{{ $err['team_name'] ?: '(Nama Tim Kosong)' }}</p>
                                            <ul class="mt-1 space-y-0.5">
                                                @foreach($err['reasons'] as $reason)
                                                    <li class="text-[9px] text-red-500 font-medium">• {{ $reason }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if(count($importTeams) === 0 && count($importErrors) === 0)
                            <div class="text-center py-6">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">File tidak mengandung data yang bisa diproses</p>
                            </div>
                        @endif
                    @endif

                    <!-- Actions -->
                    <div class="flex gap-3 pt-2 border-t border-slate-50">
                        <button type="button" wire:click="closeImportModal" class="flex-1 py-3 bg-slate-50 text-slate-400 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-100 transition-all active:scale-95">Batal</button>
                        @if($importParsed && count($importTeams) > 0)
                            <button type="button" wire:click="confirmImport" class="flex-[2] py-3 bg-emerald-500 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-xl shadow-emerald-500/20 hover:scale-[1.02] transition-all active:scale-95 flex items-center justify-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                Konfirmasi Import {{ count($importTeams) }} Tim
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
                        <h4 class="text-lg font-black text-slate-800 tracking-tight">Hapus Tim Kerja</h4>
                        <p class="text-[9px] font-black uppercase tracking-[0.2em] text-red-500/60">Tindakan ini permanen</p>
                    </div>
                </div>
                <p class="text-xs text-slate-500 font-medium mb-6">Apakah Anda yakin ingin menghapus tim ini? Semua penugasan anggota di dalam tim ini akan terlepas.</p>
                
                <div class="flex gap-3">
                    <button type="button" wire:click="cancelDelete" class="flex-1 py-3 bg-slate-50 text-slate-400 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-100 transition-all active:scale-95">Batal</button>
                    <button type="button" wire:click="executeDelete" class="flex-1 py-3 bg-red-500 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-red-500/20 hover:bg-red-600 transition-all active:scale-95">Ya, Hapus</button>
                </div>
            </div>
        </div>
    @endif
</div>

