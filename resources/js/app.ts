import App from '@/App.vue';
import pinia from '@/Plugins/pinia';
import vuetify from '@/Plugins/vuetify';
import router from '@/Router';
import { createApp } from 'vue';

createApp(App).use(vuetify).use(pinia).use(router).mount('#app');
