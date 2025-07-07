<script lang="ts" setup>
withDefaults(
  defineProps<{
    text: string;
  }>(),
  {
    text: 'Loading...',
  },
);
</script>

<template>
  <div class="text-center py-8">
    <v-progress-circular indeterminate color="primary" size="64" />
    <p class="text-body-1 text-grey-darken-1 mt-4">{{ text }}</p>
  </div>
</template>
