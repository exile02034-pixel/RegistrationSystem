<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

defineProps<{
  stats?: {
    totalUsers: number
    pendingUsers: number
    acceptedUsers: number
  }
}>()
</script>

<template>
  <Head title="Admin Dashboard" />

  <AppLayout>
    <div class="p-6 space-y-6">
      
      <!-- Page Header -->
      <div>
        <h1 class="text-3xl font-bold text-gray-800">
          Admin Dashboard
        </h1>
        <p class="text-gray-500 mt-1">
          Welcome back, Admin 👋
        </p>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        
        <div class="bg-white shadow rounded-xl p-5">
          <p class="text-sm text-gray-500">Total Users</p>
          <h2 class="text-2xl font-bold text-blue-600 mt-2">
            {{ stats?.totalUsers ?? 0 }}
          </h2>
        </div>

        <div class="bg-white shadow rounded-xl p-5">
          <p class="text-sm text-gray-500">Pending Registrations</p>
          <h2 class="text-2xl font-bold text-yellow-600 mt-2">
            {{ stats?.pendingUsers ?? 0 }}
          </h2>
        </div>

        <div class="bg-white shadow rounded-xl p-5">
          <p class="text-sm text-gray-500">Accepted Registrations</p>
          <h2 class="text-2xl font-bold text-green-600 mt-2">
            {{ stats?.acceptedUsers ?? 0 }}
          </h2>
        </div>

      </div>

     

    </div>
  </AppLayout>
</template>