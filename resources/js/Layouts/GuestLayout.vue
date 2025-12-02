<template>
    <div class="font-sans text-gray-900 antialiased min-h-screen">
        <div class="flex min-h-screen items-center p-6 bg-blue-900">
            <div class="flex flex-col lg:flex-row max-w-4xl mx-auto overflow-hidden bg-white rounded-lg shadow-xl"> 
                    <!-- Left side with image -->
                    <div class="flex h-auto w-full lg:w-1/2 items-center justify-center bg-blue-200 dark:bg-slate-500">
                        <img
                            aria-hidden="true"
                            class="object-contain w-full h-full"
                            :src="brandLogo"
                            alt="Office"
                        />
                    </div>
                    
                    <!-- Right side with form -->
                    <div class="flex h-auto w-full lg:w-1/2 items-center justify-center p-12 lg:p-6 bg-white dark:bg-slate-700">
                        <div class="w-full">
                            <h1 class="mb-4 text-xl font-semibold text-gray-700">
                                {{ formTitle }}
                            </h1>
                            <slot />
                        </div>
                    </div>
                
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import brandLogo from '@/images/brandLogo.png';

const route = useRoute();
const { t } = useI18n();

const props = defineProps({
    title: {
        type: String,
        default: '',
    },
});

const formTitle = computed(() => {
    console.log('Route meta title:', route.meta?.title);
    if ( route.meta.title ) {
        return t(route.meta.title);
    }

    return t('Welcome');
});
</script>