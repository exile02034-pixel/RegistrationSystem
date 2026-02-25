<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { useUserAdmin } from '@/composables/useUserAdmin';
import user from '@/routes/admin/user';
const { form, errors, submit,success   } = useUserAdmin()
</script>
<template>
  <AppLayout>
    <Head title="Create User" />

    <div class="p-6 space-y-6">
      <!-- Page Header -->
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold text-gray-800">Create User / Client</h1>
          <p class="text-gray-500 mt-1">This account can be used to login</p>
        </div>
        <Link
          @click="router.visit(user.index().url)"
          class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700"
        >
          ← Back to Users
        </Link>
      </div>

      <!-- User Creation Form -->
      <div class="bg-white shadow rounded-xl p-6 max-w-lg">

  <!-- Success message -->
  <div v-if="success" class="mb-4 p-3 bg-green-100 text-green-800 rounded">
    User has been successfully created!
  </div>

  <form @submit.prevent="submit" class="space-y-4">
    <!-- Name -->
    <div>
      <label class="block text-sm font-medium text-gray-700">Name</label>
      <input v-model="form.name" type="text" class="mt-1 block w-full border rounded-md p-2" placeholder="John Doe" required />
      <span class="text-red-500 text-sm">{{ errors.name }}</span>
    </div>

    <!-- Email -->
    <div>
      <label class="block text-sm font-medium text-gray-700">Email</label>
      <input v-model="form.email" type="email" class="mt-1 block w-full border rounded-md p-2" placeholder="john@example.com" required />
      <span class="text-red-500 text-sm">{{ errors.email }}</span>
    </div>

    <!-- Password -->
    <div>
      <label class="block text-sm font-medium text-gray-700">Password</label>
      <input v-model="form.password" type="password" class="mt-1 block w-full border rounded-md p-2" placeholder="********" required />
      <span class="text-red-500 text-sm">{{ errors.password }}</span>
    </div>

    <!-- Confirm Password -->
    <div>
      <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
      <input v-model="form.password_confirmation" type="password" class="mt-1 block w-full border rounded-md p-2" placeholder="********" required />
      <span class="text-red-500 text-sm">{{ errors.password_confirmation }}</span>
    </div>

    <!-- Submit Button -->
    <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
      Create User
    </button>
  </form>
</div>
    </div>
  </AppLayout>
</template>