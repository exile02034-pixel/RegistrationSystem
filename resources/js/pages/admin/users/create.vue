<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import { toast } from '@/components/ui/sonner'
import user from '@/routes/admin/user'

type EligibleClient = {
  email: string
  company_types: string[]
}

const props = defineProps<{
  eligibleClients: EligibleClient[]
}>()

const form = useForm({
  name: '',
  email: props.eligibleClients[0]?.email ?? '',
  password: '',
  password_confirmation: '',
})

const hasEligibleClients = computed(() => props.eligibleClients.length > 0)

const clientLabel = (client: EligibleClient) => {
  return client.company_types.length
    ? `${client.email} (${client.company_types.join(', ')})`
    : client.email
}

const submit = () => {
  if (!hasEligibleClients.value) {
    toast.info('No eligible completed clients are available for account creation.')
    return
  }

  form.post(user.store.url(), {
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
      form.email = props.eligibleClients[0]?.email ?? ''
      toast.success('User created successfully.')
    },
    onError: () => {
      toast.error('Unable to create user. Please check the form.')
    },
  })
}
</script>

<template>
  <AppLayout>
    <Head title="Create User" />

    <div class="relative min-h-[calc(100vh-7rem)] overflow-hidden rounded-2xl bg-[#F8FAFC] p-6 text-[#0B1F3A] dark:bg-[#0A192F] dark:text-[#E6F1FF]">
      <div class="pointer-events-none absolute inset-0">
        <div class="absolute -left-20 top-14 h-72 w-72 rounded-full bg-[#60A5FA]/35 blur-3xl dark:bg-[#2563EB]/20" />
        <div class="absolute right-0 top-0 h-80 w-80 rounded-full bg-blue-500/15 blur-3xl dark:bg-[#3B82F6]/20" />
        <div class="absolute bottom-0 left-1/3 h-80 w-80 rounded-full bg-[#60A5FA]/20 blur-3xl dark:bg-[#2563EB]/15" />
        <div class="absolute inset-0 bg-[linear-gradient(rgba(120,140,170,0.14)_1px,transparent_1px),linear-gradient(90deg,rgba(120,140,170,0.14)_1px,transparent_1px)] bg-[size:34px_34px] opacity-40 dark:bg-[linear-gradient(rgba(160,180,200,0.08)_1px,transparent_1px),linear-gradient(90deg,rgba(160,180,200,0.08)_1px,transparent_1px)] dark:opacity-30" />
      </div>

      <div class="relative space-y-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="font-['Space_Grotesk'] text-3xl font-semibold text-[#0B1F3A] dark:text-[#E6F1FF]">Create User / Client</h1>
            <p class="mt-1 font-['Public_Sans'] text-sm text-[#475569] dark:text-[#9FB3C8]">Only clients with completed registrations can be selected.</p>
          </div>
          <Link
            :href="user.index().url"
            class="inline-flex h-10 items-center justify-center rounded-xl border border-[#E2E8F0] bg-[#FFFFFF] px-4 text-sm font-medium text-[#0B1F3A] transition-colors hover:bg-[#EFF6FF] hover:text-[#1D4ED8] dark:border-[#1E3A5F] dark:bg-[#0F2747] dark:text-[#E6F1FF] dark:hover:bg-[#12325B]"
          >
            Back to Users
          </Link>
        </div>

        <div class="max-w-lg rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] p-6 shadow-sm dark:border-[#1E3A5F] dark:bg-[#12325B]">
          <p
            v-if="!hasEligibleClients"
            class="mb-4 rounded-xl border border-amber-300 bg-amber-50 p-3 text-sm text-amber-800 dark:border-amber-700/60 dark:bg-amber-900/30 dark:text-amber-300"
          >
            No completed registrations are available for user creation.
          </p>

          <form class="space-y-4" @submit.prevent="submit">
            <div>
              <label class="block text-sm font-medium text-[#0B1F3A] dark:text-[#E6F1FF]">Name</label>
              <input
                v-model="form.name"
                type="text"
                class="mt-1 block h-11 w-full rounded-xl border border-[#E2E8F0] bg-[#F8FAFC] px-3 text-sm text-[#0B1F3A] outline-none ring-[#60A5FA] transition focus:ring-2 dark:border-[#1E3A5F] dark:bg-[#0F2747] dark:text-[#E6F1FF]"
                placeholder="John Doe"
                required
              >
              <span class="text-sm text-red-500">{{ form.errors.name }}</span>
            </div>

            <div>
              <label class="block text-sm font-medium text-[#0B1F3A] dark:text-[#E6F1FF]">Client (Completed)</label>
              <select
                v-model="form.email"
                class="mt-1 block h-11 w-full rounded-xl border border-[#E2E8F0] bg-[#F8FAFC] px-3 text-sm text-[#0B1F3A] outline-none ring-[#60A5FA] transition focus:ring-2 dark:border-[#1E3A5F] dark:bg-[#0F2747] dark:text-[#E6F1FF]"
                :disabled="!hasEligibleClients"
              >
                <option v-for="client in eligibleClients" :key="client.email" :value="client.email">
                  {{ clientLabel(client) }}
                </option>
              </select>
              <span class="text-sm text-red-500">{{ form.errors.email }}</span>
            </div>

            <div>
              <label class="block text-sm font-medium text-[#0B1F3A] dark:text-[#E6F1FF]">Password</label>
              <input
                v-model="form.password"
                type="password"
                class="mt-1 block h-11 w-full rounded-xl border border-[#E2E8F0] bg-[#F8FAFC] px-3 text-sm text-[#0B1F3A] outline-none ring-[#60A5FA] transition focus:ring-2 dark:border-[#1E3A5F] dark:bg-[#0F2747] dark:text-[#E6F1FF]"
                placeholder="********"
                required
              >
              <span class="text-sm text-red-500">{{ form.errors.password }}</span>
            </div>

            <div>
              <label class="block text-sm font-medium text-[#0B1F3A] dark:text-[#E6F1FF]">Confirm Password</label>
              <input
                v-model="form.password_confirmation"
                type="password"
                class="mt-1 block h-11 w-full rounded-xl border border-[#E2E8F0] bg-[#F8FAFC] px-3 text-sm text-[#0B1F3A] outline-none ring-[#60A5FA] transition focus:ring-2 dark:border-[#1E3A5F] dark:bg-[#0F2747] dark:text-[#E6F1FF]"
                placeholder="********"
                required
              >
              <span class="text-sm text-red-500">{{ form.errors.password_confirmation }}</span>
            </div>

            <button
              type="submit"
              class="w-full rounded-xl border border-[#2563EB] bg-[#2563EB] px-4 py-2 text-white transition hover:bg-[#1D4ED8] disabled:cursor-not-allowed disabled:opacity-50 dark:hover:bg-[#3B82F6]"
              :disabled="!hasEligibleClients || form.processing"
            >
              Create User
            </button>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
