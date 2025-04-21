<script setup lang="ts">
import { Icon } from '@iconify/vue'
import { computed, ref, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'

const router = useRouter()
const route = useRoute()
const activeItem = ref(null)

const navigationItems = ref(
  router.getRoutes()
    .filter(r => r.meta?.id && !r.meta?.hidden) // Ensure the route has an ID and is not hidden
    .reduce((groups, route) => {
      const group = route.meta.group || 'Other'
      if (!groups[group]) groups[group] = []
      groups[group].push({
        id: route.meta.id,
        label: route.meta.label || route.name,
        url: route.path,
        icon: route.meta.icon || 'mdi:menu',
      })
      return groups
    }, {})
)

// Function to update the active item based on the current route
const updateActiveItem = () => {
  const currentItem = Object.values(navigationItems.value)
    .flat()
    .find(item => item.url === route.path)
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
    <div class="ex-container flex min-h-[90vh] flex-col p-3 bg-background dark:bg-dark-background">
      <div class="space-y-3">
        <div class="flex-1">
          <ul class="space-y-4 pt-2 pb-4 text-center text-sm">
            <!-- Render groups -->
            <li v-for="(items, group) in navigationItems" :key="group">
              <h3 class="text-gray-500 dark:text-gray-400 font-semibold text-lg uppercase mb-2">
                {{ group }}
              </h3>
              <ul class="space-y-2">
                <li
                  v-for="item in items"
                  :key="item.id"
                  class="cursor-pointer rounded-sm"
                >
                  <RouterLink
                    :to="item.url"
                    @click="activeItem = item.url"
                    :class="[ 
                      'flex min-w-28 flex-col items-center justify-center space-y-1 rounded-md py-2 text-xs',
                      activeItem === item.url
                        ? 'bg-primary/5 text-primary font-semibold dark:text-primary dark:font-semibold'
                        : 'hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-500 dark:hover:text-gray-500',
                      'rounded-md'
                    ]"
                  >
                    <Icon :icon="item.icon" width="24" height="24" />
                    <span>{{ item.label }}</span>
                  </RouterLink>
                </li>
              </ul>
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