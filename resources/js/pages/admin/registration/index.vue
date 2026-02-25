<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'


const email = ref('')
const success = ref('')
const error = ref('')


const sendRegistration = () => {
  success.value = ''
  error.value = ''

  router.post('/admin/registration/send', { email: email.value }, {
    onSuccess: () => {
      success.value = `Registration email sent to ${email.value}`
      email.value = ''
    },
    onError: (e) => {
      error.value = e.email || 'Failed to send registration email'
    },
  })
}
</script>

<template>
  <AppLayout>
    <div class="p-6 space-y-6">
      <h1 class="text-3xl font-bold">Registration</h1>
      <p class="text-gray-500">Send registration forms to clients via email</p>

      <!-- Feedback Messages -->
      <div v-if="success" class="p-3 bg-green-100 text-green-800 rounded">{{ success }}</div>
      <div v-if="error" class="p-3 bg-red-100 text-red-800 rounded">{{ error }}</div>

      <!-- Email input -->
      <div class="mt-4 max-w-md">
        <input
          v-model="email"
          type="email"
          placeholder="Client Email"
          class="w-full border rounded p-2"
        />
        <button @click="sendRegistration" class="mt-2 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
          Send Registration
        </button>
      </div>
    </div>
  </AppLayout>
</template>