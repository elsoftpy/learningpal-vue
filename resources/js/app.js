import { createApp } from 'vue';
import { createPinia } from 'pinia';
import { useAuthStore } from './stores/auth';
import App from './App.vue';
import router from './router';
import axios from 'axios';
import PrimeVue from 'primevue/config';
import { definePreset } from '@primeuix/themes';
import Aura from '@primeuix/themes/aura';
import i18n from '../locales';
import { useThemeStore } from './stores/theme';
import { vMaska } from 'maska/vue';
import ToastService from 'primevue/toastservice';

axios.defaults.withCredentials = true;
axios.defaults.baseURL = '/';
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.headers.common['Accept'] = 'application/json';


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

const pinia = createPinia();

app.use(PrimeVue, {
    theme: {
        preset: IplPreset,
        options: {
            darkModeSelector: '.dark',
        }
    }
})
    .use(pinia)
    .use(ToastService)
    .use(i18n)
    .use(router)
    .directive('maska', vMaska);

const initialLocale = import.meta.env.VITE_APP_LOCALE || 'en';
i18n.global.locale.value = initialLocale;
console.log(`Locale set to: ${i18n.global.locale.value}`);

const authStore = useAuthStore(pinia);

const themeStore = useThemeStore(pinia);
themeStore.initialize();

let isHandlingAuthFailure = false;

axios.interceptors.response.use(
    (response) => response,
    async (error) => {
        const status = error?.response?.status;
        const requestUrl = error?.config?.url ?? '';
        const skipAuthRecovery = [
            '/auth/login',
            '/auth/logout',
            '/auth/me',
            '/sanctum/csrf-cookie',
        ].some((path) => requestUrl.includes(path));

        if ((status === 401 || status === 419) && !skipAuthRecovery) {
            error.__authRedirectHandled = true;
            authStore.handleSessionExpiry();

            if (!isHandlingAuthFailure) {
                isHandlingAuthFailure = true;
                try {
                    const currentRoute = router.currentRoute.value;
                    const isLoginRoute = currentRoute?.name === 'login';
                    const redirectTarget = currentRoute?.fullPath || '/dashboard';

                    if (!isLoginRoute) {
                        await router.replace({
                            name: 'login',
                            query: { redirect: redirectTarget },
                        });
                    }
                } finally {
                    isHandlingAuthFailure = false;
                }
            }
        }

        return Promise.reject(error);
    }
);

authStore.checkAuth().finally(() => {
    app.mount('#app');
});
