<script setup lang="ts">
import { Download, Eye, FileDown } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip'
import type { UploadItem } from '@/types/upload'

defineProps<{
  upload: UploadItem
}>()
</script>

<template>
  <div class="flex items-center gap-2">
    <Tooltip>
      <TooltipTrigger as-child>
        <Button as-child variant="outline" size="icon-sm" class="cursor-pointer" aria-label="View as PDF">
          <a :href="upload.view_pdf_url" target="_blank" rel="noopener noreferrer">
            <Eye class="h-4 w-4" />
          </a>
        </Button>
      </TooltipTrigger>
      <TooltipContent>View as PDF</TooltipContent>
    </Tooltip>

    <Tooltip>
      <TooltipTrigger as-child>
        <Button as-child variant="outline" size="icon-sm" class="cursor-pointer" aria-label="Download DOCX">
          <a :href="upload.download_docx_url">
            <FileDown class="h-4 w-4" />
          </a>
        </Button>
      </TooltipTrigger>
      <TooltipContent>Download DOCX</TooltipContent>
    </Tooltip>

    <Tooltip>
      <TooltipTrigger as-child>
        <Button as-child variant="outline" size="icon-sm" class="cursor-pointer" aria-label="Download PDF">
          <a :href="upload.download_pdf_url">
            <Download class="h-4 w-4" />
          </a>
        </Button>
      </TooltipTrigger>
      <TooltipContent>Download PDF</TooltipContent>
    </Tooltip>

    <slot name="extra-actions" />
  </div>
</template>
