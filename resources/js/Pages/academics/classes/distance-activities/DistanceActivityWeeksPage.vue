<template>
    <PageContainer>
        <template #body>
            <div class="space-y-6">
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">
                        {{ $t('Distance Activities') }}
                    </h1>
                    <Select
                        v-if="languageLevelOptions.length"
                        v-model="selectedLanguageLevelId"
                        :options="languageLevelOptions"
                        option-label="label"
                        option-value="value"
                        :placeholder="$t('All Language Levels')"
                        show-clear
                        class="w-full md:w-56"
                        size="small"
                    />
                </div>

                <Message v-if="loadError" severity="error" size="small" variant="simple">
                    {{ loadError }}
                </Message>

                <div v-if="isLoading" class="flex justify-center py-24">
                    <ProgressSpinner style="width: 48px; height: 48px;" />
                </div>

                <div v-else-if="weeks.length === 0" class="py-24 text-center text-slate-500 dark:text-slate-400">
                    {{ $t('No weeks found for the selected level.') }}
                </div>

                <div v-else class="flex flex-col items-center gap-8 py-6">
                    <!-- Circle map -->
                    <div
                        class="relative"
                        :style="{ width: `${diameter}px`, height: `${diameter}px` }"
                    >
                        <!-- Orbit ring -->
                        <div
                            class="absolute inset-0 rounded-full border-2 border-dashed border-slate-200 pointer-events-none dark:border-slate-700"
                        />

                        <!-- Week bubbles -->
                        <button
                            v-for="(week, index) in weeks"
                            :key="week.id"
                            type="button"
                            :class="[
                                'absolute flex flex-col items-center justify-center rounded-full shadow-md transition',
                                'hover:scale-110 focus:outline-none focus:ring-2 focus:ring-offset-2',
                                bubbleClass(week.status),
                            ]"
                            :style="bubbleStyle(index, weeks.length)"
                            :title="week.title || `${$t('Week')} ${week.week_number}`"
                            @click="openWeek(week)"
                        >
                            <span class="text-[11px] font-bold leading-tight">
                                {{ week.week_number }}
                            </span>
                            <span
                                v-if="week.title"
                                class="mt-0.5 max-w-[52px] overflow-hidden text-ellipsis whitespace-nowrap px-1 text-[9px] leading-tight opacity-80"
                            >
                                {{ week.title }}
                            </span>
                        </button>

                        <!-- Centre label -->
                        <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
                            <span class="text-sm font-medium text-slate-400 dark:text-slate-500">
                                {{ weeks.length }} {{ $t('weeks') }}
                            </span>
                        </div>
                    </div>

                    <!-- Legend -->
                    <div class="flex flex-wrap items-center justify-center gap-4 text-sm text-slate-600 dark:text-slate-300">
                        <div class="flex items-center gap-1.5">
                            <span class="inline-block size-3 rounded-full bg-red-400" />
                            {{ $t('No progress') }}
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="inline-block size-3 rounded-full bg-yellow-400" />
                            {{ $t('In progress') }}
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="inline-block size-3 rounded-full bg-green-500" />
                            {{ $t('All completed') }}
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </PageContainer>
</template>

<script setup>
import { ref, watch, computed, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import axios from 'axios';
import PageContainer from '@/components/layout/pages/PageContainer.vue';
import Select from 'primevue/select';
import Message from 'primevue/message';
import ProgressSpinner from 'primevue/progressspinner';

const { t: $t } = useI18n();
const router = useRouter();
const route = useRoute();

const weeks = ref([]);
const isLoading = ref(false);
const loadError = ref(null);
const languageLevelOptions = ref([]);
const selectedLanguageLevelId = ref(route.query.language_level_id ? parseInt(route.query.language_level_id) : null);

const BUBBLE_SIZE = 64;
const ORBIT_RADIUS = 160;
const diameter = computed(() => (ORBIT_RADIUS + BUBBLE_SIZE) * 2);

const bubbleStyle = (index, total) => {
    const angle = (2 * Math.PI * index) / total - Math.PI / 2;
    const cx = diameter.value / 2;
    const cy = diameter.value / 2;
    const x = cx + Math.cos(angle) * ORBIT_RADIUS - BUBBLE_SIZE / 2;
    const y = cy + Math.sin(angle) * ORBIT_RADIUS - BUBBLE_SIZE / 2;

    return {
        width: `${BUBBLE_SIZE}px`,
        height: `${BUBBLE_SIZE}px`,
        left: `${x}px`,
        top: `${y}px`,
    };
};

const bubbleClass = (status) => {
    if (status === 'completed') {
        return 'bg-green-100 text-green-800 ring-1 ring-green-300 hover:bg-green-200 focus:ring-green-500 dark:bg-green-900/40 dark:text-green-300 dark:ring-green-700';
    }

    if (status === 'started') {
        return 'bg-yellow-100 text-yellow-800 ring-1 ring-yellow-300 hover:bg-yellow-200 focus:ring-yellow-500 dark:bg-yellow-900/40 dark:text-yellow-300 dark:ring-yellow-700';
    }

    return 'bg-red-100 text-red-800 ring-1 ring-red-300 hover:bg-red-200 focus:ring-red-500 dark:bg-red-900/40 dark:text-red-300 dark:ring-red-700';
};

const openWeek = (week) => {
    router.push({
        name: 'academics.classes.distance-activities.list',
        query: {
            week_id: week.id,
            week_title: week.title || `${$t('Week')} ${week.week_number}`,
            ...(selectedLanguageLevelId.value ? { language_level_id: selectedLanguageLevelId.value } : {}),
        },
    });
};

const fetchWeeks = async () => {
    isLoading.value = true;
    loadError.value = null;

    try {
        const params = {};

        if (selectedLanguageLevelId.value) {
            params.language_level_id = selectedLanguageLevelId.value;
        }

        const response = await axios.get('/academics/lessons/distance-activities/weeks', { params });
        weeks.value = response.data?.data?.weeks ?? [];
    } catch {
        loadError.value = $t('Failed to load weeks.');
    } finally {
        isLoading.value = false;
    }
};

onMounted(async () => {
    try {
        const response = await axios.get('/academics/lessons/distance-activities/filter-options');
        languageLevelOptions.value = response.data?.data?.language_levels ?? [];

        if (languageLevelOptions.value.length > 0 && selectedLanguageLevelId.value === null) {
            selectedLanguageLevelId.value = languageLevelOptions.value[0].value;
            // fetchWeeks will be triggered by the watch below
            return;
        }
    } catch {
        // Filter options unavailable — filter remains hidden.
    }

    fetchWeeks();
});

watch(selectedLanguageLevelId, () => {
    fetchWeeks();
});
</script>
