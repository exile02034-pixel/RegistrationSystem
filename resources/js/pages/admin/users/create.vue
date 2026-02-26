<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { useUserAdmin } from '@/composables/useUserAdmin'
import { onMounted } from 'vue'
import user from '@/routes/admin/user'

const { form, errors, submit, success } = useUserAdmin()

onMounted(() => {
  const email = new URLSearchParams(window.location.search).get('email')

  if (email && !form.email) {
    form.email = email
  }
})
</script>

<template>
  <AppLayout>
    <Head title="Create User" />

    <div class="space-y-6 p-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-800">Create User / Client</h1>
          <p class="mt-1 text-gray-500">This account can be used to login</p>
        </div>
        <Link
          @click="router.visit(user.index().url)"
          class="rounded-lg bg-gray-600 px-4 py-2 text-white hover:bg-gray-700"
        >
          ← Back to Users
        </Link>
      </div>

      <div class="max-w-lg rounded-xl bg-white p-6 shadow">
        <div v-if="success" class="mb-4 rounded bg-green-100 p-3 text-green-800">
          User has been successfully created!
        </div>

        <form class="space-y-4" @submit.prevent="submit">
          <div>
            <label class="block text-sm font-medium text-gray-700">Name</label>
            <input
              v-model="form.name"
              type="text"
              class="mt-1 block w-full rounded-md border p-2"
              placeholder="John Doe"
              required
            >
            <span class="text-sm text-red-500">{{ errors.name }}</span>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Email</label>
            <input
              v-model="form.email"
              type="email"
              class="mt-1 block w-full rounded-md border p-2"
              placeholder="john@example.com"
              required
            >
            <span class="text-sm text-red-500">{{ errors.email }}</span>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Password</label>
            <input
              v-model="form.password"
              type="password"
              class="mt-1 block w-full rounded-md border p-2"
              placeholder="********"
              required
            >
            <span class="text-sm text-red-500">{{ errors.password }}</span>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
            <input
              v-model="form.password_confirmation"
              type="password"
              class="mt-1 block w-full rounded-md border p-2"
              placeholder="********"
              required
            >
            <span class="text-sm text-red-500">{{ errors.password_confirmation }}</span>
          </div>

          <button type="submit" class="w-full rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
            Create User
          </button>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
