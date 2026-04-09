'use client';

import { useEffect, useState } from 'react';
import { useAuthStore } from '@/store/auth.store';
import { useRouter } from 'next/navigation';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { FileText, Download } from 'lucide-react';
import api from '@/lib/axios';

// Component Imports
import { PegawaiDashboardView } from '@/components/PegawaiDashboardView';
import { AdminDashboard, AdminStats } from '@/components/dashboard/AdminDashboard';
import { KetuaTimDashboard, KetuaTimStats } from '@/components/dashboard/KetuaTimDashboard';
import { PimpinanDashboard, PimpinanRekap } from '@/components/dashboard/PimpinanDashboard';

export default function DashboardPage() {
    const { user, activeRole } = useAuthStore();
    const router = useRouter();

    // Stats states
    const [adminStats, setAdminStats] = useState<AdminStats | null>(null);
    const [ketuaStats, setKetuaStats] = useState<KetuaTimStats | null>(null);
    const [pimpinanRekap, setPimpinanRekap] = useState<PimpinanRekap | null>(null);
    const [pegawaiData, setPegawaiData] = useState<any | null>(null);

    // Filter states
    const [pimpinanMonth, setPimpinanMonth] = useState(new Date().getMonth() + 1);
    const [pimpinanYear, setPimpinanYear] = useState(new Date().getFullYear());
    const [ketuaMonth, setKetuaMonth] = useState(new Date().getMonth() + 1);
    const [ketuaYear, setKetuaYear] = useState(new Date().getFullYear());
    const [pegawaiMonth, setPegawaiMonth] = useState(new Date().getMonth() + 1);
    const [pegawaiYear, setPegawaiYear] = useState(new Date().getFullYear());

    const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    useEffect(() => {
        if (!user) { router.push('/login'); }
    }, [user, router]);

    // Data Fetching Logic
    useEffect(() => {
        if (!user) return;

        if (activeRole === 'Ketua Tim') {
            api.get(`/ratings/my-stats?month=${ketuaMonth}&year=${ketuaYear}`)
                .then(res => setKetuaStats(res.data))
                .catch(err => console.error('Failed to fetch stats', err));
        } else if (activeRole === 'Admin') {
            api.get('/users/admin-stats')
                .then(res => setAdminStats(res.data))
                .catch(err => console.error('Failed to fetch admin stats', err));
        } else if (activeRole === 'Pimpinan') {
            api.get(`/ratings/pimpinan-rekap?month=${pimpinanMonth}&year=${pimpinanYear}`)
                .then(res => setPimpinanRekap(res.data))
                .catch(err => console.error('Failed to fetch pimpinan rekap', err));
        } else if (activeRole === 'Pegawai') {
            api.get(`/ratings/pegawai-dashboard?month=${pegawaiMonth}&year=${pegawaiYear}`)
                .then(res => setPegawaiData(res.data))
                .catch(err => console.error('Failed to fetch pegawai dashboard', err));
        }
    }, [activeRole, pimpinanMonth, pimpinanYear, ketuaMonth, ketuaYear, pegawaiMonth, pegawaiYear, user]);

    if (!user) return null;

    // Export Handlers
    const handleExport = (type: 'excel' | 'pdf') => {
        let m = 1, y = 2026;
        if (activeRole === 'Pimpinan') { m = pimpinanMonth; y = pimpinanYear; }
        else if (activeRole === 'Ketua Tim') { m = ketuaMonth; y = ketuaYear; }
        else { m = pegawaiMonth; y = pegawaiYear; }

        const url = type === 'excel' ? `/export/excel?month=${m}&year=${y}` : `/export/pdf?month=${m}&year=${y}`;
        window.open(`${process.env.NEXT_PUBLIC_API_URL || 'http://localhost:5000/api'}${url}`, '_blank');
    };

    return (
        <div className="space-y-8 animate-in fade-in zoom-in-95 duration-500">
            {/* Header Section */}
            <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h2 className="text-3xl font-bold tracking-tight">Dashboard Overview</h2>
                    <p className="text-muted-foreground mt-1 text-lg">
                        Masuk sebagai: <span className="font-semibold text-primary">{activeRole}</span>
                    </p>
                </div>

                <div className="flex gap-3">
                    {/* Period Selector (Dynamic based on role) */}
                    <div className="flex items-center gap-2 mr-2">
                        {activeRole === 'Pimpinan' && (
                            <>
                                <select className="border rounded-md px-3 py-2 text-sm bg-background shadow-sm h-10" value={pimpinanMonth} onChange={e => setPimpinanMonth(Number(e.target.value))}>
                                    {monthNames.map((n, i) => <option key={i} value={i + 1}>{n}</option>)}
                                </select>
                                <Input type="number" className="w-24 h-10 shadow-sm" value={pimpinanYear} onChange={e => setPimpinanYear(Number(e.target.value))} min={2026} max={2030} />
                            </>
                        )}
                        {activeRole === 'Ketua Tim' && (
                            <>
                                <select className="border rounded-md px-3 py-2 text-sm bg-background shadow-sm h-10" value={ketuaMonth} onChange={e => setKetuaMonth(Number(e.target.value))}>
                                    {monthNames.map((n, i) => <option key={i} value={i + 1}>{n}</option>)}
                                </select>
                                <Input type="number" className="w-24 h-10 shadow-sm" value={ketuaYear} onChange={e => setKetuaYear(Number(e.target.value))} min={2026} max={2030} />
                            </>
                        )}
                        {(activeRole === 'Pegawai') && (
                            <>
                                <select className="border rounded-md px-3 py-2 text-sm bg-background shadow-sm h-10" value={pegawaiMonth} onChange={e => setPegawaiMonth(Number(e.target.value))}>
                                    {monthNames.map((n, i) => <option key={i} value={i + 1}>{n}</option>)}
                                </select>
                                <Input type="number" className="w-24 h-10 shadow-sm" value={pegawaiYear} onChange={e => setPegawaiYear(Number(e.target.value))} min={2026} max={2030} />
                            </>
                        )}
                    </div>

                    {/* Global Export Buttons */}
                    {activeRole !== 'Admin' && (
                        <>
                            <Button variant="outline" className="flex gap-2 bg-white hidden md:flex" onClick={() => handleExport('pdf')}>
                                <FileText className="w-4 h-4" /> Export PDF
                            </Button>
                            <Button className="flex gap-2 hidden md:flex" onClick={() => handleExport('excel')}>
                                <Download className="w-4 h-4" /> Export Excel
                            </Button>
                        </>
                    )}
                </div>
            </div>

            {/* Dashboard View Switcher */}
            {activeRole === 'Admin' && adminStats && <AdminDashboard stats={adminStats} />}
            
            {activeRole === 'Ketua Tim' && ketuaStats && (
                <KetuaTimDashboard 
                    stats={ketuaStats} 
                    monthNames={monthNames} 
                    ketuaMonth={ketuaMonth} 
                    ketuaYear={ketuaYear} 
                />
            )}

            {activeRole === 'Pimpinan' && pimpinanRekap && (
                <PimpinanDashboard 
                    rekap={pimpinanRekap} 
                    monthNames={monthNames} 
                    pegawaiMonth={pegawaiMonth} 
                    pegawaiYear={pegawaiYear} 
                />
            )}

            {activeRole === 'Pegawai' && pegawaiData && (
                <PegawaiDashboardView data={pegawaiData} monthNames={monthNames} />
            )}
        </div>
    );
}
