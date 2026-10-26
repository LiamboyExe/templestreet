<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Pencil, Plus, Search, Trash2 } from '@lucide/vue';
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { formatDate, today } from '@/lib/format';
import {
    destroy as destroyMember,
    index as membersIndex,
    store as storeMember,
    update as updateMember,
} from '@/routes/members';
import type {
    Branch,
    Member,
    MemberStatus,
    MembershipType,
    Paginated,
} from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Members',
                href: membersIndex(),
            },
        ],
    },
});

const props = defineProps<{
    members: Paginated<Member>;
    branches: Branch[];
    filters: {
        q: string;
        status: string;
        branch: number;
    };
    isMainAdmin: boolean;
    membershipTypes: MembershipType[];
    statuses: MemberStatus[];
}>();

const filterForm = ref({
    q: props.filters.q,
    status: props.filters.status,
    branch: props.filters.branch || '',
});

const formOpen = ref(false);
const editing = ref<Member | null>(null);

const memberForm = useForm({
    branch_id: props.branches[0]?.id ?? 0,
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    address: '',
    membership_type: 'Regular' as MembershipType,
    status: 'Active' as MemberStatus,
    join_date: today(),
    approved: true,
});

const deleteOpen = ref(false);
const deleteTarget = ref<Member | null>(null);

const formTitle = computed(() =>
    editing.value ? 'Edit member' : 'Add member',
);

function applyFilters(): void {
    router.get(
        membersIndex.url(),
        {
            q: filterForm.value.q || undefined,
            status: filterForm.value.status || undefined,
            branch: filterForm.value.branch || undefined,
        },
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        },
    );
}

function clearFilters(): void {
    filterForm.value = { q: '', status: '', branch: '' };
    router.get(membersIndex.url(), {}, { preserveScroll: true });
}

function openCreate(): void {
    editing.value = null;
    memberForm.reset();
    memberForm.clearErrors();
    memberForm.branch_id = props.filters.branch || props.branches[0]?.id || 0;
    formOpen.value = true;
}

function openEdit(member: Member): void {
    editing.value = member;
    memberForm.clearErrors();
    memberForm.branch_id = member.branch_id;
    memberForm.first_name = member.first_name;
    memberForm.last_name = member.last_name;
    memberForm.email = member.email;
    memberForm.phone = member.phone ?? '';
    memberForm.address = member.address ?? '';
    memberForm.membership_type = member.membership_type;
    memberForm.status = member.status;
    memberForm.join_date = member.join_date.slice(0, 10);
    memberForm.approved = member.approved;
    formOpen.value = true;
}

function submit(): void {
    memberForm.transform((data) => ({
        ...data,
        approved: data.approved ? 1 : 0,
    }));

    const options = {
        preserveScroll: true,
        onSuccess: () => {
            formOpen.value = false;
            editing.value = null;
        },
    };

    if (editing.value) {
        memberForm.patch(updateMember(editing.value.id).url, options);
    } else {
        memberForm.post(storeMember().url, options);
    }
}

function confirmDelete(member: Member): void {
    deleteTarget.value = member;
    deleteOpen.value = true;
}

function destroy(): void {
    if (!deleteTarget.value) {
        return;
    }

    router.delete(destroyMember(deleteTarget.value.id).url, {
        preserveScroll: true,
        onSuccess: () => {
            deleteOpen.value = false;
            deleteTarget.value = null;
        },
    });
}

function goToPage(page: number): void {
    router.get(
        membersIndex.url(),
        {
            ...props.filters,
            page,
        },
        {
            preserveScroll: true,
            preserveState: true,
        },
    );
}

const selectClass =
    'h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 dark:bg-input/30';
</script>

