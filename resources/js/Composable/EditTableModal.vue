<script setup lang="ts">
import { ref, watch, onMounted } from 'vue'
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

import { Input } from '@/Components/ui/input'
import { toast } from '@/Components/ui/toast'
import { toTypedSchema } from '@vee-validate/zod'
import * as z from 'zod'

const openAlert = ref<boolean>(false)

const alertMessage = ref<AlertMessage>({
  title: 'Oh no, something went wrong!',
  description: 'Please try again later.',
  variant: ''
})

const form = ref(null)
const emit = defineEmits(['update'])
const isSubmitting = ref(false)
const isFetching = ref(false)
const props = defineProps({
  entry: {
    type: Object,
    default: null
  }
})
const isOpen = ref(false)
const formValues = ref({
  timeEntryID: '',
  amTimeIn: '',
  amTimeOut: '',
  pmTimeIn: '',
  pmTimeOut: ''
})

const user = ref(null)
const date = ref(null)
const formSchema = toTypedSchema(z.object({
  amTimeIn: z.string().optional(),
  amTimeOut: z.string().optional(),
  pmTimeIn: z.string().optional(),
  pmTimeOut: z.string().optional(),
}))

const convertTo24HourFormat = (timeString) => {
  const date = new Date(`2000-01-01 ${timeString}`);
  return date.toLocaleTimeString('en-US', { hour12: false, hour: '2-digit', minute: '2-digit' });
}

const handleDialogOpen = async () => {
  isOpen.value = true; // Open the dialog first to ensure content loads
  if (props.entry) {
    try {
      formValues.value = {
        timeEntryID: props.entry.id,
        amTimeIn: props.entry.am_time_in ? convertTo24HourFormat(props.entry.am_time_in) : '',
        amTimeOut: props.entry.am_time_out ?  convertTo24HourFormat(props.entry.am_time_out) : ' ',
        pmTimeIn: props.entry.pm_time_in ? convertTo24HourFormat(props.entry.pm_time_in) : '',
        pmTimeOut: props.entry.pm_time_out ?  convertTo24HourFormat(props.entry.pm_time_out) : '',
      };
      date.value = props.entry.temp_date
      user.value = props.entry.user_id

      if (form.value) {
        form.value.resetForm({ values: formValues.value });
      }
    } catch (error) {
      console.error('Error fetching time entry:', error);
    }
  }
}

const onSubmit = async (values) => {
  isSubmitting.value = true;
  try {
    const response = await axios.post('/api/time-entries/update', {
      user_id: user.value,
      id: formValues.value.timeEntryID,
      date: date.value,
      am_time_in: formValues.value.amTimeIn,
      am_time_out: formValues.value.amTimeOut,
      pm_time_in: formValues.value.pmTimeIn,
      pm_time_out: formValues.value.pmTimeOut
    });

    alertMessage.value.title = 'Time Entry Updated'
    alertMessage.value.description = response.data.message
    alertMessage.value.variant = 'success'
    openAlert.value = true

    emit('update');
    isOpen.value = false;
  } catch (error) {
    console.error('Error updating time entry:', error);
    alertMessage.value.title = 'Error'
    alertMessage.value.description = error.response?.data?.message || "Failed to update time entry"
    alertMessage.value.variant = 'destructive'
    openAlert.value = true
    isOpen.value = false;
  } finally {
    isSubmitting.value = false;
  }
}

// Watch for dialog close to reset form values
watch(isOpen, (newVal) => {
  if (!newVal) {
    formValues.value = {
      timeEntryID: '',
      amTimeIn: '',
      amTimeOut: '',
      pmTimeIn: '',
      pmTimeOut: ''
    };
  }
});
</script>

<template>
  <Form v-slot="{ handleSubmit }" 
  @submit="handleSubmit($event, onSubmit)"
    :validation-schema="formSchema" 
    :initial-values="formValues"
    
  >
    <ToastDialog :open="openAlert" :message="alertMessage" @close="(val) => (openAlert = val)" />
    <Dialog v-model:open="isOpen">
      <DialogTrigger as-child>
        <Button variant="outline" class="px-2 py-2 border border-orange-500 bg-white text-orange-500 rounded-lg hover:bg-orange-100 transition-colors text-xs"
        @click="handleDialogOpen">
          Edit      
        </Button>
      </DialogTrigger>
      <DialogContent class="max-h-[90vh] sm:max-w-[525px]">
        <DialogHeader>
            <DialogTitle>Edit Time Entry (<span>{{ date }}</span>) </DialogTitle>
          <DialogDescription>
            Make changes to your time Entry here. Click save when you're done.
          </DialogDescription>
        </DialogHeader>

        <form id="dialogForm" @submit="handleSubmit($event, onSubmit)">
          <div style="display:none">
            <FormField v-slot="{ componentField }" name="timeEntryID">
            <FormItem>
              <FormControl>
                <Input type="text" v-bind="componentField" v-model="formValues.timeEntryID"
                class="pr-10 py-6 text-xl"/>
              </FormControl>
              <FormDescription></FormDescription>
              <FormMessage />
            </FormItem>
          </FormField>

          <FormField v-slot="{ componentField }" name="date">
            <FormItem>
              <FormControl>
                <Input type="text" v-bind="componentField" v-model="formValues.date"
                class="pr-10 py-6 text-xl"/>
              </FormControl>
              <FormDescription></FormDescription>
              <FormMessage />
            </FormItem>
          </FormField>

          </div>
          
          <FormField v-slot="{ componentField }" name="amTimeIn">
            <FormItem>
              <FormLabel>AM Time In</FormLabel>
              <FormControl>
                <Input type="time" v-bind="componentField" v-model="formValues.amTimeIn"
                class="pr-10 py-6 text-xl"/>
              </FormControl>
              <FormDescription></FormDescription>
              <FormMessage />
            </FormItem>
          </FormField>

          <FormField v-slot="{ componentField }" name="amTimeOut">
            <FormItem>
              <FormLabel>AM Time Out</FormLabel>
              <FormControl>
                <Input type="time" v-bind="componentField" v-model="formValues.amTimeOut" class="pr-10 py-6 text-xl" />
              </FormControl>
              <FormDescription></FormDescription>
              <FormMessage />
            </FormItem>
          </FormField>

          <FormField v-slot="{ componentField }" name="pmTimeIn">
            <FormItem>
              <FormLabel>PM Time In</FormLabel>
              <FormControl>
                <Input type="time" v-bind="componentField" v-model="formValues.pmTimeIn"
                class="pr-10 py-6 text-xl" />
              </FormControl>
              <FormDescription></FormDescription>
              <FormMessage />
            </FormItem>
          </FormField>

          <FormField v-slot="{ componentField }" name="pmTimeOut">
            <FormItem>
              <FormLabel>PM Time Out</FormLabel>
              <FormControl>
                <Input type="time" v-bind="componentField" v-model="formValues.pmTimeOut" class="pr-10 py-6 text-xl" />
              </FormControl>
              <FormDescription></FormDescription>
              <FormMessage />
            </FormItem>
          </FormField>
        </form>

        <DialogFooter>
          <Button type="submit" form="dialogForm" :disabled="isSubmitting">
            <span v-if="isSubmitting" class="loader"></span> 
            <span v-if="isSubmitting">Saving changes...</span>
            <span v-else>Save changes</span>
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </Form>
</template>