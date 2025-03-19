import { createRouter, createWebHistory } from 'vue-router'
import TimeEntry from '@/Pages/DTR/TimeEntry.vue'
import TimeSheet from '@/Pages/DTR/TimeSheet.vue'
import ApprovedAttendance from '@/Pages/DTR/ApprovedAttendance.vue'
import Settings from '@/Pages/DTR/Settings.vue'
import SideNavigation from '@/Components/Sidenavigation.vue'
import AttachmentHistory from '@/Pages/DTR/AttachmentHistory.vue'

const routes = [
  {
    path: '/',
    name: 'TimeEntry',
    component: TimeEntry,
    meta: {
      id: 1,
      label: 'Add Time Entry',
      icon: 'mdi:access-time'
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
    }
  },
  {
    path: '/setting',
    name: 'Setting',
    component: Settings,
    meta:{
      id: 5,
      label: 'Settings',
      icon: 'mdi:settings-outline',
    }
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router