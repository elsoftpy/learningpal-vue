<template>
    <div class="flex w-full bg-slate-50 dark:bg-slate-900 shadow-md rounded-md">
        <div class="w-full p-2">
            <div class="flex-col space-y-2">
                <div class="flex w-full justify-between items-center bg-blue-500 dark:bg-blue-800 rounded-md p-4">
                    <div class="flex space-x-2 items-center font-semibold text-slate-50">
                        <IconWrapper 
                            name="pencil" 
                            class="w-8 h-8"
                        />                    
                        <div class="text-2xl">
                            {{ pageTitle }}
                        </div>
                    </div>
                    <div class="text-xs text-slate-300">
                        <Button 
                            @click="goBack"
                            :label="$t('Back')" 
                            icon="pi pi-arrow-left"
                            severity="warn"
                        />
                    </div>
                </div>
                <div class="flex flex-col items-center justify-center">
                    <slot name="body" />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import AppLayout from "@/Layouts/AppLayout.vue";
import { useI18n } from 'vue-i18n';
import { Button } from "primevue";
import 'primeicons/primeicons.css'
import IconWrapper from '@/components/common/IconWrapper.vue';

const { t } = useI18n();
const route = useRoute();
const router = useRouter();
const fallbackRoute = { name: 'dashboard' };

const pageTitle = computed(() => {
    const key = route.meta?.title ?? 'Leka';
    return key ? t(key) : '';
});

const goBack = () => {
    const hasHistory = typeof window !== 'undefined' && !!window.history.state?.back;

    if (hasHistory) {
        router.back();
        return;
    }

    router.push(fallbackRoute);
};

defineOptions({
  layout: AppLayout,
});

</script>
