import { createApp } from 'vue';
import PrimeVue from 'primevue/config';
import Aura from '@primeuix/themes/aura';
import router from './router';
import axios from 'axios';
import App from './App.vue';

axios.defaults.withCredentials = true;
axios.defaults.baseURL = '/';

const app = createApp(App);


app.use(PrimeVue, {
    theme: {
        preset: Aura
    }
})
    .use(router)
    .mount('#app');