<template>
    <PageContainer>
        <template #body>
            <div class="flex flex-col w-full space-y-4">
                <Message
                    v-if="errors.general"
                    severity="error"
                    size="small"
                    variant="outlined"
                    :closable="true"
                >
                    {{ errors.general }}
                </Message>

                <div v-if="loading" class="py-8 text-sm text-slate-500">
                    {{ $t('Loading...') }}
                </div>

                <div v-else>
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                        <div class="rounded border border-slate-200 p-3">
                            <div class="text-xs text-slate-500">{{ $t('Teacher') }}</div>
                            <div class="text-sm font-medium">{{ classRecord.teacher || '-' }}</div>
                        </div>
                        <div class="rounded border border-slate-200 p-3">
                            <div class="text-xs text-slate-500">{{ $t('Course') }}</div>
                            <div class="text-sm font-medium">{{ classRecord.course || '-' }}</div>
                        </div>
                        <div class="rounded border border-slate-200 p-3">
                            <div class="text-xs text-slate-500">{{ $t('Date') }}</div>
                            <div class="text-sm font-medium">{{ classRecord.date || '-' }}</div>
                        </div>
                        <div class="rounded border border-slate-200 p-3">
                            <div class="text-xs text-slate-500">{{ $t('Start') }}</div>
                            <div class="text-sm font-medium">{{ classRecord.start_time || '-' }}</div>
                        </div>
                        <div class="rounded border border-slate-200 p-3">
                            <div class="text-xs text-slate-500">{{ $t('End') }}</div>
                            <div class="text-sm font-medium">{{ classRecord.end_time || '-' }}</div>
                        </div>
                        <div class="rounded border border-slate-200 p-3">
                            <div class="text-xs text-slate-500">{{ $t('Duration (minutes)') }}</div>
                            <div class="text-sm font-medium">{{ classRecord.duration_minutes ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="mt-4 rounded border border-slate-200 p-3">
                        <div class="text-xs text-slate-500">{{ $t('Attendance') }}</div>
                        <div class="text-sm font-medium">{{ classRecord.attendance_label || '-' }}</div>
                    </div>

                    <div class="mt-4 rounded border border-slate-200 p-3">
                        <div class="text-xs text-slate-500">{{ $t('Comments') }}</div>
                        <div class="text-sm">{{ classRecord.comments || '-' }}</div>
                    </div>

                    <div class="mt-4 rounded border border-slate-200 p-3">
                        <div class="text-sm font-semibold mb-2">{{ $t('Record Details') }}</div>
                        <div v-if="!classRecord.details?.length" class="text-sm text-slate-500">
                            {{ $t('No session details available.') }}
                        </div>
                        <div v-else class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="text-left text-xs uppercase tracking-wide text-slate-600">
                                        <th class="py-2 px-2">{{ $t('Content') }}</th>
                                        <th class="py-2 px-2">{{ $t('Free Content') }}</th>
                                        <th class="py-2 px-2">{{ $t('Activity') }}</th>
                                        <th class="py-2 px-2">{{ $t('Links') }}</th>
                                        <th class="py-2 px-2">{{ $t('Attachment') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="detail in classRecord.details"
                                        :key="detail.id"
                                        class="border-t border-slate-200"
                                    >
                                        <td class="py-2 px-2">{{ detail.content_name || '-' }}</td>
                                        <td class="py-2 px-2">{{ detail.free_content || '-' }}</td>
                                        <td class="py-2 px-2">{{ detail.activity || '-' }}</td>
                                        <td class="py-2 px-2">
                                            <span v-if="!detail.links">-</span>
                                            <div v-else class="flex flex-wrap gap-1">
                                                <a
                                                    v-for="(link, index) in parseLinks(detail.links)"
                                                    :key="`${detail.id}-${index}`"
                                                    :href="link"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    class="text-blue-600 hover:underline"
                                                >
                                                    {{ link }}
                                                </a>
                                            </div>
                                        </td>
                                        <td class="py-2 px-2">
                                            <a
                                                v-if="detail.attachment_url"
                                                :href="detail.attachment_url"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="text-blue-600 hover:underline"
                                            >
                                                {{ detail.attachment_name || $t('View') }}
                                            </a>
                                            <span v-else>-</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end mt-4">
                    <Button
                        type="button"
                        icon="pi pi-arrow-left"
                        :label="$t('Back')"
                        @click="goBack"
                    />
                </div>
            </div>
        </template>
    </PageContainer>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import axios from 'axios';
import Button from 'primevue/button';
import Message from 'primevue/message';
import PageContainer from '@/components/layout/pages/PageContainer.vue';
import { useApiErrorHandler } from '@/composables/useApiErrorHandler';

const { t: $t } = useI18n();
const route = useRoute();
const router = useRouter();
const { handleApiError } = useApiErrorHandler();

const classRecord = ref({});
const loading = ref(false);
const errors = ref({
    general: '',
});

const parseLinks = (links) => {
    if (!links) {
        return [];
    }

    return String(links)
        .split('|')
        .map((item) => item.trim())
        .filter(Boolean);
};

const goBack = () => {
    router.push({ name: 'academics.classes.class-records.list' });
};

const loadClassRecord = async () => {
    loading.value = true;
    errors.value.general = '';

    try {
        const { data } = await axios.post(`/academics/lessons/class-records/${route.params.id}/data`);
        classRecord.value = data?.data?.class_record || {};
    } catch (error) {
        const apiError = handleApiError(error);
        errors.value.general = apiError?.message || $t('Failed to load class record form data.');
    } finally {
        loading.value = false;
    }
};

onMounted(loadClassRecord);
</script>
