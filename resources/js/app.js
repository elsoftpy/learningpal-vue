import { createApp } from 'vue';
import PrimeVue from 'primevue/config';
import { definePreset } from '@primeuix/themes';
import Aura from '@primeuix/themes/aura';
import router from './router';
import axios from 'axios';
import App from './App.vue';

axios.defaults.withCredentials = true;
axios.defaults.baseURL = '/';

const IplPreset = definePreset(Aura, {
    semantic: {
        primary: {
            50: "{blue.50}",
            100: "{blue.100}",
            200: "{blue.200}",
            300: "{blue.300}",
            400: "{blue.400}",
            500: "{blue.500}",
            600: "{blue.600}",
            700: "{blue.700}",
            800: "{blue.800}",
            900: "{blue.900}",
        },
    },
    components: {
        inputtext: {
            root: {
                hoverBorderColor: "{primary.500}",
                focusBorderColor: "{primary.600}",
                focusRing: {
                    width: '0.1rem',
                    style: 'solid',
                    color: '{primary.500}',
                },
            }
        },
    },
});

const app = createApp(App);


app.use(PrimeVue, {
    theme: {
        preset: IplPreset,
    }
})
    .use(router)
    .mount('#app');