<div class="space-y-8">

    {{-- Flash --}}
    @if(session('success'))
    <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 rounded-2xl px-5 py-3">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-emerald-500 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
        <p class="text-sm font-bold text-emerald-700">{{ session('success') }}</p>
    </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-800 uppercase italic tracking-tight">Super Admin</h2>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-0.5">Manajemen Satuan Kerja — PAKAR</p>
        </div>
        <div class="flex items-center gap-2">
            <button wire:click="setTab('rekap')"
                class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all {{ $activeTab === 'rekap' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }}">
                Rekap Penilaian
            </button>
            <button wire:click="setTab('satker')"
                class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all {{ $activeTab === 'satker' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }}">
                Kelola Satker
            </button>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════
         TAB: REKAP PENILAIAN
    ══════════════════════════════════════════════════════════════════ --}}
    @if($activeTab === 'rekap')
    <div class="grid grid-cols-1 gap-4">
        @forelse($rekap as $row)
        @php $satker = $row['satker']; @endphp
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            {{-- Header satker --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-50"
                style="{{ $satker->type === 'provinsi' ? 'background:linear-gradient(90deg,#1e3a5f,#003366);' : 'background:#f8fafc;' }}">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-sm font-black {{ $satker->type === 'provinsi' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-600' }}">
                        {{ substr($satker->name, 0, 2) }}
                    </div>
                    <div>
                        <p class="text-sm font-black {{ $satker->type === 'provinsi' ? 'text-white' : 'text-slate-800' }} uppercase italic tracking-tight">{{ $satker->name }}</p>
                        <p class="text-[9px] font-bold {{ $satker->type === 'provinsi' ? 'text-white/60' : 'text-slate-400' }} uppercase tracking-widest">{{ $satker->type === 'provinsi' ? 'Satker Provinsi' : 'Satker Kabupaten/Kota' }} {{ $satker->kode ? "· {$satker->kode}" : '' }}</p>
                    </div>
                </div>
                @if(!$satker->is_active)
                <span class="px-2.5 py-1 bg-rose-100 text-rose-600 text-[8px] font-black uppercase rounded-full tracking-widest">Nonaktif</span>
                @endif
            </div>

            {{-- Stats grid --}}
            <div class="grid grid-cols-2 sm:grid-cols-5 divide-x divide-slate-50">
                <div class="px-5 py-4 text-center">
                    <p class="text-[8px] font-black uppercase tracking-widest text-slate-400 mb-1">Total Pegawai</p>
                    <p class="text-2xl font-black italic text-slate-800">{{ $row['total_pegawai'] }}</p>
                </div>
                <div class="px-5 py-4 text-center">
                    <p class="text-[8px] font-black uppercase tracking-widest text-slate-400 mb-1">Total Tim</p>
                    <p class="text-2xl font-black italic text-slate-800">{{ $row['total_tim'] }}</p>
                </div>
                <div class="px-5 py-4 text-center">
                    <p class="text-[8px] font-black uppercase tracking-widest text-slate-400 mb-1">Admin</p>
                    <p class="text-2xl font-black italic {{ $row['total_admin'] > 0 ? 'text-emerald-600' : 'text-rose-500' }}">{{ $row['total_admin'] }}</p>
                </div>
                <div class="px-5 py-4 text-center">
                    <p class="text-[8px] font-black uppercase tracking-widest text-slate-400 mb-1">Sudah Dinilai</p>
                    <p class="text-2xl font-black italic text-minimal-indigo">{{ $row['rated_count'] }}</p>
                    <p class="text-[8px] font-bold text-slate-300 uppercase tracking-widest">{{ $row['pct_rated'] }}%</p>
                </div>
                <div class="px-5 py-4 text-center">
                    <p class="text-[8px] font-black uppercase tracking-widest text-slate-400 mb-1">Avg Nilai</p>
                    <p class="text-2xl font-black italic {{ $row['avg_score'] >= 80 ? 'text-emerald-600' : ($row['avg_score'] >= 60 ? 'text-amber-500' : ($row['avg_score'] > 0 ? 'text-red-500' : 'text-slate-300')) }}">
                        {{ $row['avg_score'] ?? '—' }}
                    </p>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-16 text-slate-400 font-bold text-sm">Belum ada satker terdaftar.</div>
        @endforelse
    </div>

    {{-- ══════════════════════════════════════════════════════════════════
         TAB: KELOLA SATKER
    ══════════════════════════════════════════════════════════════════ --}}
    @elseif($activeTab === 'satker')
    <div class="space-y-4">
        <div class="flex justify-end">
            <button wire:click="openCreateSatker"
                class="flex items-center gap-2 px-5 py-2.5 bg-slate-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-700 transition-all active:scale-95">
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
                        <td class="px-4 py-4 text-center">
                            <span class="text-sm font-black text-slate-700">{{ $pegawaiCount }}</span>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <span class="text-sm font-black text-slate-700">{{ $teamCount }}</span>
                        </td>
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
                            <div class="flex items-center justify-end gap-2">
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
    @endif

    {{-- ══════════════════════════════════════════════════════════════════
         MODAL: Tambah/Edit Satker
    ══════════════════════════════════════════════════════════════════ --}}
    @if($showSatkerModal)
    <div class="fixed inset-0 z-[200] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="$set('showSatkerModal', false)"></div>
        <div class="relative bg-white w-full max-w-md rounded-3xl shadow-2xl border-t-4 border-slate-900 p-8 animate-in zoom-in-95 duration-200">
            <h4 class="text-lg font-black text-slate-800 uppercase italic mb-6">
                {{ $editingSatkerId ? 'Edit Satker' : 'Tambah Satker Kabkot' }}
            </h4>
            <form wire:submit="saveSatker" class="space-y-4">
                <div>
                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Nama Satker</label>
                    <input wire:model="satkerName" type="text"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-slate-200 focus:border-slate-400 transition-all"
                        placeholder="Contoh: BPS Kabupaten Donggala">
                    @error('satkerName') <p class="text-[9px] font-black text-red-500 mt-1 uppercase tracking-widest">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Kode <span class="text-slate-300">(opsional)</span></label>
                    <input wire:model="satkerKode" type="text"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-slate-200 focus:border-slate-400 transition-all"
                        placeholder="Contoh: KAB01">
                    @error('satkerKode') <p class="text-[9px] font-black text-red-500 mt-1 uppercase tracking-widest">{{ $message }}</p> @enderror
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" wire:click="$set('showSatkerModal', false)"
                        class="flex-1 py-3 bg-slate-100 text-slate-500 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-200 transition-all">
                        Batal
                    </button>
                    <button type="submit" wire:loading.attr="disabled"
                        class="flex-[2] py-3 bg-slate-900 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-700 transition-all active:scale-95 disabled:opacity-70">
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
        <div class="relative bg-white w-full max-w-lg rounded-3xl shadow-2xl border-t-4 border-minimal-indigo p-8 animate-in zoom-in-95 duration-200">
            <h4 class="text-lg font-black text-slate-800 uppercase italic mb-1">Assign Admin Kabkot</h4>
            <p class="text-[10px] font-bold text-minimal-indigo uppercase tracking-widest mb-6">{{ $assignSatkerName }}</p>

            <div class="mb-4">
                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Cari User (Nama / NIP)</label>
                <input wire:model.live.debounce.300ms="adminSearch" type="text"
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-100 focus:border-minimal-indigo transition-all"
                    placeholder="Ketik minimal 2 karakter...">
            </div>

            {{-- Search results --}}
            @if($searchableUsers->isNotEmpty())
            <div class="space-y-2 max-h-56 overflow-y-auto mb-6 pr-1">
                @foreach($searchableUsers as $u)
                <button wire:click="selectUser('{{ $u->id }}')"
                    class="w-full flex items-center gap-3 px-4 py-3 rounded-xl border transition-all text-left
                    {{ $selectedUserId === $u->id ? 'border-minimal-indigo bg-indigo-50' : 'border-slate-100 hover:border-minimal-indigo/30 hover:bg-slate-50' }}">
                    <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center text-[11px] font-black text-slate-500 italic shrink-0">
                        {{ substr($u->name, 0, 1) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[11px] font-black text-slate-800 uppercase tracking-tight truncate">{{ $u->name }}</p>
                        <p class="text-[9px] font-mono font-bold text-slate-400">{{ $u->nip }}</p>
                    </div>
                    <div class="shrink-0 flex flex-wrap gap-1 justify-end">
                        @foreach($u->getRoleNames() as $role)
                        <span class="px-1.5 py-0.5 bg-slate-100 text-slate-500 text-[7px] font-black rounded-full uppercase tracking-widest">{{ $role }}</span>
                        @endforeach
                    </div>
                    @if($selectedUserId === $u->id)
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-minimal-indigo shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    @endif
                </button>
                @endforeach
            </div>
            @elseif(strlen($adminSearch) >= 2)
            <div class="text-center py-6 text-slate-400 text-xs font-bold mb-6">Tidak ada user ditemukan.</div>
            @else
            <div class="text-center py-6 text-slate-300 text-xs font-bold mb-6">Ketik nama atau NIP untuk mencari user.</div>
            @endif

            <div class="bg-amber-50 border border-amber-100 rounded-xl px-4 py-3 mb-5">
                <p class="text-[9px] font-black text-amber-700 uppercase tracking-widest">⚠ Catatan</p>
                <p class="text-[10px] font-bold text-amber-600 mt-1">User yang dipilih akan dipindahkan ke satker ini dan diberikan role Admin. Pastikan user yang dipilih memang admin untuk satker tersebut.</p>
            </div>

            <div class="flex gap-3">
                <button type="button" wire:click="$set('showAssignAdminModal', false)"
                    class="flex-1 py-3 bg-slate-100 text-slate-500 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-200 transition-all">
                    Batal
                </button>
                <button wire:click="assignAdmin" wire:loading.attr="disabled"
                    :disabled="{{ $selectedUserId ? 'false' : 'true' }}"
                    class="flex-[2] py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed
                    {{ $selectedUserId ? 'bg-minimal-indigo text-white hover:bg-indigo-700' : 'bg-slate-200 text-slate-400' }}">
                    <span wire:loading.remove>Assign Admin</span>
                    <span wire:loading>Memproses...</span>
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
