import { Icon } from '@iconify/vue'
import { createVuetify, type ThemeDefinition } from 'vuetify'
import 'vuetify/_styles.scss'

// import '@mdi/font/css/materialdesignicons.css' // Ensure you are using css-loader

const lightTheme: ThemeDefinition = {
  colors: {
    primary: '#125CFF',
    secondary: '#424242',
    accent: '#FAFAFA',
    error: '#EF5350',
    info: '#2196F3',
    success: '#66BB6A',
    warning: '#FFC107',
  },
  dark: false,
}

export const darkTheme: ThemeDefinition = {
  colors: {
    primary: '#757575',
    secondary: '#6200EE',
    accent: '#424242',
    error: '#EF5350',
    info: '#2196F3',
    success: '#66BB6A',
    warning: '#FFC107',
  },
  dark: true,
}

const vuetify = createVuetify({
  theme: {
    defaultTheme: 'light',
    variations: {
      colors: ['primary', 'secondary', 'accent', 'error', 'warning', 'info', 'success'],
      lighten: 1,
      darken: 2,
    },
    themes: {
      light: lightTheme,
      dark: darkTheme,
    },
  },
  defaults: {
    VCard: {
      border: 'sm',
      elevation: 0,
      rounded: 'lg',
    },
  },
  icons: {
    defaultSet: 'mdi',
    sets: {
      mdi: {
        component: Icon as any,
      },
    },
  },
})

export default vuetify
