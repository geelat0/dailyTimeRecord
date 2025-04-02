<script setup lang="ts">
import { Icon } from '@iconify/vue'
import { computed, ref, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useMediaQuery } from '@vueuse/core'


const router = useRouter()
const route = useRoute()
const activeItem = ref(null)
const isMobile = useMediaQuery('(max-width: 768px)')
const isTablet = useMediaQuery('(min-width: 769px) and (max-width: 1024px)')


const navigationItems = ref(router.getRoutes().map(r => ({
  id: r.meta?.id || null, // Ensure id exists
  label: r.meta?.label || r.name,
  url: r.path,
  icon: r.meta?.icon || 'mdi:menu' // Default icon
}))) // Remove items without IDs

// Function to update the active item based on the current route
const updateActiveItem = () => {
  const currentItem = navigationItems.value.find(item => item.url === route.path)
  if (currentItem) {
    activeItem.value = currentItem.url
  }
}

// Watch for route changes and update active item
watch(() => route.path, updateActiveItem, { immediate: true })

onMounted(updateActiveItem)


</script>

<template>

    <div 
      class="ex-container flex w-full flex-row justify-around bg-background p-3 dark:bg-dark-background"
      :class="{ 'overflow-x-auto': isMobile }"
    >
    <ul 
      class="flex w-full justify-around text-center text-sm"
      :class="{ 'gap-8': isMobile}"
    >
      <li
        v-for="item in navigationItems"
        :key="item.id"
        class="cursor-pointer"
      >
        <RouterLink
                :to="item.url"
                @click="activeItem = item.url"
                :class="[ 
                  'flex flex-col items-center justify-center space-y-1 text-xs',
                  activeItem === item.url
                    ? 'bg-primary/5 text-primary font-semibold dark:text-primary dark:font-semibold'
                    : 'hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-500 dark:hover:text-gray-500',
                    'dark:text-gray-300'
                ]"
                >
        <Icon :icon="item.icon" width="24" height="24" />
        <span>{{ item.label }}</span>
        </RouterLink>
      </li>
    </ul>
    </div>
  </template>

<style scoped>
  .ex-container {
      border-radius: 0.5rem;
  }
</style>