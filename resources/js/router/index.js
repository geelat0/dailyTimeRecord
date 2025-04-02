import { createRouter, createWebHistory } from 'vue-router'
import TimeEntry from '@/Pages/DTR/TimeEntry.vue'
import TimeSheet from '@/Pages/DTR/TimeSheet.vue'
import ApprovedAttendance from '@/Pages/DTR/ApprovedAttendance.vue'
import Settings from '@/Pages/DTR/Settings.vue'
import AttachmentHistory from '@/Pages/DTR/AttachmentHistory.vue'
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
      group: 'Time Entry'

      
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
      group: 'Time Entry'

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
      group: 'Attachments'

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
      group: 'Attachments'

    }
  },
  // {
  //   path: '/setting',
  //   name: 'Setting',
  //   component: Settings,
  //   meta:{
  //     id: 5,
  //     label: 'Settings',
  //     icon: 'mdi:settings-outline',
  //   }
  // },
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
  scrollBehavior() {
    return { top: 0, behavior: 'smooth' }
  }
});

export default router

