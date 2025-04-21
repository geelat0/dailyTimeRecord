<script setup lang="ts">
import { Button } from '@/Components/ui/button'
import axios from 'axios';
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

import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectLabel,
  SelectTrigger,
  SelectValue,
} from '@/Components/ui/select'

import { Input } from '@/Components/ui/input'
import { toast } from '@/Components/ui/toast'
import { toTypedSchema } from '@vee-validate/zod'
import { h } from 'vue'
import * as z from 'zod'
import { Calendar } from '@/Components/ui/calendar'
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover'
import { ref, onMounted, watch } from 'vue'
import { getLocalTimeZone, DateFormatter } from '@internationalized/date'
import { Textarea } from '@/Components/ui/textarea'
import ToastDialog from '@/Components/ToastDialog.vue'
import { useMediaQuery } from '@vueuse/core'

// Define the AlertMessage interface
interface AlertMessage {
  title: string;
  description: string;
  variant?: 'destructive' | 'success' | 'default';
  errorCode?: string;
}

const df = new DateFormatter('en-PH', {})
const startDate = ref(new Date());
const endDate = ref(new Date());
const selectedSchedule = ref()
const shifts = ref([])
const approvers = ref([])
const selectedApprover = ref('')
const isSubmitting = ref(false)
const isGenerating = ref(false)
const isOpen = ref(false)
const emit = defineEmits(['scheduleUpdated'])
const errors = ref({})
const isMobile = useMediaQuery('(max-width: 768px)')
const isTablet = useMediaQuery('(min-width: 769px) and (max-width: 1024px)')
const previewUrl = ref('')
const showPreview = ref(false)
const previewError = ref(false)
const dtrUrl = ref('')
const filePath = ref('')
const filename = ref('')


const openAlert = ref<boolean>(false)

const alertMessage = ref<AlertMessage>({
  title: 'Oh no, something went wrong!',
  description: 'Please try again later.',
  variant: 'default'
})

const formSchema = toTypedSchema(z.object({
  startDate: z.string().min(1, "Start date is required"),
  endDate: z.string().min(1, "End date is required"),
  approver: z.string().min(1, "Approver is required"),
}))

const props = defineProps({
  entry: {
    type: Object,
    required: true
  }
})
const user = ref(null)
const formValues = ref({
    startDate: '',
    endDate: '',
    remarks: '',
    am_time_in: '',
    am_time_out: '',
    pm_time_in: '',
    pm_time_out: '',
    shift_id: '',
    scheduleId: '',
    shift: '',
    approver: '',
})

const fetchUserData = async () => {
  try {
    const response = await axios.get('/api/auth/user')
    user.value = response.data
  } catch (error) {
    alertMessage.value.title = 'Error'
    alertMessage.value.description = error.response?.data?.message || "Failed to fetch user data"
    alertMessage.value.variant = 'destructive'
    openAlert.value = true
    isOpen.value = false
    console.error('Error fetching user data:', error)
  }
}

const formatDate = (date) => {
    const newDate = new Date(date);
    return newDate ? newDate.toLocaleDateString('en-US') : "mm/dd/yyyy";
};

const convertTo24HourFormat = (timeString) => {
  const date = new Date(`2000-01-01 ${timeString}`);
  return date.toLocaleTimeString('en-US', { hour12: false, hour: '2-digit', minute: '2-digit' });
}

const getEntry = async () => {
    if(props.entry){
        // Check if the entry's shift exists in the predefined shifts
        formValues.value.scheduleId = props.entry.id;
        startDate.value = new Date(props.entry.start_date);
        endDate.value = new Date(props.entry.end_date);
        const shiftExists = shifts.value.some(shift => shift.id === props.entry.shift_id);
        selectedSchedule.value = shiftExists ? props.entry.shift_id : '0';
        formValues.value.remarks = props.entry.remarks;
        formValues.value.shift = selectedSchedule.value == '0' ? props.entry.shift_id : selectedSchedule.value;
        formValues.value.approver = props.entry.approver_id || '';
        if(!shiftExists){
            formValues.value.am_time_in = convertTo24HourFormat(props.entry.am_time_in);
            formValues.value.am_time_out = convertTo24HourFormat(props.entry.am_time_out);
            formValues.value.pm_time_in = convertTo24HourFormat(props.entry.pm_time_in);
            formValues.value.pm_time_out = convertTo24HourFormat(props.entry.pm_time_out);
        }
    }
}

const getApprovers = async () => {
    try {
        const response = await axios.get('/api/approvers');
        approvers.value = response.data.data;
    } catch (error) {
        console.error('Error fetching approvers:', error);
        alertMessage.value.title = 'Error'
        alertMessage.value.description = 'Failed to fetch approvers'
        alertMessage.value.variant = 'destructive'
        openAlert.value = true
    }
}

