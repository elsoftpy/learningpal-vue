<template>
    <div>
        <Button
            v-if="resourceUrl"
            type="button"
            size="small"
            :label="resolvedViewLabel"
            :icon="buttonIcon"
            @click="open"
        />
        <span v-else class="text-gray-400 text-sm">{{ resolvedEmptyLabel }}</span>

        <Dialog
            v-model:visible="isDialogVisible"
            modal
            :closable="false"
            :style="{ width: '32rem' }"
        >
            <template #header>
                <div class="flex w-full justify-between items-center rounded-lg h-16 p-4 text-white bg-blue-500">
                    <span class="text-xl font-semibold">{{ resolvedModalTitle }}</span>
                    <Button
                        icon="pi pi-times"
                        rounded
                        size="small"
                        severity="primary"
                        variant="outlined"
                        class="text-white! border-2! hover:text-gray-800!"
                        @click="close"
                    />
                </div>
            </template>
            <a v-if="resourceUrl" :href="resourceUrl" target="_blank" rel="noopener noreferrer">
                <img
                    v-if="resourceType === 'image'"
                    :src="resourceUrl"
                    class="w-full rounded"
                />
                <IconWrapper
                    v-else
                    :name="iconName"
                    class="text-center mx-auto my-8"
                    :class="iconColorClass"
                    size="120"
                />
            </a>
            <div v-else class="text-center text-gray-400 py-6">
                {{ resolvedEmptyLabel }}
            </div>
        </Dialog>
    </div>
</template>
<script setup>
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import IconWrapper from '@/components/common/IconWrapper.vue';

const props = defineProps({
    data: { type: Object, default: null },
    resourceField: { type: String, default: 'resource' },
    viewLabel: { type: String, default: null },
    emptyLabel: { type: String, default: null },
    buttonIcon: { type: String, default: 'pi pi-eye' },
    modalTitle: { type: String, default: null },
});

const { t: $t } = useI18n();

const isDialogVisible = ref(false);
const resourceUrl = computed(() => props.data?.[props.resourceField]);

const resolvedViewLabel = computed(() => props.viewLabel ?? $t('View'));
const resolvedEmptyLabel = computed(() => props.emptyLabel ?? $t('None'));
const resolvedModalTitle = computed(() => props.modalTitle ?? resolvedViewLabel.value);

const resourceType = computed(() => detectResourceType(resourceUrl.value));
const iconName = computed(() => iconByType(resourceType.value));
const iconColorClass = computed(() => fileTypeColorClass(resourceType.value));

watch(resourceUrl, () => {
    isDialogVisible.value = false;
});

function open() {
    isDialogVisible.value = true;
}

function close() {
    isDialogVisible.value = false;
}

function detectResourceType(url) {
    if (!url) return 'unknown';
    const extension = url.split('?')[0].split('.').pop()?.toLowerCase();
    if (!extension) return 'unknown';

    if (['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'].includes(extension)) {
        return 'image';
    }
    if (extension === 'pdf') {
        return 'pdf';
    }
    if (['doc', 'docx'].includes(extension)) {
        return 'word';
    }
    if (['xls', 'xlsx', 'csv'].includes(extension)) {
        return 'excel';
    }
    return 'unknown';
}

function iconByType(type) {
    switch (type) {
        case 'pdf':
            return 'file-pdf';
        case 'word':
            return 'file-doc';
        case 'excel':
            return 'file-xls';
        case 'image':
            return 'file-image';
        default:
            return 'file-lines';
    }
}

function fileTypeColorClass(type) {
    switch (type) {
        case 'pdf':
            return 'text-red-600';
        case 'word':
            return 'text-blue-600';
        case 'excel':
            return 'text-green-600';
        default:
            return 'text-gray-600';
    }
}
</script>
