<script setup>
import { onMounted, ref , onUnmounted} from 'vue'
import Container from '@/Components/Container.vue'
import DateFilter from '@/Composable/DateFilter.vue'
import EditTimeEntry from '@/Composable/EditTimeEntry.vue'
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


const history = ref([]);
const selectedEntry = ref(null);
const isLoading = ref(false)
const currentPage = ref(1);
const itemsPerPage = ref(10);
const totalItems = ref(0);
const currentDateFilter = ref({ startDate: null, endDate: null });
const isMobile = useMediaQuery('(max-width: 768px)')

const fetchHistory = async (dates = {}) => {
  isLoading.value = true; // Set loading to true before fetching
    try {
        const response = await axios.get('/api/attachment-history/list', {
            params: {
                start_date: dates.startDate,
                end_date: dates.endDate
            }
        });
        history.value = response.data.data;
        totalItems.value = response.data.data.length; 

    } catch (error) {
        console.error('Error fetching time entries:', error);
    }
    finally{
      isLoading.value = false; // Set loading to true before fetching
    }
};

const handlePageChange = (page) => {
    currentPage.value = page;
    fetchTimeEntries(currentDateFilter.value); // Pass the current date filters
}

const handleDateChange = (dates) => {
  currentDateFilter.value = dates; // Store the current date filters
  currentPage.value = 1; // Reset to first page when changing dates
  fetchHistory(dates);
}

onMounted(() => {
  fetchHistory()
})

</script>

<template>
  <div class="flex flex-col gap-8">
    <Container class="flex flex-col gap-4" header-text="Attachment History">
      <template #header>
        <div :class="['flex flex-col gap-4', { 'items-end': !isMobile, 'items-center': isMobile }]">
          <DateFilter @date-change="handleDateChange"/>
        </div>
        <div class="flex flex-col gap-2">
          <div class="table-container relative">
            <div v-if="isLoading" >
              <PageLoader />
            </div>
            <Table class="table">
              <TableHeader>
                <TableRow>
                  <TableHead >Effectivity</TableHead >
                  <TableHead >Attendance Type</TableHead >
                  <TableHead >Remarks</TableHead >
                  <TableHead >Attachments</TableHead >
                  <TableHead >Created_at</TableHead >
                  <!-- <TableHead >Edit</TableHead > -->

                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="entry in history.slice((currentPage - 1) * itemsPerPage, currentPage * itemsPerPage)" :key="entry.id" >
                  <TableCell>{{ entry.effectivity }}</TableCell>
                  <TableCell>{{ entry.attendance_type }}</TableCell>
                  <TableCell>{{ entry.remarks }}</TableCell>
                  <TableCell>  
                    <ViewFiles :entry="entry" />
                  </TableCell>
                  <!-- <TableCell v-html="entry.attachment"></TableCell> -->
                  <TableCell>{{ entry.created_at }}</TableCell>
                  <!-- <TableCell  v-html="entry.edit"></TableCell> -->
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