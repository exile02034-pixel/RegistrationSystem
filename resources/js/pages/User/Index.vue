<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import Button from '@/components/ui/button/Button.vue'
import Input from '@/components/ui/input/Input.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { dashboard } from '@/routes'
import { type BreadcrumbItem } from '@/types'

type UserItem = {
  id: number
  name: string
  email: string
  role: string
  status: 'Active' | 'Inactive'
  createdAt: string
}

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: dashboard().url },
  { title: 'Users', href: '#' },
]

// Static sample users (replace later with props from backend)
const users: UserItem[] = [
  { id: 1, name: 'Jancee Capati', email: 'jancee@example.com', role: 'Admin', status: 'Active', createdAt: 'Feb 20, 2026' },
  { id: 2, name: 'Marie Santos', email: 'marie@example.com', role: 'User', status: 'Active', createdAt: 'Feb 18, 2026' },
  { id: 3, name: 'John Dela Cruz', email: 'john@example.com', role: 'User', status: 'Inactive', createdAt: 'Feb 10, 2026' },
  { id: 4, name: 'Angelica Dagal', email: 'angelica@example.com', role: 'Staff', status: 'Active', createdAt: 'Jan 30, 2026' },
]
</script>

<template>
  <Head title="Users" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="mx-auto w-full max-w-6xl">
      <!-- Header -->
      <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-semibold tracking-tight">Users</h1>
          <p class="mt-1 text-sm text-muted-foreground">
            Manage users, view details, and create new accounts.
          </p>
        </div>

        <div class="flex items-center gap-2">
          <!-- Static link (change href later) -->
          <Button as-child>
            <Link href="/CreatePage/create">Create User</Link>
          </Button>
        </div>
      </div>

      <!-- Toolbar -->
      <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full sm:max-w-sm">
          <Input type="text" placeholder="Search users (static)" />
        </div>

        <div class="flex items-center gap-2">
          <Button variant="secondary" type="button">Filter</Button>
          <Button variant="secondary" type="button">Export</Button>
        </div>
      </div>

      <!-- Table -->
      <div class="overflow-hidden rounded-2xl border bg-white shadow-sm">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm">
            <thead class="border-b bg-muted/40">
              <tr class="text-muted-foreground">
                <th class="px-5 py-3 font-medium">Name</th>
                <th class="px-5 py-3 font-medium">Email</th>
                <th class="px-5 py-3 font-medium">Role</th>
                <th class="px-5 py-3 font-medium">Status</th>
                <th class="px-5 py-3 font-medium">Created</th>
                <th class="px-5 py-3 text-right font-medium">Actions</th>
              </tr>
            </thead>

            <tbody>
              <tr
                v-for="u in users"
                :key="u.id"
                class="border-b last:border-b-0 hover:bg-muted/20"
              >
                <td class="px-5 py-4">
                  <div class="font-medium">{{ u.name }}</div>
                  <div class="text-xs text-muted-foreground">ID: {{ u.id }}</div>
                </td>

                <td class="px-5 py-4 text-muted-foreground">
                  {{ u.email }}
                </td>

                <td class="px-5 py-4">
                  <span class="rounded-full border px-2.5 py-1 text-xs">
                    {{ u.role }}
                  </span>
                </td>

                <td class="px-5 py-4">
                  <span
                    class="rounded-full px-2.5 py-1 text-xs font-medium"
                    :class="u.status === 'Active'
                      ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                      : 'bg-zinc-100 text-zinc-700 border border-zinc-200'"
                  >
                    {{ u.status }}
                  </span>
                </td>

                <td class="px-5 py-4 text-muted-foreground">
                  {{ u.createdAt }}
                </td>

                <td class="px-5 py-4">
                  <div class="flex items-center justify-end gap-2">
                    <Button variant="secondary" size="sm" type="button">View</Button>
                    <Button variant="secondary" size="sm" type="button">Edit</Button>
                    <Button variant="destructive" size="sm" type="button">Delete</Button>
                  </div>
                </td>
              </tr>

              <tr v-if="users.length === 0">
                <td colspan="6" class="px-5 py-10 text-center text-muted-foreground">
                  No users found.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Footer -->
        <div class="flex items-center justify-between border-t px-5 py-4 text-sm text-muted-foreground">
          <div>Showing {{ users.length }} users</div>
          <div class="flex items-center gap-2">
            <Button variant="secondary" size="sm" type="button">Prev</Button>
            <Button variant="secondary" size="sm" type="button">Next</Button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
