'use client';

import { useEffect, useState } from 'react';
import { useAuthStore } from '@/store/auth.store';
import { useRouter } from 'next/navigation';
import api from '@/lib/axios';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger, DialogDescription } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { UserPlus, Pencil, Trash2, Upload, Download, Search, Filter, KeyRound, Eye, EyeOff, AlertTriangle, CheckCircle2 } from 'lucide-react';

interface RoleData {
    id: string;
    roleName: string;
}

interface UserData {
    id: string;
    nip: string;
    username: string | null;
    name: string;
    email: string;
    userRoles: { role: { id: string; roleName: string } }[];
    ledTeams?: { id: string; teamName: string }[];
    teamMembers?: { team: { id: string; teamName: string } }[];
}

export default function UsersPage() {
    const { user, activeRole } = useAuthStore();
    const router = useRouter();
    const [users, setUsers] = useState<UserData[]>([]);
    const [roles, setRoles] = useState<RoleData[]>([]);
    const [loading, setLoading] = useState(true);
    const [isOpen, setIsOpen] = useState(false);

    // Edit Modal State
    const [isEditDialogOpen, setIsEditDialogOpen] = useState(false);
    const [editUserId, setEditUserId] = useState<string | null>(null);

    // Form State
    const [formData, setFormData] = useState({ nip: '', username: '', name: '', email: '', password: '' });
    const [editFormData, setEditFormData] = useState({ username: '', name: '', email: '', roleIds: [] as string[] });

    // Import state
    const [showImportDialog, setShowImportDialog] = useState(false);
    const [importFile, setImportFile] = useState<File | null>(null);
    const [importPreview, setImportPreview] = useState<{ total: number; valid: number; errors: string[]; preview: any[] } | null>(null);
    const [importing, setImporting] = useState(false);

    // Search & Filter State
    const [searchTerm, setSearchTerm] = useState('');
    const [filterRole, setFilterRole] = useState('ALL');
    const [filterTeam, setFilterTeam] = useState('ALL');

    // Reset Password State
    const [resetPwDialogOpen, setResetPwDialogOpen] = useState(false);
    const [resetPwStep, setResetPwStep] = useState<'confirm' | 'input'>('confirm');
    const [resetPwUser, setResetPwUser] = useState<UserData | null>(null);
    const [resetNewPassword, setResetNewPassword] = useState('');
    const [showResetPw, setShowResetPw] = useState(false);
    const [resetPwLoading, setResetPwLoading] = useState(false);
    const [resetPwSuccess, setResetPwSuccess] = useState('');
    const [resetPwError, setResetPwError] = useState('');

    const fetchUsers = async () => {
        try {
            const res = await api.get('/users');
            setUsers(res.data);
        } catch (err) {
            console.error('Failed to fetch users', err);
        } finally {
            setLoading(false);
        }
    };

    const fetchRoles = async () => {
        try {
            const res = await api.get('/roles');
            setRoles(res.data);
        } catch (err) {
            console.error('Failed to fetch roles', err);
        }
    };

    useEffect(() => {
        if (!user) {
            router.push('/login');
            return;
        }
        if (activeRole !== 'Admin') {
            router.push('/dashboard');
            return;
        }
        fetchUsers();
        fetchRoles();
    }, [user, activeRole, router]);

    const handleCreateUser = async (e: React.FormEvent) => {
        e.preventDefault();
        try {
            await api.post('/users', formData);
            setIsOpen(false);
            setFormData({ nip: '', username: '', name: '', email: '', password: '' });
            fetchUsers();
        } catch (err) {
            console.error(err);
            alert('Gagal menambahkan pengguna. Pastikan NIP/Username/Email belum digunakan.');
        }
    };

    const handleOpenEdit = (u: UserData) => {
        setEditUserId(u.id);
        setEditFormData({
            username: u.username || '',
            name: u.name,
            email: u.email,
            roleIds: u.userRoles.map(ur => ur.role.id)
        });
        setIsEditDialogOpen(true);
    };

    const toggleRole = (roleId: string) => {
        setEditFormData(prev => {
            const roles = prev.roleIds.includes(roleId)
                ? prev.roleIds.filter(id => id !== roleId)
                : [...prev.roleIds, roleId];
            return { ...prev, roleIds: roles };
        });
    };

    const handleUpdateUser = async (e: React.FormEvent) => {
        e.preventDefault();
        try {
            await api.put(`/users/${editUserId}`, editFormData);
            setIsEditDialogOpen(false);
            fetchUsers();
        } catch (err) {
            console.error(err);
            alert('Gagal memperbarui pengguna.');
        }
    };

    const handleDeleteUser = async (id: string) => {
        if (!confirm('Apakah Anda yakin ingin menghapus pengguna ini?')) return;
        try {
            await api.delete(`/users/${id}`);
            fetchUsers();
        } catch (err) {
            console.error(err);
            alert('Gagal menghapus pengguna.');
        }
    };

    const openResetPwDialog = (u: UserData) => {
        setResetPwUser(u);
        setResetPwStep('confirm');
        setResetNewPassword('');
        setShowResetPw(false);
        setResetPwSuccess('');
        setResetPwError('');
        setResetPwDialogOpen(true);
    };

    const handleResetPassword = async () => {
        if (!resetPwUser) return;
        if (!resetNewPassword || resetNewPassword.trim().length === 0) {
            setResetPwError('Password baru tidak boleh kosong atau hanya berisi spasi.');
            return;
        }
        setResetPwLoading(true);
        setResetPwError('');
        try {
            const res = await api.put(`/users/${resetPwUser.id}/reset-password`, { newPassword: resetNewPassword });
            setResetPwSuccess(res.data.message || 'Password berhasil direset.');
        } catch (err: any) {
            setResetPwError(err?.response?.data?.error || 'Gagal mereset password.');
        } finally {
            setResetPwLoading(false);
        }
    };

    const handleDownloadTemplate = async () => {
        try {
            const res = await api.get('/import/template', { responseType: 'blob' });
            const url = window.URL.createObjectURL(new Blob([res.data]));
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', 'template_import_pegawai.xlsx');
            document.body.appendChild(link);
            link.click();
            link.remove();
        } catch (error) {
            console.error(error);
            alert('Gagal mendownload template.');
        }
    };

    const handleUploadPreview = async () => {
        if (!importFile) return;
        const fd = new FormData();
        fd.append('file', importFile);
        try {
            const res = await api.post('/import/preview', fd, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });
            setImportPreview(res.data);
        } catch (err: any) {
            alert(err?.response?.data?.error || 'Gagal membaca file Excel.');
        }
    };

    const handleExecuteImport = async () => {
        if (!importPreview || importPreview.valid === 0) return;
        setImporting(true);
        try {
            const res = await api.post('/import/execute', { employees: importPreview.preview });
            alert(res.data.message);
            setShowImportDialog(false);
            setImportFile(null);
            setImportPreview(null);
            fetchUsers();
        } catch (err: any) {
            alert(err?.response?.data?.error || 'Gagal mengimport pegawai.');
        } finally {
            setImporting(false);
        }
    };

    // Extract unique teams for dropdown
    const uniqueTeams = Array.from(new Set(
        users.flatMap(u => [
            ...(u.ledTeams?.map(t => t.teamName) || []),
            ...(u.teamMembers?.map(tm => tm.team.teamName) || [])
        ])
    )).sort();

    // Derived filtered users
    const filteredUsers = users.filter(u => {
        // Search Filter (NIP, Username, Name, Email)
        const s = searchTerm.toLowerCase();
        const matchSearch =
            u.nip.toLowerCase().includes(s) ||
            (u.username && u.username.toLowerCase().includes(s)) ||
            u.name.toLowerCase().includes(s) ||
            u.email.toLowerCase().includes(s);

        if (!matchSearch) return false;

        // Role Filter
        if (filterRole !== 'ALL') {
            const hasRole = u.userRoles.some(ur => ur.role.roleName === filterRole);
            if (!hasRole) return false;
        }

        // Team Filter
        if (filterTeam !== 'ALL') {
            const hasTeam =
                u.ledTeams?.some(t => t.teamName === filterTeam) ||
                u.teamMembers?.some(tm => tm.team.teamName === filterTeam);
            if (!hasTeam) return false;
        }

        return true;
    });

    if (loading) return <div className="p-8">Memuat data...</div>;

    return (
        <div className="space-y-8 animate-in fade-in zoom-in-95 duration-500">
            <div className="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
                <div>
                    <h2 className="text-3xl font-bold tracking-tight">Manajemen Pengguna</h2>
                    <p className="text-muted-foreground mt-1">Daftar pegawai dan manajemen hak akses sistem.</p>
                </div>

                <div className="flex flex-wrap gap-2">
                    <Button variant="outline" className="flex items-center gap-2" onClick={handleDownloadTemplate}>
                        <Download className="w-4 h-4" /> Template Excel
                    </Button>
                    <Button variant="outline" className="flex items-center gap-2" onClick={() => { setShowImportDialog(true); setImportFile(null); setImportPreview(null); }}>
                        <Upload className="w-4 h-4" /> Import Pegawai
                    </Button>

                    <Dialog open={isOpen} onOpenChange={setIsOpen}>
                        <DialogTrigger render={<Button className="flex items-center gap-2" />}>
                            <UserPlus className="w-4 h-4" /> Tambah Pengguna
                        </DialogTrigger>
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>Tambah Pengguna Baru</DialogTitle>
                            </DialogHeader>
                            <form onSubmit={handleCreateUser} className="space-y-4">
                                <div className="space-y-2">
                                    <Label htmlFor="nip">NIP</Label>
                                    <Input id="nip" value={formData.nip} onChange={e => setFormData({ ...formData, nip: e.target.value })} required />
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="username">Username</Label>
                                    <Input id="username" value={formData.username} onChange={e => setFormData({ ...formData, username: e.target.value })} required />
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="name">Nama Lengkap</Label>
                                    <Input id="name" value={formData.name} onChange={e => setFormData({ ...formData, name: e.target.value })} required />
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="email">Email</Label>
                                    <Input id="email" type="email" value={formData.email} onChange={e => setFormData({ ...formData, email: e.target.value })} required />
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="password">Password Sementara</Label>
                                    <Input id="password" type="password" value={formData.password} onChange={e => setFormData({ ...formData, password: e.target.value })} required />
                                </div>
                                <Button type="submit" className="w-full">Simpan Pengguna</Button>
                            </form>
                        </DialogContent>
                    </Dialog>
                </div>
            </div>

            {/* Import Pegawai Dialog */}
            <Dialog open={showImportDialog} onOpenChange={setShowImportDialog}>
                <DialogContent className="max-w-xl max-h-[90vh] overflow-y-auto">
                    <DialogHeader>
                        <DialogTitle>Import Pegawai via Excel</DialogTitle>
                        <DialogDescription>
                            Upload file Excel sesuai template. Data yang valid akan dibuatkan akun dengan default role: <strong>Pegawai</strong> dan password default: <strong>pegawai123</strong>.
                        </DialogDescription>
                    </DialogHeader>

                    {!importPreview ? (
                        <div className="space-y-4">
                            <Label>Pilih file Excel (.xlsx)</Label>
                            <Input type="file" accept=".xlsx, .xls" onChange={(e) => setImportFile(e.target.files?.[0] || null)} />
                            <Button className="w-full" disabled={!importFile} onClick={handleUploadPreview}>Preview Data</Button>
                        </div>
                    ) : (
                        <div className="space-y-4">
                            <div className="bg-muted p-4 rounded-lg flex justify-between items-center text-sm">
                                <span>Total dibaca: <strong>{importPreview.total}</strong> baris</span>
                                <span className={importPreview.valid > 0 ? 'text-green-600 font-medium' : ''}>Valid: <strong>{importPreview.valid}</strong> baris</span>
                            </div>

                            {importPreview.errors.length > 0 && (
                                <div className="bg-red-50 text-red-600 p-3 rounded border border-red-200 text-sm">
                                    <strong>Kesalahan / Duplikat:</strong>
                                    <ul className="list-disc pl-5 mt-1 max-h-32 overflow-y-auto">
                                        {importPreview.errors.map((err, i) => <li key={i}>{err}</li>)}
                                    </ul>
                                </div>
                            )}

                            {importPreview.valid > 0 && (
                                <div className="border rounded-lg overflow-x-auto max-h-48 overflow-y-auto mt-2">
                                    <Table>
                                        <TableHeader className="bg-slate-50 sticky top-0">
                                            <TableRow>
                                                <TableHead>NIP</TableHead>
                                                <TableHead>Nama</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody className="text-sm">
                                            {importPreview.preview.map((p, i) => (
                                                <TableRow key={i}>
                                                    <TableCell>{p.nip}</TableCell>
                                                    <TableCell>{p.name}</TableCell>
                                                </TableRow>
                                            ))}
                                        </TableBody>
                                    </Table>
                                </div>
                            )}

                            <div className="flex gap-2 pt-2">
                                <Button variant="outline" className="w-1/2" onClick={() => { setImportFile(null); setImportPreview(null); }}>Batal / Ganti File</Button>
                                <Button
                                    className="w-1/2 bg-green-600 hover:bg-green-700"
                                    disabled={importPreview.valid === 0 || importing}
                                    onClick={handleExecuteImport}
                                >
                                    {importing ? 'Mengimport...' : `Mulai Import (${importPreview.valid} Pegawai)`}
                                </Button>
                            </div>
                        </div>
                    )}
                </DialogContent>
            </Dialog>

            {/* Control Bar for Search & Filter */}
            <Card className="shadow-sm bg-slate-50 border border-slate-200">
                <CardContent className="p-4 flex flex-col md:flex-row gap-4 justify-between items-center">
                    <div className="relative w-full md:w-1/2">
                        <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                        <Input 
                            placeholder="Cari NIP, Username, Nama, atau Email..."
                            className="pl-9 bg-white shadow-sm border-slate-300"
                            value={searchTerm}
                            onChange={(e) => setSearchTerm(e.target.value)}
                        />
                    </div>
                    <div className="flex gap-3 w-full md:w-auto">
                        <div className="relative flex-1 md:w-48">
                            <select 
                                className="w-full h-10 pl-3 pr-8 rounded-md border border-slate-300 bg-white text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-primary"
                                value={filterRole}
                                onChange={(e) => setFilterRole(e.target.value)}
                            >
                                <option value="ALL">Semua Peran</option>
                                {roles.map(r => <option key={r.id} value={r.roleName}>{r.roleName}</option>)}
                            </select>
                        </div>
                        <div className="relative flex-1 md:w-48">
                            <select 
                                className="w-full h-10 pl-3 pr-8 rounded-md border border-slate-300 bg-white text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-primary"
                                value={filterTeam}
                                onChange={(e) => setFilterTeam(e.target.value)}
                            >
                                <option value="ALL">Semua Tim</option>
                                {uniqueTeams.map(team => <option key={team} value={team}>{team}</option>)}
                            </select>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card className="shadow-md border-t-4 border-t-primary">
                <CardHeader className="pb-4 border-b bg-white">
                    <div className="flex flex-col md:flex-row justify-between md:items-center gap-2">
                        <CardTitle className="text-xl">Daftar Hasil Pengguna</CardTitle>
                        <CardDescription className="font-medium text-slate-600 bg-slate-100 px-3 py-1 rounded-full text-xs">
                            Menampilkan <strong>{filteredUsers.length}</strong> dari total {users.length} pengguna
                        </CardDescription>
                    </div>
                </CardHeader>
                <CardContent className="p-0">
                    <div className="overflow-x-auto">
                        <Table>
                            <TableHeader className="bg-slate-50/50">
                                <TableRow>
                                    <TableHead className="w-[120px]">NIP</TableHead>
                                    <TableHead className="w-[150px]">Username</TableHead>
                                    <TableHead className="min-w-[180px]">Nama Lengkap</TableHead>
                                    <TableHead className="min-w-[180px]">Email</TableHead>
                                    <TableHead className="w-[240px]">Peran & Tim</TableHead>
                                    <TableHead className="w-[100px] text-right">Aksi</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {filteredUsers.map((u) => (
                                    <TableRow key={u.id} className="hover:bg-slate-50">
                                        <TableCell className="font-medium text-slate-600">{u.nip}</TableCell>
                                        <TableCell className="text-muted-foreground">{u.username || '-'}</TableCell>
                                        <TableCell className="font-medium">{u.name}</TableCell>
                                        <TableCell className="text-muted-foreground">{u.email}</TableCell>
                                        <TableCell>
                                            <div className="flex gap-1.5 flex-wrap flex-col">
                                                {u.userRoles.length > 0 ? u.userRoles.map(ur => {
                                                    let teamLabel = '';
                                                    if (ur.role.roleName === 'Ketua Tim' && u.ledTeams && u.ledTeams.length > 0) {
                                                        teamLabel = ` (T. ${u.ledTeams.map(t => t.teamName).join(', ')})`;
                                                    } else if (ur.role.roleName === 'Pegawai' && u.teamMembers && u.teamMembers.length > 0) {
                                                        teamLabel = ` (T. ${u.teamMembers.map(tm => tm.team.teamName).join(', ')})`;
                                                    }

                                                    return (
                                                        <span key={ur.role.id} className="w-fit px-2 py-0.5 bg-primary/10 text-primary text-xs rounded-full font-semibold border border-primary/20 shadow-sm">
                                                            {ur.role.roleName}{teamLabel}
                                                        </span>
                                                    )
                                                }) : <span className="text-xs text-muted-foreground">-</span>}
                                            </div>
                                        </TableCell>
                                        <TableCell className="text-right">
                                            <div className="flex justify-end gap-1.5">
                                                <Button variant="outline" size="sm" className="h-7 w-7 p-0 hover:bg-amber-50 hover:text-amber-600 hover:border-amber-200" title="Edit" onClick={() => handleOpenEdit(u)}>
                                                    <Pencil className="w-3.5 h-3.5" />
                                                </Button>
                                                <Button variant="outline" size="sm" className="h-7 w-7 p-0 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200" title="Reset Password" onClick={() => openResetPwDialog(u)}>
                                                    <KeyRound className="w-3.5 h-3.5" />
                                                </Button>
                                                <Button variant="outline" size="sm" className="h-7 w-7 p-0 hover:bg-red-50 hover:text-red-600 hover:border-red-200" title="Hapus" onClick={() => handleDeleteUser(u.id)}>
                                                    <Trash2 className="w-3.5 h-3.5" />
                                                </Button>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))}
                                {filteredUsers.length === 0 && (
                                    <TableRow>
                                        <TableCell colSpan={6} className="text-center py-12 text-muted-foreground bg-slate-50/30">
                                            <div className="flex flex-col items-center justify-center gap-2">
                                                <Filter className="w-8 h-8 text-slate-300" />
                                                <p>Tidak ada pengguna yang cocok dengan kriteria pencarian/filter.</p>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                )}
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>

            {/* Edit Dialog */}
            <Dialog open={isEditDialogOpen} onOpenChange={setIsEditDialogOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Edit Pengguna</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={handleUpdateUser} className="space-y-4">
                        <div className="space-y-2">
                            <Label htmlFor="edit-username">Username</Label>
                            <Input id="edit-username" value={editFormData.username} onChange={e => setEditFormData({ ...editFormData, username: e.target.value })} />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="edit-name">Nama Lengkap</Label>
                            <Input id="edit-name" value={editFormData.name} onChange={e => setEditFormData({ ...editFormData, name: e.target.value })} required />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="edit-email">Email</Label>
                            <Input id="edit-email" type="email" value={editFormData.email} onChange={e => setEditFormData({ ...editFormData, email: e.target.value })} required />
                        </div>

                        <div className="space-y-2 pt-2 border-t">
                            <Label>Hak Akses Peran (Roles)</Label>
                            <div className="flex flex-wrap gap-2 mt-2">
                                {roles.map(role => (
                                    <Button
                                        key={role.id}
                                        type="button"
                                        variant={editFormData.roleIds.includes(role.id) ? 'default' : 'outline'}
                                        size="sm"
                                        onClick={() => toggleRole(role.id)}
                                        className="rounded-full shadow-sm"
                                    >
                                        {role.roleName}
                                    </Button>
                                ))}
                            </div>
                            <p className="text-xs text-muted-foreground mt-2">Pilih satu atau lebih peran untuk ditugaskan pada pegawai ini.</p>
                        </div>

                        <Button type="submit" className="w-full mt-4">Simpan Perubahan</Button>
                    </form>
                </DialogContent>
            </Dialog>

            {/* Reset Password Dialog */}
            <Dialog open={resetPwDialogOpen} onOpenChange={setResetPwDialogOpen}>
                <DialogContent className="max-w-sm">
                    <DialogHeader>
                        <DialogTitle className="flex items-center gap-2">
                            <KeyRound className="w-5 h-5 text-blue-600" />
                            Reset Password
                        </DialogTitle>
                        <DialogDescription>
                            {resetPwUser ? `Reset password untuk ${resetPwUser.name} (${resetPwUser.username || resetPwUser.nip})` : ''}
                        </DialogDescription>
                    </DialogHeader>

                    {resetPwStep === 'confirm' && !resetPwSuccess && (
                        <div className="space-y-4 mt-2">
                            <div className="bg-amber-50 border border-amber-200 rounded-lg p-3 flex items-start gap-2.5">
                                <AlertTriangle className="w-5 h-5 text-amber-500 shrink-0 mt-0.5" />
                                <div className="text-sm text-amber-800">
                                    <p className="font-semibold">Konfirmasi</p>
                                    <p className="mt-1">Apakah Anda yakin ingin mereset password untuk <strong>{resetPwUser?.name}</strong>? User akan perlu menggunakan password baru yang Anda tentukan.</p>
                                </div>
                            </div>
                            <div className="flex gap-2">
                                <Button variant="outline" className="flex-1" onClick={() => setResetPwDialogOpen(false)}>Batal</Button>
                                <Button className="flex-1" onClick={() => setResetPwStep('input')}>Ya, Lanjutkan</Button>
                            </div>
                        </div>
                    )}

                    {resetPwStep === 'input' && !resetPwSuccess && (
                        <div className="space-y-4 mt-2">
                            {resetPwError && (
                                <div className="bg-destructive/15 text-destructive text-sm p-3 rounded-md border border-destructive/20">
                                    {resetPwError}
                                </div>
                            )}
                            <div className="space-y-2">
                                <Label htmlFor="reset-new-pw">Password Baru</Label>
                                <div className="relative">
                                    <Input
                                        id="reset-new-pw"
                                        type={showResetPw ? 'text' : 'password'}
                                        value={resetNewPassword}
                                        onChange={e => setResetNewPassword(e.target.value)}
                                        placeholder="Masukkan password baru"
                                    />
                                    <button
                                        type="button"
                                        className="absolute right-2 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                                        onClick={() => setShowResetPw(!showResetPw)}
                                    >
                                        {showResetPw ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                                    </button>
                                </div>
                            </div>
                            <div className="flex gap-2">
                                <Button variant="outline" className="flex-1" onClick={() => setResetPwStep('confirm')}>Kembali</Button>
                                <Button className="flex-1" onClick={handleResetPassword} disabled={resetPwLoading}>
                                    {resetPwLoading ? 'Menyimpan...' : 'Reset Password'}
                                </Button>
                            </div>
                        </div>
                    )}

                    {resetPwSuccess && (
                        <div className="space-y-4 mt-2">
                            <div className="bg-green-50 border border-green-200 rounded-lg p-3 flex items-start gap-2.5">
                                <CheckCircle2 className="w-5 h-5 text-green-600 shrink-0 mt-0.5" />
                                <div className="text-sm text-green-800">
                                    <p className="font-semibold">Berhasil!</p>
                                    <p className="mt-1">{resetPwSuccess}</p>
                                    <p className="mt-2 text-xs text-green-600">Silakan informasikan password baru ini ke user secara pribadi.</p>
                                </div>
                            </div>
                            <Button variant="outline" className="w-full" onClick={() => setResetPwDialogOpen(false)}>Tutup</Button>
                        </div>
                    )}
                </DialogContent>
            </Dialog>
        </div>
    );
}
