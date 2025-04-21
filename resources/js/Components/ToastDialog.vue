<script setup lang="ts">
import { computed } from 'vue'

import {
  AlertDialog,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle
} from '@/Components/ui/alert-dialog'
import { Button } from '@/Components/ui/button'

// Define the AlertMessage interface
interface AlertMessage {
  title: string;
  description: string;
  variant?: 'destructive' | 'success' | 'default';
  errorCode?: string;
}

const emits = defineEmits<{
  (e: 'close', payload: boolean): void
}>()

const props = withDefaults(
  defineProps<{
    open: boolean
    message: AlertMessage
    variant?: 'destructive' | 'success' | 'default'
  }>(),
  {
    open: false,
    variant: 'default'
  }
)

const contentClass = computed(() => {
  switch (props.message.variant) {
    case 'destructive':
      return 'bg-destructive text-background'
    case 'success':
      return 'bg-green-600 text-white'
    default:
      return 'bg-white text-black'
  }
})

const buttonClass = computed(() => {
  switch (props.message.variant) {
    case 'destructive':
      return 'bg-white text-destructive font-bold hover:text-destructive'
    case 'success':
      return 'text-green-600 bg-white font-bold hover:text-green-600'
    default:
      return 'bg-white text-black'
  }
})

// Computed property to split the description into lines by '\n'
const descriptionLines = computed(() => {
  return props.message.description.split('\n')
})

// Add default export
defineOptions({
  name: 'ToastDialog'
})
</script>

<template>
  <AlertDialog :open="props.open">
    <AlertDialogContent :class="['border-none outline-hidden', contentClass]">
      <AlertDialogHeader>
        <AlertDialogTitle class="text-xl dark:text-white">
          {{ props.message.title }}
        </AlertDialogTitle>
        <AlertDialogDescription :class="['text-sm dark:text-white', contentClass]">
          <!-- Render each line separately using v-for -->
          <div v-for="(line, index) in descriptionLines" :key="index" class="mb-1">
            {{ line }}
          </div>

          <!-- Display the error code if provided -->
          <div v-if="props.message.errorCode" class="mt-4 text-sm font-semibold text-gray-800">
            Error Code: {{ props.message.errorCode }}
          </div>
        </AlertDialogDescription>
      </AlertDialogHeader>

      <AlertDialogFooter>
        <Button
          type="button"
          class="dark:bg-foreground dark:text-background"
          variant="ghost"
          @click="() => emits('close', false)"
          :class="buttonClass"
        >
          Close
        </Button>
      </AlertDialogFooter>
    </AlertDialogContent>
  </AlertDialog>
</template>

<style scoped></style>
