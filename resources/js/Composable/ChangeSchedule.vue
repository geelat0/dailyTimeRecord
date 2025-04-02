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

const df = new DateFormatter('en-PH', {})
const startDate = ref()
const endDate = ref()
const selectedSchedule = ref()
const shifts = ref([])
const isSubmitting = ref(false)
const isOpen = ref(false)
const emit = defineEmits(['scheduleUpdated'])
const errors = ref({})


const openAlert = ref<boolean>(false)

const alertMessage = ref<AlertMessage>({
  title: 'Oh no, something went wrong!',
  description: 'Please try again later.',
  variant: ''
})


async function onSubmit(values: any) {
    isSubmitting.value = true
      errors.value = {} // Reset errors

  try {
    const formData = {
      start_date: startDate.value ? startDate.value.toString().split('T')[0] : '',
      end_date: endDate.value ? endDate.value.toString().split('T')[0] : '',
      shift_id: selectedSchedule.value,
      remarks: values.remarks,
      am_time_in: values.am_time_in,
      am_time_out: values.am_time_out,
      pm_time_in: values.pm_time_in,
      pm_time_out: values.pm_time_out,
    };

    const response = await axios.post('/api/shift-schedule/store', formData);

    if (response.status === 201) {
        alertMessage.value.title = 'Schedule Updated'
        alertMessage.value.description =  response.data.message
        alertMessage.value.variant = 'success'
        openAlert.value = true


      // Reset form
      startDate.value = null;
      endDate.value = null;
      selectedSchedule.value = null;
      values.remarks = '';
      values.am_time_in = '';
      values.am_time_out = '';
      values.pm_time_in = '';
      values.pm_time_out = '';
      values.remarks = '';

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

watch(isOpen, (newValue) => {
  if (!newValue) {
    // Reset errors when the dialog is closed
    errors.value = {};
  }
});
</script>

<template>
  <Form v-slot="{ handleSubmit }" as="" keep-values >
    <ToastDialog :open="openAlert" :message="alertMessage" @close="(val) => (openAlert = val)" />

    <Dialog v-model:open="isOpen">
      <DialogTrigger as-child>
        <Button variant="outline" class="px-6 py-4 border border-orange-500 bg-orange-500 text-white rounded-lg hover:bg-orange-700 hover:text-white transition-colors text-sm">
          Change Schedule
        </Button>
      </DialogTrigger>
      <DialogContent class="sm:max-w-[650px]">
        <DialogHeader>
          <DialogTitle>Change Schedule</DialogTitle>
          <DialogDescription>
            Make changes to your schedule here. Click save when you're done.
          </DialogDescription>
        </DialogHeader>

        <form id="dialogForm" @submit="handleSubmit($event, onSubmit)">
          <div class="flex justify-between items-center gap-4 ex-container">
            <div class="mb-4 w-full">
                <FormField v-slot="{ componentField }" name="startDate">
                <FormItem>
                    <FormLabel class="required-field text-sm">Select Start Date</FormLabel>
                    <FormControl>
                    <Popover>
                        <PopoverTrigger as-child>
                        <Button variant="outline"
                         class="inline-flex items-center whitespace-nowrap rounded-md text-sm ring-offset-background transition-colors focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none border border-gray-500 dark:border-white bg-background hover:text-accent-foreground px-4 py-2 h-[52px] w-full justify-start disabled:cursor-not-allowed disabled:opacity-60 cursor-pointer cursor-pointer"
                         :class="{ 'border-red-500': errors.start_date }">
                            {{ startDate ? df.format(startDate.toDate(getLocalTimeZone())) : "mm/dd/yyyy" }}
                        </Button>
                        </PopoverTrigger>
                        <PopoverContent>
                        <Calendar v-model="startDate" v-bind="componentField" />
                        </PopoverContent>
                    </Popover>
                    </FormControl>
                
                    <FormMessage />
                    <p v-if="errors.start_date" class="text-sm text-red-500 mt-1">{{ errors.start_date[0] }}</p>

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
                        <Button variant="outline" 
                        class="inline-flex items-center whitespace-nowrap rounded-md text-sm ring-offset-background transition-colors focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none border border-gray-500 dark:border-white bg-background hover:text-accent-foreground px-4 py-2 h-[52px] w-full justify-start disabled:cursor-not-allowed disabled:opacity-60 cursor-pointer cursor-pointer"
                        :class="{ 'border-red-500': errors.end_date }" 
                        >
                            {{ endDate ? df.format(endDate.toDate(getLocalTimeZone())) : "mm/dd/yyyy" }}
                        </Button>
                        </PopoverTrigger>
                        <PopoverContent>
                        <Calendar v-model="endDate" v-bind="componentField" />
                        </PopoverContent>
                    </Popover>
                    </FormControl>
                    
                    <FormMessage />
                    <p v-if="errors.end_date" class="text-sm text-red-500 mt-1">{{ errors.end_date[0] }}</p>

                </FormItem>
                </FormField>
            </div>
          </div>

          <div class="mb-4 w-full ex-container">
            <Label class="required-field text-sm">Select Schedule</Label>
                <Select v-model="selectedSchedule">
                    <SelectTrigger  class="inline-flex px-4 py-2 h-[52px] w-full border-gray-500"
                    :class="{ 'border-red-500': errors.shift_id }">
                        <SelectValue placeholder="Select Schedule" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem v-for="shift in shifts" :key="shift.id" :value="shift.id">{{ shift.shift_name }}</SelectItem>
                        <SelectItem value="0">Others</SelectItem>
                    </SelectContent>
                </Select>
                <p v-if="errors.shift_id" class="text-sm text-red-500 mt-1">{{ errors.shift_id[0] }}</p>
                <div v-if="selectedSchedule === '0'" class="mt-2">
                    <div class="flex justify-between items-center gap-4">
                        <div class="mb-4 w-full">
                            <FormField v-slot="{ componentField }" name="am_time_in">
                                <FormItem>
                                    <FormLabel class="required-field text-sm">Enter Time In AM</FormLabel>
                                    <FormControl>
                                        <Input type="time"  v-bind="componentField" class="inline-flex items-center whitespace-nowrap rounded-md text-sm ring-offset-background transition-colors focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none border border-gray-500 dark:border-white bg-background hover:text-accent-foreground px-4 py-2 h-[52px] w-full justify-start disabled:cursor-not-allowed disabled:opacity-60 cursor-pointer cursor-pointer" 
                                        :class="{ 'border-red-500': errors.am_time_in }"/>
                                    </FormControl>
                                    <FormMessage />
                                    <p v-if="errors.am_time_in" class="text-sm text-red-500 mt-1">{{ errors.am_time_in[0] }}</p>

                                </FormItem>
                            </FormField>
                        </div>
                        <div class="mb-4 w-full">
                            <FormField v-slot="{ componentField }" name="am_time_out">
                                <FormItem>
                                    <FormLabel class="required-field text-sm">Enter Time Out AM</FormLabel>
                                    <FormControl>
                                        <Input type="time"  v-bind="componentField" class="inline-flex items-center whitespace-nowrap rounded-md text-sm ring-offset-background transition-colors focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none border border-gray-500 dark:border-white bg-background hover:text-accent-foreground px-4 py-2 h-[52px] w-full justify-start disabled:cursor-not-allowed disabled:opacity-60 cursor-pointer cursor-pointer" 
                                        :class="{ 'border-red-500': errors.am_time_out }" />
                                    </FormControl>
                                    <FormMessage />
                                    <p v-if="errors.am_time_out" class="text-sm text-red-500 mt-1">{{ errors.am_time_out[0] }}</p>

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
                                        <Input type="time"  v-bind="componentField" class="inline-flex items-center whitespace-nowrap rounded-md text-sm ring-offset-background transition-colors focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none border border-gray-500 dark:border-white bg-background hover:text-accent-foreground px-4 py-2 h-[52px] w-full justify-start disabled:cursor-not-allowed disabled:opacity-60 cursor-pointer cursor-pointer"
                                        :class="{ 'border-red-500': errors.pm_time_in }" />
                                    </FormControl>
                                    <FormMessage />
                                    <p v-if="errors.pm_time_in" class="text-sm text-red-500 mt-1">{{ errors.pm_time_in[0] }}</p>

                                </FormItem>
                            </FormField>
                        </div>
                        <div class="mb-4 w-full">
                            <FormField v-slot="{ componentField }" name="pm_time_out">
                            <FormItem>
                                <FormLabel class="required-field text-sm">Enter Time Out PM</FormLabel>
                                <FormControl>
                                    <Input type="time" v-bind="componentField"  
                                    class="inline-flex items-center whitespace-nowrap rounded-md text-sm ring-offset-background transition-colors focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none border border-gray-500 dark:border-white bg-background hover:text-accent-foreground px-4 py-2 h-[52px] w-full justify-start disabled:cursor-not-allowed disabled:opacity-60 cursor-pointer cursor-pointer"
                                    :class="{ 'border-red-500': errors.pm_time_out }"
                                    />
                                </FormControl>
                                    <FormMessage />
                                    <p v-if="errors.pm_time_out" class="text-sm text-red-500 mt-1">{{ errors.pm_time_out[0] }}</p>

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
                                :class="{ 'border-red-500': errors.remarks }" />
                        </FormControl>
                        <FormMessage />
                        <p v-if="errors.remarks" class="text-sm text-red-500 mt-1">{{ errors.remarks[0] }}</p>
                    </FormItem>
                </FormField>
            </div>
          </div>
        </form>
        <DialogFooter>
          <Button type="submit" form="dialogForm" class="bg-orange-500 hover:bg-orange-700 text-white text-base px-8 py-4 border border-orange-500 rounded-lg" :disabled="isSubmitting">
            <span v-if="isSubmitting" class="loader"></span> 
            <span v-if="isSubmitting">Saving changes...</span>
            <span v-else>Save</span>
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