async function onSubmit(values: any) {
    isSubmitting.value = true
    try {
        // Get the access token from localStorage
        const token = localStorage.getItem('token');
        
        if (!token) {
            throw new Error('No access token found');
        }

        // Ensure we have valid Date objects
        const startDateObj = new Date(startDate.value);
        const endDateObj = new Date(endDate.value);

        if (isNaN(startDateObj.getTime()) || isNaN(endDateObj.getTime())) {
            throw new Error('Invalid date format');
        }

        // Make the request to submit DTR
        const response = await axios.post(`/api/submit-dtr`, {
            user_id: user.value.id,
            subject: `DTR (For Approval) - ${startDateObj.toISOString().split('T')[0]} to ${endDateObj.toISOString().split('T')[0]}`,
            approver_id: selectedApprover.value,
            start_date: startDateObj,
            attachment: {
                file_path: filePath.value,
                filename: filename.value,
            },
            status: 'For Approval',
            headers: {
                'Authorization': `Bearer ${token}`,
            },
        });
        
        // Show success message
        alertMessage.value.title = 'Success'
        alertMessage.value.description = 'DTR submitted successfully'
        alertMessage.value.variant = 'success'
        openAlert.value = true

        // Close the dialog
        closeModal();
    } catch (error: any) {
        console.error('Error submitting DTR:', error);
        
        alertMessage.value.title = 'Error'
        alertMessage.value.description = error.response?.data?.message || "Failed to submit DTR"
        alertMessage.value.variant = 'destructive'
        openAlert.value = true
        closeModal();

    } finally {
        isSubmitting.value = false
    }
}

const getShift = async () => {
    try {   
        const response = await axios.get('/api/getShift');
        shifts.value = response.data;
    } catch (error) {
        console.error('Error fetching shift schedules:', error);
    }
}

async function previewDTR() {
  isGenerating.value = true
  previewError.value = false
    try {
        const startDateObj = new Date(startDate.value);
        const endDateObj = new Date(endDate.value);

        const formattedStartDate = startDateObj.toISOString().split('T')[0];
        const formattedEndDate = endDateObj.toISOString().split('T')[0];

        const token = localStorage.getItem('token');
        
        if (!token) {
            throw new Error('No access token found');
        }

        const response = await axios.get(`/api/download-dtr`, {
            params: {
                start_date: formattedStartDate,
                end_date: formattedEndDate,
                user_id: user.value.id
            },
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });

        if (response.data.success) {
            // Store the file path and filename
            filePath.value = response.data.file_path;
            filename.value = response.data.filename;
            
            // Use the preview URL from the response
            previewUrl.value = response.data.preview_url;
            showPreview.value = true;
            
            // Add a small delay to ensure the file is accessible
            setTimeout(() => {
                // Check if the iframe loaded successfully
                const iframe = document.querySelector('.pdf-preview-container iframe') as HTMLIFrameElement;
                if (iframe) {
                    iframe.onload = () => {
                        // Iframe loaded successfully
                    };
                    
                    // Set a timeout to check if the iframe loaded
                    setTimeout(() => {
                        try {
                            // Try to access the iframe content to check if it loaded
                            const iframeDoc = iframe.contentDocument || iframe.contentWindow?.document;
                            if (!iframeDoc || iframeDoc.body.innerHTML === '') {
                                previewError.value = true;
                                console.error('Failed to load PDF preview');
                            }
                        } catch (e) {
                            // Cross-origin error or other issues
                            previewError.value = true;
                            console.error('Error accessing iframe content:', e);
                        }
                    }, 2000);
                }
            }, 500);
        } else {
            throw new Error('Invalid response format');
        }
    } catch (error: any) {
        console.error('Error previewing DTR:', error);
        previewError.value = true;
        
        alertMessage.value.title = 'Error'
        alertMessage.value.description = error.response?.data?.message || "Failed to preview DTR"
        alertMessage.value.variant = 'destructive'
        openAlert.value = true
        closeModal();
    } finally {
      isGenerating.value = false
    }
}

const resetForm = () => {
    startDate.value = new Date()
    endDate.value = new Date()
    selectedSchedule.value = undefined
    selectedApprover.value = ''
    formValues.value = {
        startDate: '',
        endDate: '',
        remarks: '',
        am_time_in: '',
        am_time_out: '',
        pm_time_in: '',
        pm_time_out: '',
        shift_id: '',
        scheduleId: '',
        shift: '',
        approver: '',
    }
    showPreview.value = false
    previewUrl.value = ''
    previewError.value = false
    filePath.value = ''
    filename.value = ''
}

const closeModal = () => {
    isOpen.value = false;
    resetForm();
}

const watchModal = watch(isOpen, (newValue) => {
    if (!newValue) {
        resetForm()
    }
})

onMounted(() => {
    getShift();
    getApprovers();
    fetchUserData();
})
</script>

