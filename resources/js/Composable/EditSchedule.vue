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
import { ref, onMounted } from 'vue'
import { getLocalTimeZone, DateFormatter } from '@internationalized/date'
import { Textarea } from '@/Components/ui/textarea'
import ToastDialog from '@/Components/ToastDialog.vue'
import { useMediaQuery } from '@vueuse/core'



const df = new DateFormatter('en-PH', {})
const startDate = ref(new Date());
const endDate = ref(new Date());
const selectedSchedule = ref()
const shifts = ref([])
const isSubmitting = ref(false)
const isOpen = ref(false)
const emit = defineEmits(['scheduleUpdated'])
const errors = ref({})
const isMobile = useMediaQuery('(max-width: 768px)')
const isTablet = useMediaQuery('(min-width: 769px) and (max-width: 1024px)')


const openAlert = ref<boolean>(false)

const alertMessage = ref<AlertMessage>({
  title: 'Oh no, something went wrong!',
  description: 'Please try again later.',
  variant: ''
})


const formSchema = toTypedSchema(z.object({
  startDate: z.string().optional(),
  endDate: z.string().optional(),
  remarks: z.string().optional(),
  am_time_in: z.string().optional(),
  am_time_out: z.string().optional(),
  pm_time_in: z.string().optional(),
  pm_time_out: z.string().optional(),
}))

const props = defineProps({
  entry: {
    type: Object,
    required: true
  }
})

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
})

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
        if(!shiftExists){
            formValues.value.am_time_in = convertTo24HourFormat(props.entry.am_time_in);
            formValues.value.am_time_out = convertTo24HourFormat(props.entry.am_time_out);
            formValues.value.pm_time_in = convertTo24HourFormat(props.entry.pm_time_in);
            formValues.value.pm_time_out = convertTo24HourFormat(props.entry.pm_time_out);
        }
    }
}

async function onSubmit(values: any) {
    isSubmitting.value = true
  try {
    const formData = {
      id: formValues.value.scheduleId,
      start_date: startDate.value.toLocaleString('en-US', { timeZone: 'Asia/Manila', year: 'numeric', month: '2-digit', day: '2-digit' }).split(',')[0],
      end_date: endDate.value.toLocaleString('en-US', { timeZone: 'Asia/Manila', year: 'numeric', month: '2-digit', day: '2-digit' }).split(',')[0],
    //shift_id: selectedSchedule.value,
      remarks: formValues.value.remarks,
      am_time_in: formValues.value.am_time_in,
      am_time_out: formValues.value.am_time_out,
      pm_time_in: formValues.value.pm_time_in,
      pm_time_out: formValues.value.pm_time_out,
      shift_id: selectedSchedule.value == '0' ? formValues.value.shift : selectedSchedule.value,
    };


    const response = await axios.post('/api/shift-schedule/update', formData);

    if (response.status === 200) {

        alertMessage.value.title = 'Schedule Updated'
        alertMessage.value.description =  response.data.message
        alertMessage.value.variant = 'success'
        openAlert.value = true

        // Emit event to parent component
        emit('scheduleUpdated');    
        isOpen.value = false;
    }
  } catch (error: any) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    } else {

        alertMessage.value.title = 'Error'
        alertMessage.value.description = error.response?.data?.message || "Failed to submit schedule change request"
        alertMessage.value.variant = 'destructive'
        openAlert.value = true

    }
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

onMounted(() => {
    getShift();
})
</script>

