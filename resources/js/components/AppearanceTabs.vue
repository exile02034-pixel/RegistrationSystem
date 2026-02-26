<script setup lang="ts">
import { Monitor, Moon, Sun } from 'lucide-vue-next';
import { useAppearance } from '@/composables/useAppearance';

const { appearance, updateAppearance } = useAppearance();

const tabs = [
    { value: 'light', Icon: Sun, label: 'Light' },
    { value: 'dark', Icon: Moon, label: 'Dark' },
    { value: 'system', Icon: Monitor, label: 'System' },
] as const;
</script>

<template>
    <div
        class="inline-flex gap-1 rounded-xl border border-[#E2E8F0] bg-[#FFFFFF] p-1 backdrop-blur dark:border-[#1E3A5F] dark:bg-[#0B1F3A]"
    >
        <button
            v-for="{ value, Icon, label } in tabs"
            :key="value"
            @click="updateAppearance(value)"
            :aria-label="label"
            :title="label"
            :class="[
                'flex h-9 w-9 items-center justify-center rounded-md transition-colors',
                appearance === value
                    ? 'border border-[#60A5FA] bg-[#EFF6FF] text-[#2563EB] shadow-xs dark:border-[#2563EB] dark:bg-[#12325B] dark:text-[#E6F1FF]'
                    : 'text-[#475569] hover:bg-[#EFF6FF] hover:text-[#1D4ED8] dark:text-[#9FB3C8] dark:hover:bg-[#0F2747] dark:hover:text-[#E6F1FF]',
            ]"
        >
            <component :is="Icon" class="h-4 w-4" />
        </button>
    </div>
</template>
