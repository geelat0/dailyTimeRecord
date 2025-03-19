  
<script setup>
import { onMounted, ref, computed } from 'vue'
import Container from '@/Components/Container.vue'; 
import EditTimeEntry from '@/Composable/EditTimeEntry.vue'
import PageLoader from '@/Components/PageLoader.vue';

const currentTime = ref('')
const currentDate = ref('')
const isDialogOpen = ref(false)
const isLoading = ref(false)
const timeEntryId = ref(null)
const refreshEditModal = ref(false)


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

            document.getElementById('tn-time-in-pm').disabled = true;
            document.getElementById('tn-time-in-pm').classList.add('hidden')
            document.getElementById('btn-time-out-pm').classList.remove('hidden')
        }

        document.getElementById('pm_time_out').textContent = modifyTime(response.data.pm_time_out)

        if (response.data.pm_time_out) {
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
        <Container class="flex flex-col gap-4 " header-text="Add Time Entry">
            <template #header>
                <div class="text-center mb-8">
                    <h1 class="text-8xl font-bold text-orange-500" id="clock">{{ currentTime }}</h1>
                    <p class="text-xl text-black-600 mt-2" id="date">{{ currentDate }}</p>
                </div>
            </template>

            <!-- Time Buttons -->
            <div class="flex gap-4 mb-12 justify-center relative">
              <div v-if="isLoading" >
                  <PageLoader />
                </div>
                <button @click="handleTimeIn" 
                        :disabled="isLoading"
                        class="px-16 py-6 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors text-xl" id="btn-time-in">
                         AM Time In
                </button>
                <button @click="handleTimeOut" 
                        :disabled="isLoading"
                        class="px-16 py-6 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors text-xl hidden" id="btn-time-out">
                        AM Time Out
                </button>
                <button @click="handleTimeInPM" 
                        :disabled="isLoading"

                        class="px-16 py-6 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition-colors text-xl hidden" id="tn-time-in-pm">
                        PM Time In
                </button>
                <button @click="handleTimeOutPM" 
                        :disabled="isLoading"
                        class="px-16 py-6 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors text-xl hidden" id="btn-time-out-pm">
                        PM Time Out
                </button>
                

            </div>

          
            <div class="w-full max-w-8xl p-4 sm:p-10 flex justify-between items-center">
                <h2 class="text-gray-700 text-lg sm:text-xl mb-4">Today's Record </h2>
                <EditTimeEntry @time-entry-updated="getTimeEntries"
                :should-refresh="refreshEditModal" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                <!-- AM Time In -->
                <div class="bg-white rounded-lg p-4 sm:p-8 shadow-md">
                    <div class="text-2xl sm:text-3xl font-semibold text-gray-800 mb-2" id="am_time_in">00:00 --</div>
                    <div class="text-gray-600">AM Time-In</div>
                </div>

                <!-- AM Time Out -->
                <div class="bg-white rounded-lg p-4 sm:p-8 shadow-md">
                    <div class="text-2xl sm:text-3xl font-semibold text-gray-800 mb-2" id="am_time_out">00:00 --</div>
                    <div class="text-gray-600">AM Time-Out</div>
                </div>

                <!-- PM Time In -->
                <div class="bg-white rounded-lg p-4 sm:p-8 shadow-md">
                    <div class="text-2xl sm:text-3xl font-semibold text-gray-800 mb-2" id="pm_time_in">00:00 --</div>
                    <div class="text-gray-600">PM Time-In</div>
                </div>

                <!-- PM Time Out -->
                <div class="bg-white rounded-lg p-4 sm:p-8 shadow-md">
                    <div class="text-2xl sm:text-3xl font-semibold text-gray-800 mb-2" id="pm_time_out">00:00 --</div>
                    <div class="text-gray-600">PM Time-Out</div>
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
