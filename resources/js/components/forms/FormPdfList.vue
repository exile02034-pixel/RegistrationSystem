<script setup lang="ts">
import { computed } from 'vue'
import { Download, Eye, MoreHorizontal, Printer, Trash2 } from 'lucide-vue-next'
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog'
import { Button } from '@/components/ui/button'
import { Checkbox } from '@/components/ui/checkbox'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { useDelete } from '@/composables/useDelete'
import { type CompanyType, type PdfContext, useFormPdf } from '@/composables/useFormPdf'

type SubmittedField = {
  value: string | null
}

type SubmittedSection = {
  name: string
  fields: SubmittedField[]
}

type FormSubmission = {
  id: string
  submitted_at?: string | null
  sections: SubmittedSection[]
}

const props = defineProps<{
  submission: FormSubmission | null
  companyType: CompanyType
  context: PdfContext
  title?: string
  subtitle?: string
}>()

const {
  rows,
  selectedSections,
  selectedGeneratedRows,
  allSelected,
  getViewUrl,
  getDownloadUrl,
  getDeleteUrl,
  toggleSection,
  toggleAll,
  printSelected,
} = useFormPdf(props.submission, props.companyType, props.context)

const { isOpen, deleting, promptDelete, confirmDelete, reset } = useDelete()

const canPrint = computed(() => selectedGeneratedRows.value.length > 0)
const canDelete = computed(() => props.context === 'admin')

const formatCreatedAt = (value: string | null | undefined) => {
  if (!value) return 'n/a'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return value

  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: '2-digit',
  })
}
</script>

<template>
  <div class="rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] p-4 shadow-sm dark:border-[#1E3A5F] dark:bg-[#12325B]">
    <div class="mb-3 flex items-center justify-between gap-2">
      <div>
        <h3 class="font-['Space_Grotesk'] text-lg font-semibold text-[#0B1F3A] dark:text-[#E6F1FF]">{{ title ?? 'Generated PDF Forms' }}</h3>
        <p v-if="subtitle" class="text-xs text-[#64748B] dark:text-[#9FB3C8]">{{ subtitle }}</p>
      </div>
      <Button
        v-if="canPrint"
        type="button"
        variant="outline"
        class="cursor-pointer"
        @click="printSelected"
      >
        <Printer class="mr-2 h-4 w-4" />
        Print Selected ({{ selectedGeneratedRows.length }})
      </Button>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] shadow-sm dark:border-[#1E3A5F] dark:bg-[#12325B]">
      <table class="min-w-full table-fixed text-sm">
        <colgroup>
          <col class="w-10">
          <col>
          <col class="w-36">
          <col class="w-40">
        </colgroup>
        <thead class="bg-[#EFF6FF] text-left text-[#475569] dark:bg-[#0F2747] dark:text-[#9FB3C8]">
          <tr>
            <th class="w-10 px-4 py-3">
              <Checkbox
                :model-value="allSelected"
                :disabled="!rows.some((row) => row.canOpen)"
                @update:model-value="(checked) => toggleAll(Boolean(checked))"
              />
            </th>
            <th class="px-4 py-3">Form Name</th>
            <th class="px-4 py-3">Created At</th>
            <th class="px-4 py-3 text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="row in rows"
            :key="row.section"
            class="border-t border-[#E2E8F0] dark:border-[#1E3A5F]"
          >
            <td class="px-4 py-3">
              <Checkbox
                :model-value="selectedSections.includes(row.section)"
                :disabled="!row.canOpen"
                @update:model-value="(checked) => toggleSection(row.section, Boolean(checked))"
              />
            </td>
            <td class="px-4 py-3 font-medium text-[#0B1F3A] dark:text-[#E6F1FF]">{{ row.name }}</td>
            <td class="px-4 py-3 text-[#475569] dark:text-[#9FB3C8]">{{ formatCreatedAt(submission?.submitted_at) }}</td>
            <td class="px-4 py-3">
              <div class="flex items-center justify-end gap-1">
                <Button
                  v-if="row.canOpen"
                  as="a"
                  :href="getViewUrl(row.section)"
                  target="_blank"
                  variant="ghost"
                  size="icon"
                  class="h-8 w-8"
                >
                  <Eye class="h-4 w-4" />
                </Button>
                <Button
                  v-else
                  type="button"
                  variant="ghost"
                  size="icon"
                  class="h-8 w-8"
                  :disabled="true"
                >
                  <Eye class="h-4 w-4" />
                </Button>

                <Button
                  v-if="row.canOpen"
                  as="a"
                  :href="getDownloadUrl(row.section)"
                  variant="ghost"
                  size="icon"
                  class="h-8 w-8"
                >
                  <Download class="h-4 w-4" />
                </Button>
                <Button
                  v-else
                  type="button"
                  variant="ghost"
                  size="icon"
                  class="h-8 w-8"
                  :disabled="true"
                >
                  <Download class="h-4 w-4" />
                </Button>

                <DropdownMenu v-if="canDelete">
                  <DropdownMenuTrigger as-child>
                    <Button variant="ghost" size="icon" class="h-8 w-8" :disabled="!row.canOpen">
                      <MoreHorizontal class="h-4 w-4" />
                    </Button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent align="end" class="w-40">
                    <DropdownMenuItem class="text-destructive" @click="promptDelete(getDeleteUrl(row.section))">
                      <Trash2 class="mr-2 h-4 w-4" />
                      Delete
                    </DropdownMenuItem>
                  </DropdownMenuContent>
                </DropdownMenu>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <AlertDialog :open="isOpen" @update:open="(value) => !value && reset()">
    <AlertDialogContent>
      <AlertDialogHeader>
        <AlertDialogTitle>Delete PDF Form</AlertDialogTitle>
        <AlertDialogDescription>
          Are you sure you want to delete this form? This action cannot be undone.
        </AlertDialogDescription>
      </AlertDialogHeader>
      <AlertDialogFooter>
        <AlertDialogCancel :disabled="deleting" @click="reset">Cancel</AlertDialogCancel>
        <AlertDialogAction :disabled="deleting" @click="confirmDelete">Delete</AlertDialogAction>
      </AlertDialogFooter>
    </AlertDialogContent>
  </AlertDialog>
</template>
