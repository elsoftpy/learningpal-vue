<template>
    <Button
        type="submit"
        :loading="isLoading"
        :label="$t('Save Changes')"
        icon="pi pi-save"
        severity="success"
    />
</template>
<script setup>
import { useI18n } from 'vue-i18n';
import Button from 'primevue/button';

const { t: $t } = useI18n();

const props = defineProps({
    isLoading: { 
        type: Boolean, 
        default: false 
    },
});
</script>