import { Icon } from '@iconify/vue';
import { createVuetify, type ThemeDefinition } from 'vuetify';
import 'vuetify/_styles.scss';

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
};

// export const darkTheme: ThemeDefinition = {
//     colors: {
//         primary: '#757575',
//         secondary: '#6200EE',
//         accent: '#424242',
//         error: '#EF5350',
//         info: '#2196F3',
//         success: '#66BB6A',
//         warning: '#FFC107',
//     },
//     dark: true,
// };

const vuetify = createVuetify({
  theme: {
    defaultTheme: 'light',
    variations: {
      colors: [
        'primary',
        'secondary',
        'accent',
        'error',
        'warning',
        'info',
        'success',
      ],
      lighten: 1,
      darken: 2,
    },
    themes: {
      light: lightTheme,
    },
  },
  defaults: {
    VCard: {
      border: 'sm',
      elevation: 0,
      rounded: 'lg',
    },
    VTextField: {
      variant: 'outlined',
      rounded: 'lg',
      color: 'primary',
      density: 'compact',
    },
    VBtn: { variant: 'tonal', rounded: 'lg', color: 'primary' },
  },
  icons: {
    defaultSet: 'mdi',
    sets: {
      mdi: {
        component: Icon as any,
      },
    },
  },
});

export default vuetify;
