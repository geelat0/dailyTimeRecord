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

const props = defineProps({
  shouldRefresh: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['time-entry-updated'])
const isSubmitting = ref(false)
const isFetching = ref(false)
const user = ref(null)

const formSchema = toTypedSchema(z.object({
  amTimeIn: z.string().optional(),
  amTimeOut: z.string().optional(),
  pmTimeIn: z.string().optional(),
  pmTimeOut: z.string().optional(),
}))

const isOpen = ref(false)
const timeEntryData = ref({})
const formValues = ref({
  timeEntryID: '',
  amTimeIn: '',
  amTimeOut: '',
  pmTimeIn: '',
  pmTimeOut: ''
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

onMounted(() => {
  fetchUserData();
})

const onSubmit = async (values) => {
  isSubmitting.value = true
  try {
    const response = await axios.post('/api/time-entries/update', {
      id: formValues.value.timeEntryID,
      user_id: user.value.id,
      date: new Date().toISOString().split('T')[0], // Current date in YYYY-MM-DD format
      am_time_in: formValues.value.amTimeIn,
      am_time_out: formValues.value.amTimeOut,
      pm_time_in: formValues.value.pmTimeIn,
      pm_time_out: formValues.value.pmTimeOut
    })

    if (response.status === 200) {
      alertMessage.value.title = 'Time Entry Updated'
      alertMessage.value.description = response.data.message
      alertMessage.value.variant = 'success'
      openAlert.value = true
      isOpen.value = false // Close the dialog
      await getTimeEntries() // Refresh the data
      emit('time-entry-updated') // Emit event to parent component
    }
  } catch (error) {
    console.error('Error updating time entry:', error)
    alertMessage.value.title = 'Error'
    alertMessage.value.description = error.response?.data?.message || "Failed to update time entry"
    alertMessage.value.variant = 'destructive'
    openAlert.value = true
    isOpen.value = false
  } finally {
    isSubmitting.value = false
  }
}

const getTimeEntries = async () => {
  isFetching.value = true

  try {
    const response = await axios.get('/api/time-entries/get-user-time-entries')
    if (response.data) {
      const newData = {
        timeEntryID: response.data.id,
        amTimeIn: response.data.am_time_in ? new Date(response.data.am_time_in).toLocaleTimeString('en-US', { hour12: false, hour: '2-digit', minute: '2-digit' }) : '',
        amTimeOut: response.data.am_time_out ? new Date(response.data.am_time_out).toLocaleTimeString('en-US', { hour12: false, hour: '2-digit', minute: '2-digit' }) : '',
        pmTimeIn: response.data.pm_time_in ? new Date(response.data.pm_time_in).toLocaleTimeString('en-US', { hour12: false, hour: '2-digit', minute: '2-digit' }) : '',
        pmTimeOut: response.data.pm_time_out ? new Date(response.data.pm_time_out).toLocaleTimeString('en-US', { hour12: false, hour: '2-digit', minute: '2-digit' }) : ''
      }
      timeEntryData.value = newData
      formValues.value = { ...newData }
    }
  } catch (error) {
    alertMessage.value.title = 'Error'
    alertMessage.value.description = error.response?.data?.message || "Failed to fetch time entries"
    alertMessage.value.variant = 'destructive'
    openAlert.value = true
    isOpen.value = false
    console.error('Error fetching time entries:', error)

  } finally {
    isFetching.value = false
  }
}

watch(() => props.shouldRefresh, async () => {
  await getTimeEntries()
})

onMounted(() => {
  getTimeEntries()
  fetchUserData()
})
</script>

<template>
  <Form  v-slot="{ handleSubmit }" 
    @submit="handleSubmit($event, onSubmit)"
    :validation-schema="formSchema"
  >
    <ToastDialog :open="openAlert" :message="alertMessage" @close="(val) => (openAlert = val)" />
    <Dialog v-model:open="isOpen">
      <DialogTrigger as-child>
        <Button variant="outline" class="px-6 py-2 border-2 border-orange-500 bg-white text-orange-500 rounded-lg hover:bg-orange-100 transition-colors text-lg">
            <Icon icon="mdi:clock-edit-outline" width="24" height="24" class="text-orange-500"/>
      </Button>
      </DialogTrigger>
      <DialogContent class="max-h-[90vh] sm:max-w-[525px]">
        <DialogHeader>
          <DialogTitle>Edit Time Entry</DialogTitle>
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