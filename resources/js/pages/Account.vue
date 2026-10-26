<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Building2, KeyRound, Palette, Shield, UserRound } from '@lucide/vue';
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { account } from '@/routes';
import { edit as editProfile } from '@/routes/profile';
import { edit as editSecurity } from '@/routes/security';
import { edit as editAppearance } from '@/routes/appearance';
import type { User } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'My Account',
                href: account(),
            },
        ],
    },
});

const props = defineProps<{
    user: User;
}>();

const initials = computed(() =>
    props.user.name
        .split(' ')
        .map((part) => part[0])
        .join('')
        .slice(0, 2)
        .toUpperCase(),
);
</script>

<template>
    <Head title="My Account" />

    <div class="flex flex-col gap-6 p-4 md:p-6">
        <div>
            <p
                class="font-mono text-[11px] tracking-[0.2em] text-muted-foreground uppercase"
            >
                Operator
            </p>
            <h1 class="mt-1 text-2xl font-semibold tracking-tight">
                My Account
            </h1>
            <p class="mt-1 text-sm text-muted-foreground">
                Your administrator profile and security settings.
            </p>
        </div>

        <div class="grid gap-4 lg:grid-cols-3">
            <Card class="lg:col-span-2">
                <CardHeader>
                    <CardTitle class="text-base">Identity</CardTitle>
                    <CardDescription>
                        Account details used across the console.
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-5">
                    <div class="flex items-center gap-4">
                        <div
                            class="flex size-14 items-center justify-center rounded-full bg-muted text-lg font-semibold"
                        >
                            {{ initials }}
                        </div>
                        <div>
                            <p class="text-lg font-medium">{{ user.name }}</p>
                            <p class="text-sm text-muted-foreground">
                                {{ user.email }}
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-4 text-sm sm:grid-cols-2">
                        <div>
                            <p class="text-xs text-muted-foreground">
                                Username
                            </p>
                            <p class="mt-1 font-mono">
                                {{ user.username ?? '—' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground">Role</p>
                            <div class="mt-1">
                                <Badge
                                    variant="outline"
                                    class="gap-1 font-mono text-[11px]"
                                >
                                    <Shield class="size-3" />
                                    {{
                                        user.role === 'main'
                                            ? 'Main Admin'
                                            : 'Branch Admin'
                                    }}
                                </Badge>
                            </div>
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground">Branch</p>
                            <p class="mt-1 flex items-center gap-1.5">
                                <Building2 class="size-3.5" />
                                {{ user.branch?.name ?? 'All branches' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground">
                                Email verified
                            </p>
                            <p class="mt-1">
                                {{ user.email_verified_at ? 'Yes' : 'No' }}
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle class="text-base">Quick actions</CardTitle>
                    <CardDescription>
                        Manage your profile and sign-in security.
                    </CardDescription>
                </CardHeader>
                <CardContent class="flex flex-col gap-2">
                    <Button as-child variant="outline" class="justify-start">
                        <Link :href="editProfile()">
                            <UserRound class="size-4" />
                            Profile details
                        </Link>
                    </Button>
                    <Button as-child variant="outline" class="justify-start">
                        <Link :href="editSecurity()">
                            <KeyRound class="size-4" />
                            Password & security
                        </Link>
                    </Button>
                    <Button as-child variant="outline" class="justify-start">
                        <Link :href="editAppearance()">
                            <Palette class="size-4" />
                            Appearance
                        </Link>
                    </Button>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
