<script lang="ts" setup>
const searchQuery = defineModel<string>('');

const statusFilter = defineModel<string | null>('');

const sortBy = defineModel<string>('newest');

const statusOptions = [
  { title: 'All Statuses', value: '' },
  { title: 'Pending', value: 'pending' },
  { title: 'Processing', value: 'processing' },
  { title: 'Completed', value: 'completed' },
  { title: 'Cancelled', value: 'cancelled' },
];

const sortOptions = [
  { title: 'Newest First', value: 'newest' },
  { title: 'Oldest First', value: 'oldest' },
  { title: 'Highest Amount', value: 'highest' },
  { title: 'Lowest Amount', value: 'lowest' },
];
</script>

<template>
  <v-card elevation="1" class="mb-6">
    <v-card-text class="py-4">
      <v-row align="center">
        <v-col cols="12" md="6">
          <v-text-field
            v-model="searchQuery"
            placeholder="Search orders by ID or product name..."
            prepend-inner-icon="mdi-magnify"
            hide-details
            clearable
          />
        </v-col>

        <v-col cols="12" md="3">
          <v-select
            v-model="statusFilter"
            :items="statusOptions"
            placeholder="Filter by status"
            variant="outlined"
            density="compact"
            hide-details
            clearable
          />
        </v-col>

        <v-col cols="12" md="3">
          <v-select
            v-model="sortBy"
            :items="sortOptions"
            placeholder="Sort by"
            variant="outlined"
            density="compact"
            hide-details
          />
        </v-col>
      </v-row>
    </v-card-text>
  </v-card>
</template>
