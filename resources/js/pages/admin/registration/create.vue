<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Link, useForm } from '@inertiajs/vue3'

type CompanyType = {
  value: string
  label: string
}

const props = defineProps<{
  companyTypes: CompanyType[]
}>()

const form = useForm({
  email: '',
  company_type: props.companyTypes[0]?.value ?? 'corp',
})

const submit = () => {
  form.post('/admin/registration/send', {
    preserveScroll: true,
    onSuccess: () => form.reset('email'),
  })
}
</script>

<template>
  <AppLayout>
    <div class="space-y-6 p-6">
      <div class="flex items-center justify-between gap-4">
        <div>
          <h1 class="text-3xl font-bold">Send Registration Email</h1>
          <p class="text-gray-500">Select company type and send required DOCX templates.</p>
        </div>
        <Link href="/admin/registration" class="rounded border px-4 py-2 text-sm hover:bg-gray-50">
          Back to List
        </Link>
      </div>

      <div class="max-w-xl space-y-4 rounded-xl border bg-white p-5 shadow-sm">
        <p v-if="form.recentlySuccessful" class="rounded bg-green-100 p-2 text-sm text-green-700">
          Registration email sent successfully.
        </p>

        <div>
          <label class="mb-1 block text-sm font-medium">Client Email</label>
          <input
            v-model="form.email"
            type="email"
            class="w-full rounded border p-2"
            placeholder="client@example.com"
          />
          <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
        </div>

        <div>
          <label class="mb-1 block text-sm font-medium">Company Type</label>
          <select v-model="form.company_type" class="w-full rounded border p-2">
            <option v-for="type in companyTypes" :key="type.value" :value="type.value">
              {{ type.label }}
            </option>
          </select>
          <p v-if="form.errors.company_type" class="mt-1 text-sm text-red-600">{{ form.errors.company_type }}</p>
        </div>

        <button
          type="button"
          :disabled="form.processing"
          @click="submit"
          class="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 disabled:opacity-50"
        >
          Send Registration Email
        </button>
      </div>
    </div>
  </AppLayout>
</template>
