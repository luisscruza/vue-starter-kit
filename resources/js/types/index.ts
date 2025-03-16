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

export interface TeamMembership {
    team_id: number;
    role_id: number;
    created_at: string;
}

export interface TeamMember {
    id: number;
    name: string;
    email: string;
    role: 'owner' | 'admin' | 'member';
    role_id: number;
    membership: TeamMembership;
}

export interface TeamInvitation {
    id: number;
    email: string;
    role: 'admin' | 'member';
    created_at: string;
}

export interface TeamRole {
    id: number;
    name: string;
}

export interface Team {
    id: number;
    name: string;
    owner: User;
    members?: TeamMember[];
    invitations?: TeamInvitation[];
    roles: TeamRole[];
    currentUserRole?: 'owner' | 'admin' | 'member';
    created_at: string;
    updated_at: string;
}

export type { BreadcrumbItem as BreadcrumbItemType };
