import { createPinia } from 'pinia'
import { createApp } from 'vue'
import App from '@/App.vue'
import vuetify from '@/Plugins/vuetify'
import router from '@/Router'

const pinia = createPinia()

createApp(App)
  .use(vuetify)
  .use(pinia)
  .use(router)
  .mount('#app')
