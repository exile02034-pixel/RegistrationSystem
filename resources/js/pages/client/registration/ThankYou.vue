<script setup lang="ts">
import { Head } from '@inertiajs/vue3'

const props = defineProps<{
  email: string
}>()
</script>

<template>
  <Head title="Thank You">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
    <link
      href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Public+Sans:wght@400;500;600&display=swap"
      rel="stylesheet"
    />
  </Head>

  <div class="relative min-h-screen overflow-hidden bg-[#F8FAFC] text-[#0B1F3A] dark:bg-[#0A192F] dark:text-[#E6F1FF]">
    <div class="pointer-events-none absolute inset-0">
      <div class="absolute -left-20 top-14 h-72 w-72 rounded-full bg-[#60A5FA]/35 blur-3xl dark:bg-[#2563EB]/20" />
      <div class="absolute right-0 top-0 h-80 w-80 rounded-full bg-blue-500/15 blur-3xl dark:bg-[#3B82F6]/20" />
      <div class="absolute bottom-0 left-1/3 h-80 w-80 rounded-full bg-[#60A5FA]/20 blur-3xl dark:bg-[#2563EB]/15" />
      <div class="absolute inset-0 bg-[linear-gradient(rgba(120,140,170,0.14)_1px,transparent_1px),linear-gradient(90deg,rgba(120,140,170,0.14)_1px,transparent_1px)] bg-[size:34px_34px] opacity-40 dark:bg-[linear-gradient(rgba(160,180,200,0.08)_1px,transparent_1px),linear-gradient(90deg,rgba(160,180,200,0.08)_1px,transparent_1px)] dark:opacity-30" />
    </div>

    <div class="relative mx-auto flex min-h-screen max-w-3xl items-center justify-center px-4 py-8 md:px-6">
      <div class="w-full rounded-3xl border border-[#E2E8F0] bg-white p-8 shadow-sm dark:border-[#1E3A5F] dark:bg-[#12325B]">
        <h1 class="font-['Space_Grotesk'] text-3xl font-semibold">Thank You</h1>
        <p class="mt-3 font-['Public_Sans'] text-[#475569] dark:text-[#9FB3C8]">
          We received your files successfully.
        </p>
        <p class="mt-2 font-['Public_Sans'] text-sm text-[#475569] dark:text-[#9FB3C8]">
          A confirmation email was sent to <strong>{{ props.email }}</strong>.
        </p>
        <p class="mt-4 font-['Public_Sans'] text-sm text-[#475569] dark:text-[#9FB3C8]">
          Our team will review your submission and contact you if more details are needed.
        </p>

        <a
          href="/"
          class="mt-6 inline-flex items-center rounded-xl bg-[#2563EB] px-4 py-2 text-sm font-medium text-white hover:bg-[#1D4ED8]"
        >
          Visit Our Website
        </a>
      </div>
    </div>
  </div>
</template>
