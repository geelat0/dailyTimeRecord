<template>
  <div class="greeting-section">
    <div class="greeting-text">{{ greeting }}</div>
    <div class="user-name">{{ user.full_name }} </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

const user = ref({
  first_name: '',
  last_name: ''
})

const greeting = computed(() => {
  const hour = new Date().getHours()
  
  if (hour >= 5 && hour < 12) {
    return 'Good morning'
  } else if (hour >= 12 && hour < 17) {
    return 'Good afternoon'
  } else {
    return 'Good evening'
  }
})

const fetchUserData = async () => {
  try {
    const response = await axios.get('/api/auth/user')
    user.value = response.data
  } catch (error) {
    console.error('Error fetching user data:', error)
  }
}

onMounted(() => {
  fetchUserData()
})
</script>

<style scoped>
.greeting-section {
  text-align: left;
  padding: 0.5rem;
}

.greeting-text {
  font-size: 1.25rem;
  color: #6B7280;
  font-weight: 500;
  margin-bottom: 0.25rem;
}

.user-name {
  font-size: 1.875rem;
  font-weight: 700;
  background: linear-gradient(135deg, #ff6634 0%, #ff8c66 100%);
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
  letter-spacing: -0.02em;
}

@media (min-width: 640px) {
  .greeting-section {
    padding: 1rem;
  }
  
  .greeting-text {
    font-size: 1.5rem;
  }
  
  .user-name {
    font-size: 2.25rem;
  }
}
</style> 