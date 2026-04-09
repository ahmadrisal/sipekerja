'use client';

import { useEffect, useState, useCallback, Fragment } from 'react';
import { useAuthStore } from '@/store/auth.store';
import { useRouter, useSearchParams } from 'next/navigation';
import api from '@/lib/axios';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog';
import { ClipboardList, CheckCircle2, AlertCircle, Save, AlertTriangle } from 'lucide-react';

interface MemberToRate {
    id: string;
    nip: string;
    name: string;
    teams: { id: string; teamName: string }[];
}

interface ExistingRating {
    id: string;
    targetUser: { id: string; name: string; nip: string };
    team: { id: string; teamName: string };
    score: number;
    volumeWork: string | null;
    qualityWork: string | null;
    finalScore: number | null;
    notes: string | null;
}

interface FormEntry {
    score: string;
    volumeWork: string;
    qualityWork: string;
    notes: string;
    isDirty: boolean;
}

export default function RatingPage() {
    const { user, activeRole } = useAuthStore();
    const router = useRouter();
    const searchParams = useSearchParams();
    const [members, setMembers] = useState<MemberToRate[]>([]);
    const [existingRatings, setExistingRatings] = useState<ExistingRating[]>([]);
    const [loading, setLoading] = useState(true);

    // Read month/year from URL query params (synced from dashboard), fallback to current
    const now = new Date();
    const qMonth = searchParams.get('month');
    const qYear = searchParams.get('year');
    const [month, setMonth] = useState(qMonth ? Number(qMonth) : now.getMonth() + 1);
    const [year, setYear] = useState(qYear ? Number(qYear) : now.getFullYear());

    // Validation dialog state
    const [validationDialog, setValidationDialog] = useState(false);
    const [validationMessages, setValidationMessages] = useState<string[]>([]);

    // Form state per member-team mapping: { ["userId_teamId"]: FormEntry }
    const [formState, setFormState] = useState<Record<string, FormEntry>>({});
    const [submitting, setSubmitting] = useState<Record<string, boolean>>({});

    const fetchData = useCallback(async () => {
        try {
            const [membersRes, ratingsRes] = await Promise.all([
                api.get('/ratings/my-members'),
                api.get(`/ratings/my-ratings?month=${month}&year=${year}`),
            ]);
            setMembers(membersRes.data);
            setExistingRatings(ratingsRes.data);

            const init: Record<string, FormEntry> = {};
            membersRes.data.forEach((m: MemberToRate) => {
                m.teams.forEach(t => {
                    const key = `${m.id}_${t.id}`;
                    const existing = ratingsRes.data.find((r: ExistingRating) => r.targetUser.id === m.id && r.team.id === t.id);
                    if (existing) {
                        init[key] = { 
                            score: String(existing.score), 
                            volumeWork: existing.volumeWork || '', 
                            qualityWork: existing.qualityWork || '', 
                            notes: existing.notes || '', 
                            isDirty: false 
                        };
                    } else {
                        init[key] = { score: '', volumeWork: '', qualityWork: '', notes: '', isDirty: false };
                    }
                });
            });
            setFormState(init);
        } catch (err) {
            console.error(err);
        } finally {
            setLoading(false);
        }
    }, [month, year]);

    useEffect(() => {
        if (!user) { router.push('/login'); return; }
        if (activeRole !== 'Ketua Tim') { router.push('/dashboard'); return; }
        fetchData();
    }, [user, activeRole, router, fetchData]);

    // Sync URL params when month/year changes (so user can share/bookmark)
    useEffect(() => {
        const url = new URL(window.location.href);
        url.searchParams.set('month', String(month));
        url.searchParams.set('year', String(year));
        window.history.replaceState({}, '', url.toString());
    }, [month, year]);

    const handleSaveRating = async (memberId: string, teamId: string) => {
        const key = `${memberId}_${teamId}`;
        const entry = formState[key];
        
        // Validate all 3 required fields
        const missing: string[] = [];
        if (!entry || !entry.score) missing.push('Nilai Dasar');
        if (!entry || !entry.volumeWork) missing.push('Volume/Tingkat Kesulitan Pekerjaan');
        if (!entry || !entry.qualityWork) missing.push('Kualitas Pekerjaan');

        if (missing.length > 0) {
            setValidationMessages(missing);
            setValidationDialog(true);
            return;
        }

        const score = Number(entry.score);
        if (score < 1 || score > 100) { 
            setValidationMessages(['Nilai Dasar harus antara 1 - 100.']);
            setValidationDialog(true);
            return; 
        }

        setSubmitting(prev => ({ ...prev, [key]: true }));
        try {
            const existing = existingRatings.find(r => r.targetUser.id === memberId && r.team.id === teamId);
            if (existing) {
                // Update (PUT)
                await api.put(`/ratings/${existing.id}`, {
                    score,
                    volumeWork: entry.volumeWork || null,
                    qualityWork: entry.qualityWork || null,
                    notes: entry.notes || null,
                });
            } else {
                // Create (POST)
                await api.post('/ratings', {
                    targetUserId: memberId,
                    teamId: teamId,
                    score,
                    volumeWork: entry.volumeWork || null,
                    qualityWork: entry.qualityWork || null,
                    notes: entry.notes || null,
                    periodMonth: month,
                    periodYear: year,
                });
            }
            await fetchData();
        } catch (err: any) {
            const msg = err?.response?.data?.error || 'Gagal menyimpan penilaian.';
            setValidationMessages([msg]);
            setValidationDialog(true);
        } finally {
            setSubmitting(prev => ({ ...prev, [key]: false }));
        }
    };

    const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    // Progress counting: A user is rated if ALL their teams are rated. 
    // Or we could count total teams instead of members. Let's count total teams to rate.
    const totalTeamsToRate = members.reduce((sum, m) => sum + m.teams.length, 0);
    const totalRatedTeams = existingRatings.length;

    const calculateLiveScore = (score: string, volumeWork: string, qualityWork: string) => {
        if (!score) return '-';
        let volScore = 80;
        if (volumeWork === 'Ringan') volScore = 60;
        if (volumeWork === 'Berat') volScore = 100;

        let qualScore = 75;
        if (qualityWork === 'Kurang') qualScore = 50;
        if (qualityWork === 'Baik') qualScore = 90;
        if (qualityWork === 'Sangat Baik') qualScore = 100;

        let final = (Number(score) * 0.6) + (volScore * 0.2) + (qualScore * 0.2);
        return (Math.round(final * 100) / 100).toFixed(2);
    };

    if (loading) return <div className="p-8">Memuat data penilaian...</div>;

    const rows = members.flatMap(m => m.teams.map((t, index) => ({
        member: m,
        team: t,
        isFirst: index === 0,
        rowSpan: m.teams.length
    })));

    return (
        <div className="space-y-8 animate-in fade-in zoom-in-95 duration-500">
            <div className="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
                <div>
                    <h2 className="text-3xl font-bold tracking-tight flex items-center gap-2">
                        <ClipboardList className="w-8 h-8 text-primary" /> Penilaian Kinerja
                    </h2>
                    <p className="text-muted-foreground mt-1">Evaluasi bulanan anggota tim secara spesifik per tim.</p>
                </div>
                <div className="flex items-center gap-3">
                    <select
                        className="border rounded-md px-3 py-2 text-sm bg-background shadow-sm"
                        value={month}
                        onChange={e => setMonth(Number(e.target.value))}
                    >
                        {monthNames.map((n, i) => <option key={i} value={i + 1}>{n}</option>)}
                    </select>
                    <Input
                        type="number"
                        className="w-24 shadow-sm"
                        value={year}
                        onChange={e => {
                            const val = Number(e.target.value);
                            if (val > 0) setYear(val);
                        }}
                        onBlur={() => {
                            if (year < 2026) setYear(2026);
                        }}
                        min={2026}
                        max={2030}
                    />
                </div>
            </div>

            {/* Progress Bar */}
            <Card className="shadow-sm">
                <CardContent className="py-4">
                    <div className="flex items-center justify-between">
                        <p className="text-sm text-muted-foreground">
                            Progres Penilaian Tim <strong>{monthNames[month - 1]} {year}</strong>
                        </p>
                        <p className="text-sm font-semibold">
                            {totalRatedTeams} / {totalTeamsToRate} target tim dinilai
                        </p>
                    </div>
                    <div className="w-full bg-muted rounded-full h-2.5 mt-2">
                        <div
                            className="bg-primary h-2.5 rounded-full transition-all duration-500"
                            style={{ width: totalTeamsToRate > 0 ? `${(totalRatedTeams / totalTeamsToRate) * 100}%` : '0%' }}
                        />
                    </div>
                </CardContent>
            </Card>

            {members.length === 0 ? (
                <Card className="shadow-sm">
                    <CardContent className="py-12 text-center text-muted-foreground">
                        Anda belum memimpin tim mana pun, atau belum ada anggota di tim Anda.
                    </CardContent>
                </Card>
            ) : (
                <div className="rounded-xl border bg-card shadow-sm overflow-x-auto">
                    <Table>
                        <TableHeader className="bg-slate-50">
                            <TableRow>
                                <TableHead className="w-[180px]">Pegawai</TableHead>
                                <TableHead className="w-[150px]">Tim</TableHead>
                                <TableHead className="w-[120px] text-center">Nilai Dasar</TableHead>
                                <TableHead className="min-w-[200px]">Volume/Tingkat Kesulitan Pekerjaan</TableHead>
                                <TableHead className="min-w-[320px]">Kualitas Pekerjaan</TableHead>
                                <TableHead className="min-w-[200px]">Catatan</TableHead>
                                <TableHead className="w-[100px] text-center font-bold text-primary">N. Akhir</TableHead>
                                <TableHead className="w-[120px] text-right">Aksi</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {rows.map(({ member: m, team: t, isFirst, rowSpan }) => {
                                const key = `${m.id}_${t.id}`;
                                const isRated = existingRatings.some(r => r.targetUser.id === m.id && r.team.id === t.id);
                                const currentForm = formState[key] || { score: '', volumeWork: '', qualityWork: '', notes: '', isDirty: false };
                                const liveScore = calculateLiveScore(currentForm.score, currentForm.volumeWork, currentForm.qualityWork);

                                // Determine if ALL team entries for this member are rated
                                const allTeamsRated = m.teams.every(mt => 
                                    existingRatings.some(r => r.targetUser.id === m.id && r.team.id === mt.id)
                                );
                                
                                return (
                                    <TableRow 
                                        key={key} 
                                        className={`${isFirst ? "border-t-2" : ""} ${!isRated ? "bg-amber-50/70" : ""} transition-colors duration-300`}
                                    >
                                        {isFirst && (
                                            <TableCell 
                                                rowSpan={rowSpan} 
                                                className={`align-top ${!allTeamsRated ? "bg-amber-50/90 border-l-4 border-l-amber-400" : "bg-slate-50/30 border-l-4 border-l-transparent"} transition-all duration-300`}
                                            >
                                                <div className="font-medium text-slate-900">{m.name}</div>
                                                <div className="text-xs text-muted-foreground">{m.nip}</div>
                                                {!allTeamsRated && (
                                                    <div className="flex items-center gap-1 mt-1.5 text-xs text-amber-600 font-medium">
                                                        <AlertCircle className="w-3 h-3" />
                                                        Belum lengkap
                                                    </div>
                                                )}
                                            </TableCell>
                                        )}
                                        <TableCell className="font-semibold text-slate-700">{t.teamName}</TableCell>
                                        <TableCell>
                                            <Input 
                                                type="number" 
                                                min={1} max={100} 
                                                className="h-8 w-20 mx-auto text-center font-bold" 
                                                placeholder="--"
                                                value={currentForm.score}
                                                onChange={e => setFormState(prev => ({
                                                    ...prev,
                                                    [key]: { ...prev[key], score: e.target.value, isDirty: true }
                                                }))}
                                            />
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex gap-1">
                                                {['Ringan', 'Sedang', 'Berat'].map(v => (
                                                    <Button 
                                                        key={v}
                                                        size="sm" 
                                                        variant={currentForm.volumeWork === v ? "default" : "outline"}
                                                        onClick={() => setFormState(prev => ({ ...prev, [key]: { ...prev[key], volumeWork: v, isDirty: true } }))}
                                                        className="h-7 text-xs px-2"
                                                    >
                                                        {v}
                                                    </Button>
                                                ))}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex gap-1 flex-wrap">
                                                {['Kurang', 'Cukup', 'Baik', 'Sangat Baik'].map(q => (
                                                    <Button 
                                                        key={q}
                                                        size="sm" 
                                                        variant={currentForm.qualityWork === q ? "default" : "outline"}
                                                        onClick={() => setFormState(prev => ({ ...prev, [key]: { ...prev[key], qualityWork: q, isDirty: true } }))}
                                                        className="h-7 text-xs px-2"
                                                    >
                                                        {q}
                                                    </Button>
                                                ))}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <Input 
                                                type="text" 
                                                className="h-8 text-sm" 
                                                placeholder="Komentar..."
                                                value={currentForm.notes}
                                                onChange={e => setFormState(prev => ({
                                                    ...prev,
                                                    [key]: { ...prev[key], notes: e.target.value, isDirty: true }
                                                }))}
                                            />
                                        </TableCell>
                                        <TableCell className="text-center font-bold text-lg text-primary">
                                            {liveScore}
                                        </TableCell>
                                        <TableCell className="text-right">
                                            <Button 
                                                size="sm" 
                                                variant={currentForm.isDirty ? "default" : (isRated ? "outline" : "secondary")}
                                                disabled={submitting[key] || !currentForm.isDirty}
                                                className={`h-8 w-full gap-1.5 ${isRated && !currentForm.isDirty ? 'text-green-600 border-green-200 bg-green-50' : ''}`}
                                                onClick={() => handleSaveRating(m.id, t.id)}
                                            >
                                                {isRated && !currentForm.isDirty && <CheckCircle2 className="w-3.5 h-3.5 text-green-600" />}
                                                {!isRated && currentForm.isDirty && <Save className="w-3.5 h-3.5" />}
                                                {!isRated && !currentForm.isDirty && <AlertCircle className="w-3.5 h-3.5 text-orange-400" />}
                                                
                                                {submitting[key] ? "..." : (isRated ? (currentForm.isDirty ? "Simpan" : "Selesai") : "Simpan")}
                                            </Button>
                                        </TableCell>
                                    </TableRow>
                                );
                            })}
                        </TableBody>
                    </Table>
                </div>
            )}

            {/* Validation Warning Dialog */}
            <Dialog open={validationDialog} onOpenChange={setValidationDialog}>
                <DialogContent className="max-w-sm">
                    <DialogHeader>
                        <DialogTitle className="flex items-center gap-2 text-amber-600">
                            <AlertTriangle className="w-5 h-5" />
                            Penilaian Belum Lengkap
                        </DialogTitle>
                        <DialogDescription>
                            Silakan lengkapi semua isian berikut sebelum menyimpan:
                        </DialogDescription>
                    </DialogHeader>
                    <div className="space-y-2 mt-2">
                        {validationMessages.map((msg, i) => (
                            <div key={i} className="flex items-center gap-2 p-2.5 rounded-lg bg-amber-50 border border-amber-200 text-sm font-medium text-amber-800">
                                <AlertCircle className="w-4 h-4 text-amber-500 shrink-0" />
                                {msg}
                            </div>
                        ))}
                    </div>
                    <div className="flex justify-end mt-4">
                        <Button variant="outline" onClick={() => setValidationDialog(false)}>
                            Mengerti
                        </Button>
                    </div>
                </DialogContent>
            </Dialog>
        </div>
    );
}
