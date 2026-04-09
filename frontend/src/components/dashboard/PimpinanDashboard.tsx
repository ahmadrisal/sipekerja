'use client';

import React, { useState, useMemo } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Input } from '@/components/ui/input';
import { 
    TrendingUp, TrendingDown, Users, Search, ArrowDown, ArrowUp, ArrowUpDown, 
    Award, BarChart3, ChevronRight, ClipboardList, Info, AlertCircle 
} from 'lucide-react';
import { 
    BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, 
    Cell, PieChart, Pie, Legend, ScatterChart, Scatter, ZAxis, Label, ReferenceLine
} from 'recharts';
import { PegawaiDashboardView } from '@/components/PegawaiDashboardView';
import api from '@/lib/axios';

export interface PimpinanRekap {
    month: number;
    year: number;
    data: {
        id: string;
        nip: string;
        name: string;
        totalTeams: number;
        ratedTeams: number;
        averageScore: number;
        details: { teamId: string, teamName: string, leaderName: string, score: number | null }[];
    }[];
}

interface PimpinanDashboardProps {
    rekap: PimpinanRekap;
    monthNames: string[];
    pegawaiMonth: number;
    pegawaiYear: number;
}

export function PimpinanDashboard({ rekap, monthNames, pegawaiMonth, pegawaiYear }: PimpinanDashboardProps) {
    const [pimpinanTab, setPimpinanTab] = useState<'overview' | 'report'>('overview');
    const [reportUserId, setReportUserId] = useState<string>('');
    const [reportSearch, setReportSearch] = useState('');
    const [showReportSuggestions, setShowReportSuggestions] = useState(false);
    const [reportData, setReportData] = useState<any>(null);
    const [reportLoading, setReportLoading] = useState(false);
    
    // Table states
    const [pimpinanSearch, setPimpinanSearch] = useState('');
    const [pimpinanTeamFilter, setPimpinanTeamFilter] = useState('All');
    const [pimpinanTeamStatusFilter, setPimpinanTeamStatusFilter] = useState('All');
    const [pimpinanSort, setPimpinanSort] = useState<{key: keyof PimpinanRekap['data'][0] | 'averageScore', dir: 'asc' | 'desc'}>({ key: 'averageScore', dir: 'desc' });
    const [pimpinanDetailUser, setPimpinanDetailUser] = useState<PimpinanRekap['data'][0] | null>(null);

    const filteredPimpinanData = useMemo(() => {
        if (!rekap) return [];
        let data = [...rekap.data];

        if (pimpinanSearch) {
            const q = pimpinanSearch.toLowerCase();
            data = data.filter(u => u.name.toLowerCase().includes(q) || u.nip.includes(q));
        }

        if (pimpinanTeamFilter !== 'All') {
            data = data.filter(u => u.details.some(d => d.teamName === pimpinanTeamFilter));
        }

        if (pimpinanTeamStatusFilter === 'HasTeam') {
            data = data.filter(u => u.totalTeams > 0);
        } else if (pimpinanTeamStatusFilter === 'NoTeam') {
            data = data.filter(u => u.totalTeams === 0);
        }

        data.sort((a, b) => {
            const valA = a[pimpinanSort.key] as any;
            const valB = b[pimpinanSort.key] as any;
            if (valA < valB) return pimpinanSort.dir === 'asc' ? -1 : 1;
            if (valA > valB) return pimpinanSort.dir === 'asc' ? 1 : -1;
            return 0;
        });

        return data;
    }, [rekap, pimpinanSearch, pimpinanTeamFilter, pimpinanTeamStatusFilter, pimpinanSort]);

    const handleSortPimpinan = (key: keyof PimpinanRekap['data'][0]) => {
        setPimpinanSort(prev => ({
            key,
            dir: prev.key === key && prev.dir === 'desc' ? 'asc' : 'desc'
        }));
    };

    const SortIconPimpinan = ({ columnKey }: { columnKey: keyof PimpinanRekap['data'][0] }) => {
        if (pimpinanSort.key !== columnKey) return <ArrowUpDown className="inline-block ml-1 w-3 h-3 text-muted-foreground opacity-50" />;
        return pimpinanSort.dir === 'asc' 
            ? <ArrowUp className="inline-block ml-1 w-3 h-3 text-primary" />
            : <ArrowDown className="inline-block ml-1 w-3 h-3 text-primary" />;
    };

    const allTeamsPimpinan = useMemo(() => {
        if (!rekap) return [];
        const teams = new Set<string>();
        rekap.data.forEach(u => u.details.forEach(d => teams.add(d.teamName)));
        return Array.from(teams).sort();
    }, [rekap]);

    const pimpinanChartsData = useMemo(() => {
        if (!rekap || rekap.data.length === 0) return null;
        const data = rekap.data;

        // 1. Alokasi Banyaknya Anggota Tim (Team Size Distribution)
        const teamMemberCountsMap = new Map<string, number>();
        data.forEach(u => {
            u.details.forEach(d => {
                const teamName = d.teamName;
                teamMemberCountsMap.set(teamName, (teamMemberCountsMap.get(teamName) || 0) + 1);
            });
        });

        const teamSizeData = Array.from(teamMemberCountsMap.entries())
            .map(([teamName, count]) => ({ 
                teamName, 
                count,
                shortName: teamName.length > 20 ? teamName.substring(0, 20) + '...' : teamName 
            }))
            .sort((a, b) => b.count - a.count)
            .slice(0, 15); // Show top 15 teams by size

        // 2. Distribusi Performa
        let sangatBaik = 0, baik = 0, cukup = 0, kurang = 0, belumDinilai = 0;
        data.forEach(u => {
            if (u.averageScore >= 90) sangatBaik++;
            else if (u.averageScore >= 80) baik++;
            else if (u.averageScore >= 60) cukup++;
            else if (u.averageScore > 0) kurang++;
            else belumDinilai++;
        });
        const perfDistData = [
            { name: 'Sangat Baik (≥90)', value: sangatBaik, fill: '#22c55e' },
            { name: 'Baik (80-89)', value: baik, fill: '#3b82f6' },
            { name: 'Cukup (60-79)', value: cukup, fill: '#f59e0b' },
            { name: 'Kurang (<60)', value: kurang, fill: '#ef4444' },
        ].filter(d => d.value > 0);
        
        if (belumDinilai > 0) {
            perfDistData.push({ name: 'Belum Dinilai', value: belumDinilai, fill: '#94a3b8' });
        }

        // 3. Scatter Data & Quadrants
        const scatterData = data.map(u => ({
            name: u.name,
            x: u.totalTeams,
            y: parseFloat(u.averageScore.toFixed(2)),
            initials: u.name.split(' ').map((n: string) => n[0]).join('').toUpperCase().slice(0, 2)
        }));

        const avgX = data.length > 0 ? data.reduce((acc, u) => acc + u.totalTeams, 0) / data.length : 0;
        const ratedOnly = data.filter(u => u.averageScore > 0);
        const avgY = ratedOnly.length > 0 ? ratedOnly.reduce((acc, u) => acc + u.averageScore, 0) / ratedOnly.length : 0;

        return { scatterData, avgX, avgY, perfDistData, teamSizeData };
    }, [rekap]);

    React.useEffect(() => {
        if (pimpinanTab === 'report' && reportUserId) {
            setReportLoading(true);
            api.get(`/ratings/pegawai-dashboard?userId=${reportUserId}&month=${pegawaiMonth}&year=${pegawaiYear}`)
                .then(res => setReportData(res.data))
                .catch(err => console.error('Failed to fetch report', err))
                .finally(() => setReportLoading(false));
        }
    }, [pimpinanTab, reportUserId, pegawaiMonth, pegawaiYear]);

    return (
        <div className="space-y-6">
            <div className="flex gap-2 p-1 bg-muted w-fit rounded-lg">
                <Button 
                    variant={pimpinanTab === 'overview' ? 'secondary' : 'ghost'} 
                    size="sm" 
                    onClick={() => setPimpinanTab('overview')}
                    className="rounded-md"
                >
                    <BarChart3 className="w-4 h-4 mr-2" /> Ringkasan (Charts)
                </Button>
                <Button 
                    variant={pimpinanTab === 'report' ? 'secondary' : 'ghost'} 
                    size="sm" 
                    onClick={() => setPimpinanTab('report')}
                    className="rounded-md"
                >
                    <ClipboardList className="w-4 h-4 mr-2" /> Report Individu
                </Button>
            </div>

            {pimpinanTab === 'overview' ? (
                <>
                    <div className="grid gap-6 md:grid-cols-2">
                        {/* Alokasi Anggota per Tim Chart */}
                        <Card className="shadow-sm">
                            <CardHeader><CardTitle className="text-sm font-medium">Top 15 Alokasi Anggota per Tim</CardTitle></CardHeader>
                            <CardContent>
                                <div className="h-64">
                                    <ResponsiveContainer width="100%" height="100%">
                                        <BarChart data={pimpinanChartsData?.teamSizeData} layout="vertical">
                                            <CartesianGrid strokeDasharray="3 3" horizontal={true} vertical={false} />
                                            <XAxis type="number" hide />
                                            <YAxis dataKey="shortName" type="category" width={100} tick={{fontSize: 9}} />
                                            <Tooltip formatter={(val) => [`${val} Anggota`, 'Jumlah']} />
                                            <Bar dataKey="count" fill="#0ea5e9" radius={[0, 4, 4, 0]} barSize={20}>
                                                {pimpinanChartsData?.teamSizeData.map((entry, index) => (
                                                    <Cell key={`cell-${index}`} fill={index % 2 === 0 ? '#0ea5e9' : '#0284c7'} />
                                                ))}
                                            </Bar>
                                        </BarChart>
                                    </ResponsiveContainer>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Distribusi Performa Chart */}
                        <Card className="shadow-sm">
                            <CardHeader><CardTitle className="text-sm font-medium">Distribusi Performa</CardTitle></CardHeader>
                            <CardContent>
                                <div className="h-64">
                                    <ResponsiveContainer width="100%" height="100%">
                                        <PieChart>
                                            <Pie 
                                                data={pimpinanChartsData?.perfDistData} 
                                                dataKey="value" 
                                                nameKey="name" 
                                                cx="50%" cy="50%" 
                                                innerRadius={60} 
                                                outerRadius={80} 
                                                paddingAngle={5}
                                            >
                                                {pimpinanChartsData?.perfDistData.map((entry, index) => (
                                                    <Cell key={`cell-${index}`} fill={entry.fill} />
                                                ))}
                                            </Pie>
                                            <Tooltip />
                                            <Legend verticalAlign="bottom" height={36}/>
                                        </PieChart>
                                    </ResponsiveContainer>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Quadrant Chart */}
                    <Card className="shadow-sm">
                        <CardHeader>
                            <CardTitle className="text-sm font-medium flex items-center justify-between">
                                Kinerja vs Beban Kerja (Employee Quad)
                                <div className="flex gap-4 text-[10px] uppercase font-bold tracking-wider">
                                    <div className="flex items-center gap-1"><span className="w-2 h-2 rounded-full bg-blue-500" /> BPS PROV</div>
                                    <div className="flex items-center gap-1"><span className="w-2 h-2 rounded-full bg-red-400" /> Average</div>
                                </div>
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="h-[400px]">
                                <ResponsiveContainer width="100%" height="100%">
                                    <ScatterChart margin={{ top: 20, right: 20, bottom: 20, left: 20 }}>
                                        <CartesianGrid strokeDasharray="3 3" opacity={0.3} />
                                        <XAxis type="number" dataKey="x" name="Jumlah Tim" domain={[0, 'auto']}>
                                            <Label value="Jumlah Penugasan Tim (Beban Kerja)" offset={-10} position="insideBottom" style={{ fontSize: '12px', fill: '#64748b' }} />
                                        </XAxis>
                                        <YAxis type="number" dataKey="y" name="Rata-rata Nilai" domain={[0, 100]}>
                                            <Label value="Rata-rata Nilai Kinerja" angle={-90} position="insideLeft" style={{ fontSize: '12px', fill: '#64748b' }} />
                                        </YAxis>
                                        <ZAxis type="category" dataKey="name" name="Nama" />
                                        <Tooltip cursor={{ strokeDasharray: '3 3' }} />
                                        
                                        {/* Quadrant lines */}
                                        <ReferenceLine x={pimpinanChartsData?.avgX} stroke="#94a3b8" strokeDasharray="3 3" />
                                        <ReferenceLine y={pimpinanChartsData?.avgY} stroke="#94a3b8" strokeDasharray="3 3" />
                                        
                                        <Scatter name="Pegawai" data={pimpinanChartsData?.scatterData} fill="#3b82f6" shape="circle">
                                            {pimpinanChartsData?.scatterData.map((entry, index) => (
                                                <Cell key={`cell-${index}`} />
                                            ))}
                                        </Scatter>
                                    </ScatterChart>
                                </ResponsiveContainer>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Table Filters & Search */}
                    <Card className="shadow-sm">
                        <CardHeader className="pb-3">
                            <div className="flex flex-col md:flex-row justify-between md:items-center gap-4">
                                <CardTitle className="text-lg">Tabel Rekapitulasi Nilai</CardTitle>
                                <div className="flex flex-wrap items-center gap-2">
                                    <div className="relative">
                                        <Search className="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                                        <input 
                                            className="h-9 w-full md:w-64 rounded-md border border-input bg-transparent px-8 py-2 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                                            placeholder="Cari Pegawai..." 
                                            value={pimpinanSearch}
                                            onChange={e => setPimpinanSearch(e.target.value)}
                                        />
                                    </div>
                                    <select 
                                        className="h-9 px-3 border rounded-md text-sm bg-background"
                                        value={pimpinanTeamFilter}
                                        onChange={e => setPimpinanTeamFilter(e.target.value)}
                                    >
                                        <option value="All">Semua Tim</option>
                                        {allTeamsPimpinan.map(t => <option key={t} value={t}>{t}</option>)}
                                    </select>
                                    <select 
                                        className="h-9 px-3 border rounded-md text-sm bg-background"
                                        value={pimpinanTeamStatusFilter}
                                        onChange={e => setPimpinanTeamStatusFilter(e.target.value)}
                                    >
                                        <option value="All">Semua Status Plot</option>
                                        <option value="HasTeam">Sudah Ada Tim</option>
                                        <option value="NoTeam">Belum Ada Tim</option>
                                    </select>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent className="p-0">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead onClick={() => handleSortPimpinan('nip')} className="cursor-pointer">NIP <SortIconPimpinan columnKey="nip" /></TableHead>
                                        <TableHead onClick={() => handleSortPimpinan('name')} className="cursor-pointer">Nama <SortIconPimpinan columnKey="name" /></TableHead>
                                        <TableHead onClick={() => handleSortPimpinan('totalTeams')} className="cursor-pointer text-center">Jml Tim <SortIconPimpinan columnKey="totalTeams" /></TableHead>
                                        <TableHead className="text-center">Rated</TableHead>
                                        <TableHead onClick={() => handleSortPimpinan('averageScore')} className="cursor-pointer text-center font-bold">Avg Score <SortIconPimpinan columnKey="averageScore" /></TableHead>
                                        <TableHead className="text-right">Aksi</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {filteredPimpinanData.map(u => (
                                        <TableRow key={u.id} className="hover:bg-muted/50 transition-colors">
                                            <TableCell className="font-mono text-xs">{u.nip}</TableCell>
                                            <TableCell className="font-medium">{u.name}</TableCell>
                                            <TableCell className="text-center">
                                                <span className="bg-primary/5 text-primary px-2 py-0.5 rounded-full text-xs font-semibold">{u.totalTeams}</span>
                                            </TableCell>
                                            <TableCell className="text-center">
                                                <span className={`px-2 py-0.5 rounded-full text-xs font-semibold ${u.ratedTeams === u.totalTeams ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'}`}>
                                                    {u.ratedTeams}/{u.totalTeams}
                                                </span>
                                            </TableCell>
                                            <TableCell className="text-center">
                                                <span className={`text-lg font-bold ${u.averageScore >= 80 ? 'text-green-600' : u.averageScore >= 60 ? 'text-amber-600' : u.averageScore > 0 ? 'text-red-500' : 'text-muted-foreground'}`}>
                                                    {u.averageScore > 0 ? u.averageScore.toFixed(2) : '-'}
                                                </span>
                                            </TableCell>
                                            <TableCell className="text-right">
                                                <Button size="sm" variant="outline" onClick={() => setPimpinanDetailUser(u)}>Detail</Button>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>
                </>
            ) : (
                <div className="space-y-6">
                    <Card className="shadow-sm">
                        <CardHeader>
                            <CardTitle className="text-lg">Report Kinerja Indiivudu</CardTitle>
                            <CardDescription>Cari pegawai untuk melihat dashboard kinerja pribadinya.</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="relative">
                                <Search className="absolute left-3 top-3 h-5 w-5 text-muted-foreground" />
                                <Input 
                                    className="pl-10 h-12 text-lg shadow-sm" 
                                    placeholder="Cari Nama atau NIP..."
                                    value={reportSearch}
                                    onChange={e => {
                                        setReportSearch(e.target.value);
                                        setShowReportSuggestions(true);
                                    }}
                                />
                                {showReportSuggestions && reportSearch.length > 1 && (
                                    <div className="absolute z-10 w-full mt-2 bg-white border rounded-lg shadow-xl max-h-64 overflow-y-auto anima-in slide-in-from-top-2 duration-200">
                                        {rekap.data
                                            .filter(u => u.name.toLowerCase().includes(reportSearch.toLowerCase()) || u.nip.includes(reportSearch))
                                            .map(u => (
                                                <div 
                                                    key={u.id} 
                                                    className="p-3 hover:bg-muted cursor-pointer flex justify-between items-center"
                                                    onClick={() => {
                                                        setReportUserId(u.id);
                                                        setReportSearch(u.name);
                                                        setShowReportSuggestions(false);
                                                    }}
                                                >
                                                    <div>
                                                        <p className="font-semibold">{u.name}</p>
                                                        <p className="text-xs text-muted-foreground">{u.nip}</p>
                                                    </div>
                                                    <ChevronRight className="w-4 h-4 text-muted-foreground" />
                                                </div>
                                            ))}
                                    </div>
                                )}
                            </div>
                        </CardContent>
                    </Card>

                    {reportLoading ? (
                        <div className="py-20 text-center"><div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto" /></div>
                    ) : reportData ? (
                        <PegawaiDashboardView data={reportData} monthNames={monthNames} />
                    ) : (
                        <div className="bg-muted/30 border border-dashed rounded-xl py-12 text-center text-muted-foreground">
                            Silakan cari dan pilih pegawai untuk menampilkan report.
                        </div>
                    )}
                </div>
            )}

            {/* Individual Detailed Dialog */}
            <Dialog open={pimpinanDetailUser !== null} onOpenChange={() => setPimpinanDetailUser(null)}>
                <DialogContent className="max-w-2xl">
                    <DialogHeader>
                        <DialogTitle>Detail Penilaian: {pimpinanDetailUser?.name}</DialogTitle>
                    </DialogHeader>
                    {pimpinanDetailUser && (
                        <div className="space-y-4">
                            <div className="grid grid-cols-2 gap-4">
                                <div className="p-3 bg-muted rounded-lg">
                                    <p className="text-xs text-muted-foreground uppercase font-bold">NIP</p>
                                    <p className="text-lg font-mono">{pimpinanDetailUser.nip}</p>
                                </div>
                                <div className="p-3 bg-primary/5 rounded-lg border border-primary/10">
                                    <p className="text-xs text-primary/70 uppercase font-bold">Rata-rata Nilai</p>
                                    <p className="text-2xl font-black text-primary">{pimpinanDetailUser.averageScore > 0 ? pimpinanDetailUser.averageScore.toFixed(2) : 'Belum Dinilai'}</p>
                                </div>
                            </div>
                            <Table>
                                <TableHeader><TableRow><TableHead>Nama Tim</TableHead><TableHead>Leader</TableHead><TableHead className="text-right">Skor</TableHead></TableRow></TableHeader>
                                <TableBody>
                                    {pimpinanDetailUser.details.map(d => (
                                        <TableRow key={d.teamId}>
                                            <TableCell className="font-medium">{d.teamName}</TableCell>
                                            <TableCell className="text-muted-foreground text-sm">{d.leaderName}</TableCell>
                                            <TableCell className="text-right font-bold text-lg">{d.score ?? <span className="text-muted-foreground font-normal italic">-</span>}</TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                            <div className="flex justify-end pt-4 gap-2">
                                <Button variant="outline" onClick={() => setPimpinanDetailUser(null)}>Tutup</Button>
                                <Button onClick={() => { 
                                    setReportUserId(pimpinanDetailUser.id); 
                                    setReportSearch(pimpinanDetailUser.name); 
                                    setPimpinanTab('report'); 
                                    setPimpinanDetailUser(null); 
                                }}>Lihat Dashboard Lengkap</Button>
                            </div>
                        </div>
                    )}
                </DialogContent>
            </Dialog>
        </div>
    );
}
