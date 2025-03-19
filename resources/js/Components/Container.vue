<script setup lang="ts">
// Props: Define the data you want to accept
defineProps({
  containerClass: {
    type: String,
    default:
      'container mx-auto flex flex-col gap-4 rounded-lg bg-background p-8 lg:p-12 lg:max-w-full'
  },
  headerText: {
    type: String,
    default: '' // Default value for the header text
  }
})
</script>

<template>
  <div :class="containerClass">
    <!-- Header with prop -->
    <h3 v-if="headerText" class="text-xl font-semibold uppercase">
      {{ headerText }}
    </h3>
    <slot name="header"></slot>
    <!-- Slots for content -->
    <slot></slot>
    <slot name="footer"></slot>
  </div>
</template>

<style scoped>
/* Add scoped styles if needed */
</style>
