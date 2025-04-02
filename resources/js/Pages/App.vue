<script setup>
import { ref, watch, onMounted } from 'vue'
import { useMediaQuery } from '@vueuse/core'

import WordLogo from '@/Components/WordLogo.vue';
import Card from '@/Components/ui/card/Card.vue';
import Footer from '@/Components/Footer.vue';
import FadeTransition from '@/Components/FadeTransition.vue';
import Loader from '@/Components/Loader.vue';
import AvatarPopover from '@/Components/AvatarPopover.vue';
import Sidenavigation from '@/Components/Sidenavigation.vue';
import { RouterView } from 'vue-router'

import NavMenu from '@/Components/NavMenu.vue';

const loadingTime = ref(true)
const isMobile = useMediaQuery('(max-width: 768px)')
const isTablet = useMediaQuery('(min-width: 769px) and (max-width: 1024px)')
onMounted(() => {
  setTimeout(() => {
    loadingTime.value = false
  }, 3000)
})

</script>

<template>
  <FadeTransition>
    <!-- <Loader  v-if="loadingTime" /> -->
    <div
      class="bg-support relative flex min-h-screen flex-col items-center gap-4 overflow-hidden p-4 dark:text-white"
    >
      <!-- <ToastDialog /> -->
      <Card
        class="text-card-foreground rounded-lg border bg-background flex w-full items-center justify-between border-none px-6 py-4 shadow-none md:mb-0"
      >
        <WordLogo />
        <div class="flex items-center gap-4">
          <AvatarPopover />
        </div>
      </Card>
      <div v-if="isMobile || isTablet" class="w-full">
        <NavMenu/>
      </div>
      <div class="flex gap-4 w-full flex-1 ">
        <div v-if="!isMobile && !isTablet" >
          <Sidenavigation />
        </div>
        <div class="w-full">
          <RouterView/>
        </div>
      </div>
      <Footer />
    </div>
  </FadeTransition>
</template>
