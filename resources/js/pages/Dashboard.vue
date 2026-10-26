<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    Building2,
    CreditCard,
    MapPin,
    ShieldCheck,
    UserCheck,
    Users,
} from '@lucide/vue';
import { computed } from 'vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { formatDate } from '@/lib/format';
import { dashboard } from '@/routes';
import members from '@/routes/members';
import type { Branch, Member } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});

const props = defineProps<{
    stats: {
        totalMembers: number;
        activeMembers: number;
        totalBranches: number;
        totalCards: number;
        activeCards: number;
    };
    recentMembers: Member[];
    branchStats: Branch[];
    isMainAdmin: boolean;
}>();

const page = usePage();
const user = computed(() => page.props.auth.user);

const statCards = computed(() => [
    {
        label: 'Total members',
        value: props.stats.totalMembers,
        hint: `${props.stats.activeMembers} active`,
        icon: Users,
    },
    {
        label: 'Active members',
        value: props.stats.activeMembers,
        hint: props.stats.totalMembers
            ? `${Math.round((props.stats.activeMembers / props.stats.totalMembers) * 100)}% of members`
            : 'No members yet',
        icon: UserCheck,
    },
    {
        label: 'Branches',
        value: props.stats.totalBranches,
        hint: props.isMainAdmin ? 'Across the network' : 'Your branch',
        icon: Building2,
    },
    {
        label: 'NFC cards',
        value: props.stats.totalCards,
        hint: `${props.stats.activeCards} active`,
        icon: CreditCard,
    },
]);

function branchShare(count: number): number {
    return props.stats.totalMembers > 0
        ? Math.round((count / props.stats.totalMembers) * 100)
        : 0;
}
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex flex-col gap-6 p-4 md:p-6">
        <div
            class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end"
        >
            <div>
                <p
                    class="font-mono text-[11px] tracking-[0.2em] text-muted-foreground uppercase"
                >
                    Temple Street / Command center
                </p>
                <h1 class="mt-1 text-2xl font-semibold tracking-tight">
                    Welcome back, {{ user.name }}
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    {{
                        isMainAdmin
                            ? 'Network-wide membership and NFC card overview.'
                            : `Membership overview for ${user.branch?.name ?? 'your branch'}.`
                    }}
                </p>
            </div>

            <div class="flex items-center gap-2">
                <Badge variant="outline" class="gap-1.5 font-mono text-[11px]">
                    <ShieldCheck class="size-3.5 text-emerald-500" />
                    {{ isMainAdmin ? 'MAIN ADMIN' : 'BRANCH ADMIN' }}
                </Badge>
                <Button as-child size="sm">
                    <Link :href="members.index()">Manage members</Link>
                </Button>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <Card v-for="card in statCards" :key="card.label" class="py-5">
                <CardHeader
                    class="flex flex-row items-center justify-between space-y-0 pb-2"
                >
                    <CardTitle
                        class="font-mono text-[11px] font-medium tracking-[0.16em] text-muted-foreground uppercase"
                    >
                        {{ card.label }}
                    </CardTitle>
                    <component
                        :is="card.icon"
                        class="size-4 text-muted-foreground"
                    />
                </CardHeader>
                <CardContent>
                    <p class="text-3xl font-semibold tracking-tight">
                        {{ card.value }}
                    </p>
                    <p class="mt-1 text-xs text-muted-foreground">
                        {{ card.hint }}
                    </p>
                </CardContent>
            </Card>
        </div>

        <div class="grid gap-4 xl:grid-cols-5">
            <Card class="xl:col-span-2">
                <CardHeader>
                    <CardTitle class="text-base">Branch breakdown</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <template v-if="isMainAdmin">
                        <div
                            v-for="branch in branchStats"
                            :key="branch.id"
                            class="space-y-2"
                        >
                            <div
                                class="flex items-start justify-between gap-3 text-sm"
                            >
                                <div>
                                    <p class="font-medium">{{ branch.name }}</p>
                                    <p
                                        class="flex items-center gap-1 text-xs text-muted-foreground"
                                    >
                                        <MapPin class="size-3" />
                                        {{ branch.location }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="font-mono text-sm">
                                        {{ branch.members_count }}
                                    </p>
                                    <p
                                        class="text-[11px] text-muted-foreground"
                                    >
                                        {{ branch.active_members_count }} active
                                        ·
                                        {{
                                            branchShare(
                                                branch.members_count ?? 0,
                                            )
                                        }}% of total
                                    </p>
                                </div>
                            </div>
                            <div
                                class="h-1.5 overflow-hidden rounded-full bg-muted"
                            >
                                <div
                                    class="h-full rounded-full bg-emerald-500"
                                    :style="{
                                        width: `${branchShare(branch.members_count ?? 0)}%`,
                                    }"
                                />
                            </div>
                        </div>

                        <p
                            v-if="!branchStats.length"
                            class="py-6 text-center text-sm text-muted-foreground"
                        >
                            No branches yet.
                        </p>
                    </template>

                    <div v-else-if="user.branch" class="space-y-3">
                        <div class="rounded-lg border bg-muted/30 p-4">
                            <p class="font-medium">{{ user.branch.name }}</p>
                            <p
                                class="mt-1 flex items-center gap-1 text-xs text-muted-foreground"
                            >
                                <MapPin class="size-3" />
                                {{ user.branch.location }}
                            </p>
                            <p class="mt-3 text-xs text-muted-foreground">
                                Manager:
                                <span class="text-foreground">{{
                                    user.branch.manager
                                }}</span>
                            </p>
                        </div>
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div class="rounded-lg border p-3">
                                <p class="text-xs text-muted-foreground">
                                    Members
                                </p>
                                <p class="font-mono text-lg">
                                    {{ stats.totalMembers }}
                                </p>
                            </div>
                            <div class="rounded-lg border p-3">
                                <p class="text-xs text-muted-foreground">
                                    Active
                                </p>
                                <p class="font-mono text-lg">
                                    {{ stats.activeMembers }}
                                </p>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card class="xl:col-span-3">
                <CardHeader class="flex flex-row items-center justify-between">
                    <CardTitle class="text-base">Recent members</CardTitle>
                    <Button as-child variant="ghost" size="sm">
                        <Link :href="members.index()">View all</Link>
                    </Button>
                </CardHeader>
                <CardContent>
                    <div class="divide-y">
                        <div
                            v-for="member in recentMembers"
                            :key="member.id"
                            class="flex items-center gap-3 py-3 first:pt-0 last:pb-0"
                        >
                            <div
                                class="flex size-9 shrink-0 items-center justify-center rounded-full bg-muted font-mono text-xs font-semibold"
                            >
                                {{ member.initials }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium">
                                    {{ member.full_name }}
                                </p>
                                <p
                                    class="truncate text-xs text-muted-foreground"
                                >
                                    {{ member.email }}
                                </p>
                            </div>
                            <p
                                class="hidden text-xs text-muted-foreground sm:block"
                            >
                                {{ member.branch?.name }}
                            </p>
                            <StatusBadge :status="member.status" />
                            <span
                                class="hidden w-24 text-right font-mono text-[11px] text-muted-foreground md:block"
                            >
                                {{ formatDate(member.join_date) }}
                            </span>
                        </div>

                        <p
                            v-if="!recentMembers.length"
                            class="py-6 text-center text-sm text-muted-foreground"
                        >
                            No members yet. Add your first member to get
                            started.
                        </p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
