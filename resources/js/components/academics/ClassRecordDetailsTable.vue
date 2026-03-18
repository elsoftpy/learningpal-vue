<template>
    <Transition name="table-expand" appear>
        <div class="expand-panel bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg">
            <div class="overflow-hidden rounded-lg">
                <table class="w-full text-sm">
                    <thead class="bg-blue-50 dark:bg-gray-800 rounded-lg">
                        <tr class="text-left text-xs uppercase tracking-wide text-slate-600 dark:text-slate-100">
                            <th class="py-2 px-2 text-left">{{ $t('Content') }}</th>
                            <th class="py-2 px-2 text-left">{{ $t('Free Content') }}</th>
                            <th class="py-2 px-2 text-left">{{ $t('Activity') }}</th>
                            <th class="py-2 px-2 text-left">{{ $t('Links') }}</th>
                            <th class="py-2 px-2 text-left">{{ $t('Attachment') }}</th>
                            <th class="py-2 px-2">{{ $t('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="detail in details"
                            :key="detail.id"
                            class="border-t border-slate-200 dark:border-slate-700"
                        >
                            <td class="py-2 px-2 text-left">
                                {{ detail.content_name }}
                            </td>
                            <td class="py-2 px-2 text-left">
                                {{ detail.free_content }}
                            </td>
                            <td class="py-2 px-2 text-left">
                                {{ detail.activity }}
                            </td>
                            <td class="py-2 px-2 text-left">
                                <span v-if="!detail.links" class="text-gray-400">—</span>
                                <div v-else class="flex flex-wrap gap-1">
                                    <a
                                        v-for="(link, i) in parseLinks(detail.links)"
                                        :key="i"
                                        :href="link"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="text-blue-600 dark:text-blue-400 hover:underline truncate max-w-[200px] block"
                                    >{{ link }}</a>
                                </div>
                            </td>
                            <td class="py-2 px-2 text-left">
                                <ResourceViewerCell
                                    :data="detail"
                                    resource-field="attachment_url"
                                    :view-label="$t('View')"
                                    :empty-label="$t('None')"
                                    :modal-title="$t('Attachment')"
                                />
                            </td>
                            <td class="py-2 pr-4">
                                <div class="flex space-x-2">
                                    <Button
                                        type="button"
                                        size="small"
                                        :label="$t('Edit')"
                                        icon="pi pi-pencil"
                                        severity="primary"
                                        @click="handleEdit(detail)"
                                    />
                                    <Button
                                        type="button"
                                        size="small"
                                        :label="$t('Delete')"
                                        icon="pi pi-trash"
                                        severity="danger"
                                        @click="handleDelete(detail)"
                                    />
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-200 dark:border-slate-700 p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-100">{{ $t('Student Production') }}</h4>
                    <Button
                        type="button"
                        icon="pi pi-save"
                        :label="$t('Save')"
                        size="small"
                        severity="success"
                        :loading="isSaving"
                        @click="saveStudentProduction"
                    />
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <FileUpload
                            id="class-record-student-production-file"
                            :button-label="$t('Upload file')"
                            accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.txt,image/*"
                            :max-file-size="10240000"
                            empty-icon="pi pi-file"
                            :empty-message="$t('Select a student production file')"
                            status-class="px-2 py-1 rounded-full bg-sky-600 text-xs font-semibold text-white"
                            @update:modelValue="onStudentProductionFileSelect"
                        />

                        <a
                            v-if="studentProductionPreview"
                            :href="studentProductionPreview.url"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="block border border-slate-200 dark:border-slate-700 rounded-lg p-3 bg-white dark:bg-slate-900"
                        >
                            <div class="text-xs text-slate-500 mb-2">
                                {{ studentProductionFile ? $t('Selected file') : $t('Current file') }}
                            </div>
                            <img
                                v-if="studentProductionPreview.type === 'image'"
                                :src="studentProductionPreview.url"
                                :alt="studentProductionPreview.name"
                                class="max-h-36 w-auto rounded"
                            />
                            <div v-else class="flex items-center gap-3">
                                <IconWrapper
                                    :name="studentProductionPreview.iconName"
                                    :class="studentProductionPreview.iconColorClass"
                                    size="42"
                                />
                                <span class="text-sm text-slate-700 dark:text-slate-100 break-all">{{ studentProductionPreview.name }}</span>
                            </div>
                        </a>
                    </div>

                    <div class="space-y-2">
                        <AudioRecorder
                            :label="$t('Record student audio')"
                            @update:modelValue="onStudentProductionAudioSelect"
                            @error="onAudioRecorderError"
                        />

                        <div v-if="existingStudentProductionAudio && !studentProductionAudio" class="space-y-2">
                            <p class="text-xs text-slate-500">
                                {{ $t('Current audio') }}: {{ existingStudentProductionAudio.name }}
                            </p>
                            <audio :src="existingStudentProductionAudio.url" controls class="w-full" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script setup>
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useToast } from 'primevue/usetoast';
import axios from 'axios';
import Button from 'primevue/button';
import IconWrapper from '@/components/common/IconWrapper.vue';
import ResourceViewerCell from '@/components/datatable/ResourceViewerCell.vue';
import FileUpload from '@/components/form/FileUpload.vue';
import AudioRecorder from '@/components/form/AudioRecorder.vue';

const { t: $t } = useI18n();
const toast = useToast();

const props = defineProps({
    details: {
        type: Array,
        default: () => [],
    },
    classRecordId: {
        type: Number,
        required: true,
    },
    studentProductionMedia: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['delete-detail', 'edit-detail', 'student-production-saved']);

const isSaving = ref(false);
const studentProductionFile = ref(null);
const studentProductionAudio = ref(null);
const existingStudentProductionFile = ref(null);
const existingStudentProductionAudio = ref(null);
const selectedStudentProductionFileUrl = ref(null);

const canSaveProduction = computed(() => Boolean(studentProductionFile.value || studentProductionAudio.value));

const syncExistingMedia = (mediaList) => {
    const media = Array.isArray(mediaList) ? mediaList : [];
    existingStudentProductionFile.value = media.find((item) => item.media_type === 'file') || null;
    existingStudentProductionAudio.value = media.find((item) => item.media_type === 'audio') || null;
};

watch(
    () => props.studentProductionMedia,
    (value) => {
        syncExistingMedia(value);
    },
    { immediate: true }
);

const parseLinks = (links) => {
    if (!links) return [];
    return links.split('|').map((l) => l.trim()).filter(Boolean);
};

const handleEdit = (detail) => {
    emit('edit-detail', detail);
};

const handleDelete = (detail) => {
    emit('delete-detail', detail);
};

const detectResourceType = (nameOrUrl = '') => {
    const cleanValue = String(nameOrUrl || '').split('?')[0];
    const extension = cleanValue.split('.').pop()?.toLowerCase();
    if (!extension) {
        return 'unknown';
    }

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
};

const detectResourceTypeFromMimeType = (mimeType = '') => {
    const normalized = String(mimeType || '').toLowerCase();
    if (!normalized) {
        return 'unknown';
    }

    if (normalized.startsWith('image/')) {
        return 'image';
    }

    if (normalized === 'application/pdf') {
        return 'pdf';
    }

    if (
        normalized === 'application/msword'
        || normalized === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ) {
        return 'word';
    }

    if (
        normalized === 'application/vnd.ms-excel'
        || normalized === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        || normalized === 'text/csv'
    ) {
        return 'excel';
    }

    return 'unknown';
};

const iconByType = (type) => {
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
};

const fileTypeColorClass = (type) => {
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
};

const studentProductionPreview = computed(() => {
    if (studentProductionFile.value && selectedStudentProductionFileUrl.value) {
        const typeFromName = detectResourceType(studentProductionFile.value.name);
        const typeFromMime = detectResourceTypeFromMimeType(studentProductionFile.value.type);
        const type = typeFromName !== 'unknown' ? typeFromName : typeFromMime;
        return {
            url: selectedStudentProductionFileUrl.value,
            name: studentProductionFile.value.name,
            type,
            iconName: iconByType(type),
            iconColorClass: fileTypeColorClass(type),
        };
    }

    if (existingStudentProductionFile.value?.url) {
        const typeFromName = detectResourceType(existingStudentProductionFile.value.name || existingStudentProductionFile.value.url);
        const typeFromMime = detectResourceTypeFromMimeType(existingStudentProductionFile.value.mime_type);
        const type = typeFromName !== 'unknown' ? typeFromName : typeFromMime;
        return {
            url: existingStudentProductionFile.value.url,
            name: existingStudentProductionFile.value.name,
            type,
            iconName: iconByType(type),
            iconColorClass: fileTypeColorClass(type),
        };
    }

    return null;
});

const onStudentProductionFileSelect = (file) => {
    if (selectedStudentProductionFileUrl.value) {
        URL.revokeObjectURL(selectedStudentProductionFileUrl.value);
        selectedStudentProductionFileUrl.value = null;
    }

    studentProductionFile.value = file || null;

    if (file) {
        selectedStudentProductionFileUrl.value = URL.createObjectURL(file);
    }
};

const onStudentProductionAudioSelect = (file) => {
    studentProductionAudio.value = file || null;
};

const onAudioRecorderError = (message) => {
    if (!message) {
        return;
    }

    toast.add({
        severity: 'warn',
        summary: $t('Recorder'),
        detail: message,
        life: 5000,
    });
};

const saveStudentProduction = async () => {
    if (!canSaveProduction.value) {
        toast.add({
            severity: 'info',
            summary: $t('No changes'),
            detail: $t('Select a file and/or record audio before saving.'),
            life: 3000,
        });
        return;
    }

    isSaving.value = true;

    try {
        const payload = new FormData();
        if (studentProductionFile.value) {
            payload.append('student_production_file', studentProductionFile.value);
        }
        if (studentProductionAudio.value) {
            payload.append('student_production_audio', studentProductionAudio.value);
        }

        await axios.post(`/academics/lessons/class-records/${props.classRecordId}/student-production`, payload);

        toast.add({
            severity: 'success',
            summary: $t('Success'),
            detail: $t('Student production saved successfully.'),
            life: 3000,
        });

        studentProductionFile.value = null;
        studentProductionAudio.value = null;
        emit('student-production-saved');
    } catch (error) {
        const validationError = error?.response?.data?.errors;
        const firstValidationMessage = validationError
            ? Object.values(validationError)?.[0]?.[0]
            : null;

        toast.add({
            severity: 'error',
            summary: $t('Error'),
            detail: firstValidationMessage || error?.response?.data?.message || $t('Could not save student production files.'),
            life: 5000,
        });
    } finally {
        isSaving.value = false;
    }
};

onBeforeUnmount(() => {
    if (selectedStudentProductionFileUrl.value) {
        URL.revokeObjectURL(selectedStudentProductionFileUrl.value);
    }
});
</script>

<style scoped>
.expand-panel {
    overflow: hidden;
}

.table-expand-enter-active,
.table-expand-leave-active {
    transition:
        max-height 0.3s cubic-bezier(0.4, 0, 0.2, 1),
        opacity 0.3s ease;
    max-height: 1000px;
    opacity: 1;
    overflow: hidden;
}

.table-expand-enter-from,
.table-expand-leave-to {
    max-height: 0;
    opacity: 0;
}
</style>
