<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Link } from '@inertiajs/vue3'

type RegistrationLink = {
  id: number
  email: string
  company_type_label: string
  status: string
  uploads_count: number
  created_at: string | null
  client_url: string
  show_url: string
}

const props = defineProps<{
  links: RegistrationLink[]
}>()
</script>

<template>
  <AppLayout>
    <div class="space-y-8 p-6">
      <div class="flex items-center justify-between gap-4">
        <div>
        <h1 class="text-3xl font-bold">Client Registration</h1>
        <p class="text-gray-500">View all sent registration links and client submissions.</p>
        </div>
        <Link
          href="/admin/registration/create"
          class="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
        >
          Send Registration
        </Link>
      </div>

      <div class="space-y-3">
        <h2 class="text-xl font-semibold">Sent Registrations</h2>
        <div class="overflow-x-auto rounded-xl border bg-white shadow-sm">
          <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-600">
              <tr>
                <th class="px-4 py-3">Email</th>
                <th class="px-4 py-3">Company Type</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Files Uploaded</th>
                <th class="px-4 py-3">Created</th>
                <th class="px-4 py-3">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="link in links" :key="link.id" class="border-t">
                <td class="px-4 py-3">{{ link.email }}</td>
                <td class="px-4 py-3">{{ link.company_type_label }}</td>
                <td class="px-4 py-3">{{ link.status }}</td>
                <td class="px-4 py-3">{{ link.uploads_count }}</td>
                <td class="px-4 py-3">{{ link.created_at }}</td>
                <td class="px-4 py-3">
                  <a :href="link.show_url" class="text-blue-600 hover:underline">View</a>
                </td>
              </tr>
              <tr v-if="!links.length">
                <td colspan="6" class="px-4 py-6 text-center text-gray-500">No registrations sent yet.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
