'use client';

import React from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { TrendingUp, Award, Star, BarChart3, UserCheck, Briefcase, ClipboardCheck, Layers, AlertCircle } from 'lucide-react';
import { AreaChart, Area, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, BarChart, Bar, Legend } from 'recharts';

interface PegawaiDashboard {
    month: number;
    year: number;
    user: { id: string; nip: string; name: string; username: string };
    summary: {
        totalTeams: number;
        ratedTeamsThisMonth: number;
        overallAverage: number | null;
        grade: string;
        gradeColor: string;
    };
    teams: { id: string; teamName: string; leaderName: string; memberCount: number }[];
    ratingsDetail: {
        teamName: string;
        evaluatorName: string;
        score: number;
        volumeWork: string | null;
        qualityWork: string | null;
        finalScore: number | null;
        notes: string | null;
    }[];
    scoreHistory: { label: string; month: number; year: number; avgScore: number | null; ratingCount: number }[];
    teamComparison: { teamName: string; myScore: number | null; teamAvg: number | null; totalRated: number }[];
}

interface PegawaiDashboardViewProps {
    data: PegawaiDashboard;
    monthNames: string[];
}

export function PegawaiDashboardView({ data, monthNames }: PegawaiDashboardViewProps) {
    return (
        <div className="space-y-6 animate-in fade-in duration-500">
            {/* Summary Cards */}
            <div className="grid gap-6 md:grid-cols-4">
                {/* Card 1: Nilai Rata-rata */}
                <Card className="shadow-md border-t-4 border-t-primary bg-gradient-to-br from-white to-primary/5 hover:shadow-lg transition-shadow">
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium text-muted-foreground">Nilai Rata-rata</CardTitle>
                        <div className="w-10 h-10 bg-primary/10 rounded-xl flex items-center justify-center">
                            <Star className="w-5 h-5 text-primary" />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div className="text-4xl font-bold tracking-tight">
                            {data.summary.overallAverage !== null ? data.summary.overallAverage : '-'}
                        </div>
                        <p className="text-xs text-muted-foreground mt-1">
                            Periode {monthNames[(data.month || 1) - 1]} {data.year}
                        </p>
                    </CardContent>
                </Card>

                {/* Card 2: Grade Kinerja */}
                <Card className={`shadow-md border-t-4 hover:shadow-lg transition-shadow ${
                    data.summary.gradeColor === 'green' ? 'border-t-green-500 bg-gradient-to-br from-white to-green-50' :
                    data.summary.gradeColor === 'blue' ? 'border-t-blue-500 bg-gradient-to-br from-white to-blue-50' :
                    data.summary.gradeColor === 'amber' ? 'border-t-amber-500 bg-gradient-to-br from-white to-amber-50' :
                    data.summary.gradeColor === 'red' ? 'border-t-red-500 bg-gradient-to-br from-white to-red-50' :
                    'border-t-slate-400 bg-gradient-to-br from-white to-slate-50'
                }`}>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium text-muted-foreground">Grade Kinerja</CardTitle>
                        <div className={`w-10 h-10 rounded-xl flex items-center justify-center ${
                            data.summary.gradeColor === 'green' ? 'bg-green-100' :
                            data.summary.gradeColor === 'blue' ? 'bg-blue-100' :
                            data.summary.gradeColor === 'amber' ? 'bg-amber-100' :
                            data.summary.gradeColor === 'red' ? 'bg-red-100' :
                            'bg-slate-100'
                        }`}>
                            <Award className={`w-5 h-5 ${
                                data.summary.gradeColor === 'green' ? 'text-green-600' :
                                data.summary.gradeColor === 'blue' ? 'text-blue-600' :
                                data.summary.gradeColor === 'amber' ? 'text-amber-600' :
                                data.summary.gradeColor === 'red' ? 'text-red-600' :
                                'text-slate-500'
                            }`} />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div className={`text-2xl font-bold tracking-tight ${
                            data.summary.gradeColor === 'green' ? 'text-green-700' :
                            data.summary.gradeColor === 'blue' ? 'text-blue-700' :
                            data.summary.gradeColor === 'amber' ? 'text-amber-700' :
                            data.summary.gradeColor === 'red' ? 'text-red-700' :
                            'text-slate-500'
                        }`}>
                            {data.summary.grade}
                        </div>
                        <p className="text-xs text-muted-foreground mt-1">
                            {data.summary.overallAverage !== null
                                ? data.summary.overallAverage >= 90 ? '≥ 90: Sangat Baik'
                                : data.summary.overallAverage >= 80 ? '80 - 89: Baik'
                                : data.summary.overallAverage >= 60 ? '60 - 79: Cukup'
                                : '< 60: Perlu Perbaikan'
                                : 'Belum ada penilaian'
                            }
                        </p>
                    </CardContent>
                </Card>

                {/* Card 3: Total Tim */}
                <Card className="shadow-md border-t-4 border-t-indigo-500 bg-gradient-to-br from-white to-indigo-50 hover:shadow-lg transition-shadow">
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium text-muted-foreground">Total Tim</CardTitle>
                        <div className="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center">
                            <Briefcase className="w-5 h-5 text-indigo-600" />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div className="text-4xl font-bold tracking-tight text-indigo-700">
                            {data.summary.totalTeams}
                        </div>
                        <p className="text-xs text-muted-foreground mt-1">Total tim yang diikuti</p>
                    </CardContent>
                </Card>

                {/* Card 4: Status Penilaian */}
                <Card className="shadow-md border-t-4 border-t-emerald-500 bg-gradient-to-br from-white to-emerald-50 hover:shadow-lg transition-shadow">
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium text-muted-foreground">Status Penilaian</CardTitle>
                        <div className="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center">
                            <UserCheck className="w-5 h-5 text-emerald-600" />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div className="text-4xl font-bold tracking-tight text-emerald-700">
                            {data.summary.ratedTeamsThisMonth}/{data.summary.totalTeams}
                        </div>
                        <p className="text-xs text-muted-foreground mt-1">
                            Tim yang sudah dinilai
                        </p>
                    </CardContent>
                </Card>
            </div>

            {/* Charts Row */}
            <div className="grid gap-6 md:grid-cols-2">
                {/* Score Trend Chart */}
                <Card className="shadow-md">
                    <CardHeader className="pb-2">
                        <CardTitle className="text-base flex items-center gap-2">
                            <TrendingUp className="w-5 h-5 text-primary" /> Tren Nilai 6 Bulan Terakhir
                        </CardTitle>
                        <CardDescription>Rata-rata nilai akhir per bulan</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="h-64">
                            <ResponsiveContainer width="100%" height="100%">
                                <AreaChart data={data.scoreHistory} margin={{ top: 10, right: 10, left: 0, bottom: 0 }}>
                                    <defs>
                                        <linearGradient id="gradientScore" x1="0" y1="0" x2="0" y2="1">
                                            <stop offset="5%" stopColor="hsl(var(--primary))" stopOpacity={0.3} />
                                            <stop offset="95%" stopColor="hsl(var(--primary))" stopOpacity={0} />
                                        </linearGradient>
                                    </defs>
                                    <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#e2e8f0" />
                                    <XAxis dataKey="label" tick={{ fontSize: 11 }} />
                                    <YAxis domain={[0, 100]} tick={{ fontSize: 11 }} />
                                    <Tooltip
                                        formatter={(value: any) => value !== null ? [value, 'Nilai'] : ['Belum dinilai', '']}
                                        contentStyle={{ borderRadius: 8, border: '1px solid #e2e8f0', boxShadow: '0 2px 8px rgba(0,0,0,0.08)' }}
                                    />
                                    <Area type="monotone" dataKey="avgScore" stroke="hsl(var(--primary))" fill="url(#gradientScore)" strokeWidth={2.5} dot={{ r: 4, fill: 'hsl(var(--primary))' }} connectNulls />
                                </AreaChart>
                            </ResponsiveContainer>
                        </div>
                    </CardContent>
                </Card>

                {/* Team Comparison Chart */}
                <Card className="shadow-md">
                    <CardHeader className="pb-2">
                        <CardTitle className="text-base flex items-center gap-2">
                            <BarChart3 className="w-5 h-5 text-indigo-500" /> Perbandingan dengan Tim
                        </CardTitle>
                        <CardDescription>Nilai Pribadi vs Rata-rata Tim ({monthNames[(data.month || 1) - 1]} {data.year})</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="h-64">
                            {data.teamComparison.length > 0 ? (
                                <ResponsiveContainer width="100%" height="100%">
                                    <BarChart data={data.teamComparison} margin={{ top: 10, right: 10, left: 0, bottom: 0 }}>
                                        <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#e2e8f0" />
                                        <XAxis dataKey="teamName" tick={{ fontSize: 11 }} />
                                        <YAxis domain={[0, 100]} tick={{ fontSize: 11 }} />
                                        <Tooltip contentStyle={{ borderRadius: 8, border: '1px solid #e2e8f0', boxShadow: '0 2px 8px rgba(0,0,0,0.08)' }} />
                                        <Bar dataKey="myScore" fill="hsl(var(--primary))" name="Nilai Pegawai" radius={[4, 4, 0, 0]} barSize={30} />
                                        <Bar dataKey="teamAvg" fill="#94a3b8" name="Rata-rata Tim" radius={[4, 4, 0, 0]} barSize={30} />
                                        <Legend />
                                    </BarChart>
                                </ResponsiveContainer>
                            ) : (
                                <div className="flex items-center justify-center h-full text-muted-foreground text-sm">
                                    Belum ada data tim.
                                </div>
                            )}
                        </div>
                    </CardContent>
                </Card>
            </div>

            {/* Detail Table */}
            {data.ratingsDetail.length > 0 && (
                <Card className="shadow-md">
                    <CardHeader className="pb-3 border-b">
                        <CardTitle className="text-base flex items-center gap-2">
                            <ClipboardCheck className="w-5 h-5 text-green-600" /> Detail Penilaian Kinerja
                        </CardTitle>
                        <CardDescription>Rincian penilaian dari setiap tim pada {monthNames[(data.month || 1) - 1]} {data.year}</CardDescription>
                    </CardHeader>
                    <CardContent className="p-0">
                        <div className="overflow-x-auto">
                            <Table>
                                <TableHeader className="bg-slate-50/50">
                                    <TableRow>
                                        <TableHead>Tim</TableHead>
                                        <TableHead>Penilai</TableHead>
                                        <TableHead className="text-center">Nilai Dasar</TableHead>
                                        <TableHead className="text-center">Volume</TableHead>
                                        <TableHead className="text-center">Kualitas</TableHead>
                                        <TableHead className="text-center font-semibold text-primary">Nilai Akhir</TableHead>
                                        <TableHead>Catatan</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {data.ratingsDetail.map((r, i) => (
                                        <TableRow key={i} className="hover:bg-slate-50">
                                            <TableCell className="font-medium text-slate-700">{r.teamName}</TableCell>
                                            <TableCell className="text-muted-foreground">{r.evaluatorName}</TableCell>
                                            <TableCell className="text-center">{r.score}</TableCell>
                                            <TableCell className="text-center">
                                                <span className={`px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider ${
                                                    r.volumeWork === 'Berat' ? 'bg-red-100 text-red-700' :
                                                    r.volumeWork === 'Sedang' ? 'bg-amber-100 text-amber-700' :
                                                    r.volumeWork === 'Ringan' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500'
                                                }`}>
                                                    {r.volumeWork || 'N/A'}
                                                </span>
                                            </TableCell>
                                            <TableCell className="text-center">
                                                <span className={`px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider ${
                                                    r.qualityWork === 'Sangat Baik' ? 'bg-green-100 text-green-700' :
                                                    r.qualityWork === 'Baik' ? 'bg-blue-100 text-blue-700' :
                                                    r.qualityWork === 'Cukup' ? 'bg-amber-100 text-amber-700' :
                                                    r.qualityWork === 'Kurang' ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-500'
                                                }`}>
                                                    {r.qualityWork || 'N/A'}
                                                </span>
                                            </TableCell>
                                            <TableCell className="text-center">
                                                <span className="font-bold text-lg text-primary">{r.finalScore !== null ? r.finalScore : '-'}</span>
                                            </TableCell>
                                            <TableCell className="text-muted-foreground text-sm max-w-[200px] truncate" title={r.notes || ''}>
                                                {r.notes || <span className="text-slate-300 italic">Tidak ada catatan</span>}
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </div>
                    </CardContent>
                </Card>
            )}

            {data.ratingsDetail.length === 0 && (
                <Card className="shadow-sm border-dashed border-2">
                    <CardContent className="py-12 text-center">
                        <AlertCircle className="w-12 h-12 text-slate-300 mx-auto mb-4" />
                        <p className="text-lg font-medium text-slate-500">Belum Ada Penilaian</p>
                        <p className="text-sm text-muted-foreground mt-1">Data penilaian untuk periode ini belum tersedia.</p>
                    </CardContent>
                </Card>
            )}

            {/* Team Cards */}
            <Card className="shadow-md">
                <CardHeader className="pb-3 border-b">
                    <CardTitle className="text-base flex items-center gap-2">
                        <Layers className="w-5 h-5 text-indigo-500" /> Kontribusi dalam Tim
                    </CardTitle>
                    <CardDescription>Semua tim yang melibatkan pegawai ini</CardDescription>
                </CardHeader>
                <CardContent className="pt-4">
                    <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                        {data.teams.map(team => (
                            <div key={team.id} className="p-4 rounded-xl border bg-gradient-to-br from-white to-slate-50 hover:shadow-md transition-all group">
                                <div className="flex items-start justify-between">
                                    <div>
                                        <h4 className="font-semibold text-base group-hover:text-primary transition-colors">{team.teamName}</h4>
                                        <p className="text-xs text-muted-foreground mt-0.5">Ketua: {team.leaderName}</p>
                                    </div>
                                    <span className="px-2.5 py-1 bg-indigo-100 text-indigo-700 text-[10px] font-bold rounded-full uppercase">
                                        {team.memberCount} Anggota
                                    </span>
                                </div>
                                {(() => {
                                    const comp = data.teamComparison.find(c => c.teamName === team.teamName);
                                    if (!comp || comp.myScore === null) {
                                        return <p className="text-xs text-muted-foreground mt-3 pt-3 border-t italic">Belum ada nilai di tim ini</p>;
                                    }
                                    return (
                                        <div className="mt-3 pt-3 border-t flex items-center justify-between">
                                            <div>
                                                <p className="text-[10px] text-muted-foreground uppercase font-bold tracking-wider">Nilai</p>
                                                <p className="text-xl font-bold text-primary">{comp.myScore}</p>
                                            </div>
                                            <div className="text-right">
                                                <p className="text-[10px] text-muted-foreground uppercase font-bold tracking-wider">Rata Tim</p>
                                                <p className="text-xl font-bold text-slate-400">{comp.teamAvg || '-'}</p>
                                            </div>
                                            {comp.myScore !== null && comp.teamAvg !== null && (
                                                <div className={`px-2 py-1 rounded-lg text-[10px] font-black uppercase tracking-tighter ${
                                                    comp.myScore >= comp.teamAvg ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'
                                                }`}>
                                                    {comp.myScore >= comp.teamAvg ? '↑ High' : '↓ Low'}
                                                </div>
                                            )}
                                        </div>
                                    );
                                })()}
                            </div>
                        ))}
                        {data.teams.length === 0 && (
                            <div className="col-span-full text-center py-8 text-muted-foreground italic">
                                Belum tergabung dalam tim manapun.
                            </div>
                        )}
                    </div>
                </CardContent>
            </Card>
        </div>
    );
}
