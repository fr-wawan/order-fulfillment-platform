<script setup lang="ts">
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';

type StatusTone =
    | 'primary'
    | 'neutral'
    | 'success'
    | 'warning'
    | 'danger'
    | 'info';

const props = defineProps<{
    status: string;
    label?: string;
    tone?: StatusTone;
}>();

const toneClasses: Record<StatusTone, string> = {
    primary: '',
    neutral: 'border-transparent bg-secondary text-secondary-foreground',
    success:
        'border-transparent bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300',
    warning:
        'border-transparent bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300',
    danger: 'border-transparent bg-destructive/10 text-destructive dark:bg-destructive/20',
    info: 'border-transparent bg-sky-100 text-sky-800 dark:bg-sky-950 dark:text-sky-300',
};

const normalizedStatus = computed(() =>
    props.status
        .trim()
        .toLowerCase()
        .replace(/[\s-]+/g, '_'),
);

const resolvedTone = computed(() => props.tone ?? 'neutral');

const resolvedLabel = computed(
    () =>
        props.label ??
        normalizedStatus.value
            .replace(/_/g, ' ')
            .replace(/^./, (character) => character.toUpperCase()),
);
</script>

<template>
    <Badge
        :variant="resolvedTone === 'primary' ? 'default' : 'outline'"
        :class="toneClasses[resolvedTone]"
    >
        {{ resolvedLabel }}
    </Badge>
</template>
