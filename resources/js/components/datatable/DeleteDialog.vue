<template>
    <Dialog
        :visible="props.visible"
        modal
        :closable="false"
        @update:visible="emit('update:visible', $event)"
    >
        <template #header >
            <div class="flex w-full justify-between items-center rounded-lg h-16 p-4 text-white bg-red-500">
                <span class="text-xl font-semibold">{{ $t('Delete Confirmation') }}</span>
                <Button
                    icon="pi pi-times"
                    rounded
                    size="small"
                    severity="danger"
                    variant="outlined"
                    class="text-white! border-2! hover:text-gray-800!"
                    @click="close"
                />
            </div>
        </template>
        <span class="flex p-4 items-center font-semibold mb-4 text-center">
            {{ message }}
        </span>
        <div class="flex justify-end gap-2">
            <Button 
                type="button" 
                :label="$t('Cancel')" 
                severity="secondary" 
                @click="close">
            </Button>
            <Button 
                type="button" 
                :label="$t('Delete')"
                severity="danger" 
                @click="onConfirm">
            </Button>
        </div>
    </Dialog>
</template>
<script setup>
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
    
const props = defineProps({
    visible: {
        type: Boolean,
        required: true
    },
    onDelete: {
        type: Function,
        required: true
    },
    message: {
        type: String,
        required: true
    },
    loading: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['update:visible']);

const close = () => {
    emit('update:visible', false);
};

const onConfirm = async() => {
    await props.onDelete();
    close();
};
</script>