<template>
    <Head title="Members" />

    <div class="flex flex-col gap-6 p-4 md:p-6">
        <div
            class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center"
        >
            <div>
                <p
                    class="font-mono text-[11px] tracking-[0.2em] text-muted-foreground uppercase"
                >
                    Registry
                </p>
                <h1 class="mt-1 text-2xl font-semibold tracking-tight">
                    Members
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    {{ members.total }} member{{
                        members.total === 1 ? '' : 's'
                    }}
                    in view.
                </p>
            </div>

            <Button @click="openCreate">
                <Plus class="size-4" />
                Add member
            </Button>
        </div>

        <Card>
            <CardContent>
                <form
                    class="flex flex-col gap-3 md:flex-row md:items-end"
                    @submit.prevent="applyFilters"
                >
                    <div class="flex-1 space-y-1.5">
                        <Label for="search">Search</Label>
                        <div class="relative">
                            <Search
                                class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                            />
                            <Input
                                id="search"
                                v-model="filterForm.q"
                                placeholder="Name or email"
                                class="pl-9"
                            />
                        </div>
                    </div>

                    <div class="space-y-1.5 md:w-40">
                        <Label for="status">Status</Label>
                        <select
                            id="status"
                            v-model="filterForm.status"
                            :class="selectClass"
                        >
                            <option value="">All statuses</option>
                            <option
                                v-for="status in statuses"
                                :key="status"
                                :value="status"
                            >
                                {{ status }}
                            </option>
                        </select>
                    </div>

                    <div v-if="isMainAdmin" class="space-y-1.5 md:w-48">
                        <Label for="branch">Branch</Label>
                        <select
                            id="branch"
                            v-model="filterForm.branch"
                            :class="selectClass"
                        >
                            <option value="">All branches</option>
                            <option
                                v-for="branch in branches"
                                :key="branch.id"
                                :value="branch.id"
                            >
                                {{ branch.name }}
                            </option>
                        </select>
                    </div>

                    <div class="flex gap-2">
                        <Button type="submit">Filter</Button>
                        <Button
                            type="button"
                            variant="ghost"
                            @click="clearFilters"
                            >Reset</Button
                        >
                    </div>
                </form>
            </CardContent>
        </Card>

        <Card class="overflow-hidden py-0">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b bg-muted/40 text-left">
                            <th class="px-4 py-3 font-medium">Member</th>
                            <th class="px-4 py-3 font-medium">Branch</th>
                            <th class="px-4 py-3 font-medium">Type</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium">Joined</th>
                            <th class="px-4 py-3 text-right font-medium">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="member in members.data"
                            :key="member.id"
                            class="border-b last:border-0 hover:bg-muted/20"
                        >
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex size-9 shrink-0 items-center justify-center rounded-full bg-muted font-mono text-xs font-semibold"
                                    >
                                        {{ member.initials }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate font-medium">
                                            {{ member.full_name }}
                                        </p>
                                        <p
                                            class="truncate text-xs text-muted-foreground"
                                        >
                                            {{ member.email }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                {{ member.branch?.name }}
                            </td>
                            <td class="px-4 py-3">
                                <StatusBadge :status="member.membership_type" />
                            </td>
                            <td class="px-4 py-3">
                                <StatusBadge :status="member.status" />
                            </td>
                            <td
                                class="px-4 py-3 font-mono text-xs text-muted-foreground"
                            >
                                {{ formatDate(member.join_date) }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-1">
                                    <Button
                                        variant="ghost"
                                        size="icon-sm"
                                        aria-label="Edit member"
                                        @click="openEdit(member)"
                                    >
                                        <Pencil class="size-4" />
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon-sm"
                                        aria-label="Delete member"
                                        class="text-destructive hover:text-destructive"
                                        @click="confirmDelete(member)"
                                    >
                                        <Trash2 class="size-4" />
                                    </Button>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="!members.data.length">
                            <td
                                colspan="6"
                                class="px-4 py-12 text-center text-sm text-muted-foreground"
                            >
                                No members match your filters.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div
                class="flex flex-col gap-2 border-t px-4 py-3 text-xs text-muted-foreground sm:flex-row sm:items-center sm:justify-between"
            >
                <p>
                    Showing {{ members.from ?? 0 }}–{{ members.to ?? 0 }} of
                    {{ members.total }}
                </p>
                <div class="flex items-center gap-2">
                    <Button
                        variant="outline"
                        size="sm"
                        :disabled="members.current_page <= 1"
                        @click="goToPage(members.current_page - 1)"
                    >
                        Previous
                    </Button>
                    <span class="font-mono"
                        >{{ members.current_page }} /
                        {{ members.last_page }}</span
                    >
                    <Button
                        variant="outline"
                        size="sm"
                        :disabled="members.current_page >= members.last_page"
                        @click="goToPage(members.current_page + 1)"
                    >
                        Next
                    </Button>
                </div>
            </div>
        </Card>
    </div>

    <Dialog v-model:open="formOpen">
        <DialogContent class="sm:max-w-2xl">
            <form class="space-y-4" @submit.prevent="submit">
                <DialogHeader>
                    <DialogTitle>{{ formTitle }}</DialogTitle>
                    <DialogDescription>
                        {{
                            editing
                                ? 'Update this member’s details.'
                                : 'Add a new member to the registry.'
                        }}
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-1.5">
                        <Label for="first_name">First name</Label>
                        <Input
                            id="first_name"
                            v-model="memberForm.first_name"
                        />
                        <InputError :message="memberForm.errors.first_name" />
                    </div>
                    <div class="space-y-1.5">
                        <Label for="last_name">Last name</Label>
                        <Input id="last_name" v-model="memberForm.last_name" />
                        <InputError :message="memberForm.errors.last_name" />
                    </div>
                    <div class="space-y-1.5">
                        <Label for="email">Email</Label>
                        <Input
                            id="email"
                            type="email"
                            v-model="memberForm.email"
                        />
                        <InputError :message="memberForm.errors.email" />
                    </div>
                    <div class="space-y-1.5">
                        <Label for="phone">Phone</Label>
                        <Input id="phone" v-model="memberForm.phone" />
                        <InputError :message="memberForm.errors.phone" />
                    </div>
                    <div class="space-y-1.5 sm:col-span-2">
                        <Label for="address">Address</Label>
                        <Input id="address" v-model="memberForm.address" />
                        <InputError :message="memberForm.errors.address" />
                    </div>
                    <div class="space-y-1.5">
                        <Label for="membership_type">Membership type</Label>
                        <select
                            id="membership_type"
                            v-model="memberForm.membership_type"
                            :class="selectClass"
                        >
                            <option
                                v-for="type in membershipTypes"
                                :key="type"
                                :value="type"
                            >
                                {{ type }}
                            </option>
                        </select>
                        <InputError
                            :message="memberForm.errors.membership_type"
                        />
                    </div>
                    <div class="space-y-1.5">
                        <Label for="member_status">Status</Label>
                        <select
                            id="member_status"
                            v-model="memberForm.status"
                            :class="selectClass"
                        >
                            <option
                                v-for="status in statuses"
                                :key="status"
                                :value="status"
                            >
                                {{ status }}
                            </option>
                        </select>
                        <InputError :message="memberForm.errors.status" />
                    </div>
                    <div class="space-y-1.5">
                        <Label for="join_date">Join date</Label>
                        <Input
                            id="join_date"
                            type="date"
                            v-model="memberForm.join_date"
                        />
                        <InputError :message="memberForm.errors.join_date" />
                    </div>
                    <div class="space-y-1.5">
                        <Label for="branch_id">Branch</Label>
                        <select
                            id="branch_id"
                            v-model.number="memberForm.branch_id"
                            :class="selectClass"
                            :disabled="!isMainAdmin"
                        >
                            <option
                                v-for="branch in branches"
                                :key="branch.id"
                                :value="branch.id"
                            >
                                {{ branch.name }}
                            </option>
                        </select>
                        <InputError :message="memberForm.errors.branch_id" />
                    </div>
                </div>

                <label class="flex items-start gap-2 text-sm">
                    <Checkbox
                        id="approved"
                        v-model="memberForm.approved"
                        class="mt-0.5"
                    />
                    <span>
                        Public profile approved
                        <span class="block text-xs text-muted-foreground">
                            Approved members are visible on their NFC profile
                            page.
                        </span>
                    </span>
                </label>

                <DialogFooter>
                    <Button
                        type="button"
                        variant="secondary"
                        @click="formOpen = false"
                    >
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="memberForm.processing">
                        {{ editing ? 'Save changes' : 'Add member' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>

    <Dialog v-model:open="deleteOpen">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Delete member?</DialogTitle>
                <DialogDescription>
                    {{ deleteTarget?.full_name }} and their NFC card will be
                    permanently removed.
                </DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button variant="secondary" @click="deleteOpen = false">
                    Cancel
                </Button>
                <Button variant="destructive" @click="destroy">
                    Delete member
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
