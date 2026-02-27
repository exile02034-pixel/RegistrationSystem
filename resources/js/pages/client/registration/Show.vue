<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import { toast } from '@/components/ui/sonner'

type TemplateFile = {
  key: string
  name: string
  download_url: string
}

const props = defineProps<{
  token: string
  email: string
  status: string
  companyTypeLabel: string
  templates: TemplateFile[]
  uploadUrl: string
  qrCodeUrl: string
}>()

const form = useForm({
  files: [] as File[],
})

const fileInput = ref<HTMLInputElement | null>(null)
const selectedFiles = ref<File[]>([])
const queuedFiles = ref<File[]>([])

const onFileChange = (event: Event) => {
  const target = event.target as HTMLInputElement
  selectedFiles.value = Array.from(target.files ?? [])
}

const addToQueue = () => {
  if (!selectedFiles.value.length) {
    return
  }

  queuedFiles.value = [...queuedFiles.value, ...selectedFiles.value]
  selectedFiles.value = []

  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const removeQueuedFile = (index: number) => {
  queuedFiles.value = queuedFiles.value.filter((_, fileIndex) => fileIndex !== index)
}

const submit = () => {
  form.files = queuedFiles.value

  form.post(props.uploadUrl, {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
      queuedFiles.value = []
      selectedFiles.value = []
      if (fileInput.value) {
        fileInput.value.value = ''
      }
      toast.success('Files uploaded successfully.')
    },
    onError: () => {
      toast.error('Unable to upload files. Please try again.')
    },
  })
}
</script>

