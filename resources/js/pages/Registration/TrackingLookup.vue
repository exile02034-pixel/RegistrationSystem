<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'

const props = defineProps<{
  statusMessage: string
  errorMessage: string
  requestLinkUrl: string
}>()

const form = useForm({
  email: '',
})

const submit = () => {
  form.post(props.requestLinkUrl, {
    preserveScroll: true,
  })
}
</script>

<template>
  <Head title="Track Submission" />

  <div class="relative min-h-screen overflow-hidden bg-[#F8FAFC] text-[#0B1F3A] dark:bg-[#0A192F] dark:text-[#E6F1FF]">
    <div class="pointer-events-none absolute inset-0">
      <div class="absolute -left-20 top-14 h-72 w-72 rounded-full bg-[#60A5FA]/35 blur-3xl dark:bg-[#2563EB]/20" />
      <div class="absolute right-0 top-0 h-80 w-80 rounded-full bg-blue-500/15 blur-3xl dark:bg-[#3B82F6]/20" />
    </div>

    <div class="relative mx-auto flex min-h-screen max-w-3xl items-center justify-center px-4 py-8">
      <div class="w-full rounded-3xl border border-[#E2E8F0] bg-white p-8 shadow-sm dark:border-[#1E3A5F] dark:bg-[#12325B]">
        <h1 class="font-['Space_Grotesk'] text-3xl font-semibold">Track Your Submission</h1>
        <p class="mt-3 text-[#475569] dark:text-[#9FB3C8]">
          Enter your email to receive a secure tracking link.
        </p>

        <p v-if="props.statusMessage" class="mt-4 rounded-xl border border-emerald-300 bg-emerald-50 p-3 text-sm text-emerald-700">
          {{ props.statusMessage }}
        </p>

        <p v-if="props.errorMessage" class="mt-4 rounded-xl border border-rose-300 bg-rose-50 p-3 text-sm text-rose-700">
          {{ props.errorMessage }}
        </p>

        <form class="mt-6 space-y-3" @submit.prevent="submit">
          <label class="block text-sm font-medium">Email address</label>
          <input
            v-model="form.email"
            type="email"
            autocomplete="email"
            class="h-11 w-full rounded-xl border border-[#E2E8F0] bg-[#F8FAFC] px-3 text-sm text-[#0B1F3A] outline-none ring-[#60A5FA] transition focus:ring-2"
            placeholder="you@example.com"
            required
          >
          <p v-if="form.errors.email" class="text-xs text-rose-600">{{ form.errors.email }}</p>

          <button
            type="submit"
            class="inline-flex rounded-xl bg-[#2563EB] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#1D4ED8] disabled:cursor-not-allowed disabled:opacity-60"
            :disabled="form.processing"
          >
            {{ form.processing ? 'Sending...' : 'Send secure link' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>
