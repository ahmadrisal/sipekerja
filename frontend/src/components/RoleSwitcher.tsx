'use client';

import { useAuthStore } from '../store/auth.store';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Button } from '@/components/ui/button';
import { ChevronDown, Shield } from 'lucide-react';

export function RoleSwitcher() {
    const { user, activeRole, switchRole } = useAuthStore();

    if (!user || user.roles.length <= 1) return null;

    return (
        <DropdownMenu>
            <DropdownMenuTrigger className="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none border border-input bg-background shadow-sm hover:bg-accent hover:text-accent-foreground h-9 px-4 py-2 border-primary/20 hover:bg-primary/5">
                <Shield className="w-4 h-4 text-primary" />
                <span className="max-w-[150px] truncate font-medium">{activeRole}</span>
                <ChevronDown className="w-4 h-4 opacity-50" />
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" className="w-[200px]">
                {user.roles.map((role) => (
                    <DropdownMenuItem
                        key={role}
                        onClick={() => switchRole(role)}
                        className={activeRole === role ? 'bg-primary/10 text-primary font-semibold' : ''}
                    >
                        {role}
                    </DropdownMenuItem>
                ))}
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
