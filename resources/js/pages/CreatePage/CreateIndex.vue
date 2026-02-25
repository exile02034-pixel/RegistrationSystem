<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import Button from '@/components/ui/button/Button.vue'
import Input from '@/components/ui/input/Input.vue'
import Label from '@/components/ui/label/Label.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { dashboard } from '@/routes'
import { type BreadcrumbItem } from '@/types'



const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: dashboard().url },
  { title: 'Create User', href: '#' },
]
</script>

<template>
  <Head title="Create User" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="mx-auto w-full max-w-2xl">
      <!-- Page header -->
      <div class="mb-6">
        <h1 class="text-2xl font-semibold tracking-tight">Create User</h1>
        <p class="mt-1 text-sm text-muted-foreground">
          Add a new user by filling out the details below.
        </p>
      </div>

      <!-- Form card -->
      <div class="rounded-2xl border bg-white p-6 shadow-sm">
        <div class="grid gap-5">
          <!-- Name -->
          <div class="grid gap-2">
            <Label for="name">Name</Label>
            <Input id="name" type="text" placeholder="Juan Dela Cruz" />
          </div>

          <!-- Email -->
          <div class="grid gap-2">
            <Label for="email">Email</Label>
            <Input id="email" type="email" placeholder="juan@example.com" />
          </div>

          <!-- Actions -->
          <div class="mt-2 flex items-center justify-end gap-2">
            <Button variant="secondary" type="button">Cancel</Button>
            <Button type="button">Create</Button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
