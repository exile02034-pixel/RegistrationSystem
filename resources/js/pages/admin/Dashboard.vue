<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import ActivityTypeIcon from '@/components/admin/ActivityTypeIcon.vue'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { useActivityLogs } from '@/composables/admin/useActivityLogs'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps<{
  stats?: {
    totalUsers: number
    pendingUsers: number
    acceptedUsers: number
    totalSubmissions: number
  }
  recentActivities?: Array<{
    id: number
    type: string
    description: string
    performed_by_name: string | null
    performed_by_email: string | null
    performed_by_role: string | null
    company_type: string | null
    created_at: string | null
  }>
}>()

const { relativeTime, roleLabel } = useActivityLogs(props.recentActivities ?? [])
</script>

<template>
  <Head title="Admin Dashboard" />

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
          <CardHeader class="px-0 pb-2">
            <CardTitle class="font-['Space_Grotesk'] text-3xl font-semibold text-[#0B1F3A] dark:text-[#E6F1FF]">
              Admin Dashboard
            </CardTitle>
          </CardHeader>
          <CardContent class="px-0">
            <p class="font-['Public_Sans'] text-sm text-[#475569] dark:text-[#9FB3C8]">
              Manage users, registration flow, and client submissions in one place.
            </p>
          </CardContent>
        </Card>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
          <Card class="rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] p-5 dark:border-[#1E3A5F] dark:bg-[#0F2747]">
            <p class="font-['Public_Sans'] text-sm text-[#475569] dark:text-[#9FB3C8]">Total Users</p>
            <h2 class="mt-2 font-['Space_Grotesk'] text-3xl font-semibold text-[#2563EB]">{{ stats?.totalUsers ?? 0 }}</h2>
          </Card>

          <Card class="rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] p-5 dark:border-[#1E3A5F] dark:bg-[#0F2747]">
            <p class="font-['Public_Sans'] text-sm text-[#475569] dark:text-[#9FB3C8]">Pending Registrations</p>
            <h2 class="mt-2 font-['Space_Grotesk'] text-3xl font-semibold text-amber-600">{{ stats?.pendingUsers ?? 0 }}</h2>
          </Card>

          <Card class="rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] p-5 dark:border-[#1E3A5F] dark:bg-[#0F2747]">
            <p class="font-['Public_Sans'] text-sm text-[#475569] dark:text-[#9FB3C8]">Accepted Registrations</p>
            <h2 class="mt-2 font-['Space_Grotesk'] text-3xl font-semibold text-emerald-600">{{ stats?.acceptedUsers ?? 0 }}</h2>
          </Card>

          <Card class="rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] p-5 dark:border-[#1E3A5F] dark:bg-[#0F2747]">
            <p class="font-['Public_Sans'] text-sm text-[#475569] dark:text-[#9FB3C8]">Submitted Forms</p>
            <h2 class="mt-2 font-['Space_Grotesk'] text-3xl font-semibold text-violet-600">{{ stats?.totalSubmissions ?? 0 }}</h2>
          </Card>
        </div>

        <Card class="rounded-3xl border border-[#E2E8F0] bg-[#FFFFFF] p-6 dark:border-[#1E3A5F] dark:bg-[#12325B]">
          <CardHeader class="px-0 pb-2">
            <CardTitle class="font-['Space_Grotesk'] text-xl font-semibold text-[#0B1F3A] dark:text-[#E6F1FF]">
              Quick Navigation
            </CardTitle>
          </CardHeader>
          <CardContent class="px-0">
            <div class="flex flex-wrap gap-3">
              <Button as-child variant="outline" class="border-[#2563EB] bg-[#EFF6FF] text-[#2563EB] hover:bg-[#DBEAFE] dark:border-[#1E3A5F] dark:bg-[#0F2747] dark:text-[#E6F1FF]">
                <Link href="/admin/user">Users</Link>
              </Button>
              <Button as-child variant="outline" class="border-[#2563EB] bg-[#EFF6FF] text-[#2563EB] hover:bg-[#DBEAFE] dark:border-[#1E3A5F] dark:bg-[#0F2747] dark:text-[#E6F1FF]">
                <Link href="/admin/registration">Registration</Link>
              </Button>
              <Button as-child variant="outline" class="border-[#2563EB] bg-[#EFF6FF] text-[#2563EB] hover:bg-[#DBEAFE] dark:border-[#1E3A5F] dark:bg-[#0F2747] dark:text-[#E6F1FF]">
                <Link href="/notifications">Notifications</Link>
              </Button>
            
            </div>
          </CardContent>
        </Card>

        <Card class="rounded-3xl border border-[#E2E8F0] bg-[#FFFFFF] p-6 dark:border-[#1E3A5F] dark:bg-[#12325B]">
          <CardHeader class="px-0 pb-2">
            <CardTitle class="font-['Space_Grotesk'] text-xl font-semibold text-[#0B1F3A] dark:text-[#E6F1FF]">
              Recent Activities
            </CardTitle>
          </CardHeader>
          <CardContent class="px-0">
            <div v-if="!(recentActivities?.length)" class="text-sm text-[#475569] dark:text-[#9FB3C8]">
              No recent activity yet.
            </div>
            <div v-else class="space-y-3">
              <div
                v-for="activity in recentActivities"
                :key="activity.id"
                class="flex items-start justify-between gap-3 rounded-xl border border-[#E2E8F0] bg-[#F8FAFC] p-3 dark:border-[#1E3A5F] dark:bg-[#0F2747]"
              >
                <div class="flex items-start gap-3">
                  <span class="mt-0.5 rounded-full bg-[#EFF6FF] p-2 text-[#2563EB] dark:bg-[#12325B] dark:text-[#93C5FD]">
                    <ActivityTypeIcon :type="activity.type" class="h-4 w-4" />
                  </span>
                  <div>
                    <p class="text-sm font-medium">{{ activity.description }}</p>
                    <p class="text-xs text-[#64748B] dark:text-[#9FB3C8]">
                      {{ activity.performed_by_name ?? 'Unknown' }} ({{ activity.performed_by_email ?? 'n/a' }})
                    </p>
                    <Badge variant="outline" class="mt-1">{{ roleLabel(activity.performed_by_role) }}</Badge>
                  </div>
                </div>
                <p class="shrink-0 text-xs text-[#64748B] dark:text-[#9FB3C8]">{{ relativeTime(activity.created_at) }}</p>
              </div>
            </div>
            <Button as-child variant="outline" class="mt-3 border-[#2563EB] bg-[#EFF6FF] text-[#2563EB] hover:bg-[#DBEAFE] dark:border-[#1E3A5F] dark:bg-[#0F2747] dark:text-[#E6F1FF]">
              <Link href="/admin/activity-logs">View All Activity</Link>
            </Button>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>
