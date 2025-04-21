<script setup>
import { onMounted, ref } from 'vue'
import Container from '@/Components/Container.vue'
import DateFilter from '@/Composable/DateFilter.vue'
import ChangeSchedule from '@/Composable/ChangeSchedule.vue'
import EditSchedule from '@/Composable/EditSchedule.vue'
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


const requestStatus = ref([]);
const selectedEntry = ref(null);
const currentPage = ref(1);
const itemsPerPage = ref(10);
const totalItems = ref(0);
const isMobile = useMediaQuery('(max-width: 768px)')
const isTablet = useMediaQuery('(min-width: 769px) and (max-width: 1024px)')

const currentDateFilter = ref({ startDate: null, endDate: null });


const fetchRequestStatus = async (dates = {}) => {
    try {
        const response = await axios.get('/api/request-status/list', {
            params: {
                start_date: dates.startDate,
                end_date: dates.endDate,
                page: currentPage.value,
                items_per_page: itemsPerPage.value
            }
        });
        requestStatus.value = response.data.data;
        totalItems.value = response.data.data.length; 
    } catch (error) {
        console.error('Error fetching time entries:', error);
    }
};

const openAttachment = async (attachmentPath) => {
    try {
        
        // Check if the attachment is already a presigned URL
        if (attachmentPath.includes('X-Amz-Signature')) {
            window.open(attachmentPath, '_blank');
            return;
        }
        
        // Check if the attachment is a regular URL
        if (attachmentPath.startsWith('http')) {
            window.open(attachmentPath, '_blank');
            return;
        }
        
        // Otherwise, get a presigned URL from the server
        const response = await axios.post('/api/request-status/open-attachment', {
            attachment_path: attachmentPath
        });
        
        
        if (response.data.success && response.data.url) {
            window.open(response.data.url, '_blank');
        } else {
            console.error('Failed to open attachment:', response.data.error);
        }
    } catch (error) {
        console.error('Error opening attachment:', error);
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
    fetchRequestStatus(currentDateFilter.value); // Pass the current date filters
}

const handleDateChange = (dates) => {
  currentDateFilter.value = dates; // Store the current date filters
  currentPage.value = 1; // Reset to first page when changing dates
  fetchRequestStatus(dates);
}

onMounted(() => {
    fetchRequestStatus()
})

</script>

<template>
  <div class="flex flex-col gap-8">
    <Container class="flex flex-col gap-4" header-text="Request Status">
      <template #header>
        <div :class="isMobile ? 'flex flex-col gap-4 items-center' : isTablet ? 'flex flex-col gap-4 items-start' : 'flex justify-between items-end'">
            <DateFilter class="w-full sm:w-auto" @date-change="handleDateChange"/>
        </div>

        <div class="flex flex-col gap-2">
          <div class="table-container">
            <Table class="table">
              <TableHeader>
                <TableRow>
                  <TableHead>Subject</TableHead>
                  <TableHead>Status</TableHead>
                  <TableHead>Approver</TableHead>
                  <TableHead>Attachment</TableHead>
                  <TableHead>Created_at</TableHead>
                  <TableHead>Updated_at</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="entry in requestStatus.slice((currentPage - 1) * itemsPerPage, currentPage * itemsPerPage)" :key="entry.id">
                  <TableCell>{{ entry.subject }}</TableCell>
                  <TableCell>{{ entry.status }}</TableCell>
                  <TableCell>{{ entry.approver_id }}</TableCell>
                  <TableCell>
                    <button v-if="entry.attachment" 
                       @click="openAttachment(entry.attachment)" 
                       class="inline-flex items-center justify-center px-3 py-1 text-sm font-medium text-white bg-orange-500 rounded-md hover:bg-orange-600">
                      Preview
                    </button>
                    <span v-else>-</span>
                  </TableCell>
                  <TableCell>{{ entry.created_at }}</TableCell>
                  <TableCell>{{ entry.updated_at }}</TableCell>
                
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