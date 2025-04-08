<script setup>
import { onMounted, ref } from 'vue'
import Container from '@/Components/Container.vue'
import DateFilter from '@/Composable/DateFilter.vue'
import EditTableModal from '@/Composable/EditTableModal.vue'
import PageLoader from '@/Components/PageLoader.vue';
import ViewFiles from '@/Composable/ViewFiles.vue';

import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/Components/ui/table'

import {
  Pagination,
  PaginationList,
  PaginationListItem,
  PaginationNext,
  PaginationPrev,
} from '@/Components/ui/pagination'
import { useMediaQuery } from '@vueuse/core'


const timeEntries = ref([]);
const selectedEntry = ref(null);
const currentPage = ref(1);
const itemsPerPage = ref(31);
const totalItems = ref(0);
const isLoading = ref(false)
const isMobile = useMediaQuery('(max-width: 768px)')

const fetchTimeEntries = async (dates = {}) => {
  isLoading.value = true; // Set loading to true before fetching
  await new Promise(resolve => setTimeout(resolve, 1000)); // Load for 3 seconds
    try {
      const filterDates = Object.keys(dates).length ? dates : {
        startDate: new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0],
        endDate: new Date(new Date().getFullYear(), new Date().getMonth() + 1, 0).toISOString().split('T')[0]
      };
      const response = await axios.get(`/api/time-entries/list/${filterDates.startDate}/${filterDates.endDate}`);

        timeEntries.value = response.data.data;
        console.log(response.data.data);
        totalItems.value = response.data.data.length; 
    } catch (error) {
        console.error('Error fetching time entries:', error);
    }finally{
      isLoading.value = false; // Set loading to true before fetching
    }
};


const handleEditClick = (entry) => {
  // Only update if it's a different entry
  if (!selectedEntry.value || selectedEntry.value.id !== entry.id) {
    selectedEntry.value = {
      id: entry.id,
      amTimeIn: entry.am_time_in ? formatTime(entry.am_time_in) : '',
      amTimeOut: entry.am_time_out ? formatTime(entry.am_time_out) : '',
      pmTimeIn: entry.pm_time_in ? formatTime(entry.pm_time_in) : '',
      pmTimeOut: entry.pm_time_out ? formatTime(entry.pm_time_out) : '',
      date: entry.date
    }
  }
}

// Add this helper function
const formatTime = (timeString) => {
  // Convert "8:00 AM" format to "08:00" 24-hour format
  const date = new Date(`2000-01-01 ${timeString}`);
  return date.toLocaleTimeString('en-US', { hour12: false, hour: '2-digit', minute: '2-digit' });
}

const currentDateFilter = ref({ startDate: null, endDate: null });


const handlePageChange = (page) => {
    currentPage.value = page;
    fetchTimeEntries(currentDateFilter.value); // Pass the current date filters
}

const handleDateChange = (dates) => {
  currentDateFilter.value = dates; // Store the current date filters
    currentPage.value = 1; // Reset to first page when changing dates
    fetchTimeEntries(dates);
}

onMounted(() => {
  fetchTimeEntries()
})

</script>

<template>
  <div class="flex flex-col gap-8">
    <Container class="flex flex-col gap-4" header-text="View Time Sheet">
      <template #header>
        <div :class="['flex flex-col gap-4', { 'items-end': !isMobile, 'items-center': isMobile }]">
          <DateFilter @date-change="handleDateChange"/>
        </div>
        <div class="flex flex-col gap-2">
          <div class="table-container relative">
            <div v-if="isLoading" >
              <PageLoader />
            </div>
            <Table class="table" >              
              <TableHeader>
                <TableRow>
                  <TableHead >Day</TableHead >
                  <TableHead >AM Time In</TableHead >
                  <TableHead >AM Time Out</TableHead >
                  <TableHead >PM Time In</TableHead >
                  <TableHead >PM Time Out</TableHead >
                  <TableHead >Rendered</TableHead >
                  <TableHead >Excess</TableHead >
                  <TableHead >Late</TableHead >
                  <TableHead >Undertime</TableHead >
                  <TableHead >Remarks</TableHead >
                  <TableHead >Attendance Type</TableHead >
                  <TableHead >Attachment</TableHead >
                  <TableHead >Edit</TableHead >

                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="entry in timeEntries.slice((currentPage - 1) * itemsPerPage, currentPage * itemsPerPage)" :key="entry.id">
                  <TableCell v-html="entry.Day">
                    
                  </TableCell>
                  <TableCell>{{ entry.am_time_in }}</TableCell>
                  <TableCell>{{ entry.am_time_out }}</TableCell>
                  <TableCell>{{ entry.pm_time_in }}</TableCell>
                  <TableCell>{{ entry.pm_time_out }}</TableCell>
                  <TableCell>{{ entry.rendered_hours }}</TableCell>
                  <TableCell>{{ entry.excess_minutes }}</TableCell>
                  <TableCell>{{ entry.late_hours }}</TableCell>
                  <TableCell>{{ entry.undertime_minutes }}</TableCell>
                  <TableCell v-html="entry.remarks"></TableCell>
                  <TableCell>{{ entry.attendance_type }}</TableCell>
                  <!-- <TableCell v-html="entry.attachment"></TableCell> -->
                  <TableCell>  
                    <ViewFiles :entry="entry" />
                  </TableCell>
                  <TableCell >
                    <EditTableModal
                        :entry="{ id: entry.id, 
                                  date: entry.temp_date, 
                                  temp_date: entry.temp_date,
                                  am_time_in: entry.am_time_in,
                                  am_time_out: entry.am_time_out,
                                  pm_time_in: entry.pm_time_in,
                                  pm_time_out: entry.pm_time_out,
                                  }"
                        @update="fetchTimeEntries"
                      />
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>

          </div>
        </div>

        <div class="flex flex-col gap-4 items-end">
          <div class="flex justify-end space-x-4 items-end">
            <Pagination v-slot="{ page }" :items-per-page="itemsPerPage" :total="totalItems" :sibling-count="1" show-edges :default-page="currentPage">
                <PaginationList v-slot="{ items }" class="flex items-center gap-1">
                  <PaginationPrev @click="handlePageChange(page - 1)" :disabled="page === 1" class="border rounded p-2" />
                  <template v-for="(item, index) in items">
                    <PaginationListItem v-if="item.type === 'page'" :key="index" :value="item.value" as-child>
                      <Button class="w-10 h-10 p-0 border rounded" :class="item.value === page ? 'bg-orange-500 text-white' : 'border-gray-300'" @click="handlePageChange(item.value)">
                        {{ item.value }}
                      </Button>
                    </PaginationListItem>
                  </template>
                  <PaginationNext @click="handlePageChange(page + 1)" :disabled="page === Math.ceil(totalItems / itemsPerPage)" class="border rounded p-2" />
                </PaginationList>
            </Pagination>
          </div>
        </div>

      </template>
    </Container>
  </div>
</template>