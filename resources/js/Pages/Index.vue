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

const loadingTime = ref(true)

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
      class="bg-support relative flex min-h-screen flex-col items-center gap-4 overflow-hidden p-4"
    >
    <!-- <ToastDialog :open="showAlert" :message="alertMessage" @close="(val) => (showAlert = val)" /> -->
    <Card
        class="text-card-foreground rounded-lg border bg-background flex w-full items-center justify-between border-none px-6 py-4 shadow-none md:mb-0"
      >
        <WordLogo />
        <div class="flex items-center gap-4">
          <AvatarPopover />
        </div>
      </Card>

      <Sidenavigation />
      <!-- <RouterView class="w-full flex-1" /> -->
      <Footer />
    </div>
  </FadeTransition>
</template>
