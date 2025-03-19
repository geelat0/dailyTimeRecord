<script setup lang="ts">
import { onMounted , onUnmounted, watch} from 'vue'
import { Icon } from '@iconify/vue';
import { cn } from '@/lib/utils';
import { Button } from '@/Components/ui/button'

import { Calendar } from '@/Components/ui/calendar'
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover'
import {
  DateFormatter,
  type DateValue,
  getLocalTimeZone,
} from '@internationalized/date'
import { CalendarIcon } from 'lucide-vue-next'
import { ref } from 'vue'

const df = new DateFormatter('en-PH', {
//   dateStyle: 'long',
})

const startDate = ref<DateValue>()
const endDate = ref<DateValue>()

const emit = defineEmits(['dateChange'])

const handleFilter = () => {
  if (startDate.value && endDate.value) {
    emit('dateChange', {
      startDate: startDate.value.toString(),
      endDate: endDate.value.toString()
    })
  }
}

const value = ref<DateValue>()
</script>
<template>
    <div class="flex space-x-4">
        <div>
            <label for="start-date" class="block text-sm font-medium text-gray-700">Start Date</label>
            <Popover>
                <PopoverTrigger as-child>
                    <Button
                        id="start-date"
                        variant="outline"
                        :class="cn(
                            'w-[280px] justify-start text-left font-normal border-black',
                            !startDate && 'text-muted-foreground',
                        )"
                    >
                        <CalendarIcon class="mr-2 h-4 w-4" />
                        {{ startDate ? df.format(startDate.toDate(getLocalTimeZone())) : "Pick a start date" }}
                    </Button>
                </PopoverTrigger>
                <PopoverContent class="w-auto p-0">
                    <Calendar v-model="startDate" initial-focus />
                </PopoverContent>
            </Popover>
        </div>
        <div>
            <label for="end-date" class="block text-sm font-medium text-gray-700">End Date</label>
            <Popover>
                <PopoverTrigger as-child>
                    <Button
                        id="end-date"
                        variant="outline"
                        :class="cn(
                            'w-[280px] justify-start text-left font-normal border-black',
                            !endDate && 'text-muted-foreground',
                        )"
                    >
                        <CalendarIcon class="mr-2 h-4 w-4" />
                        {{ endDate ? df.format(endDate.toDate(getLocalTimeZone())) : "Pick an end date" }}
                    </Button>
                </PopoverTrigger>
                <PopoverContent class="w-auto p-0">
                    <Calendar v-model="endDate" initial-focus />
                </PopoverContent>
            </Popover>
        </div>
        <div class="flex items-end">
            <Button 
                @click="handleFilter"
                :disabled="!startDate || !endDate"
                class="bg-primary text-white hover:bg-primary/90">
                <Icon icon="mdi:filter-multiple-outline" width="24" height="24" />
                Filter
            </Button>
        </div>
    </div>
</template>