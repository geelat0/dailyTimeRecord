<script setup lang="ts">
import { Icon } from '@iconify/vue'
</script>

<template>
<div class="absolute inset-0  z-10 bg-white/70">
  <div class="flex w-full flex-1 flex-col items-center justify-center">
    <Icon
      icon="mdi:loading"
      width="150"
      class="text-primary animate-spin disabled:cursor-not-allowed"
    /> 
    <p class="text-primary mt-4 text-2xl uppercase">Loading...</p>
  </div>
</div>
</template>