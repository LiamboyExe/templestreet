import type { User } from './auth';

export type MembershipType = 'Regular' | 'Premium' | 'Student' | 'Senior';
export type MemberStatus = 'Active' | 'Inactive' | 'Pending';
export type NfcCardStatus = 'Active' | 'Inactive' | 'Lost';

export type Branch = {
    id: number;
    name: string;
    location: string;
    manager: string;
    phone: string;
    email: string;
    members_count?: number;
    active_members_count?: number;
    inactive_members_count?: number;
    pending_members_count?: number;
    local_admins_count?: number;
    admins?: User[];
    created_at: string;
    updated_at: string;
};

export type Member = {
    id: number;
    branch_id: number;
    first_name: string;
    last_name: string;
    full_name: string;
    initials: string;
    email: string;
    phone: string | null;
    address: string | null;
    membership_type: MembershipType;
    status: MemberStatus;
    join_date: string;
    profile_slug: string;
    approved: boolean;
    branch?: Branch;
    nfc_card?: NfcCard | null;
    profile_url?: string;
    created_at: string;
    updated_at: string;
};

export type NfcCard = {
    id: number;
    member_id: number;
    card_uid: string;
    profile_url: string;
    card_status: NfcCardStatus;
    issued_date: string;
    notes: string | null;
    member?: Member;
    created_at: string;
    updated_at: string;
};

export type Paginated<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
};
