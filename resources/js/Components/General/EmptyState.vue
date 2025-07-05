<script lang="ts" setup>
withDefaults(
  defineProps<{
    title?: string;
    description?: string;
    icon?: string;
    backAction?: () => void;
  }>(),
  {
    title: 'Not Found',
    description:
      'The item you are looking for does not exist or has been removed.',
    icon: 'mdi-package-variant-closed',
  },
);
</script>

<template>
  <div class="text-center py-12">
    <v-icon
      :icon="icon"
      size="80"
      color="grey-lighten-1"
      class="mb-4"
    />
    <h2 class="text-h5 font-weight-medium mb-2 text-grey-darken-1">
      {{ title }}
    </h2>
    <p class="text-body-1 text-grey-darken-2 mb-6">
      {{ description }}
    </p>
    <v-btn v-if="backAction" color="primary" @click="backAction">
      Back to Orders
    </v-btn>
  </div>
</template>
