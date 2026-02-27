<script setup lang="ts">
import { Moon, Sun } from 'lucide-vue-next';
import { useAppearance } from '@/composables/useAppearance';

const { resolvedAppearance, updateAppearance } = useAppearance();

const toggleAppearance = () => {
    updateAppearance(resolvedAppearance.value === 'dark' ? 'light' : 'dark');
};
</script>

<template>
    <button
        @click="toggleAppearance"
        :aria-label="
            resolvedAppearance === 'dark'
                ? 'Switch to light mode'
                : 'Switch to dark mode'
        "
        :title="
            resolvedAppearance === 'dark'
                ? 'Switch to light mode'
                : 'Switch to dark mode'
        "
        class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-[#E2E8F0] bg-[#FFFFFF] text-[#0B1F3A] transition-colors hover:bg-[#EFF6FF] hover:text-[#1D4ED8] dark:border-[#1E3A5F] dark:bg-[#12325B] dark:text-[#E6F1FF] dark:hover:bg-[#0F2747]"
    >
        <Sun v-if="resolvedAppearance === 'dark'" class="size-4" />
        <Moon v-else class="size-4" />
    </button>
</template>
