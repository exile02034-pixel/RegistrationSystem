<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { useRegistrationEmailForm } from '@/composables/admin/useRegistrationEmailForm'
import AppLayout from '@/layouts/AppLayout.vue'
import type { AdminRegistrationCreatePageProps } from '@/types'

const props = defineProps<AdminRegistrationCreatePageProps>()
const { form, submit } = useRegistrationEmailForm(props.companyTypes)
</script>

<template>
  <AppLayout>
    <div class="app-page">
      <div class="app-page-bg">
        <div class="absolute -left-20 top-14 h-72 w-72 rounded-full bg-[#60A5FA]/35 blur-3xl dark:bg-[#2563EB]/20" />
        <div class="absolute right-0 top-0 h-80 w-80 rounded-full bg-blue-500/15 blur-3xl dark:bg-[#3B82F6]/20" />
        <div class="absolute bottom-0 left-1/3 h-80 w-80 rounded-full bg-[#60A5FA]/20 blur-3xl dark:bg-[#2563EB]/15" />
        <div class="app-page-pattern" />
      </div>

      <div class="app-page-content">
        <Card class="app-card rounded-3xl p-6 backdrop-blur">
          <CardHeader class="flex flex-row items-start justify-between gap-4 px-0 pb-4">
            <div class="space-y-3">
              <Badge variant="secondary" class="w-fit px-3 py-1 tracking-wide">
                Admin Registration
              </Badge>
              <CardTitle class="app-title">
                Send Registration Email
              </CardTitle>
              <p class="app-subtitle">
                Select company type and send the required DOCX templates to your client.
              </p>
            </div>

            <Button as-child variant="outline">
              <Link href="/admin/registration">Back to List</Link>
            </Button>
          </CardHeader>
        </Card>

        <Card class="app-card max-w-2xl rounded-3xl p-6">
          <CardContent class="space-y-5 px-0 pb-0">
            <p
              v-if="form.recentlySuccessful"
              class="rounded-xl border border-emerald-300 bg-emerald-50 px-3 py-2 text-sm text-emerald-700 dark:border-emerald-700/60 dark:bg-emerald-900/30 dark:text-emerald-300"
            >
              Registration email sent successfully.
            </p>

            <div class="app-form-grid">
              <Label class="app-label">Client Email</Label>
              <Input
                v-model="form.email"
                type="email"
                class="app-input"
                placeholder="client@example.com"
              />
              <p v-if="form.errors.email" class="app-error">{{ form.errors.email }}</p>
            </div>

            <div class="app-form-grid">
              <Label class="app-label">Company Type</Label>
              <select
                v-model="form.company_type"
                class="app-input"
              >
                <option v-for="type in companyTypes" :key="type.value" :value="type.value">
                  {{ type.label }}
                </option>
              </select>
              <p v-if="form.errors.company_type" class="app-error">{{ form.errors.company_type }}</p>
            </div>

            <Button
              type="button"
              :disabled="form.processing"
              @click="submit"
            >
              Send Registration Email
            </Button>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>
