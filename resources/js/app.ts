import { createPinia } from 'pinia'
import { createApp } from 'vue'
import App from '@/App.vue'
import vuetify from '@/plugins/vuetify'
import router from '@/router'

const pinia = createPinia()

createApp(App)
  .use(vuetify)
  .use(pinia)
  .use(router)
  .mount('#app')
