'use client';

import { useEffect, useState } from 'react';
import { useAuthStore } from '@/store/auth.store';
import { useRouter } from 'next/navigation';
import api from '@/lib/axios';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Plus, Pencil, Trash2, Users, Crown, Search, Filter } from 'lucide-react';

interface SimpleUser {
    id: string;
    nip: string;
    name: string;
}

interface TeamMember {
    user: SimpleUser;
}

interface TeamData {
    id: string;
    teamName: string;
    leader: SimpleUser | null;
    members: TeamMember[];
}

export default function TeamsPage() {
    const { user, activeRole } = useAuthStore();
    const router = useRouter();
    const [teams, setTeams] = useState<TeamData[]>([]);
    const [allUsers, setAllUsers] = useState<SimpleUser[]>([]);
    const [loading, setLoading] = useState(true);

    // Create Dialog
    const [isCreateOpen, setIsCreateOpen] = useState(false);
    const [createForm, setCreateForm] = useState({ teamName: '', leaderId: '', memberIds: [] as string[] });

    // Edit Dialog
    const [isEditOpen, setIsEditOpen] = useState(false);
    const [editTeamId, setEditTeamId] = useState<string | null>(null);
    const [editForm, setEditForm] = useState({ teamName: '', leaderId: '', memberIds: [] as string[] });

    // Search and Filter State
    const [searchTerm, setSearchTerm] = useState('');
    const [filterLeader, setFilterLeader] = useState('ALL');
    const [filterSize, setFilterSize] = useState('ALL');

    const fetchTeams = async () => {
        try {
            const res = await api.get('/teams');
            setTeams(res.data);
        } catch (err) {
            console.error('Failed to fetch teams', err);
        } finally {
            setLoading(false);
        }
    };

    const fetchUsers = async () => {
        try {
            const res = await api.get('/users');
            setAllUsers(res.data.map((u: any) => ({ id: u.id, nip: u.nip, name: u.name })));
        } catch (err) {
            console.error('Failed to fetch users', err);
        }
    };

    useEffect(() => {
        if (!user) { router.push('/login'); return; }
        if (activeRole !== 'Admin' && activeRole !== 'Pimpinan') { router.push('/dashboard'); return; }
        fetchTeams();
        fetchUsers();
    }, [user, activeRole, router]);

    // --- Create ---
    const handleCreate = async (e: React.FormEvent) => {
        e.preventDefault();
        try {
            await api.post('/teams', createForm);
            setIsCreateOpen(false);
            setCreateForm({ teamName: '', leaderId: '', memberIds: [] });
            fetchTeams();
        } catch (err) { console.error(err); alert('Gagal membuat tim.'); }
    };

    // --- Edit ---
    const openEdit = (t: TeamData) => {
        setEditTeamId(t.id);
        setEditForm({
            teamName: t.teamName,
            leaderId: t.leader?.id || '',
            memberIds: t.members.map(m => m.user.id),
        });
        setIsEditOpen(true);
    };

    const handleUpdate = async (e: React.FormEvent) => {
        e.preventDefault();
        try {
            await api.put(`/teams/${editTeamId}`, editForm);
            setIsEditOpen(false);
            fetchTeams();
        } catch (err) { console.error(err); alert('Gagal memperbarui tim.'); }
    };

    // --- Delete ---
    const handleDelete = async (id: string) => {
        if (!confirm('Hapus tim ini beserta seluruh anggotanya?')) return;
        try { await api.delete(`/teams/${id}`); fetchTeams(); }
        catch (err) { console.error(err); alert('Gagal menghapus tim.'); }
    };

    // --- Toggle member ---
    const toggleMember = (userId: string, form: typeof createForm, setForm: typeof setCreateForm) => {
        setForm(prev => {
            const ids = prev.memberIds.includes(userId)
                ? prev.memberIds.filter(id => id !== userId)
                : [...prev.memberIds, userId];
            return { ...prev, memberIds: ids };
        });
    };

    // --- Reusable user picker ---
    const UserPicker = ({ label, form, setForm, fieldKey }: {
        label: string;
        form: { leaderId: string; memberIds: string[] };
        setForm: React.Dispatch<React.SetStateAction<any>>;
        fieldKey: 'leader' | 'members';
    }) => {
        if (fieldKey === 'leader') {
            return (
                <div className="space-y-2">
                    <Label>{label}</Label>
                    <select
                        className="w-full border rounded-md px-3 py-2 text-sm bg-background"
                        value={form.leaderId}
                        onChange={e => setForm((prev: any) => ({ ...prev, leaderId: e.target.value }))}
                    >
                        <option value="">— Pilih Ketua Tim —</option>
                        {allUsers.map(u => (
                            <option key={u.id} value={u.id}>{u.name} ({u.nip})</option>
                        ))}
                    </select>
                </div>
            );
        }
        return (
            <div className="space-y-2">
                <Label>{label}</Label>
                <div className="max-h-48 overflow-y-auto border rounded-md p-2 space-y-1">
                    {allUsers.filter(u => u.id !== form.leaderId).map(u => (
                        <label key={u.id} className="flex items-center gap-2 px-2 py-1.5 rounded hover:bg-muted cursor-pointer text-sm">
                            <input
                                type="checkbox"
                                checked={form.memberIds.includes(u.id)}
                                onChange={() => toggleMember(u.id, form as any, setForm)}
                                className="rounded"
                            />
                            {u.name} <span className="text-muted-foreground">({u.nip})</span>
                        </label>
                    ))}
                </div>
            </div>
        );
    };

    // --- Derived Logic Filters ---
    const filteredTeams = teams.filter(t => {
        // Deep Search Text
        const s = searchTerm.toLowerCase();
        let matchSearch = false;

        if (t.teamName.toLowerCase().includes(s)) matchSearch = true;
        if (t.leader && (t.leader.name.toLowerCase().includes(s) || t.leader.nip.toLowerCase().includes(s))) matchSearch = true;
        if (t.members.some(m => m.user.name.toLowerCase().includes(s) || m.user.nip.toLowerCase().includes(s))) matchSearch = true;
        
        if (!matchSearch) return false;

        // Leader Filter
        if (filterLeader === 'HAS_LEADER' && !t.leader) return false;
        if (filterLeader === 'NO_LEADER' && t.leader) return false;

        // Size Filter
        if (filterSize === 'EMPTY' && t.members.length !== 0) return false;
        if (filterSize === 'SMALL' && (t.members.length === 0 || t.members.length > 5)) return false;
        if (filterSize === 'LARGE' && t.members.length <= 5) return false;

        return true;
    }).sort((a, b) => b.members.length - a.members.length);

    if (loading) return <div className="p-8">Memuat data tim...</div>;

    return (
        <div className="space-y-8 animate-in fade-in zoom-in-95 duration-500">
            <div className="flex justify-between items-center">
                <div>
                    <h2 className="text-3xl font-bold tracking-tight">Manajemen Tim</h2>
                    <p className="text-muted-foreground mt-1">Kelola tim kerja, cari anggota spesifik, dan atur struktur kepemimpinan.</p>
                </div>
                {activeRole === 'Admin' && (
                <Dialog open={isCreateOpen} onOpenChange={setIsCreateOpen}>
                    <DialogTrigger render={<Button className="flex items-center gap-2" />}>
                        <Plus className="w-4 h-4" /> Buat Tim Baru
                    </DialogTrigger>
                    <DialogContent className="max-w-lg">
                        <DialogHeader><DialogTitle>Buat Tim Baru</DialogTitle></DialogHeader>
                        <form onSubmit={handleCreate} className="space-y-4">
                            <div className="space-y-2">
                                <Label>Nama Tim</Label>
                                <Input value={createForm.teamName} onChange={e => setCreateForm({ ...createForm, teamName: e.target.value })} required />
                            </div>
                            <UserPicker label="Ketua Tim" form={createForm} setForm={setCreateForm} fieldKey="leader" />
                            <UserPicker label="Anggota Tim" form={createForm} setForm={setCreateForm} fieldKey="members" />
                            <Button type="submit" className="w-full">Simpan Tim</Button>
                        </form>
                    </DialogContent>
                </Dialog>
                )}
            </div>

            {/* Control Bar for Search & Filter */}
            <Card className="shadow-sm bg-slate-50 border border-slate-200">
                <CardContent className="p-4 flex flex-col md:flex-row gap-4 justify-between items-center">
                    <div className="relative w-full md:w-1/2">
                        <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                        <Input 
                            placeholder="Cari Tim, Ketua, atau Anggota spesifik..."
                            className="pl-9 bg-white shadow-sm border-slate-300"
                            value={searchTerm}
                            onChange={(e) => setSearchTerm(e.target.value)}
                        />
                    </div>
                    <div className="flex gap-3 w-full md:w-auto">
                        <div className="relative flex-1 md:w-48">
                            <select 
                                className="w-full h-10 pl-3 pr-8 rounded-md border border-slate-300 bg-white text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-primary"
                                value={filterLeader}
                                onChange={(e) => setFilterLeader(e.target.value)}
                            >
                                <option value="ALL">Semua Kepemimpinan</option>
                                <option value="HAS_LEADER">Tim Ada Ketua</option>
                                <option value="NO_LEADER">Belum Ada Ketua</option>
                            </select>
                        </div>
                        <div className="relative flex-1 md:w-48">
                            <select 
                                className="w-full h-10 pl-3 pr-8 rounded-md border border-slate-300 bg-white text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-primary"
                                value={filterSize}
                                onChange={(e) => setFilterSize(e.target.value)}
                            >
                                <option value="ALL">Semua Ukuran Tim</option>
                                <option value="EMPTY">Kosong (0)</option>
                                <option value="SMALL">Kecil (1-5 orang)</option>
                                <option value="LARGE">Besar (&#62;5 orang)</option>
                            </select>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <div className="flex justify-between items-center mb-1">
                <h3 className="text-lg font-medium tracking-tight">Daftar Tim Pekerja</h3>
                <div className="text-sm font-medium text-slate-600 bg-slate-100 px-3 py-1 rounded-full">
                    Menampilkan <strong>{filteredTeams.length}</strong> dari {teams.length} tim terdaftar
                </div>
            </div>

            {/* Team Cards Grid */}
            <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                {filteredTeams.map(t => (
                    <Card key={t.id} className="shadow-md border-t-4 border-t-primary hover:shadow-lg transition-shadow bg-white flex flex-col">
                        <CardHeader className="pb-3 border-b">
                            <div className="flex justify-between items-start">
                                <div>
                                    <CardTitle className="text-lg">{t.teamName}</CardTitle>
                                    <CardDescription className="mt-1 flex items-center gap-1.5 font-medium">
                                        <Users className="w-3.5 h-3.5" /> {t.members.length} anggota
                                    </CardDescription>
                                </div>
                                {activeRole === 'Admin' && (
                                <div className="flex gap-1 bg-slate-50 p-1 rounded-lg">
                                    <Button variant="ghost" size="sm" className="h-8 w-8 p-0 text-slate-500 hover:text-amber-600 hover:bg-amber-50" onClick={() => openEdit(t)} title="Edit">
                                        <Pencil className="w-4 h-4" />
                                    </Button>
                                    <Button variant="ghost" size="sm" className="h-8 w-8 p-0 text-slate-500 hover:text-red-600 hover:bg-red-50" onClick={() => handleDelete(t.id)} title="Hapus">
                                        <Trash2 className="w-4 h-4" />
                                    </Button>
                                </div>
                                )}
                            </div>
                        </CardHeader>
                        <CardContent className="space-y-4 pt-4 flex-1 flex flex-col">
                            {/* Leader */}
                            <div className={`flex items-center gap-3 border rounded-lg px-3 py-2.5 ${t.leader ? 'bg-amber-50/50 border-amber-200' : 'bg-red-50/50 border-red-200'}`}>
                                <Crown className={`w-5 h-5 ${t.leader ? 'text-amber-500' : 'text-red-400'}`} />
                                <div>
                                    <p className={`text-[11px] uppercase tracking-wider font-bold ${t.leader ? 'text-amber-600' : 'text-red-500'}`}>Ketua Tim</p>
                                    <p className={`text-sm font-semibold truncate ${!t.leader && 'text-red-600 italic font-medium'}`}>
                                        {t.leader ? `${t.leader.name} (${t.leader.nip})` : 'Belum ditentukan'}
                                    </p>
                                </div>
                            </div>

                            {/* Members Table */}
                            <div className="flex-1 flex flex-col">
                                {t.members.length > 0 ? (
                                    <div className="rounded-md border flex-1">
                                        <Table>
                                            <TableHeader>
                                                <TableRow className="bg-slate-50/70 hover:bg-slate-50/70">
                                                    <TableHead className="text-xs h-8">NIP</TableHead>
                                                    <TableHead className="text-xs h-8">Nama Anggota</TableHead>
                                                </TableRow>
                                            </TableHeader>
                                            <TableBody>
                                                {t.members.map(m => (
                                                    <TableRow key={m.user.id} className="hover:bg-slate-50 border-b-0">
                                                        <TableCell className="text-xs font-medium text-slate-500 py-2">{m.user.nip}</TableCell>
                                                        <TableCell className="text-xs py-2">{m.user.name}</TableCell>
                                                    </TableRow>
                                                ))}
                                            </TableBody>
                                        </Table>
                                    </div>
                                ) : (
                                    <div className="flex-1 flex items-center justify-center border border-dashed rounded-md p-4 bg-slate-50/50">
                                        <p className="text-xs text-muted-foreground text-center">Tim masih kosongan, belum ada anggota.</p>
                                    </div>
                                )}
                            </div>
                        </CardContent>
                    </Card>
                ))}

                {filteredTeams.length === 0 && (
                    <div className="col-span-full border-2 border-dashed rounded-xl flex flex-col items-center justify-center py-16 text-muted-foreground bg-slate-50/50">
                        <Filter className="w-10 h-10 text-slate-300 mb-2" />
                        <p className="font-medium text-slate-600">Tidak ada tim yang ditemukan.</p>
                        <p className="text-sm">Silakan ubah kata kunci pencarian atau reset filter di atas.</p>
                    </div>
                )}
            </div>

            {/* Edit Dialog */}
            <Dialog open={isEditOpen} onOpenChange={setIsEditOpen}>
                <DialogContent className="max-w-lg">
                    <DialogHeader><DialogTitle>Edit Tim</DialogTitle></DialogHeader>
                    <form onSubmit={handleUpdate} className="space-y-4">
                        <div className="space-y-2">
                            <Label>Nama Tim</Label>
                            <Input value={editForm.teamName} onChange={e => setEditForm({ ...editForm, teamName: e.target.value })} required />
                        </div>
                        <UserPicker label="Ketua Tim" form={editForm} setForm={setEditForm} fieldKey="leader" />
                        <UserPicker label="Anggota Tim" form={editForm} setForm={setEditForm} fieldKey="members" />
                        <Button type="submit" className="w-full">Simpan Perubahan</Button>
                    </form>
                </DialogContent>
            </Dialog>
        </div>
    );
}
