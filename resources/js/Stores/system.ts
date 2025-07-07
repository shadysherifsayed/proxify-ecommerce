import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useSystemStore = defineStore(
  'system',
  () => {
    const isDarkMode = ref(false);

    return {
      isDarkMode,
    };
  },
  {
    persist: {
      pick: ['isDarkMode'],
    },
  },
);
