<script setup lang="ts" >
import { ref, onMounted, watch} from 'vue'
import axios from 'axios'

import { Button } from '@/Components/ui/button'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/Components/ui/dialog'

import {
  Form,
  FormControl,
  FormDescription,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from '@/Components/ui/form'

import ToastDialog from '@/Components/ToastDialog.vue'
import PageLoader from '@/Components/PageLoader.vue'

import { Input } from '@/Components/ui/input'
import { toTypedSchema } from '@vee-validate/zod'
import { Icon } from '@iconify/vue'
import * as z from 'zod'

const openAlert = ref<boolean>(false)

const alertMessage = ref<AlertMessage>({
  title: 'Oh no, something went wrong!',
  description: 'Please try again later.',
  variant: ''
})

const isOpen = ref(false)
const imageLoading = ref(true) // State to track image loading
const isDownloading = ref(false)
const props = defineProps({
  entry: {
    type: Object,
    default: null
  },
})
const attachments = ref([])
const handleFileDialog = () => {

  isOpen.value = true
  if (props.entry && props.entry.attachment) {
    try {
      attachments.value = JSON.parse(props.entry.attachment) // Parse the attachment JSON
    } catch (error) {
      console.error('Invalid attachment format:', error)
      attachments.value = [] // Fallback to an empty array if parsing fails
    }
  }
}

const handleImageLoad = () => {
  imageLoading.value = false // Set to false when the image is fully loaded
}

const getFileIcon = (fileType: string): string => {
  if (fileType.includes('pdf')) {
    return 'mdi:file-pdf-box'
  } else if (fileType.includes('wordprocessingml')) {
    return 'mdi:file-word'
  } else if (fileType.includes('spreadsheetml')) {
    return 'mdi:file-excel'
  } else if (fileType.includes('jpg') || fileType.includes('jpeg') || fileType.includes('png')) {
    return 'mdi:file-image'
  }
  return 'mdi:file-document-outline'
}

const truncateFileName = (fileName: string): string => {
  return fileName.length > 11 ? fileName.substring(0, 20) + '...' : fileName;
};

const downloadFile = async (url: string) => {
    try {
        isDownloading.value = true
        const response = await axios.get(url, {
            responseType: 'blob',
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`
            }
        });
        
        const blob = new Blob([response.data]);
        const link = document.createElement('a');
        link.href = window.URL.createObjectURL(blob);
        const fileName = url.split('/').pop() || 'download';
        const isImage = fileName.match(/\.(jpg|jpeg|png)$/i);
        const finalFileName = isImage ? fileName : fileName.split('.')[0] + '.pdf';
        
        link.download = finalFileName;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    } catch (error) {
        console.error('Error downloading file:', error);
    } finally {
        isDownloading.value = false
    }
};

</script>

<template>
    <ToastDialog :open="openAlert" :message="alertMessage" @close="(val) => (openAlert = val)" />
    <Dialog v-model:open="isOpen">
      <DialogTrigger as-child>
        <Button
          variant="outline"
          class="px-2 py-2 border border-orange-500 bg-white text-orange-500 rounded-lg hover:bg-orange-100 transition-colors text-xs"
          @click="handleFileDialog"
        >
          <Icon icon="mdi:file-multiple-outline" width="24" height="24" class="text-orange-500" />
        </Button>
      </DialogTrigger>
      <DialogContent class="max-h-[90vh] sm:max-w-[700px]">
        <DialogHeader>
          <DialogTitle>View Files (<span>{{ props.entry.temp_date || `${props.entry.start_date} - ${props.entry.end_date}` }}</span>)</DialogTitle>
          <DialogDescription>
            Below are the files attached to this entry.
          </DialogDescription>
        </DialogHeader>
        <div class="flex flex-col gap-4">
            <div v-if="attachments.length > 0">
            <!-- Grid layout for files -->
            <div class="grid grid-cols-3 gap-4">
            <template v-for="(file, index) in attachments" :key="index">
                <template v-if="file.is_image">
                <!-- Render image preview -->
                <div class="flex flex-col items-center border border-gray-300 rounded-lg overflow-hidden bg-white">
                    <a
                    :href="file.file_url"
                    target="_blank"
                    class="block w-full"
                    @click.prevent="downloadFile(file.file_url)"
                    >
                    <img
                        :src="file.file_url"
                        :alt="file.file_name"
                        class="w-full h-32 object-cover"
                        @load="handleImageLoad"
                    />
                    <div class="flex items-center gap-2 mt-2 px-2">
                        <Icon :icon="getFileIcon(file.file_type)" width="16" height="16" />
                        <span class="text-sm text-gray-700 truncate" :title="file.file_name">
                            {{ truncateFileName(file.file_name) }}
                        </span>
                    </div>
                    
                    </a>                   
                </div>
                </template>
                <template v-else>
                <!-- Render non-image file as a thumbnail -->
                <div class="flex flex-col items-center justify-center p-4 border border-gray-300 overflow-hidden rounded-lg bg-gray-100">
                    <a
                        :href="file.file_url"
                        target="_blank"
                        download
                        class="flex items-center justify-center w-16 h-16 bg-red-500 text-white rounded-lg relative"
                        @click.prevent="downloadFile(file.file_url)"
                    >
                        <PageLoader v-if="isDownloading" />
                        <span class="text-white text-xl font-bold">
                            {{ file.file_type.split('.').pop().toUpperCase() }}
                        </span>
                    </a>
                    <a
                    :href="file.file_url"
                    target="_blank"
                    class="text-black-500 underline mt-2 text-center text-sm flex items-center gap-2"
                    @click.prevent="downloadFile(file.file_url)"
                    >
                    <Icon :icon="getFileIcon(file.file_type)" width="16" height="16" />
                    <span class="text-sm text-gray-700 truncate" :title="file.file_name">
                        {{ truncateFileName(file.file_name) }}
                    </span>
                    </a>
                </div>
                </template>
            </template>
            </div>
            </div>
            <div v-else>
                <p class="text-gray-500">No attachments available.</p>
            </div>
        </div>
        <DialogFooter>
          <Button variant="outline" @click="isOpen = false">Close</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </template>


<style scoped>
/* Truncate text with ellipsis */
.truncate {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  display: block;
  max-width: 100%;
}
</style>