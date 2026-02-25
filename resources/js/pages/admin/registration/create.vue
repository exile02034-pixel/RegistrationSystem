<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { reactive } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps<{
  token: string
  email: string
}>()

const form = reactive({
  name: '',
  email: props.email,
  phone: '',
  address: '',
  password: '',
  password_confirmation: '',
})

const errors = reactive({
  name: '',
  email: '',
  phone: '',
  address: '',
  password: '',
  password_confirmation: '',
})

const submit = () => {
  router.post(`/registration/${props.token}`, form, {
    onSuccess: () => {
      alert('Account successfully created! You can now login.')
      router.visit('/login')
    },
    onError: (e) => Object.assign(errors, e)
  })
}
</script>

<template>
  <AppLayout>
    <div class="p-6 max-w-lg mx-auto space-y-6">
      <h1 class="text-3xl font-bold">Client Registration</h1>
      <p class="text-gray-500">Please fill out your information</p>

      <div class="bg-white shadow rounded-xl p-6">
        <form @submit.prevent="submit" class="space-y-4">
          <!-- Name -->
          <div>
            <label class="block text-sm font-medium">Name</label>
            <input v-model="form.name" type="text" class="w-full border rounded p-2" placeholder="John Doe" />
            <span class="text-red-500 text-sm">{{ errors.name }}</span>
          </div>

          <!-- Email -->
          <div>
            <label class="block text-sm font-medium">Email</label>
            <input v-model="form.email" type="email" class="w-full border rounded p-2" placeholder="john@example.com" />
            <span class="text-red-500 text-sm">{{ errors.email }}</span>
          </div>

          <!-- Phone -->
          <div>
            <label class="block text-sm font-medium">Phone</label>
            <input v-model="form.phone" type="text" class="w-full border rounded p-2" placeholder="09123456789" />
            <span class="text-red-500 text-sm">{{ errors.phone }}</span>
          </div>

          <!-- Address -->
          <div>
            <label class="block text-sm font-medium">Address</label>
            <input v-model="form.address" type="text" class="w-full border rounded p-2" placeholder="123 Street, City" />
            <span class="text-red-500 text-sm">{{ errors.address }}</span>
          </div>

          <!-- Submit -->
          <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
            Submit Registration
          </button>
        </form>
      </div>
    </div>
  </AppLayout>
</template>