<template>
    
  <Form v-slot="{ handleSubmit }" as="" keep-values>
    <ToastDialog :open="openAlert" :message="alertMessage" @close="(val) => (openAlert = val)" />

    <Dialog v-model:open="isOpen" @update:open="(val) => isOpen = val">
      <DialogTrigger as-child>
        <Button variant="outline" class="px-8 py-4 border border-orange-500 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors text-base"
        @click="getEntry"
        >
          Generate DTR
        </Button>
      </DialogTrigger>
      <DialogContent class="sm:max-w-[800px]">
        <DialogHeader>
            <DialogTitle>Generate DTR</DialogTitle>
          <DialogDescription>
            Generate DTR for the selected date range.
          </DialogDescription>
        </DialogHeader>

        <form id="dialogForm" @submit="handleSubmit($event, onSubmit)">

          <input type="hidden" name="id" v-model="formValues.scheduleId" />
          <input type="hidden" name="shift" v-model="formValues.shift" />
            
          <div class="flex justify-between items-center gap-4 ex-container">
            <div class="mb-4 w-full">
                <FormField v-slot="{ componentField }" name="startDate">
                <FormItem>
                    <FormLabel class="required-field text-sm">Select Start Date</FormLabel>
                    <FormControl>
                    <Popover>
                        <PopoverTrigger as-child>
                        <Button variant="outline" class="inline-flex items-center whitespace-nowrap rounded-md text-sm ring-offset-background transition-colors focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none border border-gray-500 dark:border-white bg-background hover:text-accent-foreground px-4 py-2 h-[52px] w-full justify-start disabled:cursor-not-allowed disabled:opacity-60 cursor-pointer cursor-pointer">
                            {{ formatDate(startDate) ??  "mm/dd/yyyy" }}
                        </Button>
                        
                        </PopoverTrigger>
                        <PopoverContent>
                        <Calendar v-model="startDate" v-bind="componentField" />
                        </PopoverContent>
                    </Popover>
                    </FormControl>
                
                    <FormMessage />
                </FormItem>
                </FormField>
            </div>
            <div class="mb-4 w-full">
                <FormField v-slot="{ componentField }" name="endDate">
                <FormItem>
                    <FormLabel class="required-field text-sm">Select End Date</FormLabel>
                    <FormControl>
                    <Popover>
                        <PopoverTrigger as-child>
                        <Button variant="outline" class="inline-flex items-center whitespace-nowrap rounded-md text-sm ring-offset-background transition-colors focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none border border-gray-500 dark:border-white bg-background hover:text-accent-foreground px-4 py-2 h-[52px] w-full justify-start disabled:cursor-not-allowed disabled:opacity-60 cursor-pointer cursor-pointer">
                            {{ formatDate(endDate) ?? "mm/dd/yyyy" }}
                        </Button>
                        </PopoverTrigger>
                        <PopoverContent>
                        <Calendar v-model="endDate" v-bind="componentField" />
                        </PopoverContent>
                    </Popover>
                    </FormControl>
                    
                    <FormMessage />
                </FormItem>
                </FormField>
            </div>
          </div>

          <div class="mb-4 w-full ex-container">
            <FormField v-slot="{ componentField }" name="approver">
              <FormItem>
                <FormLabel class="required-field text-sm">Select Approver</FormLabel>
                <Select v-model="selectedApprover" v-bind="componentField">
                  <FormControl>
                    <SelectTrigger class="inline-flex px-4 py-2 h-[52px] w-full border-gray-500">
                      <SelectValue placeholder="Select an approver" />
                    </SelectTrigger>
                  </FormControl>
                  <SelectContent>
                    <SelectGroup>
                      <SelectLabel>Approvers</SelectLabel>
                      <SelectItem v-for="approver in approvers" :key="approver.id" :value="approver.id">
                        {{ approver.full_name }}
                      </SelectItem>
                    </SelectGroup>
                  </SelectContent>
                </Select>
                <FormMessage />
              </FormItem>
            </FormField>
          </div>
          
        </form>

        <div v-if="showPreview" class="mt-4">
            <div v-if="previewError" class="text-red-500 text-center p-4">
                Failed to load PDF. Please try again.
            </div>
            <div v-else class="pdf-preview-container">
                <iframe 
                    :src="previewUrl" 
                    class="w-full h-[500px] border border-gray-200 rounded-lg"
                    @error="previewError = true"
                ></iframe>
            </div>
        </div>

        <DialogFooter class="flex gap-2">
            <Button type="button" @click="previewDTR" class="bg-gray-500 hover:bg-gray-700 text-white text-base px-8 py-4 border border-gray-500 rounded-lg" :disabled="isGenerating">
                <span v-if="isGenerating" class="loader"></span> 
                <span v-if="isGenerating">Loading...</span>
                <span v-else>Preview</span>
            </Button>
            <Button v-if="showPreview" type="submit" form="dialogForm" class="bg-orange-500 hover:bg-orange-700 text-white text-base px-8 py-4 border border-orange-500 rounded-lg" :disabled="isSubmitting">
                <span v-if="isSubmitting" class="loader"></span> 
                <span v-if="isSubmitting">Submitting ...</span>
                <span v-else>Submit</span>
            </Button>     
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </Form>
</template> 

<style scoped>
.ex-container {
  border-radius: 0.5rem;
}

.pdf-preview-container {
  width: 100%;
  height: 500px;
  overflow: hidden;
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
}
</style>