<template>
    <Dialog
        :visible="props.visible"
        modal
        :closable="false"
        @update:visible="emit('update:visible', $event)"
    >
        <template #header>
            <div class="flex w-full justify-between items-center rounded-lg h-16 p-4 text-white bg-blue-500">
                <span class="text-xl font-semibold">{{ $t('Class Schedule Feedback') }}</span>
                <Button
                    icon="pi pi-times"
                    rounded
                    size="small"
                    severity="info"
                    variant="outlined"
                    class="text-white! border-2! hover:text-gray-800!"
                    :disabled="props.loading"
                    @click="close"
                />
            </div>
        </template>

        <div class="flex items-start gap-3 p-4">
            <Button
                type="button"
                icon="pi pi-info-circle"
                severity="info"
                rounded
                text
                aria-hidden="true"
                class="shrink-0 pointer-events-none"
            />
            <div class="w-full space-y-3">
                <p class="font-semibold text-slate-700">
                    {{ $t('Add or review feedback for this class schedule.') }}
                </p>
                <Textarea
                    v-model="localFeedback"
                    rows="6"
                    class="w-full"
                    :placeholder="$t('Type feedback here')"
                    :disabled="props.loading || props.readonly"
                    :readonly="props.readonly"
                />
            </div>
        </div>

        <div class="flex justify-end gap-2">
            <Button
                type="button"
                :label="props.dismissLabel"
                severity="secondary"
                :disabled="props.loading"
                @click="close"
            />
            <Button
                v-if="props.showDelete"
                type="button"
                :label="$t('Delete')"
                severity="danger"
                :disabled="props.deleteDisabled || props.loading"
                @click="onDelete"
            />
            <Button
                v-if="props.showSave"
                type="button"
                :label="$t('Save')"
                severity="info"
                :loading="props.loading"
                :disabled="props.saveDisabled"
                @click="onSave"
            />
        </div>
    </Dialog>
</template>

<script setup>
import { ref, watch } from 'vue';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import Textarea from 'primevue/textarea';

const props = defineProps({
    visible: {
        type: Boolean,
        required: true,
    },
    feedback: {
        type: String,
        default: '',
    },
    onSave: {
        type: Function,
        required: true,
    },
    onDelete: {
        type: Function,
        required: true,
    },
    loading: {
        type: Boolean,
        default: false,
    },
    readonly: {
        type: Boolean,
        default: false,
    },
    showSave: {
        type: Boolean,
        default: true,
    },
    saveDisabled: {
        type: Boolean,
        default: false,
    },
    showDelete: {
        type: Boolean,
        default: true,
    },
    deleteDisabled: {
        type: Boolean,
        default: false,
    },
    dismissLabel: {
        type: String,
        default: 'Cancel',
    },
});

const emit = defineEmits(['update:visible']);
const localFeedback = ref(props.feedback ?? '');

watch(
    () => props.feedback,
    (value) => {
        localFeedback.value = value ?? '';
    }
);

watch(
    () => props.visible,
    (visible) => {
        if (visible) {
            localFeedback.value = props.feedback ?? '';
        }
    }
);

const close = () => {
    if (props.loading) {
        return;
    }

    emit('update:visible', false);
};

const onSave = async () => {
    if (props.saveDisabled) {
        return;
    }

    await props.onSave(localFeedback.value);
    close();
};

const onDelete = async () => {
    if (props.deleteDisabled) {
        return;
    }

    await props.onDelete();
    close();
};
</script>