<script setup lang="ts">
import { ref } from 'vue'
import { useDropZone } from '@vueuse/core'
import { Icon } from '@iconify/vue'
import { Button } from '@/Components/ui/button'
import { Separator } from '@/Components/ui/separator'
import Container from '@/Components/Container.vue'
export interface Attachment {
  name: string
  size: number
  type: string
  lastModified: number
  file: File
  path?: string
  url?: string
}

const filesData = ref<Attachment[]>([])

const MAX_FILE_SIZE = 5 * 1024 * 1024 // 5MB in bytes
const totalFileSize = ref<number>(0)
const errorMessage = ref<string>('')

const formatFileSize = (bytes: number): string => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const ALLOWED_FILE_TYPES = {
  'application/pdf': true,
  'image/jpeg': true,
  'image/png': true,
  'image/gif': true
}

const getFileIcon = (fileType: string): string => {
  if (fileType.includes('pdf')) {
    return 'mdi:file-pdf-box'
  } else if (fileType.includes('wordprocessingml')) {
    return 'mdi:file-word'
  } else if (fileType.includes('spreadsheetml')) {
    return 'mdi:file-excel'
  } else if (fileType.includes('image')) {
    return 'mdi:file-image'
  }
  return 'mdi:file-document-outline'
}

function onDrop(files: File[] | null) {
  errorMessage.value = ''
  if (files) {
    const validFiles = files.filter((file) => {
      if (file.size > MAX_FILE_SIZE) {
        errorMessage.value = `File "${file.name}" exceeds 5MB limit`
        return false
      }
      if (!ALLOWED_FILE_TYPES[file.type as keyof typeof ALLOWED_FILE_TYPES]) {
        errorMessage.value = `File "${file.name}" is not an allowed file type. Only PDF and image files are accepted.`
        return false
      }
      return true
    })

    const newFiles = validFiles
      .filter((file) => !filesData.value.some((existingFile) => existingFile.name === file.name))
      .map((file) => ({
        name: file.name,
        size: file.size,
        type: file.type,
        lastModified: file.lastModified,
        file: file // Include the actual file object
      }))

    if (newFiles.length > 0) {
      filesData.value = [...filesData.value, ...newFiles]
      totalFileSize.value = filesData.value.reduce((acc, file) => acc + file.size, 0)
    }
  }
}

const removefile = (index: number) => {
  const removedFile = filesData.value[index]
  filesData.value.splice(index, 1)
  totalFileSize.value -= removedFile.size
}

const dropZoneRef = ref<HTMLElement | null>(null)

useDropZone(dropZoneRef, {
  onDrop: (files: File[] | null) => {
    if (files) {
      onDrop(Array.from(files))
    }
  }
})

const fileInputRef = ref<HTMLInputElement>()

const handleFileSelect = () => {
  fileInputRef.value?.click()
}

const onFileSelected = (event: Event) => {
  const input = event.target as HTMLInputElement
  if (input.files) {
    const files = Array.from(input.files)
    onDrop(files)
    input.value = ''
  }
}

const getFiles = () => {
  return filesData.value
}

const clearFiles = () => {
  filesData.value = []
  totalFileSize.value = 0
  errorMessage.value = ''
}

const props = withDefaults(
  defineProps<{
    isRequired?: boolean
  }>(),
  {
    isRequired: false
  }
)

defineExpose({ clearFiles, getFiles })
</script>

<template>
  <Container class="flex flex-col gap-4" header-text="attachments">
    <div>
      <div class="mb-2 flex items-center justify-between">
        <p class="text-sm">
          Supporting Documents <span v-if="props.isRequired == false">(Optional)</span>
        </p>

        <p class="text-xs">Max File Size: 25MB each</p>
      </div>
      <div
        ref="dropZoneRef"
        class="ex-container bg-accent flex min-h-[200px] items-center justify-center gap-3 p-6"
        @dragover.prevent
        @drop.prevent
      >
        <div class="flex flex-col items-center pb-6">
          <input
            ref="fileInputRef"
            type="file"
            multiple
            accept=".pdf,.jpg,.jpeg,.png"
            class="hidden"
            @change="onFileSelected"
          />
          <div v-if="filesData.length > 0" class="w-full p-6">
            <div class="grid grid-cols-1 gap-2 md:grid-cols-2 lg:grid-cols-3">
              <div
                v-for="(file, index) in filesData"
                :key="index"
                class="bg-background flex items-center justify-between rounded-lg border p-3"
              >
                <div class="flex min-w-0 items-center gap-3 overflow-hidden">
                  <Icon :icon="getFileIcon(file.type)" width="24" class="shrink-0" />
                  <span class="w-[320px] truncate text-sm font-medium">{{ file.name }}</span>
                </div>
                <Button
                  @click="removefile(index)"
                  variant="ghost"
                  size="icon"
                  class="h-8 w-8 shrink-0 text-red-500 hover:bg-red-50 hover:text-red-600"
                >
                  <Icon icon="mdi:close" width="18" />
                </Button>
              </div>
            </div>
          </div>
          <div v-else class="flex flex-col items-center">
            <Icon icon="mdi:tray-upload" width="50" />
            <p class="text-xl font-bold capitalize">Drag and drop your files here.</p>
          </div>
          <Separator class="my-4" label="Or" />
          <Button variant="outline" @click="handleFileSelect">
            Select File <Icon icon="mdi:attachment-vertical"></Icon>
          </Button>
        </div>
      </div>
      <div class="mt-2 flex items-center justify-between">
        <p class="text-xs">Files to Upload: {{ filesData.length }}</p>
        <p class="text-xs">Total Size: {{ formatFileSize(totalFileSize) }}</p>
      </div>
      <p v-if="errorMessage" class="mt-2 text-sm text-red-500">{{ errorMessage }}</p>
    </div>
  </Container>
</template>

<style scoped>
.ex-container {
  border-radius: 0.5rem;
}
</style>
