<template>
    <div class="login-container">
      <div class="container">
        <!-- Logo -->
        <div class="logo">
          <img src="/bear-logo-dswd.png" style="width:60px; margin-right: 8px;">
          <img src="/DSWD_FULL_TEXT.png" style="width:100px;">
        </div>
        
        <h3>EMPOWEREX Login</h3>
        
        <form @submit.prevent="login">
          <div class="form-group">
            <label for="email">Email</label>
            <input
              id="email"
              v-model="form.email"
              name="email"
              type="email"
              required
              autofocus
            />
          </div>

          <div class="form-group">
            <label for="password">Password</label>
            <input
              id="password"
              v-model="form.password"
              name="password"
              type="password"
              required
            />
          </div>

          <button type="submit">Login</button>
        </form>
      </div>
    </div>
</template>

<script>
import axios from 'axios';

// Add axios interceptor
axios.interceptors.request.use(
  config => {
    const token = localStorage.getItem('token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  error => {
    return Promise.reject(error);
  }
);

// Add response interceptor
axios.interceptors.response.use(
  response => response,
  error => {
    if (error.response && error.response.status === 401) {
      localStorage.removeItem('token');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export default {
  data() {
    return {
      form: {
        email: '',
        password: '',
      },
    };
  },
  methods: {
    async login() {
      try {
        const response = await axios.post('/api/login', this.form);
        const token = response.data.access_token;
        localStorage.setItem('token', token);
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        window.location.replace('/');
      } catch (error) {
        console.error('Login failed:', error);
        alert('Login failed. Please check your credentials.');
      }
    },
  },
};
</script>

<style scoped>
.login-container {
  background-color: #f7fafc;
  height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  font-family: Arial, sans-serif;
  margin: 0;
}

.container {
    width: 100%;
    max-width: 400px;
    background: #fff;
    padding: 20px;
    border-radius: 16px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    box-sizing: border-box; /* Prevent content overflow */
}

.logo {
  text-align: center;
  margin-bottom: 20px;
}

.logo img {
  height: auto;
  display: inline-block;
}

h3 {
  font-size: 1.2rem;
  font-weight: bold;
  color: #4a5568;
  text-align: center;
  margin-bottom: 20px;
}

.form-group {
  margin-bottom: 16px;
}

label {
  display: block;
  font-size: 0.875rem;
  color: #666;
  margin-bottom: 6px;
}

input {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  font-size: 0.875rem;
  color: #333;
  background: #fff;
  transition: all 0.2s ease;
}

input:focus {
  outline: none;
  border-color: #ff6634;
  box-shadow: 0 0 0 3px rgba(255, 102, 52, 0.1);
}

button {
  width: 100%;
  padding: 10px;
  background: #ff6634;
  color: #fff;
  border: none;
  border-radius: 6px;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  margin-top: 8px;
  transition: background-color 0.2s ease;
}

button:hover {
  background: #ff5520;
}

button:active {
  transform: translateY(1px);
}
</style>