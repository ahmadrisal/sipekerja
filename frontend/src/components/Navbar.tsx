'use client';

import { useState } from 'react';
import { useAuthStore } from '../store/auth.store';
import { RoleSwitcher } from './RoleSwitcher';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { LogOut, KeyRound, Eye, EyeOff } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog';
import Link from 'next/link';
import api from '@/lib/axios';

export function Navbar() {
    const { user, logout, activeRole } = useAuthStore();
    const [showPasswordDialog, setShowPasswordDialog] = useState(false);
    const [currentPassword, setCurrentPassword] = useState('');
    const [newPassword, setNewPassword] = useState('');
    const [confirmPassword, setConfirmPassword] = useState('');
    const [showCurrent, setShowCurrent] = useState(false);
    const [showNew, setShowNew] = useState(false);
    const [pwError, setPwError] = useState('');
    const [pwSuccess, setPwSuccess] = useState('');
    const [pwLoading, setPwLoading] = useState(false);

    const handleChangePassword = async (e: React.FormEvent) => {
        e.preventDefault();
        setPwError('');
        setPwSuccess('');

        if (!currentPassword || !newPassword) {
            setPwError('Semua field wajib diisi.');
            return;
        }
        if (newPassword.trim().length === 0) {
            setPwError('Password baru tidak boleh hanya berisi spasi.');
            return;
        }
        if (newPassword !== confirmPassword) {
            setPwError('Konfirmasi password tidak cocok.');
            return;
        }

        setPwLoading(true);
        try {
            const res = await api.put('/auth/change-password', { currentPassword, newPassword });
            setPwSuccess(res.data.message || 'Password berhasil diubah!');
            setCurrentPassword('');
            setNewPassword('');
            setConfirmPassword('');
        } catch (err: any) {
            setPwError(err?.response?.data?.error || 'Gagal mengubah password.');
        } finally {
            setPwLoading(false);
        }
    };

    const openPasswordDialog = () => {
        setPwError('');
        setPwSuccess('');
        setCurrentPassword('');
        setNewPassword('');
        setConfirmPassword('');
        setShowCurrent(false);
        setShowNew(false);
        setShowPasswordDialog(true);
    };

    return (
        <>
            <header className="sticky top-0 z-50 w-full border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60 shadow-sm">
                <div className="container mx-auto flex h-16 items-center px-4 justify-between">
                    <div className="flex items-center justify-between gap-8">
                        <div className="flex items-center gap-2">
                            <div className="w-8 h-8 bg-primary rounded-lg flex items-center justify-center">
                                <span className="text-primary-foreground font-bold text-lg">S</span>
                            </div>
                            <span className="font-bold text-xl text-primary tracking-tight mr-4">SIPEKERJA</span>
                        </div>

                        {user && (
                            <nav className="hidden md:flex items-center gap-6">
                                <Link href="/dashboard" className="text-sm font-medium text-muted-foreground hover:text-primary transition-colors">
                                    Dashboard
                                </Link>
                                {activeRole === 'Admin' && (
                                    <Link href="/users" className="text-sm font-medium text-muted-foreground hover:text-primary transition-colors">
                                        Manajemen Pengguna
                                    </Link>
                                )}
                                {(activeRole === 'Admin' || activeRole === 'Pimpinan') && (
                                    <Link href="/teams" className="text-sm font-medium text-muted-foreground hover:text-primary transition-colors">
                                        Manajemen Tim
                                    </Link>
                                )}
                                {activeRole === 'Ketua Tim' && (
                                    <Link href="/penilaian" className="text-sm font-medium text-muted-foreground hover:text-primary transition-colors">
                                        Penilaian Kinerja
                                    </Link>
                                )}
                            </nav>
                        )}
                    </div>

                    {user && (
                        <div className="flex items-center gap-4">
                            <RoleSwitcher />
                            <div className="flex items-center gap-2 border-l pl-4">
                                <Avatar className="w-8 h-8">
                                    <AvatarFallback className="bg-primary/10 text-primary font-medium">
                                        {user.name.charAt(0).toUpperCase()}
                                    </AvatarFallback>
                                </Avatar>
                                <div className="hidden md:flex flex-col">
                                    <span className="text-sm font-semibold">{user.name}</span>
                                    <span className="text-xs text-muted-foreground">@{user.username}</span>
                                </div>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    onClick={openPasswordDialog}
                                    className="ml-1 text-muted-foreground hover:text-amber-600"
                                    title="Ubah Password"
                                >
                                    <KeyRound className="w-4 h-4" />
                                </Button>
                                <Button variant="ghost" size="icon" onClick={logout} className="text-muted-foreground hover:text-destructive" title="Logout">
                                    <LogOut className="w-4 h-4" />
                                </Button>
                            </div>
                        </div>
                    )}
                </div>
            </header>

            {/* Change Password Dialog */}
            <Dialog open={showPasswordDialog} onOpenChange={setShowPasswordDialog}>
                <DialogContent className="max-w-sm">
                    <DialogHeader>
                        <DialogTitle className="flex items-center gap-2">
                            <KeyRound className="w-5 h-5 text-primary" />
                            Ubah Password
                        </DialogTitle>
                        <DialogDescription>
                            Masukkan password lama dan password baru Anda.
                        </DialogDescription>
                    </DialogHeader>
                    <form onSubmit={handleChangePassword} className="space-y-4 mt-2">
                        {pwError && (
                            <div className="bg-destructive/15 text-destructive text-sm p-3 rounded-md border border-destructive/20">
                                {pwError}
                            </div>
                        )}
                        {pwSuccess && (
                            <div className="bg-green-50 text-green-700 text-sm p-3 rounded-md border border-green-200">
                                {pwSuccess}
                            </div>
                        )}

                        <div className="space-y-2">
                            <Label htmlFor="current-pw">Password Lama</Label>
                            <div className="relative">
                                <Input
                                    id="current-pw"
                                    type={showCurrent ? 'text' : 'password'}
                                    value={currentPassword}
                                    onChange={e => setCurrentPassword(e.target.value)}
                                    placeholder="••••••••"
                                    required
                                />
                                <button
                                    type="button"
                                    className="absolute right-2 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                                    onClick={() => setShowCurrent(!showCurrent)}
                                >
                                    {showCurrent ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                                </button>
                            </div>
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="new-pw">Password Baru</Label>
                            <div className="relative">
                                <Input
                                    id="new-pw"
                                    type={showNew ? 'text' : 'password'}
                                    value={newPassword}
                                    onChange={e => setNewPassword(e.target.value)}
                                    placeholder="••••••••"
                                    required
                                />
                                <button
                                    type="button"
                                    className="absolute right-2 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                                    onClick={() => setShowNew(!showNew)}
                                >
                                    {showNew ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                                </button>
                            </div>
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="confirm-pw">Konfirmasi Password Baru</Label>
                            <Input
                                id="confirm-pw"
                                type="password"
                                value={confirmPassword}
                                onChange={e => setConfirmPassword(e.target.value)}
                                placeholder="••••••••"
                                required
                            />
                        </div>

                        <Button type="submit" className="w-full" disabled={pwLoading}>
                            {pwLoading ? 'Menyimpan...' : 'Simpan Password Baru'}
                        </Button>
                    </form>
                </DialogContent>
            </Dialog>
        </>
    );
}
