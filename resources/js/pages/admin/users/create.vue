<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { onMounted } from 'vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { useUserAdmin } from '@/composables/useUserAdmin'
import AppLayout from '@/layouts/AppLayout.vue'
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

    <div class="app-page">
      <div class="app-page-bg">
        <div class="absolute -left-20 top-14 h-72 w-72 rounded-full bg-[#60A5FA]/35 blur-3xl dark:bg-[#2563EB]/20" />
        <div class="absolute right-0 top-0 h-80 w-80 rounded-full bg-blue-500/15 blur-3xl dark:bg-[#3B82F6]/20" />
        <div class="absolute bottom-0 left-1/3 h-80 w-80 rounded-full bg-[#60A5FA]/20 blur-3xl dark:bg-[#2563EB]/15" />
        <div class="app-page-pattern" />
      </div>

      <div class="relative space-y-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="app-title">Create User / Client</h1>
            <p class="app-subtitle mt-1">This account can be used to login.</p>
          </div>
          <Button as-child variant="outline">
            <Link @click="router.visit(user.index().url)">Back to Users</Link>
          </Button>
        </div>

        <div class="app-card max-w-lg p-6">
          <div v-if="success" class="mb-4 rounded-xl border border-emerald-300 bg-emerald-50 p-3 text-sm text-emerald-800 dark:border-emerald-700/60 dark:bg-emerald-900/30 dark:text-emerald-300">
            User has been successfully created.
          </div>

          <form class="space-y-4" @submit.prevent="submit">
            <div class="app-form-grid">
              <Label class="app-label">Name</Label>
              <Input
                v-model="form.name"
                type="text"
                class="app-input"
                placeholder="John Doe"
                required
              />
              <span v-if="errors.name" class="app-error">{{ errors.name }}</span>
            </div>

            <div class="app-form-grid">
              <Label class="app-label">Email</Label>
              <Input
                v-model="form.email"
                type="email"
                class="app-input"
                placeholder="john@example.com"
                required
              />
              <span v-if="errors.email" class="app-error">{{ errors.email }}</span>
            </div>

            <div class="app-form-grid">
              <Label class="app-label">Password</Label>
              <Input
                v-model="form.password"
                type="password"
                class="app-input"
                placeholder="********"
                required
              />
              <span v-if="errors.password" class="app-error">{{ errors.password }}</span>
            </div>

            <div class="app-form-grid">
              <Label class="app-label">Confirm Password</Label>
              <Input
                v-model="form.password_confirmation"
                type="password"
                class="app-input"
                placeholder="********"
                required
              />
              <span v-if="errors.password_confirmation" class="app-error">{{ errors.password_confirmation }}</span>
            </div>

            <Button type="submit" class="w-full">Create User</Button>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
