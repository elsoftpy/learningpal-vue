<template>
    <div class="flex items-center space-x-2">
        <ToggleSwitch 
            v-model="checked" 
            @click="themeStore.toggle()"
            inputId="theme-toggle"
        />
        <label 
            for="theme-toggle" 
            class="text-sm font-medium text-slate-900 dark:text-white cursor-pointer select-none"
        >
            {{ $t('Dark mode') }}
        </label>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useThemeStore } from '@/stores/theme';
import { useI18n } from 'vue-i18n';
import ToggleSwitch from 'primevue/toggleswitch';

const themeStore = useThemeStore();
const { t: $t } = useI18n();
const checked = ref(themeStore.isDark);
</script>