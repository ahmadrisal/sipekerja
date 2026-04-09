import { create } from 'zustand';
import { persist } from 'zustand/middleware';

interface User {
    id: string;
    nip: string;
    username: string;
    name: string;
    roles: string[];
}

interface AuthState {
    user: User | null;
    token: string | null;
    activeRole: string | null;
    setAuth: (user: User, token: string) => void;
    logout: () => void;
    switchRole: (role: string) => void;
}

export const useAuthStore = create<AuthState>()(
    persist(
        (set) => ({
            user: null,
            token: null,
            activeRole: null,
            setAuth: (user, token) =>
                set({
                    user,
                    token,
                    // Default to the first role or Pegawai
                    activeRole: user.roles.includes('Ketua Tim')
                        ? 'Ketua Tim'
                        : user.roles[0] || 'Pegawai',
                }),
            logout: () => set({ user: null, token: null, activeRole: null }),
            switchRole: (role) => set({ activeRole: role }),
        }),
        {
            name: 'auth-storage',
        }
    )
);
