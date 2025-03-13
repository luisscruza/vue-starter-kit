import type { PageProps } from '@inertiajs/core';
import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
    teams: Team[] | null;
    currentTeam: Team | null;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
}

export interface SharedData extends PageProps {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export interface TeamMember extends User {
    role: 'admin' | 'member';
    membership: {
        created_at: string;
    };
}

export interface TeamInvitation {
    id: number;
    email: string;
    role: 'admin' | 'member';
    created_at: string;
}

export interface Team {
    id: number;
    name: string;
    owner: User;
    members?: TeamMember[];
    invitations?: TeamInvitation[];
    currentUserRole?: 'owner' | 'admin' | 'member';
    created_at: string;
    updated_at: string;
}

export type { BreadcrumbItem as BreadcrumbItemType };
