<script setup lang="ts">
// defineProps<{
//   ordersCount: number;
//   totalSpent: number;
// }>();
</script>

<template>
  <div class="d-flex justify-space-between align-center mb-6">
    <div>
      <h1 class="text-h4 font-weight-bold mb-2">My Orders</h1>
      <p class="text-body-1 text-grey-darken-1">
        Track and manage your order history
      </p>
    </div>

    <!-- Quick Stats -->
    <!-- <div class="d-flex ga-4">
      <v-card elevation="2" class="px-4 py-3 text-center">
        <div class="text-h5 font-weight-bold text-primary">
          {{ ordersCount }}
        </div>
        <div class="text-caption text-grey-darken-1">Total Orders</div>
      </v-card>

      <v-card elevation="2" class="px-4 py-3 text-center">
        <div class="text-h5 font-weight-bold text-success">
          {{ totalSpent?.toFixed(2) ?? '0.00' }}
        </div>
        <div class="text-caption text-grey-darken-1">Total Spent</div>
      </v-card>
    </div> -->
  </div>
</template>
