<template>
    <PageContainer>
        <template #body>
            <div class="space-y-6">
                <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                    <div class="space-y-2">
                        <div class="text-xs uppercase tracking-wide text-slate-500">
                            {{ $t('Distance Activity') }} #{{ distanceActivity.id || '—' }}
                        </div>
                        <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">
                            {{ distanceActivity.title || $t('Distance Activity') }}
                        </h1>
                        <div class="flex flex-col gap-2 text-sm text-slate-600 dark:text-slate-300">
                            <span>{{ $t('Teacher') }}: {{ distanceActivity.teacher_name || '—' }}</span>
                            <span>{{ $t('Course') }}: {{ distanceActivity.course_name || '—' }}</span>
                            <div class="flex flex-wrap items-center gap-2">
                                <span>{{ $t('Status') }}:</span>
                                <Tag
                                    :value="distanceActivity.display_status || $t('Pending')"
                                    :severity="statusSeverity(distanceActivity.status)"
                                />
                                <button
                                    v-if="!isStudentView"
                                    type="button"
                                    class="inline-flex items-center gap-2 rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-xs font-semibold text-sky-700 transition hover:bg-sky-100 dark:border-sky-500/30 dark:bg-sky-500/10 dark:text-sky-300 dark:hover:bg-sky-500/20"
                                    @click="openGlobalProgressDialog"
                                >
                                    <i class="pi pi-chart-line text-[11px]" />
                                    <span>{{ $t('Progress') }}: {{ distanceActivity.progress || '0/0' }} {{ $t('completed') }}</span>
                                </button>
                            </div>
                        </div>
                        <p v-if="distanceActivity.comments" class="text-sm text-slate-600 dark:text-slate-300">
                            {{ distanceActivity.comments }}
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <Button
                            type="button"
                            icon="pi pi-refresh"
                            severity="secondary"
                            outlined
                            :label="$t('Refresh')"
                            :loading="isLoading"
                            @click="fetchDistanceActivity"
                        />
                        <Button
                            type="button"
                            icon="pi pi-arrow-left"
                            :label="$t('Back to distance activities')"
                            @click="goBack"
                        />
                    </div>
                </div>

                <Message v-if="loadError" severity="error" size="small" variant="simple">
                    {{ loadError }}
                </Message>

                <div v-if="isLoading" class="flex justify-center py-16">
                    <ProgressSpinner style="width: 40px; height: 40px" stroke-width="4" />
                </div>

                <div v-else class="space-y-4">
                    <div
                        v-for="detail in distanceActivity.details || []"
                        :key="detail.id"
                        class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-950"
                    >
                        <div class="flex flex-col gap-4">
                            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                            {{ $t('Task') }} {{ detail.sequence }}
                                        </span>
                                        <Tag
                                            :value="detailStatusLabel(detail)"
                                            :severity="detailStatusSeverity(detail)"
                                        />
                                        <button
                                            v-if="!isStudentView"
                                            type="button"
                                            class="inline-flex items-center gap-2 rounded-full border border-sky-200 bg-sky-50 px-3 py-1.5 text-xs font-semibold text-sky-700 transition hover:bg-sky-100 dark:border-sky-500/30 dark:bg-sky-500/10 dark:text-sky-300 dark:hover:bg-sky-500/20"
                                            @click="openTaskProgressDialog(detail)"
                                        >
                                            <i class="pi pi-users text-[11px]" />
                                            <span>{{ detail.completed_students_count || 0 }}/{{ detail.assigned_students_count || 0 }}</span>
                                        </button>
                                    </div>
                                    <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                                        {{ detail.activity }}
                                    </h2>
                                    <div class="space-y-1 text-sm text-slate-600 dark:text-slate-300">
                                        <div>{{ $t('Content') }}: {{ detail.content_name || '—' }}</div>
                                        <div>{{ $t('Type') }}: {{ detail.display_type }}</div>
                                        <div v-if="detail.free_content">{{ $t('Free Content') }}: {{ detail.free_content }}</div>
                                        <div v-if="parseLinks(detail.links).length" class="space-y-1">
                                            <div>{{ $t('Links') }}:</div>
                                            <div
                                                v-for="(link, index) in parseLinks(detail.links)"
                                                :key="`${detail.id}-link-text-${index}`"
                                            >
                                                <a
                                                    href="#"
                                                    class="text-blue-600 underline dark:text-blue-400 break-all"
                                                    @click.prevent="openLink(detail, link)"
                                                >
                                                    {{ link }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex flex-col items-start gap-3 lg:items-end">
                                    <div class="flex flex-wrap gap-2 lg:justify-end">
                                        <Button
                                            v-for="(link, index) in parseLinks(detail.links)"
                                            :key="`${detail.id}-link-${index}`"
                                            type="button"
                                            icon="pi pi-external-link"
                                            severity="secondary"
                                            outlined
                                            :label="$t('Open Link')"
                                            @click="openLink(detail, link)"
                                        />
                                        <Button
                                            v-if="detail.study_material_url"
                                            type="button"
                                            icon="pi pi-file"
                                            severity="info"
                                            outlined
                                            :label="detail.study_material_name || $t('Study Material')"
                                            @click="openStudyMaterial(detail.study_material_url)"
                                        />
                                        <Button
                                            v-if="isStudentView"
                                            type="button"
                                            icon="pi pi-check"
                                            :severity="detail.completed ? 'success' : 'primary'"
                                            :disabled="detail.completed || isDetailLocked(detail) || !canMarkCompleted(detail)"
                                            :loading="Boolean(detailLoadingMap[detail.id]?.complete)"
                                            :label="detail.completed ? $t('Completed') : $t('Mark Completed')"
                                            @click="markCompleted(detail)"
                                        />
                                    </div>

                                    <div
                                        v-if="isStudentView && detail.type === 'video' && detail.video_opened_at && isDetailLocked(detail) && lockCountdown(detail)"
                                        class="mt-3 rounded-3xl border border-slate-200 bg-[radial-gradient(circle_at_top,_rgba(251,191,36,0.18),_transparent_55%),linear-gradient(135deg,_rgba(255,251,235,1),_rgba(255,247,237,1))] px-4 py-5 text-center shadow-sm dark:border-slate-700 dark:bg-[radial-gradient(circle_at_top,_rgba(245,158,11,0.16),_transparent_45%),linear-gradient(135deg,_rgba(30,41,59,0.9),_rgba(15,23,42,0.95))]"
                                    >
                                        <div class="mx-auto flex w-full max-w-[9.5rem] flex-col items-center">
                                            <div class="relative flex h-28 w-28 items-center justify-center">
                                                <svg class="h-28 w-28 -rotate-90" viewBox="0 0 120 120" aria-hidden="true">
                                                    <circle
                                                        cx="60"
                                                        cy="60"
                                                        r="48"
                                                        fill="none"
                                                        class="stroke-slate-200 dark:stroke-slate-700"
                                                        stroke-width="10"
                                                    />
                                                    <circle
                                                        cx="60"
                                                        cy="60"
                                                        r="48"
                                                        fill="none"
                                                        class="stroke-amber-500 dark:stroke-amber-300"
                                                        stroke-width="10"
                                                        stroke-linecap="round"
                                                        :stroke-dasharray="countdownCircumference"
                                                        :stroke-dashoffset="countdownStrokeOffset(detail)"
                                                    />
                                                </svg>
                                                <div class="absolute inset-0 flex flex-col items-center justify-center">
                                                    <span class="max-w-[4.5rem] text-center text-[9px] font-semibold uppercase leading-tight tracking-[0.14em] text-slate-500 dark:text-slate-400">
                                                        {{ $t('Ready In') }}
                                                    </span>
                                                    <span class="mt-1 text-2xl font-semibold tabular-nums leading-none text-slate-900 dark:text-slate-100">
                                                        {{ lockCountdown(detail) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="mt-3 text-[11px] font-medium uppercase tracking-[0.18em] text-amber-700 dark:text-amber-300">
                                                {{ $t('Video In Progress') }}
                                            </div>
                                            <div class="mt-1 text-xs text-slate-600 dark:text-slate-300">
                                                {{ $t('Complete after the timer finishes') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <Message
                                v-if="isStudentView && (isDetailLocked(detail) || completionRequirementMessage(detail))"
                                severity="warn"
                                size="small"
                                variant="simple"
                            >
                                {{ completionRequirementMessage(detail) || detail.completion_lock_message || $t('This task can be completed after') }}
                                <template v-if="!completionRequirementMessage(detail) && detail.next_completion_locked_until">
                                    {{ ` ${formatDateTime(detail.next_completion_locked_until)}.` }}
                                </template>
                            </Message>

                            <div
                                v-if="detail.type === 'production'"
                                class="rounded-lg border border-dashed border-slate-200 p-4 dark:border-slate-700"
                            >
                                <div v-if="isStudentView">
                                    <div class="mb-3 text-sm font-medium text-slate-700 dark:text-slate-200">
                                        {{ $t('Student Production') }}
                                    </div>
                                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                                        <div class="space-y-3">
                                            <FileUpload
                                                :id="`distance-activity-file-${detail.id}`"
                                                :button-label="$t('Upload file')"
                                                accept=".pdf,.doc,.docx,.xls,.xlsx,.txt,image/*"
                                                :max-file-size="10240000"
                                                empty-icon="pi pi-file"
                                                :empty-message="$t('Select a production file')"
                                                status-class="px-2 py-1 rounded-full bg-sky-600 text-xs font-semibold text-white"
                                                @update:modelValue="setProductionFile(detail.id, 'file', $event)"
                                            />

                                            <div
                                                v-for="media in detail.student_production_media.filter((item) => item.media_type === 'file')"
                                                :key="media.id"
                                            >
                                                <a
                                                    :href="media.url"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    class="text-sm text-blue-600 underline dark:text-blue-400"
                                                >
                                                    {{ media.name }}
                                                </a>
                                            </div>
                                        </div>

                                        <div class="space-y-3">
                                            <AudioRecorder
                                                :label="$t('Record student audio')"
                                                @update:modelValue="setProductionFile(detail.id, 'audio', $event)"
                                                @error="onAudioError"
                                            />

                                            <div
                                                v-for="media in detail.student_production_media.filter((item) => item.media_type === 'audio')"
                                                :key="media.id"
                                            >
                                                <p class="mb-2 text-xs text-slate-500">{{ media.name }}</p>
                                                <audio :src="media.url" controls class="w-full" />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-4 flex justify-end">
                                        <Button
                                            type="button"
                                            icon="pi pi-save"
                                            severity="success"
                                            :loading="Boolean(detailLoadingMap[detail.id]?.upload)"
                                            :disabled="!canSaveProduction(detail.id)"
                                            :label="$t('Save Production')"
                                            @click="saveProduction(detail)"
                                        />
                                    </div>
                                </div>

                                <div v-else>
                                    <div class="mb-3 text-sm font-medium text-slate-700 dark:text-slate-200">
                                        {{ $t('Student Submissions') }}
                                    </div>
                                    <div v-if="detail.student_submissions?.length" class="space-y-3">
                                        <div
                                            v-for="submission in paginatedStudentSubmissions(detail)"
                                            :key="submission.id"
                                            class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-900"
                                        >
                                            <div class="flex flex-col gap-2 md:flex-row md:items-start md:justify-between">
                                                <div class="space-y-1">
                                                    <div class="font-medium text-slate-900 dark:text-slate-100">
                                                        {{ submission.student_name }}
                                                    </div>
                                                    <div class="text-xs text-slate-500 dark:text-slate-400">
                                                        {{ $t('Status') }}:
                                                        <Tag
                                                            :value="submission.completed ? $t('Completed') : $t('Pending')"
                                                            :severity="submission.completed ? 'success' : 'warn'"
                                                        />
                                                    </div>
                                                </div>
                                                <div v-if="submission.completed_at" class="text-xs text-slate-500 dark:text-slate-400">
                                                    {{ $t('Completed At') }}: {{ formatDateTime(submission.completed_at) }}
                                                </div>
                                            </div>
                                            <div
                                                v-if="canModerateTaskCompletion"
                                                class="mt-3 flex flex-wrap gap-2"
                                            >
                                                <Button
                                                    type="button"
                                                    size="small"
                                                    :severity="submission.completed ? 'warn' : 'success'"
                                                    :icon="submission.completed ? 'pi pi-undo' : 'pi pi-check'"
                                                    :label="submission.completed ? $t('Mark Pending') : $t('Mark Completed')"
                                                    :loading="Boolean(adminActionLoading[`detail-student-${submission.id}`])"
                                                    @click="toggleManagedCompletion(submission)"
                                                />
                                            </div>
                                            <div v-if="submission.media?.length" class="mt-3 space-y-2">
                                                <div
                                                    v-for="media in submission.media"
                                                    :key="media.id"
                                                    class="rounded-lg border border-slate-200 bg-white p-3 dark:border-slate-700 dark:bg-slate-950"
                                                >
                                                    <div class="mb-2 text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                                        {{ media.media_type === 'audio' ? $t('Audio') : $t('File') }}
                                                    </div>
                                                    <template v-if="media.media_type === 'audio'">
                                                        <p class="mb-2 text-sm text-slate-700 dark:text-slate-300">{{ media.name }}</p>
                                                        <audio :src="media.url" controls class="w-full" />
                                                    </template>
                                                    <template v-else>
                                                        <a
                                                            :href="media.url"
                                                            target="_blank"
                                                            rel="noopener noreferrer"
                                                            class="text-sm text-blue-600 underline dark:text-blue-400"
                                                        >
                                                            {{ media.name }}
                                                        </a>
                                                    </template>
                                                    <div
                                                        v-if="canDeleteStudentSubmission"
                                                        class="mt-3 flex justify-end"
                                                    >
                                                        <Button
                                                            type="button"
                                                            size="small"
                                                            severity="danger"
                                                            icon="pi pi-trash"
                                                            :label="$t('Delete')"
                                                            :loading="Boolean(adminActionLoading[`media-${media.id}`])"
                                                            @click="confirmDeleteSubmissionMedia(submission, media)"
                                                        />
                                                    </div>
                                                </div>
                                            </div>
                                            <div v-else class="mt-3 text-sm text-slate-500 dark:text-slate-400">
                                                {{ $t('No submissions yet.') }}
                                            </div>
                                        </div>

                                        <Paginator
                                            v-if="detail.student_submissions.length > studentSubmissionRows"
                                            v-model:first="studentSubmissionPagination[detail.id]"
                                            :rows="studentSubmissionRows"
                                            :total-records="detail.student_submissions.length"
                                            template="PrevPageLink PageLinks NextPageLink"
                                        />
                                    </div>
                                    <div v-else class="text-sm text-slate-500 dark:text-slate-400">
                                        {{ $t('No submissions yet.') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="!distanceActivity.details?.length"
                        class="rounded-lg border border-dashed border-slate-200 p-6 text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400"
                    >
                        {{ $t('No distance activity tasks available.') }}
                    </div>
                </div>
            </div>
            
        <Dialog
            v-model:visible="progressDialogVisible"
            modal
            :closable="false"
            :style="{ width: 'min(92vw, 42rem)' }"
            :breakpoints="{ '960px': '92vw', '640px': '96vw' }"
            :dismissable-mask="true"
        >
            <template #header>
                <div class="flex w-full items-center justify-between rounded-lg bg-blue-500 p-4 text-white">
                    <div class="flex items-center gap-3">
                        <i class="pi pi-chart-bar text-lg" />
                        <span class="text-lg font-semibold">{{ progressDialogTitle }}</span>
                    </div>
                    <Button
                        icon="pi pi-times"
                        rounded
                        size="small"
                        severity="info"
                        variant="outlined"
                        class="text-white! border-2! hover:text-gray-800!"
                        @click="progressDialogVisible = false"
                    />
                </div>
            </template>

            <div class="space-y-4">
                <div
                    v-if="progressDialogContext"
                    class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-900"
                >
                    <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
                        {{ progressDialogMode === 'global' ? $t('Week Progress') : $t('Task Progress') }}
                    </div>
                    <div class="mt-2 flex flex-wrap items-center gap-2">
                        <div class="text-base font-semibold text-slate-900 dark:text-slate-100">
                            {{ progressDialogContext.title }}
                        </div>
                        <Tag
                            :value="progressDialogContext.progressLabel"
                            severity="info"
                        />
                    </div>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <button
                            type="button"
                            class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold transition"
                            :class="progressDialogStatusOrder === 'pending'
                                ? 'border-amber-300 bg-amber-100 text-amber-800 dark:border-amber-500/40 dark:bg-amber-500/20 dark:text-amber-200'
                                : 'border-slate-200 bg-white text-slate-500 hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-300 dark:hover:bg-slate-800'"
                            @click="toggleProgressDialogStatusOrder('pending')"
                        >
                            {{ $t('Pending') }}
                        </button>
                        <button
                            type="button"
                            class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold transition"
                            :class="progressDialogStatusOrder === 'completed'
                                ? 'border-emerald-300 bg-emerald-100 text-emerald-800 dark:border-emerald-500/40 dark:bg-emerald-500/20 dark:text-emerald-200'
                                : 'border-slate-200 bg-white text-slate-500 hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-300 dark:hover:bg-slate-800'"
                            @click="toggleProgressDialogStatusOrder('completed')"
                        >
                            {{ $t('Completed') }}
                        </button>
                    </div>
                </div>

                <div v-if="paginatedProgressEntries.length" class="max-h-[55vh] space-y-3 overflow-y-auto pr-1">
                    <div
                        v-for="entry in paginatedProgressEntries"
                        :key="entry.id"
                        class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-950"
                    >
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <div class="font-medium text-slate-900 dark:text-slate-100">
                                        {{ entry.student_name }}
                                    </div>
                                    <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                        {{ $t('Student') }}
                                    </div>
                                </div>

                                <div class="flex flex-col items-start gap-2 sm:items-end">
                                    <Tag
                                        :value="entry.completed ? $t('Completed') : $t('Pending')"
                                        :severity="entry.completed ? 'success' : 'warn'"
                                    />
                                    <div v-if="entry.completed_at" class="text-xs text-slate-500 dark:text-slate-400">
                                        {{ $t('Completed At') }}: {{ formatDateTime(entry.completed_at) }}
                                    </div>
                                    <div
                                        v-else
                                        class="text-xs text-slate-500 dark:text-slate-400"
                                    >
                                        {{ $t('Awaiting completion') }}
                                    </div>
                                    <Button
                                        v-if="progressDialogMode === 'task' && canModerateTaskCompletion"
                                        type="button"
                                        size="small"
                                        :severity="entry.completed ? 'warn' : 'success'"
                                        :icon="entry.completed ? 'pi pi-undo' : 'pi pi-check'"
                                        :label="entry.completed ? $t('Mark Pending') : $t('Mark Completed')"
                                        :loading="Boolean(adminActionLoading[`detail-student-${entry.id}`])"
                                        @click="toggleManagedCompletion(entry)"
                                    />
                                </div>
                        </div>
                    </div>
                </div>

                <div v-else class="rounded-2xl border border-dashed border-slate-200 p-6 text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
                    {{ $t('No students assigned.') }}
                </div>

                <Paginator
                    v-if="orderedProgressDialogEntries.length > progressDialogRows"
                    v-model:first="progressDialogFirst"
                    :rows="progressDialogRows"
                    :total-records="orderedProgressDialogEntries.length"
                    template="PrevPageLink PageLinks NextPageLink"
                />
            </div>
        </Dialog>

        <Dialog
            v-model:visible="deleteSubmissionDialogVisible"
            modal
            :closable="false"
            :style="{ width: 'min(92vw, 30rem)' }"
            :breakpoints="{ '960px': '92vw', '640px': '96vw' }"
            :dismissable-mask="true"
        >
            <template #header>
                <div class="flex w-full items-center justify-between rounded-lg bg-red-500 p-4 text-white">
                    <div class="flex items-center gap-3">
                        <i class="pi pi-exclamation-triangle text-lg" />
                        <span class="text-lg font-semibold">{{ $t('Delete Confirmation') }}</span>
                    </div>
                    <Button
                        icon="pi pi-times"
                        rounded
                        size="small"
                        severity="info"
                        variant="outlined"
                        class="text-white! border-2! hover:text-gray-800!"
                        @click="closeDeleteSubmissionDialog"
                    />
                </div>
            </template>

            <div class="space-y-4">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                    {{ $t('Are you sure you want to delete this submission?') }}
                </div>

                <div
                    v-if="deleteSubmissionTarget"
                    class="rounded-2xl border border-dashed border-slate-200 p-4 text-sm dark:border-slate-700"
                >
                    <div class="font-medium text-slate-900 dark:text-slate-100">
                        {{ deleteSubmissionTarget.submission.student_name }}
                    </div>
                    <div class="mt-1 text-slate-500 dark:text-slate-400">
                        {{ deleteSubmissionTarget.media.name }}
                    </div>
                </div>

                <div class="flex justify-end gap-2">
                    <Button
                        type="button"
                        severity="secondary"
                        outlined
                        :label="$t('Cancel')"
                        @click="closeDeleteSubmissionDialog"
                    />
                    <Button
                        type="button"
                        severity="danger"
                        icon="pi pi-trash"
                        :label="$t('Delete')"
                        :loading="Boolean(deleteSubmissionTarget && adminActionLoading[`media-${deleteSubmissionTarget.media.id}`])"
                        @click="deleteSubmissionMedia"
                    />
                </div>
            </div>
        </Dialog>
        </template>
    </PageContainer>
</template>

<script setup>
import { computed, onBeforeUnmount, reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useToast } from 'primevue/usetoast';
import axios from 'axios';
import PageContainer from '@/components/layout/pages/PageContainer.vue';
import Button from 'primevue/button';
import FileUpload from '@/components/form/FileUpload.vue';
import AudioRecorder from '@/components/form/AudioRecorder.vue';
import Message from 'primevue/message';
import ProgressSpinner from 'primevue/progressspinner';
import Tag from 'primevue/tag';
import Dialog from 'primevue/dialog';
import Paginator from 'primevue/paginator';
import { useApiErrorHandler } from '@/composables/useApiErrorHandler';
import { usePermissions } from '@/composables/usePermissions';

const { t: $t, locale } = useI18n();
const route = useRoute();
const router = useRouter();
const toast = useToast();
const { handleApiError } = useApiErrorHandler();
const { can } = usePermissions();

const distanceActivityId = computed(() => route.params.id);
const isLoading = ref(true);
const loadError = ref('');
const distanceActivity = reactive({
    id: null,
    title: '',
    teacher_name: '',
    course_name: '',
    comments: '',
    status: 'pending',
    display_status: '',
    progress: '0/0',
    progress_completed: 0,
    progress_total: 0,
    viewer_mode: 'student',
    student_assignments: [],
    details: [],
});
const detailLoadingMap = reactive({});
const pendingProduction = reactive({});
const studentSubmissionPagination = reactive({});
const adminActionLoading = reactive({});
const nowMs = ref(Date.now());
let unlockIntervalId = null;
const countdownRadius = 48;
const countdownCircumference = 2 * Math.PI * countdownRadius;
const progressDialogVisible = ref(false);
const progressDialogMode = ref('task');
const progressDialogContext = ref(null);
const progressDialogDetailId = ref(null);
const deleteSubmissionDialogVisible = ref(false);
const deleteSubmissionTarget = ref(null);
const progressDialogFirst = ref(0);
const progressDialogRows = 3;
const studentSubmissionRows = 3;
const progressDialogStatusOrder = ref('');

const applyDistanceActivityData = (activity = {}) => {
    distanceActivity.id = activity.id ?? null;
    distanceActivity.title = activity.title ?? '';
    distanceActivity.teacher_name = activity.teacher_name ?? '';
    distanceActivity.course_name = activity.course_name ?? '';
    distanceActivity.comments = activity.comments ?? '';
    distanceActivity.status = activity.status ?? 'pending';
    distanceActivity.display_status = activity.display_status ?? '';
    distanceActivity.progress = activity.progress ?? '0/0';
    distanceActivity.progress_completed = activity.progress_completed ?? 0;
    distanceActivity.progress_total = activity.progress_total ?? 0;
    distanceActivity.viewer_mode = activity.viewer_mode ?? 'student';
    distanceActivity.student_assignments = Array.isArray(activity.student_assignments) ? activity.student_assignments : [];
    distanceActivity.details = Array.isArray(activity.details) ? activity.details : [];

    distanceActivity.details.forEach((detail) => {
        ensurePendingProduction(detail.id);
        if (studentSubmissionPagination[detail.id] === undefined) {
            studentSubmissionPagination[detail.id] = 0;
        }
    });

    syncProgressDialogContext();
    nowMs.value = Date.now();
    syncUnlockTimer();
};

const ensurePendingProduction = (detailId) => {
    if (!pendingProduction[detailId]) {
        pendingProduction[detailId] = {
            file: null,
            audio: null,
        };
    }

    if (!detailLoadingMap[detailId]) {
        detailLoadingMap[detailId] = {
            upload: false,
            complete: false,
            video: false,
        };
    }
};

const fetchDistanceActivity = async () => {
    isLoading.value = true;
    loadError.value = '';

    try {
        const response = await axios.post(`/academics/lessons/distance-activities/${distanceActivityId.value}/data`);
        applyDistanceActivityData(response.data?.data?.distance_activity || {});
    } catch (error) {
        loadError.value = handleApiError(error)?.message || $t('Failed to load distance activity.');
    } finally {
        isLoading.value = false;
    }
};

const goBack = () => {
    router.push({ name: 'academics.classes.distance-activities.list' });
};

const isStudentView = computed(() => distanceActivity.viewer_mode === 'student');
const canModerateTaskCompletion = computed(() => can('reset distance activity completion'));
const canDeleteStudentSubmission = computed(() => can('delete distance activity submissions'));
const progressDialogTitle = computed(() => progressDialogMode.value === 'global'
    ? $t('Week Progress')
    : $t('Assigned Students'));
const progressDialogEntries = computed(() => progressDialogContext.value?.entries || []);
const orderedProgressDialogEntries = computed(() => {
    if (!progressDialogStatusOrder.value) {
        return progressDialogEntries.value;
    }

    const prioritizedCompleted = progressDialogStatusOrder.value === 'completed';

    return [...progressDialogEntries.value].sort((left, right) => {
        const leftPriority = Boolean(left.completed) === prioritizedCompleted ? 0 : 1;
        const rightPriority = Boolean(right.completed) === prioritizedCompleted ? 0 : 1;

        if (leftPriority !== rightPriority) {
            return leftPriority - rightPriority;
        }

        return String(left.student_name || '').localeCompare(String(right.student_name || ''));
    });
});
const paginatedProgressEntries = computed(() =>
    orderedProgressDialogEntries.value.slice(progressDialogFirst.value, progressDialogFirst.value + progressDialogRows)
);

const statusSeverity = (status) => {
    if (status === 'completed') {
        return 'success';
    }

    if (status === 'started') {
        return 'info';
    }

    return 'warn';
};

const detailStatusSeverity = (detail) => {
    if (detail.completed) {
        return 'success';
    }

    if (!isStudentView.value && detail.completed_students_count > 0) {
        return 'info';
    }

    return 'warn';
};

const detailStatusLabel = (detail) => {
    if (detail.completed) {
        return $t('Completed');
    }

    if (!isStudentView.value && detail.completed_students_count > 0) {
        return $t('Started');
    }

    return $t('Pending');
};

const parseLinks = (links) => {
    if (!links) {
        return [];
    }

    return String(links)
        .split(/\r?\n|\|/)
        .map((value) => value.trim())
        .filter(Boolean);
};

const syncProgressDialogContext = () => {
    if (!progressDialogVisible.value || !progressDialogContext.value) {
        return;
    }

    if (progressDialogMode.value === 'global') {
        progressDialogContext.value = {
            title: distanceActivity.title || $t('Distance Activity'),
            progressLabel: distanceActivity.progress || '0/0',
            entries: distanceActivity.student_assignments || [],
        };
        return;
    }

    const detail = (distanceActivity.details || []).find((item) => item.id === progressDialogDetailId.value);
    if (!detail) {
        progressDialogVisible.value = false;
        progressDialogContext.value = null;
        progressDialogDetailId.value = null;
        return;
    }

    progressDialogContext.value = {
        title: detail.activity,
        progressLabel: `${detail.completed_students_count || 0}/${detail.assigned_students_count || 0}`,
        entries: detail.student_submissions || [],
    };
};

const openTaskProgressDialog = (detail) => {
    progressDialogFirst.value = 0;
    progressDialogStatusOrder.value = '';
    progressDialogMode.value = 'task';
    progressDialogDetailId.value = detail.id;
    progressDialogContext.value = {
        title: detail.activity,
        progressLabel: `${detail.completed_students_count || 0}/${detail.assigned_students_count || 0}`,
        entries: detail.student_submissions || [],
    };
    progressDialogVisible.value = true;
};

const openGlobalProgressDialog = () => {
    progressDialogFirst.value = 0;
    progressDialogStatusOrder.value = '';
    progressDialogMode.value = 'global';
    progressDialogDetailId.value = null;
    progressDialogContext.value = {
        title: distanceActivity.title || $t('Distance Activity'),
        progressLabel: distanceActivity.progress || '0/0',
        entries: distanceActivity.student_assignments || [],
    };
    progressDialogVisible.value = true;
};

const toggleProgressDialogStatusOrder = (status) => {
    progressDialogFirst.value = 0;
    progressDialogStatusOrder.value = progressDialogStatusOrder.value === status ? '' : status;
};

const openInNewTab = (url) => {
    const anchor = document.createElement('a');
    anchor.href = url;
    anchor.target = '_blank';
    anchor.rel = 'noopener noreferrer';
    anchor.style.display = 'none';

    document.body.appendChild(anchor);
    anchor.click();
    document.body.removeChild(anchor);
};

const openStudyMaterial = (url) => {
    openInNewTab(url);
};

const openLink = async (detail, link) => {
    openInNewTab(link);

    if (isStudentView.value && detail.type === 'video') {
        ensurePendingProduction(detail.id);
        detailLoadingMap[detail.id].video = true;

        try {
            await axios.post(`/academics/lessons/distance-activities/details/${detail.id}/video-open`);
            await fetchDistanceActivity();
        } catch (error) {
            const apiError = handleApiError(error);
            toast.add({
                severity: 'error',
                summary: $t('Error'),
                detail: apiError?.message || $t('Unable to open the video activity.'),
                life: 4000,
            });
        } finally {
            detailLoadingMap[detail.id].video = false;
        }

        return;
    }
};

const setProductionFile = (detailId, type, file) => {
    ensurePendingProduction(detailId);
    pendingProduction[detailId][type] = file;
};

const canSaveProduction = (detailId) => Boolean(
    pendingProduction[detailId]?.file || pendingProduction[detailId]?.audio
);

const hasSavedProduction = (detail) => Array.isArray(detail?.student_production_media)
    && detail.student_production_media.length > 0;

const completionRequirementMessage = (detail) => {
    if (detail?.completed) {
        return '';
    }

    if (detail?.type === 'video' && !detail?.video_opened_at) {
        return $t('Open the video link before marking this task as completed.');
    }

    if (detail?.type === 'production' && !hasSavedProduction(detail)) {
        return $t('Save your production before marking this task as completed.');
    }

    return '';
};

const canMarkCompleted = (detail) => !completionRequirementMessage(detail);

const paginatedStudentSubmissions = (detail) => {
    const first = studentSubmissionPagination[detail.id] || 0;
    return (detail.student_submissions || []).slice(first, first + studentSubmissionRows);
};

const confirmDeleteSubmissionMedia = (submission, media) => {
    deleteSubmissionTarget.value = { submission, media };
    deleteSubmissionDialogVisible.value = true;
};

const closeDeleteSubmissionDialog = () => {
    deleteSubmissionDialogVisible.value = false;
    deleteSubmissionTarget.value = null;
};

const toggleManagedCompletion = async (entry) => {
    adminActionLoading[`detail-student-${entry.id}`] = true;

    try {
        const completed = !entry.completed;
        const response = await axios.post(`/academics/lessons/distance-activities/detail-students/${entry.id}/complete`, {
            completed,
        });

        applyDistanceActivityData(response.data?.data?.distance_activity || {});

        toast.add({
            severity: 'success',
            summary: $t('Success'),
            detail: completed ? $t('Task completed successfully.') : $t('Task marked as pending successfully.'),
            life: 3000,
        });
    } catch (error) {
        const apiError = handleApiError(error);
        toast.add({
            severity: 'error',
            summary: $t('Error'),
            detail: apiError?.message || $t('Unable to update the task status.'),
            life: 4000,
        });
    } finally {
        adminActionLoading[`detail-student-${entry.id}`] = false;
    }
};

const deleteSubmissionMedia = async () => {
    if (!deleteSubmissionTarget.value) {
        return;
    }

    const { submission, media } = deleteSubmissionTarget.value;
    adminActionLoading[`media-${media.id}`] = true;

    try {
        const response = await axios.post(
            `/academics/lessons/distance-activities/detail-students/${submission.id}/submissions/${media.id}/destroy`
        );

        applyDistanceActivityData(response.data?.data?.distance_activity || {});
        closeDeleteSubmissionDialog();

        toast.add({
            severity: 'success',
            summary: $t('Success'),
            detail: $t('Submission deleted successfully.'),
            life: 3000,
        });
    } catch (error) {
        const apiError = handleApiError(error);
        toast.add({
            severity: 'error',
            summary: $t('Error'),
            detail: apiError?.message || $t('Unable to delete the submission.'),
            life: 4000,
        });
    } finally {
        adminActionLoading[`media-${media.id}`] = false;
    }
};

const saveProduction = async (detail) => {
    ensurePendingProduction(detail.id);
    detailLoadingMap[detail.id].upload = true;

    try {
        const payload = new FormData();
        if (pendingProduction[detail.id].file) {
            payload.append('student_production_file', pendingProduction[detail.id].file);
        }
        if (pendingProduction[detail.id].audio) {
            payload.append('student_production_audio', pendingProduction[detail.id].audio);
        }

        const response = await axios.post(`/academics/lessons/distance-activities/details/${detail.id}/student-production`, payload, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });

        pendingProduction[detail.id].file = null;
        pendingProduction[detail.id].audio = null;

        applyDistanceActivityData(response.data?.data?.distance_activity || {});

        toast.add({
            severity: 'success',
            summary: $t('Success'),
            detail: $t('Student production saved successfully.'),
            life: 3000,
        });
    } catch (error) {
        const apiError = handleApiError(error);
        toast.add({
            severity: 'error',
            summary: $t('Error'),
            detail: apiError?.message || $t('Unable to save student production.'),
            life: 4000,
        });
    } finally {
        detailLoadingMap[detail.id].upload = false;
    }
};

const markCompleted = async (detail) => {
    ensurePendingProduction(detail.id);
    detailLoadingMap[detail.id].complete = true;

    try {
        const response = await axios.post(`/academics/lessons/distance-activities/details/${detail.id}/complete`, {
            completed: true,
        });

        applyDistanceActivityData(response.data?.data?.distance_activity || {});

        toast.add({
            severity: 'success',
            summary: $t('Success'),
            detail: $t('Task completed successfully.'),
            life: 3000,
        });
    } catch (error) {
        const apiError = handleApiError(error);
        toast.add({
            severity: 'error',
            summary: $t('Error'),
            detail: apiError?.message || $t('Unable to complete the task.'),
            life: 4000,
        });
    } finally {
        detailLoadingMap[detail.id].complete = false;
    }
};

const isDetailLocked = (detail) => {
    if (detail?.completion_lock_message && !detail?.next_completion_locked_until) {
        return true;
    }

    if (!detail?.next_completion_locked_until) {
        return false;
    }

    return new Date(detail.next_completion_locked_until).getTime() > nowMs.value;
};

const syncUnlockTimer = () => {
    const hasFutureLocks = (distanceActivity.details || []).some((detail) => {
        if (!detail?.next_completion_locked_until) {
            return false;
        }

        return new Date(detail.next_completion_locked_until).getTime() > nowMs.value;
    });

    if (hasFutureLocks && unlockIntervalId === null) {
        unlockIntervalId = window.setInterval(() => {
            nowMs.value = Date.now();
        }, 1000);
    }

    if (!hasFutureLocks && unlockIntervalId !== null) {
        window.clearInterval(unlockIntervalId);
        unlockIntervalId = null;
    }
};

const lockCountdown = (detail) => {
    if (!detail?.next_completion_locked_until) {
        return '';
    }

    const remainingMs = new Date(detail.next_completion_locked_until).getTime() - nowMs.value;
    if (remainingMs <= 0) {
        return '';
    }

    const totalSeconds = Math.ceil(remainingMs / 1000);
    const minutes = Math.floor(totalSeconds / 60);
    const seconds = totalSeconds % 60;

    return `${minutes}:${String(seconds).padStart(2, '0')}`;
};

const countdownRemainingMs = (detail) => {
    if (!detail?.next_completion_locked_until || !detail?.video_opened_at) {
        return 0;
    }

    return Math.max(0, new Date(detail.next_completion_locked_until).getTime() - nowMs.value);
};

const countdownTotalMs = (detail) => {
    if (!detail?.next_completion_locked_until || !detail?.video_opened_at) {
        return 0;
    }

    const total = new Date(detail.next_completion_locked_until).getTime() - new Date(detail.video_opened_at).getTime();
    return Math.max(0, total);
};

const countdownProgress = (detail) => {
    const total = countdownTotalMs(detail);
    if (!total) {
        return 0;
    }

    const remaining = countdownRemainingMs(detail);
    const elapsedRatio = 1 - (remaining / total);

    return Math.min(1, Math.max(0, elapsedRatio));
};

const countdownStrokeOffset = (detail) => {
    return countdownCircumference * (1 - countdownProgress(detail));
};

const formatDateTime = (value) => {
    if (!value) {
        return '—';
    }

    return new Intl.DateTimeFormat(locale.value, {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
};

const onAudioError = (message) => {
    toast.add({
        severity: 'error',
        summary: $t('Error'),
        detail: message || $t('Unable to record audio.'),
        life: 4000,
    });
};

fetchDistanceActivity();

onBeforeUnmount(() => {
    if (unlockIntervalId !== null) {
        window.clearInterval(unlockIntervalId);
        unlockIntervalId = null;
    }
});
</script>
