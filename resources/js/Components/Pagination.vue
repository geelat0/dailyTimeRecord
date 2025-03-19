<script setup>
import {
  Pagination,
  PaginationList,
  PaginationListItem,
  PaginationNext,
  PaginationPrev,
} from '@/Components/ui/pagination'
import { Button } from '@/Components/ui/button'

defineProps({
  currentPage: {
    type: Number,
    required: true
  },
  itemsPerPage: {
    type: Number,
    required: true
  },
  totalItems: {
    type: Number,
    required: true
  }
})

const emit = defineEmits(['pageChange'])

const handlePageChange = (page) => {
  emit('pageChange', page)
}
</script>

<template>
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