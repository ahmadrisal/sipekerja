<div class="space-y-8 animate-in fade-in zoom-in-95 duration-500">
    <!-- Header & Period Picker -->
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
        <div>
            <h2 class="text-3xl font-black text-bps-blue italic tracking-tight flex items-center gap-3 underline decoration-amber-400 decoration-8 underline-offset-8">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-bps-blue" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                Input Penilaian Kinerja
            </h2>
            <p class="text-slate-400 font-bold uppercase tracking-widest text-[10px] mt-4">Evaluasi bulanan anggota tim secara spesifik per tim kerja.</p>
        </div>
        <div class="flex items-center gap-3 bg-white p-2 rounded-2xl border border-slate-200 shadow-sm">
            <select
                wire:model.live="month"
                class="border-none rounded-xl px-4 py-2 text-sm font-black text-bps-blue uppercase tracking-tight focus:ring-0 bg-slate-50 cursor-pointer"
            >
                @foreach($monthNames as $i => $name)
                    <option value="{{ $i + 1 }}">{{ $name }}</option>
                @endforeach
            </select>
            <input
                wire:model.live="year"
                type="number"
                class="w-24 border-none rounded-xl px-4 py-2 text-sm font-black text-bps-blue focus:ring-0 bg-slate-50"
                min="2024"
                max="2030"
            >
        </div>
    </div>

    <!-- Progress Card -->
    @php
        $totalTargets = count($formState);
        $ratedTargets = collect($formState)->where('is_rated', true)->count();
        $progress = $totalTargets > 0 ? ($ratedTargets / $totalTargets) * 100 : 0;
    @endphp
    <div class="bg-white p-6 rounded-[2.5rem] border border-slate-200/60 shadow-sm relative overflow-hidden group">
        <div class="absolute top-0 left-0 w-2 h-full bg-bps-blue opacity-10 group-hover:opacity-100 transition-opacity"></div>
        <div class="flex items-center justify-between mb-4">
            <p class="text-xs font-black text-slate-400 uppercase tracking-widest">
                Progres Penilaian Tim <span class="text-bps-blue italic underline decoration-amber-400 decoration-2 underline-offset-4">{{ $monthNames[$month-1] }} {{ $year }}</span>
            </p>
            <p class="text-sm font-black text-bps-blue italic">
                {{ $ratedTargets }} / {{ $totalTargets }} <span class="text-[10px] text-slate-400 not-italic font-bold uppercase ml-1">target telah dinilai</span>
            </p>
        </div>
        <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden border border-slate-200/50">
            <div
                class="bg-gradient-to-r from-bps-blue to-blue-500 h-full rounded-full transition-all duration-1000 ease-out shadow-[0_0_15px_rgba(0,51,102,0.3)]"
                style="width: {{ $progress }}%"
            ></div>
        </div>
    </div>

    @if(count($members) === 0)
        <div class="bg-white py-20 rounded-[3rem] border-2 border-dashed border-slate-200 text-center">
            <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
            </div>
            <p class="text-xl font-black text-slate-400 uppercase italic">Tidak Ada Anggota Tim</p>
            <p class="text-xs text-slate-300 font-bold uppercase tracking-widest mt-1">Anda belum memimpin tim atau belum ada anggota yang terdaftar.</p>
        </div>
    @else
        <!-- Assessment Table -->
        <div class="bg-white rounded-[2.5rem] border border-slate-200/60 shadow-xl overflow-hidden shadow-blue-900/5">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50/80 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                            <th class="px-8 py-5 border-b border-slate-100">Pegawai</th>
                            <th class="px-6 py-5 border-b border-slate-100">Tim Kerja</th>
                            <th class="px-4 py-5 border-b border-slate-100 text-center">Nilai Dasar</th>
                            <th class="px-6 py-5 border-b border-slate-100">Volume/Kesulitan</th>
                            <th class="px-6 py-5 border-b border-slate-100">Kualitas Kerja</th>
                            <th class="px-4 py-5 border-b border-slate-100">Catatan</th>
                            <th class="px-6 py-5 border-b border-slate-100 text-center text-bps-blue italic underline decoration-bps-blue/10 decoration-dashed">N. Akhir</th>
                            <th class="px-8 py-5 border-b border-slate-100 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($members as $mId => $member)
                            @foreach($member['teams'] as $index => $team)
                                @php $key = "{$mId}_{$team->id}"; @endphp
                                <tr 
                                    x-data="{ 
                                        score: @entangle('formState.' . $key . '.score'),
                                        volume: @entangle('formState.' . $key . '.volume_work'),
                                        quality: @entangle('formState.' . $key . '.quality_work'),
                                        get finalScore() {
                                            if (!this.score) return '-';
                                            let v = this.volume === 'Berat' ? 100 : (this.volume === 'Ringan' ? 60 : 80);
                                            let q = this.quality === 'Sangat Baik' ? 100 : (this.quality === 'Baik' ? 90 : (this.quality === 'Kurang' ? 50 : 75));
                                            return ((this.score * 0.8) + (v * 0.1) + (q * 0.1)).toFixed(2);
                                        }
                                    }"
                                    class="group transition-colors {{ !$formState[$key]['is_rated'] ? 'bg-amber-50/30' : 'hover:bg-slate-50/50' }}"
                                >
                                    @if($index === 0)
                                        <td rowspan="{{ count($member['teams']) }}" class="px-8 py-6 align-top border-r border-slate-50 bg-white">
                                            <div class="flex flex-col gap-1">
                                                <p class="text-sm font-black text-slate-800 uppercase tracking-tight leading-none">{{ $member['name'] }}</p>
                                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">NIP: {{ $member['nip'] }}</p>
                                                @php
                                                    $allRated = true;
                                                    foreach($member['teams'] as $t) {
                                                        if (!$formState["{$mId}_{$t->id}"]['is_rated']) $allRated = false;
                                                    }
                                                @endphp
                                                @if(!$allRated)
                                                    <span class="mt-3 px-3 py-1 bg-amber-100 text-amber-700 text-[9px] font-black rounded-full uppercase tracking-tighter w-fit flex items-center gap-1">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                                                        Belum Lengkap
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                    @endif
                                    
                                    <td class="px-6 py-6 font-black text-bps-blue uppercase text-xs italic tracking-tighter">
                                        {{ $team->team_name }}
                                    </td>

                                    <td class="px-4 py-6 text-center">
                                        <input 
                                            wire:model.live.debounce.300ms="formState.{{ $key }}.score"
                                            x-model="score"
                                            type="number" 
                                            class="w-20 bg-slate-50 border border-slate-200 rounded-xl px-2 py-2 text-center text-sm font-black text-bps-blue focus:ring-4 focus:ring-bps-blue/10 focus:border-bps-blue transition-all"
                                            placeholder="--"
                                            @input="$wire.updateField('{{ $key }}', 'score', $event.target.value)"
                                        >
                                    </td>

                                    <td class="px-6 py-6">
                                        <div class="flex gap-1">
                                            @foreach(['Ringan', 'Sedang', 'Berat'] as $v)
                                                <button 
                                                    wire:click="updateField('{{ $key }}', 'volume_work', '{{ $v }}')"
                                                    @click="volume = '{{ $v }}'"
                                                    class="px-2 py-1.5 rounded-lg text-[9px] font-black uppercase tracking-tighter transition-all {{ $formState[$key]['volume_work'] === $v ? 'bg-bps-blue text-white shadow-lg shadow-blue-900/20' : 'bg-slate-50 text-slate-400 hover:bg-slate-100 hover:text-slate-600' }}"
                                                >
                                                    {{ $v }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </td>

                                    <td class="px-6 py-6">
                                        <div class="flex gap-1 flex-wrap max-w-[200px]">
                                            @foreach(['Kurang', 'Cukup', 'Baik', 'Sangat Baik'] as $q)
                                                <button 
                                                    wire:click="updateField('{{ $key }}', 'quality_work', '{{ $q }}')"
                                                    @click="quality = '{{ $q }}'"
                                                    class="px-2 py-1.5 rounded-lg text-[9px] font-black uppercase tracking-tighter transition-all {{ $formState[$key]['quality_work'] === $q ? 'bg-bps-blue text-white shadow-lg shadow-blue-900/20' : 'bg-slate-50 text-slate-400 hover:bg-slate-100 hover:text-slate-600' }}"
                                                >
                                                    {{ $q }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </td>

                                    <td class="px-4 py-6">
                                        <input 
                                            wire:model.blur="formState.{{ $key }}.notes"
                                            type="text" 
                                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-700 focus:ring-4 focus:ring-bps-blue/10 focus:border-bps-blue transition-all"
                                            placeholder="Catatan..."
                                            @input="$wire.updateField('{{ $key }}', 'notes', $event.target.value)"
                                        >
                                    </td>

                                    <td class="px-6 py-6 text-center">
                                        <span x-text="finalScore" class="text-xl font-black text-bps-blue italic tracking-tighter"></span>
                                    </td>

                                    <td class="px-8 py-6 text-right">
                                        <button 
                                            wire:click="saveRating('{{ $key }}')"
                                            wire:loading.attr="disabled"
                                            class="w-full relative px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all active:scale-95 disabled:opacity-50
                                                {{ $formState[$key]['is_dirty'] ? 'bg-bps-blue text-white shadow-xl shadow-blue-900/20' : ($formState[$key]['is_rated'] ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed') }}"
                                            @if(!$formState[$key]['is_dirty']) 
                                                @if(!$formState[$key]['is_rated']) disabled @endif
                                            @endif
                                        >
                                            <span wire:loading.remove wire:target="saveRating('{{ $key }}')">
                                                @if($formState[$key]['is_rated'] && !$formState[$key]['is_dirty'])
                                                    <div class="flex items-center justify-center gap-2">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17l-5-5"/></svg>
                                                        Tersimpan
                                                    </div>
                                                @else
                                                    Simpan
                                                @endif
                                            </span>
                                            <span wire:loading wire:target="saveRating('{{ $key }}')">...</span>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Validation Dialog -->
    @if($showValidationDialog)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="$set('showValidationDialog', false)"></div>
            <div class="bg-white w-full max-w-sm rounded-[2.5rem] shadow-2xl relative overflow-hidden flex flex-col p-8 animate-in zoom-in-95 duration-200 border-t-8 border-amber-500">
                <div class="w-16 h-16 bg-amber-50 rounded-2xl flex items-center justify-center mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-amber-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                </div>
                <h4 class="text-xl font-black text-bps-blue uppercase italic leading-none mb-2">Penilaian Belum Lengkap</h4>
                <p class="text-xs text-slate-400 font-bold uppercase tracking-widest mb-6">Silakan lengkapi isian berikut:</p>
                
                <div class="space-y-2 mb-8">
                    @foreach($validationMessages as $msg)
                        <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <div class="w-2 h-2 bg-amber-500 rounded-full"></div>
                            <span class="text-sm font-black text-slate-700 italic">{{ $msg }}</span>
                        </div>
                    @endforeach
                </div>

                <button 
                    wire:click="$set('showValidationDialog', false)"
                    class="w-full py-4 bg-bps-blue text-white rounded-2xl text-xs font-black uppercase tracking-widest shadow-xl shadow-blue-900/20 active:scale-95 transition-all"
                >
                    Saya Mengerti
                </button>
            </div>
        </div>
    @endif
</div>