<template>
  <Head title="Client Registration Upload">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
    <link
      href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Public+Sans:wght@400;500;600&display=swap"
      rel="stylesheet"
    />
  </Head>

  <div class="relative min-h-screen overflow-hidden bg-[#F8FAFC] text-[#0B1F3A] dark:bg-[#0A192F] dark:text-[#E6F1FF]">
    <div class="pointer-events-none absolute inset-0">
      <div class="absolute -left-20 top-14 h-72 w-72 rounded-full bg-[#60A5FA]/35 blur-3xl dark:bg-[#2563EB]/20" />
      <div class="absolute right-0 top-0 h-80 w-80 rounded-full bg-blue-500/15 blur-3xl dark:bg-[#3B82F6]/20" />
      <div class="absolute bottom-0 left-1/3 h-80 w-80 rounded-full bg-[#60A5FA]/20 blur-3xl dark:bg-[#2563EB]/15" />
      <div class="absolute inset-0 bg-[linear-gradient(rgba(120,140,170,0.14)_1px,transparent_1px),linear-gradient(90deg,rgba(120,140,170,0.14)_1px,transparent_1px)] bg-[size:34px_34px] opacity-40 dark:bg-[linear-gradient(rgba(160,180,200,0.08)_1px,transparent_1px),linear-gradient(90deg,rgba(160,180,200,0.08)_1px,transparent_1px)] dark:opacity-30" />
    </div>

    <div class="relative mx-auto max-w-5xl space-y-6 px-4 py-8 md:px-6">
      <div class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm dark:border-[#1E3A5F] dark:bg-[#12325B]">
        <h1 class="font-['Space_Grotesk'] text-3xl font-semibold">Client Registration Upload</h1>
        <p class="mt-3 font-['Public_Sans'] text-sm text-[#475569] dark:text-[#9FB3C8]"><strong>Email:</strong> {{ email }}</p>
        <p class="font-['Public_Sans'] text-sm text-[#475569] dark:text-[#9FB3C8]"><strong>Company Type:</strong> {{ companyTypeLabel }}</p>
        <p class="font-['Public_Sans'] text-sm text-[#475569] dark:text-[#9FB3C8]"><strong>Status:</strong> {{ status }}</p>
      </div>

      <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm dark:border-[#1E3A5F] dark:bg-[#12325B]">
          <h2 class="font-['Space_Grotesk'] text-xl font-semibold">Download Required DOCX Files</h2>
          <p class="mt-1 font-['Public_Sans'] text-sm text-[#475569] dark:text-[#9FB3C8]">Use the same files attached in your email.</p>

          <ul class="mt-4 space-y-3">
            <li
              v-for="file in templates"
              :key="file.key"
              class="flex items-center justify-between rounded-xl border border-[#E2E8F0] bg-[#F8FAFC] p-3 dark:border-[#1E3A5F] dark:bg-[#0F2747]"
            >
              <span class="text-sm">{{ file.name }}</span>
              <a :href="file.download_url" class="text-sm font-medium text-[#2563EB] hover:underline">Download</a>
            </li>
          </ul>
        </div>

        <div class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm dark:border-[#1E3A5F] dark:bg-[#12325B]">
          <h2 class="font-['Space_Grotesk'] text-xl font-semibold">Upload Completed Files</h2>
          <p class="mt-1 font-['Public_Sans'] text-sm text-[#475569] dark:text-[#9FB3C8]">
            Click Upload Files to stage files first, then click Submit Files to save them.
            You can submit the required files now and send any missing documents later if needed.
          </p>

          <form class="mt-4 space-y-4" @submit.prevent="submit">
            <p v-if="form.recentlySuccessful" class="rounded-xl bg-green-100 p-2 text-sm text-green-700">
              Files uploaded successfully.
            </p>

            <input
              ref="fileInput"
              type="file"
              multiple
              accept=".docx"
              @change="onFileChange"
              class="w-full rounded-xl border border-[#E2E8F0] bg-[#F8FAFC] p-2 text-sm dark:border-[#1E3A5F] dark:bg-[#0F2747]"
            />
            <p v-if="form.errors.files" class="text-sm text-red-600">{{ form.errors.files }}</p>
            <p v-if="form.errors['files.0']" class="text-sm text-red-600">{{ form.errors['files.0'] }}</p>
            <p class="text-sm text-[#475569] dark:text-[#9FB3C8]">
              Required files (exact names): {{ templates.map((file) => file.name).join(', ') }}
            </p>

            <div class="grid gap-3 md:grid-cols-2">
              <button
                type="button"
                :disabled="!selectedFiles.length"
                @click="addToQueue"
                class="w-full rounded-xl border border-[#2563EB] bg-[#EFF6FF] px-4 py-2 text-sm font-medium text-[#2563EB] hover:bg-[#DBEAFE] disabled:cursor-not-allowed disabled:opacity-50"
              >
                Upload Files
              </button>

              <button
                type="submit"
                :disabled="form.processing || !queuedFiles.length"
                class="w-full rounded-xl bg-[#2563EB] px-4 py-2 text-sm font-medium text-white hover:bg-[#1D4ED8] disabled:cursor-not-allowed disabled:opacity-50"
              >
                Submit Files
              </button>
            </div>

            <div class="rounded-xl border border-[#E2E8F0] bg-[#F8FAFC] p-3 dark:border-[#1E3A5F] dark:bg-[#0F2747]">
              <p class="text-sm font-medium">Staged Files</p>
              <p v-if="!queuedFiles.length" class="mt-2 text-sm text-[#475569] dark:text-[#9FB3C8]">No files staged yet.</p>

              <ul v-else class="mt-2 space-y-2">
                <li
                  v-for="(file, index) in queuedFiles"
                  :key="`${file.name}-${file.size}-${index}`"
                  class="flex items-center justify-between rounded-lg border border-[#E2E8F0] bg-white px-3 py-2 text-sm dark:border-[#1E3A5F] dark:bg-[#12325B]"
                >
                  <span class="truncate pr-2">{{ file.name }}</span>
                  <button
                    type="button"
                    class="text-red-600 hover:underline"
                    @click="removeQueuedFile(index)"
                  >
                    Remove
                  </button>
                </li>
              </ul>
            </div>
          </form>
        </div>
      </div>

      <div class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm dark:border-[#1E3A5F] dark:bg-[#12325B]">
        <h2 class="font-['Space_Grotesk'] text-xl font-semibold">Mobile Option (QR)</h2>
        <p class="mt-1 font-['Public_Sans'] text-sm text-[#475569] dark:text-[#9FB3C8]">Scan this QR code to open the same upload page on your phone.</p>
        <img :src="qrCodeUrl" alt="Registration QR Code" class="mt-4 h-56 w-56 rounded-xl border border-[#E2E8F0] dark:border-[#1E3A5F]" />
      </div>
    </div>
  </div>
</template>
