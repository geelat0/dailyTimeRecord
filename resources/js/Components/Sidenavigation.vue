<script setup lang="ts">
import { Icon } from '@iconify/vue'
import { computed, ref, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'

const router = useRouter()
const route = useRoute()
const activeItem = ref(null)

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
  <!-- <div class="flex gap-4 w-full flex-1"> -->
    <div class="ex-container flex min-h-[90vh] flex-col bg-white p-3">
      <div class="space-y-3">
        <div class="flex-1">
          <ul class="space-y-2 pt-2 pb-4 text-center text-sm">
            <li
              v-for="item in navigationItems"
              :key="item.id"
              class="cursor-pointer rounded-sm"
            >
              <RouterLink
                :to="item.url"
                @click="activeItem = item.url"
                :class="[ 
                  'flex min-w-28 flex-col items-center justify-center space-y-1 rounded-md py-2 text-xs',
                  activeItem === item.url
                    ? 'bg-primary/5 text-primary font-semibold'
                    : 'hover:bg-gray-100'
                ]"
              >
                <Icon :icon="item.icon" width="24" height="24" />
                <span>{{ item.label }}</span>
              </RouterLink>
            </li>
          </ul>
        </div>
      </div>
    </div>
    
  <!-- </div> -->
</template>

<style scoped>
  .ex-container {
      border-radius: 0.5rem;
  }
</style>