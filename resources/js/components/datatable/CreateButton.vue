<template>
    <Can :permission="props.permission" :mode="props.mode">
        <Button
            :label="props.label"
            icon="pi pi-plus"
            size="small"
            @click="pushRoute"
        />
    </Can>
</template>
<script setup>
import { useToast } from 'primevue/usetoast';
import { useRouter } from 'vue-router';
import Can from '@/components/auth/Can.vue';
import Button from 'primevue/button';

const router = useRouter();
const toast = useToast();

const props = defineProps({
    permission: {
        type: [String, Array],
        default: 'create users',
    },
    mode: {
        type: String,
        default: 'any', // 'any' or 'all'
    },
    label: {
        type: String,
        default: '',
    },
    routeName: {
        type: String,
        default: ''
    },
})

const pushRoute = () => {
    if (!props.routeName) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'No route name provided for Create Button component.',
        });

        return;
    }

    if (props.routeName) {
        router.push({ name: props.routeName });
    }
};

</script>