'use client';

import React, { useState, useEffect } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Input } from '@/components/ui/input';
import { Users, Layers, AlertCircle, Search } from 'lucide-react';
import { useRouter } from 'next/navigation';

export interface AdminStats {
    stats: {
        totalUsers: number;
        totalTeams: number;
        unassignedUsersCount: number;
        largestTeam: { teamName: string; count: number };
        avgMembersPerTeam: string;
        mostTeamsEmployee: { name: string; count: number };
        minTeamsPerEmployee: number;
        avgTeamsPerEmployee: string;
    };
    userDetails: { id: string; nip: string; name: string; teamNames: string[] }[];
    teamDetails: { id: string; teamName: string; memberCount: number; members: string[] }[];
    unassignedUsers: { id: string; nip: string; name: string }[];
}

interface AdminDashboardProps {
    stats: AdminStats;
}

export function AdminDashboard({ stats }: AdminDashboardProps) {
    const router = useRouter();
    const [adminDialogType, setAdminDialogType] = useState<'users' | 'teams' | 'unassigned' | null>(null);
    const [searchQuery, setSearchQuery] = useState('');

    useEffect(() => {
        if (!adminDialogType) setSearchQuery('');
    }, [adminDialogType]);

    return (
        <div className="space-y-6">
            <div className="grid gap-6 md:grid-cols-3">
                <Card className="shadow-md hover:shadow-lg transition-all border-t-4 border-t-blue-500 cursor-pointer" onClick={() => setAdminDialogType('users')}>
                    <CardHeader className="flex space-y-0 flex-row items-center justify-between pb-2">
                        <CardTitle className="text-sm font-medium">Total Pegawai</CardTitle>
                        <Users className="w-4 h-4 text-blue-500" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-3xl font-bold">{stats.stats.totalUsers}</div>
                        <p className="text-xs text-muted-foreground mt-1">Klik untuk melihat detail & tim</p>
                    </CardContent>
                </Card>

                <Card className="shadow-md hover:shadow-lg transition-all border-t-4 border-t-green-500 cursor-pointer" onClick={() => setAdminDialogType('teams')}>
                    <CardHeader className="flex space-y-0 flex-row items-center justify-between pb-2">
                        <CardTitle className="text-sm font-medium">Total Tim</CardTitle>
                        <Layers className="w-4 h-4 text-green-500" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-3xl font-bold">{stats.stats.totalTeams}</div>
                        <p className="text-xs text-muted-foreground mt-1">Klik untuk melihat anggota tim</p>
                    </CardContent>
                </Card>

                <Card className="shadow-md hover:shadow-lg transition-all border-t-4 border-t-red-500 cursor-pointer" onClick={() => setAdminDialogType('unassigned')}>
                    <CardHeader className="flex space-y-0 flex-row items-center justify-between pb-2">
                        <CardTitle className="text-sm font-medium">Belum Diplot Tim</CardTitle>
                        <AlertCircle className="w-4 h-4 text-red-500" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-3xl font-bold text-red-600">{stats.stats.unassignedUsersCount}</div>
                        <p className="text-xs text-red-500/80 mt-1">Pegawai tanpa tim. Klik untuk plot tim.</p>
                    </CardContent>
                </Card>
            </div>

            <div className="grid gap-6 md:grid-cols-2 mt-6">
                <Card className="shadow-sm">
                    <CardHeader>
                        <CardTitle className="text-base flex items-center gap-2">
                            <Layers className="w-5 h-5 text-primary" /> Statistik Tim
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div className="flex justify-between items-center border-b pb-2">
                            <span className="text-sm text-muted-foreground">Total Tim</span>
                            <span className="font-semibold">{stats.stats.totalTeams}</span>
                        </div>
                        <div className="flex justify-between items-center border-b pb-2">
                            <span className="text-sm text-muted-foreground">Tim Terbesar</span>
                            <span className="font-semibold text-right">
                                {stats.stats.largestTeam.teamName} <span className="text-xs bg-muted px-2 py-0.5 rounded-full ml-1">{stats.stats.largestTeam.count} anggota</span>
                            </span>
                        </div>
                        <div className="flex justify-between items-center pb-1">
                            <span className="text-sm text-muted-foreground">Rata-rata Anggota / Tim</span>
                            <span className="font-semibold">{stats.stats.avgMembersPerTeam}</span>
                        </div>
                    </CardContent>
                </Card>

                <Card className="shadow-sm">
                    <CardHeader>
                        <CardTitle className="text-base flex items-center gap-2">
                            <Users className="w-5 h-5 text-primary" /> Statistik Pegawai
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div className="flex justify-between items-center border-b pb-2">
                            <span className="text-sm text-muted-foreground">Pegawai dg Tim Terbanyak</span>
                            <span className="font-semibold text-right">
                                {stats.stats.mostTeamsEmployee.name} <span className="text-xs bg-muted px-2 py-0.5 rounded-full ml-1">{stats.stats.mostTeamsEmployee.count} tim</span>
                            </span>
                        </div>
                        <div className="flex justify-between items-center border-b pb-2">
                            <span className="text-sm text-muted-foreground">Rata-rata Tim / Pegawai</span>
                            <span className="font-semibold">{stats.stats.avgTeamsPerEmployee}</span>
                        </div>
                        <div className="flex justify-between items-center pb-1">
                            <span className="text-sm text-muted-foreground">Minimal Tim / Pegawai</span>
                            <span className="font-semibold">{stats.stats.minTeamsPerEmployee}</span>
                        </div>
                    </CardContent>
                </Card>
            </div>

            {/* Admin Dialogs */}
            <Dialog open={adminDialogType !== null} onOpenChange={(open) => !open && setAdminDialogType(null)}>
                <DialogContent className="max-w-2xl max-h-[85vh] overflow-y-auto">
                    {adminDialogType === 'users' && (
                        <>
                            <DialogHeader><DialogTitle>Daftar Pegawai & Plot Tim</DialogTitle></DialogHeader>
                            <div className="relative mb-2 mt-2">
                                <Search className="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground" />
                                <Input placeholder="Cari nama atau NIP pegawai..." value={searchQuery} onChange={(e) => setSearchQuery(e.target.value)} className="pl-9" />
                            </div>
                            <Table>
                                <TableHeader><TableRow><TableHead>NIP</TableHead><TableHead>Nama</TableHead><TableHead>Tim</TableHead></TableRow></TableHeader>
                                <TableBody>
                                    {stats.userDetails
                                        .filter(u => u.name.toLowerCase().includes(searchQuery.toLowerCase()) || u.nip.includes(searchQuery))
                                        .map(u => (
                                            <TableRow key={u.id}>
                                                <TableCell>{u.nip}</TableCell>
                                                <TableCell className="font-medium">{u.name}</TableCell>
                                                <TableCell>
                                                    {u.teamNames.length > 0 ? (
                                                        <div className="flex flex-wrap gap-1">
                                                            {u.teamNames.map(t => <span key={t} className="text-xs bg-primary/10 text-primary px-2 py-1 rounded-md">{t}</span>)}
                                                        </div>
                                                    ) : <span className="text-xs text-red-500 bg-red-50 px-2 py-1 rounded-md">Belum diplot</span>}
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                </TableBody>
                            </Table>
                        </>
                    )}

                    {adminDialogType === 'teams' && (
                        <>
                            <DialogHeader><DialogTitle>Daftar Tim & Anggota</DialogTitle></DialogHeader>
                            <div className="relative mb-2 mt-2">
                                <Search className="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground" />
                                <Input placeholder="Cari nama tim..." value={searchQuery} onChange={(e) => setSearchQuery(e.target.value)} className="pl-9" />
                            </div>
                            <Table>
                                <TableHeader><TableRow><TableHead>Nama Tim</TableHead><TableHead>Jml Anggota</TableHead><TableHead>Daftar Anggota</TableHead></TableRow></TableHeader>
                                <TableBody>
                                    {stats.teamDetails
                                        .filter(t => t.teamName.toLowerCase().includes(searchQuery.toLowerCase()))
                                        .map(t => (
                                            <TableRow key={t.id}>
                                                <TableCell className="font-medium">{t.teamName}</TableCell>
                                                <TableCell>{t.memberCount}</TableCell>
                                                <TableCell className="text-sm text-muted-foreground">{t.members.join(', ')}</TableCell>
                                            </TableRow>
                                        ))}
                                </TableBody>
                            </Table>
                        </>
                    )}

                    {adminDialogType === 'unassigned' && (
                        <>
                            <DialogHeader><DialogTitle>Pegawai Belum Diplot</DialogTitle><DialogDescription>Daftar pegawai yang belum memiliki tim. Klik tombok untuk berpindah ke halaman Manajemen Tim.</DialogDescription></DialogHeader>
                            <div className="relative mb-2 mt-2">
                                <Search className="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground" />
                                <Input placeholder="Cari nama atau NIP pegawai..." value={searchQuery} onChange={(e) => setSearchQuery(e.target.value)} className="pl-9" />
                            </div>
                            {stats.unassignedUsers.length > 0 ? (
                                <div className="space-y-4">
                                    <ul className="space-y-2">
                                        {stats.unassignedUsers
                                            .filter(u => u.name.toLowerCase().includes(searchQuery.toLowerCase()) || u.nip.includes(searchQuery))
                                            .map(u => (
                                                <li key={u.id} className="flex justify-between items-center p-3 bg-muted/50 rounded-lg border">
                                                    <span><strong>{u.name}</strong> <span className="text-muted-foreground text-sm">({u.nip})</span></span>
                                                </li>
                                            ))}
                                    </ul>
                                    <div className="flex justify-end pt-4">
                                        <Button onClick={() => router.push('/teams')}>Manajemen Tim</Button>
                                    </div>
                                </div>
                            ) : (
                                <div className="text-center py-6 text-muted-foreground">Semua pegawai sudah terplot ke dalam tim.</div>
                            )}
                        </>
                    )}
                </DialogContent>
            </Dialog>
        </div>
    );
}
