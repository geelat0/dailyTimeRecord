import { createRouter, createWebHistory } from 'vue-router'
import TimeEntry from '@/Pages/DTR/TimeEntry.vue'
import TimeSheet from '@/Pages/DTR/TimeSheet.vue'
import ApprovedAttendance from '@/Pages/DTR/ApprovedAttendance.vue'
import Settings from '@/Pages/DTR/Settings.vue'
import AttachmentHistory from '@/Pages/DTR/AttachmentHistory.vue'
import Login from '@/Pages/Auth/Login.vue'
import RequestStatus from '@/Pages/DTR/RequestStatus.vue'
import { ref } from 'vue'


const routes = [
  {
    path: '/',
    name: 'TimeEntry',
    component: TimeEntry,
    meta: {
      id: 1,
      label: 'Add Time Entry',
      icon: 'mdi:access-time',
      group: 'Time Entry',
      hidden: false      
    }
  },
  {
    path: '/view-time-sheet',
    name: 'TimeSheet',
    component: TimeSheet,
    meta:{
      id: 2,
      label: 'View Time Sheet',
      icon: 'mdi:timetable',
      group: 'Time Entry',
      hidden: false

    }
  },
  {
    path: '/add-approved-attendance-or-absence',
    name: 'ApprovedAttendance',
    component: ApprovedAttendance,
    meta:{
      id: 3,
      label: 'Add Approved Attendance/Absence',
      icon: 'mdi:add',
      group: 'Attachments',
      hidden: false

    }
  },
  {
    path: '/attachment-history',
    name: 'AttachmentHistory',
    component: AttachmentHistory,
    meta:{
      id: 4,
      label: 'Attachment History',
      icon: 'mdi:attach-file',
      group: 'Attachments',
      hidden: false

    }
  },
  {
    path: '/login',
    name: 'Login',
    component: Login,
    meta:{
      id: 5,
      label: 'Login',
      hidden: true
    }
  },
  {
    path: '/settings',
    name: 'Settings',
    component: Settings,
    meta:{
      id: 6,
      label: 'Settings',
      icon: 'mdi:settings',
      group: 'Settings',
      hidden: true
    }
  },

  {
    path: '/request-status',
    name: 'RequestStatus',
    component: RequestStatus,
    meta: {
      id: 6,
      label: 'Request Status',
      group: 'Request Status',
      hidden: true
    }
  },
  {
    path: '/logout',
    name: 'Logout',
    beforeEnter(to, from, next) {
      axios.post('/logout')
        .then(() => {
          localStorage.removeItem('token');
          next({ name: 'Login' });
        })
        .catch(error => {
          console.error('Logout error:', error);
          next(false);
        });
    },
    meta:{
      id: 7,
      icon: 'mdi:logout',
      label: 'Logout',
      hidden: false
    }
  },
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
  scrollBehavior() {
      return { top: 0, behavior: 'smooth' }
  }
});

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token');
  if (to.name !== 'Login' && !token) {
    next({ name: 'Login' });
  } else {
    next();
  }
});

export default router

