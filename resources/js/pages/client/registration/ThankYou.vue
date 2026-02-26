<script setup lang="ts">
const props = defineProps<{
  email: string
}>()
</script>

<template>
  <div class="min-h-screen bg-gray-100 p-4 md:p-8">
    <div class="mx-auto max-w-2xl rounded-xl border bg-white p-8 shadow-sm">
      <h1 class="text-2xl font-bold text-gray-900">Thank You</h1>
      <p class="mt-3 text-gray-700">
        We received your files successfully.
      </p>
      <p class="mt-2 text-sm text-gray-600">
        A confirmation email was sent to <strong>{{ props.email }}</strong>.
      </p>
      <p class="mt-4 text-sm text-gray-600">
        Our team will review your submission and contact you if more details are needed.
      </p>

      <a
        href="/"
        class="mt-6 inline-flex items-center rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
      >
        Visit Our Website
      </a>
    </div>
  </div>
</template>
