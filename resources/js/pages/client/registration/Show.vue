<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

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
    },
  })
}
</script>

<template>
  <div class="min-h-screen bg-gray-100 p-4 md:p-8">
    <div class="mx-auto max-w-4xl space-y-6">
      <div class="rounded-xl border bg-white p-6 shadow-sm">
        <h1 class="text-2xl font-bold">Client Registration Upload</h1>
        <p class="mt-1 text-sm text-gray-600"><strong>Email:</strong> {{ email }}</p>
        <p class="text-sm text-gray-600"><strong>Company Type:</strong> {{ companyTypeLabel }}</p>
        <p class="text-sm text-gray-600"><strong>Status:</strong> {{ status }}</p>
      </div>

      <div class="grid gap-6 md:grid-cols-2">
        <div class="rounded-xl border bg-white p-6 shadow-sm">
          <h2 class="text-lg font-semibold">Download Required DOCX Files</h2>
          <p class="mt-1 text-sm text-gray-600">Use the same files attached in your email.</p>

          <ul class="mt-4 space-y-3">
            <li v-for="file in templates" :key="file.key" class="flex items-center justify-between rounded border p-3">
              <span class="text-sm">{{ file.name }}</span>
              <a :href="file.download_url" class="text-sm text-blue-600 hover:underline">Download</a>
            </li>
          </ul>
        </div>

        <div class="rounded-xl border bg-white p-6 shadow-sm">
          <h2 class="text-lg font-semibold">Upload Completed Files</h2>
          <p class="mt-1 text-sm text-gray-600">Click Upload Files to stage files first, then click Submit Files to save them.</p>

          <form class="mt-4 space-y-4" @submit.prevent="submit">
            <p v-if="form.recentlySuccessful" class="rounded bg-green-100 p-2 text-sm text-green-700">
              Files uploaded successfully.
            </p>

            <input ref="fileInput" type="file" multiple @change="onFileChange" class="w-full rounded border p-2" />
            <p v-if="form.errors.files" class="text-sm text-red-600">{{ form.errors.files }}</p>
            <p v-if="form.errors['files.0']" class="text-sm text-red-600">{{ form.errors['files.0'] }}</p>

            <div class="grid gap-3 md:grid-cols-2">
              <button
                type="button"
                :disabled="!selectedFiles.length"
                @click="addToQueue"
                class="w-full rounded bg-slate-700 px-4 py-2 text-white hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-50"
              >
                Upload Files
              </button>

              <button
                type="submit"
                :disabled="form.processing || !queuedFiles.length"
                class="w-full rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
              >
                Submit Files
              </button>
            </div>

            <div class="rounded border p-3">
              <p class="text-sm font-medium text-gray-700">Staged Files</p>
              <p v-if="!queuedFiles.length" class="mt-2 text-sm text-gray-500">No files staged yet.</p>

              <ul v-else class="mt-2 space-y-2">
                <li
                  v-for="(file, index) in queuedFiles"
                  :key="`${file.name}-${file.size}-${index}`"
                  class="flex items-center justify-between rounded bg-gray-50 px-3 py-2 text-sm"
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

      <div class="rounded-xl border bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold">Mobile Option (QR)</h2>
        <p class="mt-1 text-sm text-gray-600">Scan this QR code to open the same upload page on your phone.</p>
        <img :src="qrCodeUrl" alt="Registration QR Code" class="mt-4 h-56 w-56 rounded border" />
      </div>
    </div>
  </div>
</template>
