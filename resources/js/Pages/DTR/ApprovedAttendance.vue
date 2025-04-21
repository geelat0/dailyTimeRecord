<script setup lang="ts">
import { onMounted, ref } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'
import Container from '@/Components/Container.vue'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import AttachmentInput from '@/Components/AttachmentInput.vue'

import DatePicker from '@/Components/DatePicker.vue'

import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectLabel,
  SelectTrigger,
  SelectValue,
} from '@/Components/ui/select'

import {
  Form,
  FormControl,
  FormDescription,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from '@/Components/ui/form'

import { Textarea } from '@/Components/ui/textarea'
import { Calendar } from '@/Components/ui/calendar'
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover'
import { CalendarIcon } from 'lucide-vue-next'
import ToastDialog from '@/Components/ToastDialog.vue'
import {
  DateFormatter,
  getLocalTimeZone,
} from '@internationalized/date'

import { cn } from '@/lib/utils'

const dateRanges = ref([
  {
    id: 1,
    startDate: '',
    endDate: ''
  }
])

const openAlert = ref<boolean>(false)
const alertMessage = ref<AlertMessage>({
  title: 'Oh no, something went wrong!',
  description: 'Please try again later.',
  variant: ''
})

const attendanceType = ref('')
const attendanceTypes = ref([])
const remarks = ref('')
const files = ref([])
const totalSize = ref(0)
const isLoading = ref(false)
const attachmentInputRef = ref(null)
const errors = ref({})

const router = useRouter()

const user = ref(null)

const fetchUserData = async () => {
  try {
    const response = await axios.get('/api/auth/user')
    user.value = response.data
  } catch (error) {
    console.error('Error fetching user data:', error)
  }
}

const handleSubmit = async () => {
  isLoading.value = true
  errors.value = {} // Reset errors
  try {
    const formData = new FormData()
    formData.append('attendance_type', attendanceType.value)
    formData.append('remarks', remarks.value)
    formData.append('user_id', user.value.id)
    dateRanges.value.forEach((range, index) => {
      formData.append(`start_date[${index}]`, range.startDate)
      formData.append(`end_date[${index}]`, range.endDate)
    })
    const files = attachmentInputRef.value.getFiles()
    files.forEach((file, index) => {
      formData.append(`file[${index}]`, file.file)
    })

    const response = await axios.post('/api/approved-attendance/store', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
    attendanceType.value = ''
    remarks.value = ''
    dateRanges.value = [{ startDate: '', endDate: '' }]
    files.value = []
    totalSize.value = 0
    isLoading.value = false

    alertMessage.value.title = 'Success'  
    alertMessage.value.description = response.data.message
    alertMessage.value.variant = 'success'
    openAlert.value = true

    // Wait for the toast dialog to close before redirecting
    setTimeout(() => {
        router.push({ name: 'AttachmentHistory' })
    }, 2000); // Adjust the timeout duration as needed

  } catch (error) {
    console.error('Error submitting form:', error)
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    }else{
      alertMessage.value.title = 'Error'
      alertMessage.value.description = error.response?.data?.message || "Failed to submit schedule change request"
      alertMessage.value.variant = 'destructive'
      openAlert.value = true
    } 
    isLoading.value = false
  }
}

const df = new DateFormatter('en-PH', {})

// Fetch attendance types on component mount
const fetchAttendanceTypes = async () => {
  try {
    const response = await axios.get('/api/getAttendanceType') // Adjust the URL as needed
    attendanceTypes.value = response.data
  } catch (error) {
    console.error('Error fetching attendance types:', error)
  }
}

onMounted(() => {
  fetchUserData()
  fetchAttendanceTypes()
})

</script>

<template>
    <div class="flex flex-col bg-background gap-8">
      <ToastDialog :open="openAlert" :message="alertMessage" @close="(val) => (openAlert = val)" />
        <div class="w-full">
            <DatePicker :dateRanges="dateRanges" @update:dateRanges="dateRanges = $event" :errors="errors" />
            <AttachmentInput ref="attachmentInputRef" :errors="errors" />
        </div>
      
        <div class="px-10">
            <div class="">
                <Label for="attendanceType" class="required-field text-sm">Attendance Type</Label>
                <Select v-model="attendanceType">
                    <SelectTrigger class="inline-flex px-4 py-2 h-[52px] w-full border-gray-500" :class="{ 'border-red-500': errors.attendance_type }">
                    <SelectValue placeholder="Select attendance type" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectGroup>
                        <SelectLabel>Attendance Types</SelectLabel>
                        <SelectItem v-for="type in attendanceTypes" :key="type.id" :value="type.id">
                            {{ type.type }}
                        </SelectItem>
                      </SelectGroup>
                    </SelectContent>
                </Select>
                <p v-if="errors.attendance_type" class="text-sm text-red-500 mt-1">{{ errors.attendance_type[0] }}</p>
            </div>

            <div class="relative flex flex-col mt-6 mb-6">
                <Label for="remarks" class="required-field text-sm">Remarks</Label>
                <Textarea v-model="remarks"
                            id="remarks"
                            placeholder="Enter remarks"
                            class="min-h-[100px] border-gray-500"
                              :class="{ 'border-red-500': errors.remarks }" />
                <p v-if="errors.remarks" class="text-sm text-red-500 mt-1">{{ errors.remarks[0] }}</p>
            </div>

            <div class="flex justify-center">
                
            </div>
        </div>

        <div class="save-button">
            <div class="mx-auto flex flex-col gap-4 p-12">
                <div class="flex items-center justify-center">
                <Button type="submit" @click="handleSubmit" :disabled="isLoading">
                  <span v-if="isLoading" class="loader"></span> 
                  <span v-if="isLoading">Saving changes...</span>
                  <span v-else class="font-semibold uppercase">Save Approved Attendance or Absence</span>
                </Button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.ex-container {
  border-radius: 0.5rem;
}
</style>
