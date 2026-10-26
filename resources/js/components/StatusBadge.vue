<script setup lang="ts">
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';

const props = defineProps<{
    status: string;
}>();

const classes = computed(() => {
    switch (props.status) {
        case 'Active':
            return 'border-emerald-500/30 bg-emerald-500/10 text-emerald-700 dark:text-emerald-300';
        case 'Pending':
            return 'border-amber-500/30 bg-amber-500/10 text-amber-700 dark:text-amber-300';
        case 'Inactive':
            return 'border-zinc-500/30 bg-zinc-500/10 text-zinc-600 dark:text-zinc-300';
        case 'Lost':
            return 'border-rose-500/30 bg-rose-500/10 text-rose-700 dark:text-rose-300';
        case 'Premium':
            return 'border-violet-500/30 bg-violet-500/10 text-violet-700 dark:text-violet-300';
        case 'Student':
            return 'border-sky-500/30 bg-sky-500/10 text-sky-700 dark:text-sky-300';
        case 'Senior':
            return 'border-orange-500/30 bg-orange-500/10 text-orange-700 dark:text-orange-300';
        default:
            return 'border-border bg-muted text-muted-foreground';
    }
});
</script>

<template>
    <Badge variant="outline" :class="classes">{{ status }}</Badge>
</template>
