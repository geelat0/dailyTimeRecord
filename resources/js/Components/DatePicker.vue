<script setup>
import { onMounted, ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import Container from '@/Components/Container.vue'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Calendar } from '@/Components/ui/calendar'
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover'
import { CalendarIcon } from 'lucide-vue-next'
import {
  DateFormatter,
  getLocalTimeZone,
} from '@internationalized/date'

import { cn } from '@/lib/utils'
import { defineProps, defineEmits } from 'vue'

const props = defineProps({
  dateRanges: Array,
  errors: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['update:dateRanges'])

const df = new DateFormatter('en-PH', {})

const addDateRange = () => {
  const newDateRanges = [...props.dateRanges, { id: props.dateRanges.length + 1, startDate: '', endDate: '' }]
  emit('update:dateRanges', newDateRanges)
}

const removeDateRange = (id) => {
  const newDateRanges = props.dateRanges.filter(range => range.id !== id)
  emit('update:dateRanges', newDateRanges)
}

const updateStartDate = (id, date) => {
  const newDateRanges = props.dateRanges.map(range => range.id === id ? { ...range, startDate: date } : range)
  emit('update:dateRanges', newDateRanges)
}

const updateEndDate = (id, date) => {
  const newDateRanges = props.dateRanges.map(range => range.id === id ? { ...range, endDate: date } : range)
  emit('update:dateRanges', newDateRanges)
}

</script>

<template>
    <div class="flex flex-col gap-8">
        <Container class="flex flex-col gap-4" header-text="Add Approved Attendance or Absence">
            <template #header>
                    <div class="p-2">
                        <div class="mb-3">
                            <div v-for="(range, index) in dateRanges" 
                                 :key="range.id"
                                 class="bg-accent rounded p-4 mb-4 shadow-sm">
                                <div class="flex justify-between items-center">
                                    <span class="bg-orange-500 flex h-8 w-8 items-center justify-center rounded-full font-bold text-white">
                                        {{ index + 1 }}
                                    </span>
                                    <Button
                                        v-if="range.id !== 1"
                                        variant="ghost"
                                        size="icon"
                                        @click="removeDateRange(range.id)"
                                        class="text-red-500 hover:text-red-700"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                        </svg>
                                    </Button>
                                </div>
                                
                                <div class="flex justify-between items-center gap-4">
                                    <div class="mb-4 w-full">
                                        <Label for="startDate" class="required-field text-sm">Start Date</Label>
                                        <Popover>
                                            <PopoverTrigger as-child>
                                                <Button
                                                    :id="`startDate${range.id}`"
                                                    variant="outline"
                                                    :class="cn(
                                                        'inline-flex items-center whitespace-nowrap rounded-md text-sm ring-offset-background transition-colors focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none border border-gray-500 dark:border-white bg-background hover:text-accent-foreground px-4 py-2 h-[52px] w-full justify-start disabled:cursor-not-allowed disabled:opacity-60 cursor-pointer cursor-pointer',
                                                        !range.startDate && 'text-muted-foreground',
                                                        props.errors[`start_date.${index}`] && 'border-red-500'
                                                    )"
                                                >
                                                    <CalendarIcon class="mr-2 h-4 w-4" />
                                                    {{ range.startDate ? df.format(range.startDate.toDate(getLocalTimeZone())) : "Pick a start date" }}
                                                </Button>
                                            </PopoverTrigger>
                                            <PopoverContent class="w-auto p-0">
                                                <Calendar v-model="range.startDate" @update:modelValue="updateStartDate(range.id, $event)" initial-focus />
                                            </PopoverContent>
                                        </Popover>
                                        <p v-if="props.errors[`start_date.${index}`]" class="text-sm text-red-500 mt-1">{{ props.errors[`start_date.${index}`][0] }}</p>
                                    </div>
                                    <div class="mb-4 w-full">
                                        <Label for="endDate" class="required-field text-sm">End Date</Label>
                                        <Popover>
                                            <PopoverTrigger as-child>
                                                <Button
                                                    :id="`endDate${range.id}`"
                                                    variant="outline"
                                                    :class="cn(
                                                        'inline-flex items-center whitespace-nowrap rounded-md text-sm ring-offset-background transition-colors focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none border border-gray-500 dark:border-white bg-background hover:text-accent-foreground px-4 py-2 h-[52px] w-full justify-start disabled:cursor-not-allowed disabled:opacity-60 cursor-pointer cursor-pointer',
                                                        !range.endDate && 'text-muted-foreground',
                                                        props.errors[`end_date.${index}`] && 'border-red-500'
                                                    )"
                                                >
                                                    <CalendarIcon class="mr-2 h-4 w-4" />
                                                    {{ range.endDate ? df.format(range.endDate.toDate(getLocalTimeZone())) : "Pick an end date" }}
                                                </Button>
                                            </PopoverTrigger>
                                            <PopoverContent class="w-auto p-0">
                                                <Calendar v-model="range.endDate" @update:modelValue="updateEndDate(range.id, $event)" initial-focus />
                                            </PopoverContent>
                                        </Popover>
                                        <p v-if="props.errors[`end_date.${index}`]" class="text-sm text-red-500 mt-1">{{ props.errors[`end_date.${index}`][0] }}</p>
                                    </div>
                                </div>
                            </div>

                            <Button variant="outline" 
                                    type="button"
                                    class="text-orange-500 border-orange-500"
                                    @click="addDateRange">
                                <div class="flex items-center gap-2">
                                    <div class="font-bold">Add Another Date</div>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                </div>
                            </Button>
                        </div>
                    </div>
            </template>
        </Container>
    </div>
</template>

<style scoped>
.ex-container {
  border-radius: 0.5rem;
}
</style>
