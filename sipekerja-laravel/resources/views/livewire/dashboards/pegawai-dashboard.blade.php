<div class="font-outfit space-y-6 pb-12">
    @if(!$isFromPimpinan)
    <!-- Header Area -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-800 tracking-tight italic">Personal Performance</h2>
            <p class="text-slate-400 text-[11px] font-medium">Monitoring capaian kinerja dan kontribusi penugasan tim.</p>
        </div>
        <div class="flex gap-2 p-1 bg-white rounded-xl shadow-sm border border-slate-100">
            <div class="px-4 py-2 text-[10px] font-black uppercase tracking-widest text-minimal-indigo bg-minimal-indigo/5 rounded-lg border border-minimal-indigo/10 flex items-center gap-2">
                <span class="w-1.5 h-1.5 rounded-full bg-minimal-indigo animate-pulse"></span>
                Pegawai Dashboard
            </div>
        </div>
    </div>
    @endif

    @if(!$isFromPimpinan)
    <!-- Top Horizontal Row: Filters & Quick Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        <!-- Filter Card -->
        <div class="bg-white rounded-[1.5rem] p-5 shadow-sm border border-slate-100 flex flex-col justify-center gap-2 h-full">
            <div class="flex items-center gap-2 mb-1 px-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-minimal-indigo" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                <span class="text-[9px] font-black uppercase tracking-widest text-slate-400">Periode Penilaian</span>
            </div>
            
            <div class="grid grid-cols-2 gap-2">
                <div class="relative group">
                    <select wire:model.live="month" class="w-full h-9 pl-3 pr-8 rounded-lg border border-slate-100 bg-slate-50/50 text-[10px] font-black uppercase tracking-wider text-slate-700 appearance-none focus:ring-4 focus:ring-minimal-indigo/10 transition-all cursor-pointer">
                        @foreach($monthNames as $num => $name)
                            <option value="{{ $num }}">{{ substr($name, 0, 3) }}</option>
                        @endforeach
                    </select>
                    <div class="absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none text-slate-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                    </div>
                </div>

                <div class="relative group">
                    <select wire:model.live="year" class="w-full h-9 pl-3 pr-8 rounded-lg border border-slate-100 bg-slate-50/50 text-[10px] font-black uppercase tracking-wider text-slate-700 appearance-none focus:ring-4 focus:ring-minimal-indigo/10 transition-all cursor-pointer">
                        @foreach(range(date('Y')-2, date('Y')) as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                    <div class="absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none text-slate-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                    </div>
                </div>
            </div>
        </div>



        <!-- Teams Metric Card -->
        <div class="bg-white p-5 rounded-[1.5rem] shadow-sm border border-slate-100 flex items-center justify-between group relative overflow-hidden h-full">
            <div class="absolute top-0 right-0 w-12 h-12 bg-slate-900/5 rounded-bl-[1.5rem]"></div>
            <div class="flex items-center gap-3 relative z-10">
                <div class="w-11 h-11 rounded-xl bg-slate-900 text-white flex items-center justify-center shadow-xl">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <div>
                    <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 group-hover:text-minimal-indigo transition-colors">Penempatan Tim</p>
                    <p class="text-[10px] font-bold text-slate-500 leading-none mt-0.5">{{ $data['summary']['ratedTeamsThisMonth'] }}/{{ $data['summary']['totalTeams'] }} Rated</p>
                </div>
            </div>
            <p class="text-3xl font-black text-slate-800 relative z-10 tracking-tighter">{{ $data['summary']['totalTeams'] }}</p>
        </div>
    </div>
    @endif

    <!-- Analytics Board (Charts Grid) -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <!-- 6-Month Tend Chart -->
        <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-slate-100">
            <div class="mb-6 px-1">
                <h3 class="text-lg font-black text-slate-800 tracking-tight">Tren Performa</h3>
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">Histori Skor Selama 6 Bulan Terakhir</p>
            </div>
            <div id="trendChart" class="h-80 w-full" wire:ignore></div>
        </div>

        <!-- Team Comparison Bar Chart -->
        <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-slate-100">
            <div class="mb-6 px-1">
                <h3 class="text-lg font-black text-slate-800 tracking-tight">Komparasi Tim</h3>
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">Skor Pribadi vs Rerata Tim Penugasan</p>
            </div>
            <div id="comparisonChart" class="h-80 w-full" wire:ignore></div>
        </div>
    </div>

    <!-- Detail Penilaian Table -->
    <div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden">
        <div class="p-8 border-b border-slate-50 flex items-center justify-between">
            <div>
                <h3 class="text-xl font-black text-slate-800 tracking-tight italic">Detail Penilaian</h3>
                <p class="text-[9px] font-black uppercase tracking-widest text-minimal-indigo opacity-60">Record Penilaian Masuk — {{ $monthNames[$month] }} {{ $year }}</p>
            </div>
        </div>
        
        <div class="overflow-x-auto px-4 pb-4">
            @if(count($data['ratingsDetail']) > 0)
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50/30 text-[9px] font-black text-slate-400 uppercase tracking-widest">
                            <th class="px-6 py-4">Tim & Penilai</th>
                            <th class="px-4 py-4 text-center">Base</th>
                            <th class="px-4 py-4 text-center">Volume</th>
                            <th class="px-4 py-4 text-center">Kualitas</th>
                            <th class="px-6 py-4 text-right">Nilai Akhir</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($data['ratingsDetail'] as $r)
                        <tr class="hover:bg-slate-50 group transition-all duration-300">
                            <td class="px-6 py-4">
                                <p class="text-[11px] font-black text-slate-700 leading-tight group-hover:text-minimal-indigo transition-colors uppercase">{{ $r->team->team_name }}</p>
                                <p class="text-[9px] font-bold text-slate-400 mt-0.5 uppercase tracking-tighter">Ketua: {{ $r->evaluator->name }}</p>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="text-[11px] font-bold text-slate-600">{{ $r->score }}</span>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="px-2 py-0.5 rounded-lg text-[8px] font-black uppercase {{ $r->volume_work === 'Berat' ? 'bg-rose-50 text-rose-600' : ($r->volume_work === 'Ringan' ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-500') }}">
                                    {{ $r->volume_work ?: 'N/A' }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="px-2 py-0.5 rounded-lg text-[8px] font-black uppercase {{ $r->quality_work === 'Sangat Baik' ? 'bg-emerald-50 text-emerald-600' : ($r->quality_work === 'Kurang' ? 'bg-rose-50 text-rose-600' : 'bg-blue-50 text-minimal-indigo') }}">
                                    {{ $r->quality_work ?: 'N/A' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-xl font-black text-minimal-indigo italic tracking-tighter">{{ number_format($r->final_score, 2) }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="py-16 text-center">
                    <div class="w-16 h-16 bg-slate-50 text-slate-200 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-dashed border-slate-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                    </div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic leading-relaxed">Belum ada penilaian kinerja<br>untuk periode yang dipilih. —</p>
                </div>
            @endif
        </div>
    </div>



    <script type="application/json" id="pegawaiTrendData">
        {!! json_encode($data['scoreHistory']) !!}
    </script>
    <script type="application/json" id="pegawaiCompData">
        {!! json_encode($data['teamComparison']) !!}
    </script>

    @script
    <script>
        let trend, comparison;

        const initPegawaiCharts = () => {
            const trendScript = document.querySelector("#pegawaiTrendData");
            const compScript = document.querySelector("#pegawaiCompData");
            
            if (!trendScript || !compScript) return;
            
            let dataTrend = [];
            let dataComp = [];
            try {
                dataTrend = JSON.parse(trendScript.textContent);
                dataComp = JSON.parse(compScript.textContent);
            } catch (e) { return; }
            
            const trendEl = document.querySelector("#trendChart");
            const compEl = document.querySelector("#comparisonChart");

            if (!trendEl || !compEl) return;

            // Trend History Area Chart
            const trendOptions = {
                series: [{
                    name: 'Skor Kinerja',
                    data: dataTrend.map(h => h.avgScore)
                }],
                chart: { 
                    type: 'area', 
                    height: 350, 
                    fontFamily: 'Outfit, sans-serif', 
                    toolbar: { show: false },
                    zoom: { enabled: false },
                    animations: { enabled: true, easing: 'easeinout', speed: 800 }
                },
                stroke: { curve: 'smooth', width: 4, colors: ['#6366f1'] },
                fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.25, opacityTo: 0.05, stops: [0, 90, 100] } },
                xaxis: { 
                    categories: dataTrend.map(h => h.label), 
                    labels: { style: { fontSize: '9px', fontWeight: 800, colors: '#64748b' } },
                    tooltip: { enabled: false }
                },
                yaxis: { max: 100, labels: { style: { fontSize: '9px', fontWeight: 800, colors: '#64748b' } } },
                colors: ['#6366f1'],
                markers: { size: 6, strokeColors: '#fff', strokeWidth: 3, hover: { size: 8 } },
                grid: { borderColor: '#f8fafc' },
                tooltip: { theme: 'dark' }
            };
            
            if(trend) trend.destroy();
            trend = new ApexCharts(trendEl, trendOptions);
            trend.render();

            // Team Comparison Bar Chart
            const compOptions = {
                series: [
                    { name: 'Saya', data: dataComp.map(c => c.myScore) },
                    { name: 'Rerata Tim', data: dataComp.map(c => c.teamAvg) }
                ],
                chart: { 
                    type: 'bar', 
                    height: 350, 
                    fontFamily: 'Outfit, sans-serif', 
                    toolbar: { show: false },
                    zoom: { enabled: false },
                    animations: { enabled: true, easing: 'easeinout', speed: 800 }
                },
                plotOptions: { bar: { borderRadius: 8, columnWidth: '60%', dataLabels: { position: 'top' } } },
                xaxis: { 
                    categories: dataComp.map(c => c.teamName), 
                    labels: { style: { fontSize: '9px', fontWeight: 800, colors: '#64748b' }, rotate: -45, hideOverlappingLabels: true } 
                },
                yaxis: { max: 100, labels: { style: { fontSize: '9px', fontWeight: 800, colors: '#64748b' } } },
                colors: ['#6366f1', '#e2e8f0'],
                legend: { position: 'bottom', fontSize: '10px', fontWeight: 900 },
                tooltip: { theme: 'dark' },
                grid: { borderColor: '#f8fafc' }
            };

            if(comparison) comparison.destroy();
            comparison = new ApexCharts(compEl, compOptions);
            comparison.render();
        };

        initPegawaiCharts();
        
        $wire.on('refreshPegawaiCharts', () => {
            setTimeout(initPegawaiCharts, 100);
        });
    </script>
    @endscript
</div>
