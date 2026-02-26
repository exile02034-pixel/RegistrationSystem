<script setup lang="ts">
import { Moon, Sun } from 'lucide-vue-next';
import { computed } from 'vue';
import { useAppearance } from '@/composables/useAppearance';

const { appearance, updateAppearance } = useAppearance();

const isDark = computed(() => appearance.value === 'dark');

const toggleAppearance = () => {
    updateAppearance(isDark.value ? 'light' : 'dark');
};
</script>

<template>
    <div
        class="inline-flex gap-1 rounded-xl border border-[#E2E8F0] bg-[#FFFFFF] p-1 backdrop-blur dark:border-[#1E3A5F] dark:bg-[#0B1F3A]"
    >
        <button
            @click="toggleAppearance"
            :aria-label="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
            :title="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
            class="flex h-9 w-9 items-center justify-center rounded-md border border-[#60A5FA] bg-[#EFF6FF] text-[#2563EB] shadow-xs transition-colors hover:bg-[#DBEAFE] hover:text-[#1D4ED8] dark:border-[#2563EB] dark:bg-[#12325B] dark:text-[#E6F1FF] dark:hover:bg-[#0F2747]"
        >
            <Moon v-if="isDark" class="h-4 w-4" />
            <Sun v-else class="h-4 w-4" />
        </button>
    </div>
</template>
