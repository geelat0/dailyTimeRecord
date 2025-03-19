<script setup>
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

const attendanceType = ref('')
const attendanceTypes = ref([])
const remarks = ref('')
const files = ref([])
const totalSize = ref(0)
const isLoading = ref(false)
const attachmentInputRef = ref(null)

const handleSubmit = async () => {
  isLoading.value = true
  try {
    const formData = new FormData()
    formData.append('attendance_type', attendanceType.value)
    formData.append('remarks', remarks.value)
    formData.append('user_id', 1)
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

    console.log('Response:', response.data)
    alert('Approved attendance or absence saved successfully')
  } catch (error) {
    console.error('Error submitting form:', error)
    alert('An error occurred while saving approved attendance or absence. Please try again.')
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
  fetchAttendanceTypes()
})

</script>

<template>
    <div class="flex flex-col gap-8">
        <div class="w-full">
      <DatePicker :dateRanges="dateRanges" @update:dateRanges="dateRanges = $event" />
        </div>
        <div class="w-full">
            <AttachmentInput ref="attachmentInputRef" />
        </div>

        <div class="ex-container bg-background p-4">
            <div class="p-2">
                <div class="">
                    <Label for="attendanceType" class="required-label text-sm">Attendance Type</Label>
                    <Select v-model="attendanceType">
                        <SelectTrigger class="inline-flex px-4 py-2 h-[52px] w-full border-gray-500 ">
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
                </div>

                <div class="relative flex flex-col mt-6 mb-6">
                    <Label for="remarks" class="required-label text-sm">Remarks</Label>
                    <Textarea v-model="remarks"
                                id="remarks"
                                placeholder="Enter remarks"
                                class="min-h-[100px] border-gray-500" />
                </div>

                <div class="flex justify-center">
                    
                </div>
            </div>
        </div>

        <div class="save-button">
            <div class="ex-container bg-background mx-auto flex flex-col gap-4 p-12">
                <div class="flex items-center justify-center">
                <Button type="submit" @click="handleSubmit" :disabled="isLoading">
                    <span class="font-semibold uppercase">Save Approved Attendance or Absence</span>
                    <Icon v-if="!isLoading" icon="mdi:send"
                /></Button>
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
