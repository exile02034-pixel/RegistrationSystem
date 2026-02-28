<script setup lang="ts">
import { Head } from '@inertiajs/vue3'

const props = defineProps<{
  trackingLookupUrl: string
}>()
</script>

<template>
  <Head title="Registration Submitted" />

  <div class="relative min-h-screen overflow-hidden bg-[#F8FAFC] text-[#0B1F3A] dark:bg-[#0A192F] dark:text-[#E6F1FF]">
    <div class="pointer-events-none absolute inset-0">
      <div class="absolute -left-20 top-14 h-72 w-72 rounded-full bg-[#60A5FA]/35 blur-3xl dark:bg-[#2563EB]/20" />
      <div class="absolute right-0 top-0 h-80 w-80 rounded-full bg-blue-500/15 blur-3xl dark:bg-[#3B82F6]/20" />
    </div>

    <div class="relative mx-auto flex min-h-screen max-w-3xl items-center justify-center px-4 py-8">
      <div class="w-full rounded-3xl border border-[#E2E8F0] bg-white p-8 shadow-sm dark:border-[#1E3A5F] dark:bg-[#12325B]">
        <h1 class="font-['Space_Grotesk'] text-3xl font-semibold">Thank You</h1>
        <p class="mt-3 text-[#475569] dark:text-[#9FB3C8]">Your registration form was submitted successfully.</p>
        <a
          :href="props.trackingLookupUrl"
          class="mt-6 inline-flex rounded-xl bg-[#2563EB] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#1D4ED8]"
        >
          Track my submission
        </a>
      </div>
    </div>
  </div>
</template>
