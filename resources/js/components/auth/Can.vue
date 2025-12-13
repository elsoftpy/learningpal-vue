<template>
    <slot v-if="isAllowed"/>
</template>
<script setup>
import { computed } from 'vue';
import { usePermissions } from '@/composables/usePermissions.js';

const props = defineProps({
    permission: {
        type: [String, Array],
        required: true,
    },
    mode: {
        type: String,
        default: 'any', // 'any' or 'all'
    },
});

const { can } = usePermissions();

const isAllowed = computed(() => {
    if (props.mode === 'all') {
        return canAll(props.permission);
    }

    return can(props.permission);
});
</script>