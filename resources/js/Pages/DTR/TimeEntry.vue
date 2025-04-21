<script setup>
import { onMounted, ref, computed } from 'vue'
import Container from '@/Components/Container.vue'; 
import EditTimeEntry from '@/Composable/EditTimeEntry.vue'
import PageLoader from '@/Components/PageLoader.vue';
import GreetingCard from '@/Components/GreetingCard.vue';
import { useMediaQuery } from '@vueuse/core'

const currentTime = ref('')
const currentDate = ref('')
const isDialogOpen = ref(false)
const isLoading = ref(false)
const timeEntryId = ref(null)
const refreshEditModal = ref(false)
const isMobile = useMediaQuery('(max-width: 768px)')


const updateTime = () => {
  const now = new Date()
  currentTime.value = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
  currentDate.value = now.toLocaleDateString([], { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })
}


// Add these new functions
const handleTimeIn = async () => {
  try {
    isLoading.value = true
    const response = await axios.post('/api/time-entries/time-in')
    // Hide time-in button and show time-out button
    document.getElementById('btn-time-in').classList.add('hidden')
    document.getElementById('btn-time-out').classList.remove('hidden')
    document.getElementById('am_time_in').textContent = currentTime.value
    await getTimeEntries()
    refreshEditModal.value = !refreshEditModal.value // Toggle to trigger refresh
  } catch (error) {
    console.error('Error:', error)
  } finally {
    isLoading.value = false
  }
}

const handleTimeOut = async () => {
  try {
    isLoading.value = true
    const response = await axios.post('/api/time-entries/time-out', {
      id: timeEntryId.value
    })
    // Hide time-out button and show PM time-in button
    document.getElementById('btn-time-out').classList.add('hidden')
    document.getElementById('tn-time-in-pm').classList.remove('hidden')
    document.getElementById('am_time_out').textContent = currentTime.value
    await getTimeEntries()
    refreshEditModal.value = !refreshEditModal.value // Toggle to trigger refresh
  } catch (error) {
    console.error('Error:', error)
  } finally {
    isLoading.value = false
  }
}

const handleTimeInPM = async () => {
  try {
    isLoading.value = true
    const response = await axios.post('/api/time-entries/time-in-pm', {
        id: timeEntryId.value

    })
    // Hide PM time-in button and show PM time-out button
    document.getElementById('tn-time-in-pm').classList.add('hidden')
    document.getElementById('btn-time-out-pm').classList.remove('hidden')
    document.getElementById('pm_time_in').textContent = currentTime.value
    await getTimeEntries()
    refreshEditModal.value = !refreshEditModal.value // Toggle to trigger refresh
  } catch (error) {
    console.error('Error:', error)
  } finally {
    isLoading.value = false
  }
}

const handleTimeOutPM = async () => {
  try {
    isLoading.value = true
    const response = await axios.post('/api/time-entries/time-out-pm', {
        id: timeEntryId.value

    })
    document.getElementById('pm_time_out').textContent = currentTime.value
    await getTimeEntries()
    refreshEditModal.value = !refreshEditModal.value // Toggle to trigger refresh
  } catch (error) {
    console.error('Error:', error)
  } finally {
    isLoading.value = false
  }
}

const modifyTime = (timeString) => {
  if (!timeString) return '00:00 --'
  // Extract time portion from datetime string if full datetime is provided
  const timePart = timeString.includes(' ') ? timeString.split(' ')[1] : timeString
  const date = new Date(`2000-01-01 ${timePart}`)
  return date.toLocaleTimeString([], { 
    hour: '2-digit', 
    minute: '2-digit',
    hour12: true 
  }).replace(/\s/, '') // Remove space between time and AM/PM
}

const getTimeEntries = async () => {
    try {
      isLoading.value = true;

        const response = await axios.get('/api/time-entries/get-user-time-entries')

        // Convert each time entry using modifyTime
        timeEntryId.value = response.data.id
        document.getElementById('am_time_in').textContent = modifyTime(response.data.am_time_in)

        if (response.data.am_time_in) {

            document.getElementById('btn-time-in').disabled = true;
            document.getElementById('btn-time-in').classList.add('hidden')
            document.getElementById('btn-time-out').classList.remove('hidden')
        }

        document.getElementById('am_time_out').textContent = modifyTime(response.data.am_time_out)

        if (response.data.am_time_out) {

            document.getElementById('btn-time-out').disabled = true;
            document.getElementById('btn-time-out').classList.add('hidden')
            document.getElementById('tn-time-in-pm').classList.remove('hidden')
        }


        document.getElementById('pm_time_in').textContent = modifyTime(response.data.pm_time_in)

        if (response.data.pm_time_in) {

            document.getElementById('btn-time-out').classList.add('hidden')
            document.getElementById('btn-time-in').classList.add('hidden')

            document.getElementById('tn-time-in-pm').disabled = true;
            document.getElementById('tn-time-in-pm').classList.add('hidden')
            document.getElementById('btn-time-out-pm').classList.remove('hidden')
        }

        document.getElementById('pm_time_out').textContent = modifyTime(response.data.pm_time_out)

        if (response.data.pm_time_out) {
            document.getElementById('btn-time-out').classList.add('hidden')
            document.getElementById('btn-time-in').classList.add('hidden')
            document.getElementById('tn-time-in-pm').classList.add('hidden')
            document.getElementById('btn-time-out-pm').classList.add('hidden')
        }


    } catch (error) {
        console.error('Error:', error)
    }finally{
      isLoading.value = false

    }
}

onMounted(() => {
  updateTime()
  setInterval(updateTime, 1000)
  getTimeEntries()
})

</script>