<template>
    
  <Form v-slot="{ handleSubmit }" as="" keep-values>
    <ToastDialog :open="openAlert" :message="alertMessage" @close="(val) => (openAlert = val)" />

    <Dialog v-model:open="isOpen">
      <DialogTrigger as-child>
        <Button variant="outline" class="px-2 py-2 border border-orange-500 bg-white text-orange-500 rounded-lg hover:bg-orange-100 transition-colors text-xs"
        @click="getEntry"
        >
          Edit
        </Button>
      </DialogTrigger>
      <DialogContent class="sm:max-w-[650px]">
        <DialogHeader>
          <DialogTitle>Edit Schedule</DialogTitle>
          <DialogDescription>
            Make changes to your schedule here. Click save when you're done.
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
            <Label class="required-field text-sm">Select Schedule</Label>
                <Select v-model="selectedSchedule">
                    <SelectTrigger class="inline-flex px-4 py-2 h-[52px] w-full border-gray-500">
                        <SelectValue placeholder="Select Schedule" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem v-for="shift in shifts" :key="shift.id" :value="shift.id">{{ shift.shift_name }}</SelectItem>
                        <SelectItem value="0">Others</SelectItem>
                    </SelectContent>
                </Select>
                <div v-if="selectedSchedule === '0'" class="mt-2">
                    <div class="flex justify-between items-center gap-4">
                        <div class="mb-4 w-full">
                            <FormField v-slot="{ componentField }" name="am_time_in">
                                <FormItem>
                                    <FormLabel class="required-field text-sm">Enter Time In AM</FormLabel>
                                    <FormControl>
                                        <Input type="time" v-bind="componentField" v-model="formValues.am_time_in" class="inline-flex items-center whitespace-nowrap rounded-md text-sm ring-offset-background transition-colors focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none border border-gray-500 dark:border-white bg-background hover:text-accent-foreground px-4 py-2 h-[52px] w-full justify-start disabled:cursor-not-allowed disabled:opacity-60 cursor-pointer cursor-pointer" />
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            </FormField>
                        </div>
                        <div class="mb-4 w-full">
                            <FormField v-slot="{ componentField }" name="am_time_out">
                                <FormItem>
                                    <FormLabel class="required-field text-sm">Enter Time Out AM</FormLabel>
                                    <FormControl>
                                        <Input type="time" v-bind="componentField" v-model="formValues.am_time_out" class="inline-flex items-center whitespace-nowrap rounded-md text-sm ring-offset-background transition-colors focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none border border-gray-500 dark:border-white bg-background hover:text-accent-foreground px-4 py-2 h-[52px] w-full justify-start disabled:cursor-not-allowed disabled:opacity-60 cursor-pointer cursor-pointer" />
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            </FormField>
                        </div>
                    </div>
                    <div class="flex justify-between items-center gap-4">
                        <div class="mb-4 w-full">
                            <FormField v-slot="{ componentField }" name="pm_time_in">
                                <FormItem>
                                    <FormLabel class="required-field text-sm">Enter Time In PM</FormLabel>
                                    <FormControl>
                                        <Input type="time" v-bind="componentField" v-model="formValues.pm_time_in" class="inline-flex items-center whitespace-nowrap rounded-md text-sm ring-offset-background transition-colors focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none border border-gray-500 dark:border-white bg-background hover:text-accent-foreground px-4 py-2 h-[52px] w-full justify-start disabled:cursor-not-allowed disabled:opacity-60 cursor-pointer cursor-pointer" />
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            </FormField>
                        </div>
                        <div class="mb-4 w-full">
                            <FormField v-slot="{ componentField }" name="pm_time_out">
                                <FormItem>
                                    <FormLabel class="required-field text-sm">Enter Time Out PM</FormLabel>
                                    <FormControl>
                                        <Input type="time" v-bind="componentField" v-model="formValues.pm_time_out" class="inline-flex items-center whitespace-nowrap rounded-md text-sm ring-offset-background transition-colors focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none border border-gray-500 dark:border-white bg-background hover:text-accent-foreground px-4 py-2 h-[52px] w-full justify-start disabled:cursor-not-allowed disabled:opacity-60 cursor-pointer cursor-pointer" />
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            </FormField>
                        </div>
                    </div>
                </div>
           </div>

          <div class="mb-4 w-full ex-container">
            <div class="relative flex flex-col mt-6 mb-6">
                <FormField v-slot="{ componentField }" name="remarks">
                    <FormItem>
                        <FormLabel class="required-field text-sm">Remarks</FormLabel>
                        <FormControl>
                            <Textarea 
                                v-bind="componentField"
                                placeholder="Enter remarks"
                                class="min-h-[100px] border-gray-500"
                                v-model="formValues.remarks" />
                        </FormControl>
                        <FormMessage />
                    </FormItem>
                </FormField>
            </div>
          </div>
        </form>
        <DialogFooter>
          <Button type="submit" form="dialogForm" class="bg-orange-500 hover:bg-orange-700 text-white text-base px-8 py-4 border border-orange-500 rounded-lg" :disabled="isSubmitting">
            <span v-if="isSubmitting" class="loader"></span> 
            <span v-if="isSubmitting">Saving changes...</span>
            <span v-else>Save Changes</span>
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
</style>