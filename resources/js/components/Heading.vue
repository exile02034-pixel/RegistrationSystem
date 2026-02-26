<script setup lang="ts">
type Props = {
    title: string;
    description?: string;
    variant?: 'default' | 'small';
};

withDefaults(defineProps<Props>(), {
    variant: 'default',
});
</script>

<template>
    <header :class="variant === 'small' ? '' : 'mb-8 space-y-0.5'">
        <h2
            :class="
                variant === 'small'
                    ? 'mb-0.5 text-base font-semibold text-[#0B1F3A] dark:text-[#E6F1FF]'
                    : 'text-xl font-semibold tracking-tight text-[#0B1F3A] dark:text-[#E6F1FF]'
            "
        >
            {{ title }}
        </h2>
        <p
            v-if="description"
            class="text-sm text-[#475569] dark:text-[#9FB3C8]"
        >
            {{ description }}
        </p>
    </header>
</template>