<template>
    <div class="flex flex-col gap-8">
        <Container class="flex flex-col gap-4 ">
            <template #header>
              <div class="w-full sm:w-auto">
                        <GreetingCard />
                    </div>
                <div class="flex flex-col items-center mb-8">
                    <div class="text-center">
                        <h1 :class="isMobile ? 'text-6xl' : 'text-8xl'" class="font-bold bg-gradient-to-r from-orange-500 to-orange-600 text-transparent bg-clip-text" id="clock">{{ currentTime }}</h1>
                        <p class="text-xl text-gray-600 mt-2 font-medium" id="date">{{ currentDate }}</p>
                    </div>
                </div>
            </template>

            <!-- Time Buttons -->
            <div class="flex flex-wrap gap-4 justify-center relative mb-8">
              <div v-if="isLoading" class="absolute inset-0 flex items-center justify-center bg-white/50 backdrop-blur-sm">
                  <PageLoader />
              </div>
              <button @click="handleTimeIn" 
                      :disabled="isLoading"
                      class="min-w-[200px] px-8 py-4 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-xl hover:from-orange-600 hover:to-orange-700 transition-all shadow-lg hover:shadow-orange-500/30 disabled:opacity-50 text-lg font-semibold" 
                      id="btn-time-in">
                      <i class="fas fa-sign-in-alt mr-2"></i> AM Time In
              </button>
              <button @click="handleTimeOut" 
                      :disabled="isLoading"
                      class="min-w-[200px] px-8 py-4 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-xl hover:from-red-600 hover:to-red-700 transition-all shadow-lg hover:shadow-red-500/30 disabled:opacity-50 text-lg font-semibold hidden" 
                      id="btn-time-out">
                      <i class="fas fa-sign-out-alt mr-2"></i> AM Time Out
              </button>
              <button @click="handleTimeInPM" 
                      :disabled="isLoading"
                      class="min-w-[200px] px-8 py-4 bg-gradient-to-r from-yellow-500 to-yellow-600 text-white rounded-xl hover:from-yellow-600 hover:to-yellow-700 transition-all shadow-lg hover:shadow-yellow-500/30 disabled:opacity-50 text-lg font-semibold hidden" 
                      id="tn-time-in-pm">
                      <i class="fas fa-sun mr-2"></i> PM Time In
              </button>
              <button @click="handleTimeOutPM" 
                      :disabled="isLoading"
                      class="min-w-[200px] px-8 py-4 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-xl hover:from-orange-600 hover:to-orange-700 transition-all shadow-lg hover:shadow-orange-500/30 disabled:opacity-50 text-lg font-semibold hidden" 
                      id="btn-time-out-pm">
                      <i class="fas fa-moon mr-2"></i> PM Time Out
              </button>
            </div>

            <div class="w-full max-w-8xl px-4 sm:px-10 flex justify-between items-center mb-6">
                <h2 class="text-gray-700 text-xl sm:text-2xl font-bold dark:text-white">Today's Record</h2>
                <EditTimeEntry @time-entry-updated="getTimeEntries"
                :should-refresh="refreshEditModal" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 sm:gap-8 p-4 sm:p-6 dark:bg-dark-background">
                <!-- AM Time In -->
                <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-lg hover:shadow-xl transition-shadow border border-gray-100">
                    <div class="flex items-center mb-4">
                        <div class="p-3 bg-orange-100 rounded-lg mr-4">
                            <i class="fas fa-sun text-orange-500 text-xl"></i>
                        </div>
                        <div>
                            <div class="text-3xl sm:text-4xl font-bold text-gray-800 mb-1 dark:text-white tracking-wide" id="am_time_in">00:00 --</div>
                            <div class="text-gray-500 dark:text-gray-400 font-medium">AM Time-In</div>
                        </div>
                    </div>
                </div>

                <!-- AM Time Out -->
                <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-lg hover:shadow-xl transition-shadow border border-gray-100">
                    <div class="flex items-center mb-4">
                        <div class="p-3 bg-red-100 rounded-lg mr-4">
                            <i class="fas fa-sign-out-alt text-red-500 text-xl"></i>
                        </div>
                        <div>
                            <div class="text-3xl sm:text-4xl font-bold text-gray-800 mb-1 dark:text-white tracking-wide" id="am_time_out">00:00 --</div>
                            <div class="text-gray-500 dark:text-gray-400 font-medium">AM Time-Out</div>
                        </div>
                    </div>
                </div>

                <!-- PM Time In -->
                <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-lg hover:shadow-xl transition-shadow border border-gray-100">
                    <div class="flex items-center mb-4">
                        <div class="p-3 bg-yellow-100 rounded-lg mr-4">
                            <i class="fas fa-sun text-yellow-500 text-xl"></i>
                        </div>
                        <div>
                            <div class="text-3xl sm:text-4xl font-bold text-gray-800 mb-1 dark:text-white tracking-wide" id="pm_time_in">00:00 --</div>
                            <div class="text-gray-500 dark:text-gray-400 font-medium">PM Time-In</div>
                        </div>
                    </div>
                </div>

                <!-- PM Time Out -->
                <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-lg hover:shadow-xl transition-shadow border border-gray-100">
                    <div class="flex items-center mb-4">
                        <div class="p-3 bg-orange-100 rounded-lg mr-4">
                            <i class="fas fa-moon text-orange-500 text-xl"></i>
                        </div>
                        <div>
                            <div class="text-3xl sm:text-4xl font-bold text-gray-800 mb-1 dark:text-white tracking-wide" id="pm_time_out">00:00 --</div>
                            <div class="text-gray-500 dark:text-gray-400 font-medium">PM Time-Out</div>
                        </div>
                    </div>
                </div>
            </div>  
        </Container>
    </div>
</template>

<style scoped>
    .ex-container {
        border-radius: 0.5rem;
    }
</style>
