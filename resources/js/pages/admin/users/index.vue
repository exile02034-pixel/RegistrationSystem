<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3'
import Button from '@/components/ui/button/Button.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import user from '@/routes/admin/user'
const props = defineProps<{ users: any[] }>()
</script>

<template>
  <AppLayout>
    <div class="p-6 space-y-6">

      <!-- Page Header -->
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold text-gray-800">Users / Clients</h1>
          <p class="text-gray-500 mt-1">Manage all users in the system</p>
        </div>
        <Link
          @click="router.visit(user.create().url)"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          + Create User
        </Link>
      </div>

      <!-- Users Table -->
      <div class="overflow-x-auto bg-white shadow rounded-xl">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="user in props.users" :key="user.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ user.name }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ user.email }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <span
                  :class="user.status === 'Accepted' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'"
                  class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                >
                  {{ new Date(user.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <Button>View</Button>
                <Button class="bg-red-600 m-2">Delete</Button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

    </div>
  </AppLayout>
</template>
