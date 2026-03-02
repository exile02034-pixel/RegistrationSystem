<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3'
import { Badge } from '@/components/ui/badge'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { toast } from '@/components/ui/sonner'
import AppLayout from '@/layouts/AppLayout.vue'

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
    onSuccess: () => {
      form.reset('email')
      toast.success('Registration email sent successfully.')
    },
    onError: () => {
      toast.error('Unable to send registration email.')
    },
  })
}
</script>

<template>
  <AppLayout>
    <div class="relative min-h-[calc(100vh-7rem)] overflow-hidden rounded-2xl bg-[#F8FAFC] p-6 text-[#0B1F3A] dark:bg-[#0A192F] dark:text-[#E6F1FF]">
      <div class="pointer-events-none absolute inset-0">
        <div class="absolute -left-20 top-14 h-72 w-72 rounded-full bg-[#60A5FA]/35 blur-3xl dark:bg-[#2563EB]/20" />
        <div class="absolute right-0 top-0 h-80 w-80 rounded-full bg-blue-500/15 blur-3xl dark:bg-[#3B82F6]/20" />
        <div class="absolute bottom-0 left-1/3 h-80 w-80 rounded-full bg-[#60A5FA]/20 blur-3xl dark:bg-[#2563EB]/15" />
        <div class="absolute inset-0 bg-[linear-gradient(rgba(120,140,170,0.14)_1px,transparent_1px),linear-gradient(90deg,rgba(120,140,170,0.14)_1px,transparent_1px)] bg-[size:34px_34px] opacity-40 dark:bg-[linear-gradient(rgba(160,180,200,0.08)_1px,transparent_1px),linear-gradient(90deg,rgba(160,180,200,0.08)_1px,transparent_1px)] dark:opacity-30" />
      </div>

      <div class="relative space-y-6">
        <Card class="rounded-3xl border border-[#E2E8F0] bg-[#FFFFFF] p-6 backdrop-blur dark:border-[#1E3A5F] dark:bg-[#12325B]">
          <CardHeader class="flex flex-row items-start justify-between gap-4 px-0 pb-4">
            <div class="space-y-3">
              <Badge class="inline-flex w-fit items-center rounded-full border border-[#60A5FA] bg-[#EFF6FF] px-3 py-1 font-['Public_Sans'] text-xs font-medium tracking-wide text-[#2563EB] dark:border-[#2563EB] dark:bg-[#0F2747] dark:text-[#E6F1FF]">
                Admin Registration
              </Badge>
              <CardTitle class="font-['Space_Grotesk'] text-3xl font-semibold text-[#0B1F3A] dark:text-[#E6F1FF]">
                Send Registration Email
              </CardTitle>
              <p class="font-['Public_Sans'] text-sm text-[#475569] dark:text-[#9FB3C8]">
                Select company type and send the required DOCX templates to your client.
              </p>
            </div>

            <Link
              href="/admin/registration"
              class="inline-flex h-10 items-center justify-center rounded-xl border border-[#E2E8F0] bg-[#FFFFFF] px-4 text-sm font-medium text-[#0B1F3A] transition-colors hover:bg-[#EFF6FF] hover:text-[#1D4ED8] dark:border-[#1E3A5F] dark:bg-[#0F2747] dark:text-[#E6F1FF] dark:hover:bg-[#12325B]"
            >
              Back to List
            </Link>
          </CardHeader>
        </Card>

        <Card class="max-w-2xl rounded-3xl border border-[#E2E8F0] bg-[#FFFFFF] p-6 shadow-sm dark:border-[#1E3A5F] dark:bg-[#12325B]">
          <CardContent class="space-y-5 px-0 pb-0">
            <p
              v-if="form.recentlySuccessful"
              class="rounded-xl border border-emerald-300 bg-emerald-50 px-3 py-2 text-sm text-emerald-700 dark:border-emerald-700/60 dark:bg-emerald-900/30 dark:text-emerald-300"
            >
              Registration email sent successfully.
            </p>

            <div class="space-y-2">
              <label class="block font-['Public_Sans'] text-sm font-medium text-[#0B1F3A] dark:text-[#E6F1FF]">Client Email</label>
              <input
                v-model="form.email"
                type="email"
                class="h-11 w-full rounded-xl border border-[#E2E8F0] bg-[#F8FAFC] px-3 text-sm text-[#0B1F3A] outline-none ring-[#60A5FA] transition focus:ring-2 dark:border-[#1E3A5F] dark:bg-[#0F2747] dark:text-[#E6F1FF]"
                placeholder="client@example.com"
              />
              <p v-if="form.errors.email" class="text-sm text-red-600">{{ form.errors.email }}</p>
            </div>

            <div class="space-y-2">
              <label class="block font-['Public_Sans'] text-sm font-medium text-[#0B1F3A] dark:text-[#E6F1FF]">Company Type</label>
              <select
                v-model="form.company_type"
                class="h-11 w-full rounded-xl border border-[#E2E8F0] bg-[#F8FAFC] px-3 text-sm text-[#0B1F3A] outline-none ring-[#60A5FA] transition focus:ring-2 dark:border-[#1E3A5F] dark:bg-[#0F2747] dark:text-[#E6F1FF]"
              >
                <option v-for="type in companyTypes" :key="type.value" :value="type.value">
                  {{ type.label }}
                </option>
              </select>
              <p v-if="form.errors.company_type" class="text-sm text-red-600">{{ form.errors.company_type }}</p>
            </div>

            <button
              type="button"
              :disabled="form.processing"
              @click="submit"
              class="inline-flex h-11 items-center justify-center rounded-xl border border-[#2563EB] bg-[#2563EB] px-4 text-sm font-medium text-white transition hover:bg-[#1D4ED8] disabled:cursor-not-allowed disabled:opacity-50 dark:border-[#2563EB] dark:bg-[#2563EB] dark:text-[#E6F1FF] dark:hover:bg-[#3B82F6]"
            >
              Send Registration Email
            </button>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>
