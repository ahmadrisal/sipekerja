'use client';

import React, { useState } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { TrendingUp, Users, AlertCircle, ClipboardCheck, Layers } from 'lucide-react';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, Cell, LabelList } from 'recharts';
import { useRouter } from 'next/navigation';

export interface TeamDetail {
    id: string;
    teamName: string;
    members: { id: string; nip: string; name: string }[];
}

export interface UnratedMember {
    id: string;
    nip: string;
    name: string;
    teams: string[];
}

export interface KetuaTimStats {
    teamCount: number;
    uniqueMemberCount: number;
    ratedCount: number;
    unratedCount: number;
    currentMonth: number;
    currentYear: number;
    teamDetails: TeamDetail[];
    unratedMembers: UnratedMember[];
    teamChartData: { teamName: string; avgScore: number; totalRated: number }[];
}

interface KetuaTimDashboardProps {
    stats: KetuaTimStats;
    monthNames: string[];
    ketuaMonth: number;
    ketuaYear: number;
}

export function KetuaTimDashboard({ stats, monthNames, ketuaMonth, ketuaYear }: KetuaTimDashboardProps) {
    const router = useRouter();
    const [showTeamsDialog, setShowTeamsDialog] = useState(false);
    const [showMembersDialog, setShowMembersDialog] = useState(false);
    const [showUnratedDialog, setShowUnratedDialog] = useState(false);

    return (
        <div className="space-y-6">
            <div className="grid gap-6 md:grid-cols-3">
                {/* Card 1: Tim yang Dipimpin */}
                <Card
                    className="shadow-md hover:shadow-lg transition-shadow border-t-4 border-t-primary cursor-pointer hover:ring-2 hover:ring-primary/30"
                    onClick={() => setShowTeamsDialog(true)}
                >
                    <CardHeader className="flex space-y-0 flex-row items-center justify-between pb-2">
                        <CardTitle className="text-sm font-medium">Tim yang Dipimpin</CardTitle>
                        <Layers className="w-4 h-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-3xl font-bold">{stats.teamCount}</div>
                        <p className="text-xs text-muted-foreground mt-1">Klik untuk melihat daftar tim</p>
                    </CardContent>
                </Card>

                {/* Card 2: Total Anggota Tim */}
                <Card
                    className="shadow-md hover:shadow-lg transition-shadow border-t-4 border-t-green-500 cursor-pointer hover:ring-2 hover:ring-green-300"
                    onClick={() => setShowMembersDialog(true)}
                >
                    <CardHeader className="flex space-y-0 flex-row items-center justify-between pb-2">
                        <CardTitle className="text-sm font-medium">Total Anggota Tim</CardTitle>
                        <Users className="w-4 h-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-3xl font-bold">{stats.uniqueMemberCount}</div>
                        <p className="text-xs text-muted-foreground mt-1">Klik untuk melihat detail anggota</p>
                    </CardContent>
                </Card>

                {/* Card 3: Belum Dinilai */}
                <Card
                    className={`shadow-md hover:shadow-lg transition-shadow border-t-4 cursor-pointer ${stats.unratedCount > 0 ? 'border-t-amber-500 hover:ring-2 hover:ring-amber-300' : 'border-t-green-500 hover:ring-2 hover:ring-green-300'}`}
                    onClick={() => stats.unratedCount > 0 ? setShowUnratedDialog(true) : router.push(`/penilaian?month=${ketuaMonth}&year=${ketuaYear}`)}
                >
                    <CardHeader className="flex space-y-0 flex-row items-center justify-between pb-2">
                        <CardTitle className="text-sm font-medium">Belum Dinilai Bulan Ini</CardTitle>
                        {stats.unratedCount > 0
                            ? <AlertCircle className="w-4 h-4 text-amber-500" />
                            : <ClipboardCheck className="w-4 h-4 text-green-500" />
                        }
                    </CardHeader>
                    <CardContent>
                        <div className={`text-3xl font-bold ${stats.unratedCount > 0 ? 'text-amber-600' : 'text-green-600'}`}>
                            {stats.unratedCount}
                        </div>
                        <p className="text-xs text-muted-foreground mt-1">
                            {stats.unratedCount > 0
                                ? `Klik untuk melihat siapa saja`
                                : `Semua sudah dinilai ✓`
                            }
                        </p>
                    </CardContent>
                </Card>
            </div>

            {/* Progress bar */}
            <Card className="shadow-sm">
                <CardContent className="py-4">
                    <div className="flex items-center justify-between">
                        <p className="text-sm text-muted-foreground">
                            Progres Penilaian — <strong>{monthNames[ketuaMonth - 1]} {ketuaYear}</strong>
                        </p>
                        <p className="text-sm font-semibold">
                            {stats.ratedCount} / {stats.uniqueMemberCount} dinilai
                        </p>
                    </div>
                    <div className="w-full bg-muted rounded-full h-3 mt-2">
                        <div
                            className="bg-primary h-3 rounded-full transition-all duration-500"
                            style={{ width: stats.uniqueMemberCount > 0 ? `${(stats.ratedCount / stats.uniqueMemberCount) * 100}%` : '0%' }}
                        />
                    </div>
                </CardContent>
            </Card>

            {/* Chart: Rata-rata Nilai per Tim */}
            {stats.teamChartData.length > 0 && (
                <Card className="shadow-sm">
                    <CardHeader>
                        <CardTitle className="text-base flex items-center gap-2">
                            <TrendingUp className="w-5 h-5 text-primary" />
                            Rata-Rata Nilai Anggota per Tim — {monthNames[ketuaMonth - 1]} {ketuaYear}
                        </CardTitle>
                        <CardDescription>Perbandingan skor rata-rata kinerja anggota di setiap tim yang Anda pimpin.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <ResponsiveContainer width="100%" height={280}>
                            <BarChart data={stats.teamChartData} margin={{ top: 10, right: 20, left: 0, bottom: 5 }}>
                                <CartesianGrid strokeDasharray="3 3" stroke="#e5e7eb" />
                                <XAxis dataKey="teamName" tick={{ fontSize: 13, fontWeight: 600 }} />
                                <YAxis domain={[0, 100]} tick={{ fontSize: 12 }} />
                                <Tooltip
                                    formatter={(value: any, name: any) => [value, name === 'avgScore' ? 'Rata-Rata' : name]}
                                    contentStyle={{ borderRadius: '8px', border: '1px solid #e5e7eb' }}
                                    cursor={{ fill: 'rgba(0,0,0,0.04)' }}
                                />
                                <Bar dataKey="avgScore" radius={[6, 6, 0, 0]} maxBarSize={80}>
                                    {stats.teamChartData.map((entry, index) => {
                                        const color = entry.avgScore >= 80 ? '#22c55e' : entry.avgScore >= 60 ? '#f59e0b' : '#ef4444';
                                        return <Cell key={index} fill={color} />;
                                    })}
                                    <LabelList dataKey="avgScore" position="top" style={{ fontSize: 14, fontWeight: 700 }} />
                                </Bar>
                            </BarChart>
                        </ResponsiveContainer>
                        <div className="flex items-center justify-center gap-6 mt-2 text-xs text-muted-foreground">
                            <span className="flex items-center gap-1"><span className="w-3 h-3 rounded-sm bg-green-500 inline-block" /> ≥ 80 (Baik)</span>
                            <span className="flex items-center gap-1"><span className="w-3 h-3 rounded-sm bg-amber-500 inline-block" /> 60–79 (Cukup)</span>
                            <span className="flex items-center gap-1"><span className="w-3 h-3 rounded-sm bg-red-500 inline-block" /> &lt; 60 (Kurang)</span>
                        </div>
                    </CardContent>
                </Card>
            )}

            {stats.unratedCount > 0 && (
                <div className="flex justify-center">
                    <Button size="lg" className="flex gap-2" onClick={() => router.push('/penilaian')}>
                        <ClipboardCheck className="w-5 h-5" /> Lanjutkan Penilaian Kinerja
                    </Button>
                </div>
            )}

            {/* ---- Dialog 1: Daftar Tim ---- */}
            <Dialog open={showTeamsDialog} onOpenChange={setShowTeamsDialog}>
                <DialogContent className="max-w-md">
                    <DialogHeader>
                        <DialogTitle>Tim yang Anda Pimpin</DialogTitle>
                    </DialogHeader>
                    <div className="space-y-3 mt-2">
                        {stats.teamDetails.map((t, i) => (
                            <div key={t.id} className="flex items-center gap-3 p-3 rounded-lg bg-muted/50">
                                <span className="text-lg font-bold text-primary">{i + 1}.</span>
                                <div>
                                    <p className="font-semibold">{t.teamName}</p>
                                    <p className="text-xs text-muted-foreground">{t.members.length} anggota</p>
                                </div>
                            </div>
                        ))}
                    </div>
                </DialogContent>
            </Dialog>

            {/* ---- Dialog 2: Detail Anggota per Tim ---- */}
            <Dialog open={showMembersDialog} onOpenChange={setShowMembersDialog}>
                <DialogContent className="max-w-lg">
                    <DialogHeader>
                        <DialogTitle>Anggota Tim</DialogTitle>
                    </DialogHeader>
                    <div className="mt-2 border rounded-lg overflow-hidden">
                        <table className="w-full text-sm">
                            <thead>
                                <tr className="bg-muted">
                                    <th className="text-left px-4 py-2 font-medium">Nama Tim</th>
                                    <th className="text-left px-4 py-2 font-medium">Anggota</th>
                                </tr>
                            </thead>
                            <tbody>
                                {stats.teamDetails.map(t => (
                                    <tr key={t.id} className="border-t">
                                        <td className="px-4 py-3 font-semibold align-top">{t.teamName}</td>
                                        <td className="px-4 py-3">
                                            {t.members.length > 0
                                                ? t.members.map(m => m.name).join(', ')
                                                : <span className="text-muted-foreground italic">Belum ada anggota</span>
                                            }
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </DialogContent>
            </Dialog>

            {/* ---- Dialog 3: Belum Dinilai ---- */}
            <Dialog open={showUnratedDialog} onOpenChange={setShowUnratedDialog}>
                <DialogContent className="max-w-lg">
                    <DialogHeader>
                        <DialogTitle>Pegawai Belum Dinilai — {monthNames[ketuaMonth - 1]} {ketuaYear}</DialogTitle>
                    </DialogHeader>
                    <div className="space-y-3 mt-2">
                        {stats.unratedMembers.map(m => (
                            <div key={m.id} className="flex items-center justify-between p-3 rounded-lg bg-amber-50 border border-amber-200">
                                <div>
                                    <p className="font-semibold">{m.name}</p>
                                    <p className="text-xs text-muted-foreground">NIP: {m.nip}</p>
                                </div>
                                <span className="text-xs bg-amber-100 text-amber-700 px-2 py-1 rounded-full">
                                    {m.teams.join(', ')}
                                </span>
                            </div>
                        ))}
                    </div>
                    <div className="flex justify-end mt-4">
                        <Button onClick={() => { setShowUnratedDialog(false); router.push(`/penilaian?month=${ketuaMonth}&year=${ketuaYear}`); }}>
                            Mulai Menilai
                        </Button>
                    </div>
                </DialogContent>
            </Dialog>
        </div>
    );
}
