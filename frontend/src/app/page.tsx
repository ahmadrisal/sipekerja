'use client';

import { useEffect, useState } from 'react';
import { useAuthStore } from '@/store/auth.store';
import { useRouter } from 'next/navigation';

export default function Home() {
    const { token, user } = useAuthStore();
    const router = useRouter();
    const [isHydrated, setIsHydrated] = useState(false);

    // Handle Zustand hydration
    useEffect(() => {
        // We use a small timeout to ensure Zustand persist has had a chance to load from localStorage
        const checkHydration = () => {
            if (useAuthStore.persist.hasHydrated()) {
                setIsHydrated(true);
            } else {
                setTimeout(checkHydration, 50);
            }
        };
        checkHydration();
    }, []);

    useEffect(() => {
        if (!isHydrated) return;

        if (token && user) {
            router.replace('/dashboard');
        } else {
            router.replace('/login');
        }
    }, [token, user, router, isHydrated]);

    return (
        <div className="flex min-h-[80vh] flex-col items-center justify-center space-y-4 animate-in fade-in duration-700">
            <div className="w-16 h-16 bg-primary rounded-2xl flex items-center justify-center shadow-xl shadow-primary/20 animate-bounce">
                <span className="text-primary-foreground font-bold text-3xl">S</span>
            </div>
            <div className="flex flex-col items-center space-y-2">
                <h1 className="text-2xl font-bold tracking-tight text-slate-900">SIPEKERJA</h1>
                <p className="text-slate-500 animate-pulse">Menyiapkan dashboard Anda...</p>
            </div>
        </div>
    );
}
