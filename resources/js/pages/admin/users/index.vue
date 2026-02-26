<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import Button from '@/components/ui/button/Button.vue'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps<{ users: any[] }>()
const isCreateModalOpen = ref(false)

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const submit = () => {
  form.post('/admin/user', {
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
      isCreateModalOpen.value = false
    },
  })
}
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
        <Dialog :open="isCreateModalOpen" @update:open="isCreateModalOpen = $event">
          <DialogTrigger as-child>
            <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
              + Create User
            </button>
          </DialogTrigger>
          <DialogContent class="sm:max-w-lg">
            <DialogHeader>
              <DialogTitle>Create User / Client</DialogTitle>
            </DialogHeader>

            <div class="space-y-4">
              <div v-if="form.recentlySuccessful" class="mb-4 p-3 bg-green-100 text-green-800 rounded">
                User has been successfully created.
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Name</label>
                <input v-model="form.name" type="text" class="mt-1 block w-full border rounded-md p-2" placeholder="John Doe" required />
                <p v-if="form.errors.name" class="text-red-500 text-sm">{{ form.errors.name }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Email</label>
                <input v-model="form.email" type="email" class="mt-1 block w-full border rounded-md p-2" placeholder="john@example.com" required />
                <p v-if="form.errors.email" class="text-red-500 text-sm">{{ form.errors.email }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Password</label>
                <input v-model="form.password" type="password" class="mt-1 block w-full border rounded-md p-2" placeholder="********" required />
                <p v-if="form.errors.password" class="text-red-500 text-sm">{{ form.errors.password }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
                <input v-model="form.password_confirmation" type="password" class="mt-1 block w-full border rounded-md p-2" placeholder="********" required />
                <p v-if="form.errors.password_confirmation" class="text-red-500 text-sm">{{ form.errors.password_confirmation }}</p>
              </div>

              <div class="flex justify-end gap-2 pt-2">
                <button
                  type="button"
                  class="rounded border px-4 py-2 text-sm hover:bg-gray-50"
                  :disabled="form.processing"
                  @click="isCreateModalOpen = false"
                >
                  Cancel
                </button>
                <button
                  type="button"
                  class="rounded bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700 disabled:opacity-50"
                  :disabled="form.processing"
                  @click="submit"
                >
                  Create User
                </button>
              </div>
            </div>
          </DialogContent>
        </Dialog>
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
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-700">
                  {{ new Date(user.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <Button
                  v-if="user.registration_show_url"
                  as="a"
                  :href="user.registration_show_url"
                >
                  View Files
                </Button>
                <Button v-else class="opacity-50 cursor-not-allowed" :disabled="true">
                  No Files
                </Button>
                <Button class="bg-red-600 m-2">Delete</Button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

    </div>
  </AppLayout>
</template>
