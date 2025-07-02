import { createApp } from 'vue'
import App from '@/App.vue'
import vuetify from '@/Plugins/vuetify'
import pinia from '@/Plugins/pinia'
import router from '@/Router'

createApp(App)
  .use(vuetify)
  .use(pinia)
  .use(router)
  .mount('#app')
