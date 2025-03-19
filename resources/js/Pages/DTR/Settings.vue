<script setup>
import { onMounted, ref } from 'vue'
import Container from '@/Components/Container.vue'
import DateFilter from '@/Composable/DateFilter.vue'
import ChangeSchedule from '@/Composable/ChangeSchedule.vue'
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

const shiftSchedule = ref([]);
const selectedEntry = ref(null);
const currentPage = ref(1);
const itemsPerPage = ref(10);
const totalItems = ref(0);

const currentDateFilter = ref({ startDate: null, endDate: null });


const fetchShiftSchedule = async (dates = {}) => {
    try {
        const response = await axios.get('/api/shift-schedule/list', {
            params: {
                start_date: dates.startDate,
                end_date: dates.endDate,
                page: currentPage.value,
                items_per_page: itemsPerPage.value
            }
        });
        shiftSchedule.value = response.data.data;
        totalItems.value = response.data.data.length; 
    } catch (error) {
        console.error('Error fetching time entries:', error);
    }
};

const handleEditClick = (entry) => {
  selectedEntry.value = {
    id: entry.id,
    amTimeIn: entry.am_time_in,
    amTimeOut: entry.am_time_out,
    pmTimeIn: entry.pm_time_in,
    pmTimeOut: entry.pm_time_out,
    date: entry.date
  };
}


const handlePageChange = (page) => {
    currentPage.value = page;
    fetchShiftSchedule(currentDateFilter.value); // Pass the current date filters
}

const handleDateChange = (dates) => {
  currentDateFilter.value = dates; // Store the current date filters
  currentPage.value = 1; // Reset to first page when changing dates
  fetchShiftSchedule(dates);
}

onMounted(() => {
    fetchShiftSchedule()
})

</script>

<template>
  <div class="flex flex-col gap-8">
    <Container class="flex flex-col gap-4" header-text="Setting">
      <template #header>
  
        <div class="flex justify-between items-end">
            <ChangeSchedule @schedule-updated="fetchShiftSchedule"/>
            <DateFilter @date-change="handleDateChange"/>
        </div>

        <div class="flex flex-col gap-2">
          <div class="table-container">
            <Table class="table">
              <TableHeader>
                <TableRow>
                  <TableHead>Filed_on</TableHead>
                  <TableHead>Effectivity</TableHead>
                  <TableHead>Schedule</TableHead>
                  <TableHead>Remarks</TableHead>
                  <TableHead>Status</TableHead>
                  <TableHead>Action</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="entry in shiftSchedule.slice((currentPage - 1) * itemsPerPage, currentPage * itemsPerPage)" :key="entry.id">
                  <TableCell>{{ entry.filed_on }}</TableCell>
                  <TableCell>{{ entry.effectivity }}</TableCell>
                  <TableCell>{{ entry.schedule }}</TableCell>
                  <TableCell>{{ entry.remarks }}</TableCell>
                  <TableCell>{{ entry.status }}</TableCell>
                  <TableCell v-html="entry.action"></TableCell>
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