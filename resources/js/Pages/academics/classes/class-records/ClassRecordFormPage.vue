<template>
	<Form
		:key="formKey"
		v-slot="$form"
		:resolver="resolver"
		:initial-values="initialValues"
		:validateOnBlur="true"
		@submit="handleSubmit"
	>
		<PageContainer>
			<template #body>
				<div class="flex flex-col w-full space-y-4">
					<Message
						v-if="errors?.general"
						severity="error"
						size="small"
						variant="outlined"
						:closable="true"
					>
						{{ Array.isArray(errors?.general) ? errors?.general.join(', ') : errors?.general }}
					</Message>

					<div class="flex flex-col md:flex-row w-full space-y-4 md:space-y-0 md:space-x-2">
						<div class="flex flex-col w-full md:w-1/2">
							<label for="class_schedule_detail_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
								{{ $t('Class Schedule Session') }}
								<span class="text-red-500">*</span>
							</label>
							<Select
								id="class_schedule_detail_id"
								name="class_schedule_detail_id"
								:options="classScheduleDetailsOptions"
								option-label="name"
								option-value="id"
								:placeholder="$t('Select a session')"
								class="w-full"
								:disabled="isClassScheduleDetailLocked"
								:loading="loadingOptions"
								@change="onSessionSelectChange"
							/>
							<Message
								v-if="$form.class_schedule_detail_id?.invalid"
								severity="error"
								size="small"
								variant="simple"
							>
								{{ $form.class_schedule_detail_id?.error?.message }}
							</Message>
							<Message
								v-if="errors?.class_schedule_detail_id"
								severity="error"
								size="small"
								variant="simple"
							>
								{{ Array.isArray(errors?.class_schedule_detail_id) ? errors?.class_schedule_detail_id.join(', ') : errors?.class_schedule_detail_id }}
							</Message>
						</div>

						<div class="flex flex-col w-full md:w-1/2">
							<label for="teacher_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
								{{ $t('Teacher') }}
								<span class="text-red-500">*</span>
							</label>
							<Select
								id="teacher_id"
								name="teacher_id"
								:options="teachersOptions"
								option-label="name"
								option-value="id"
								:placeholder="$t('Select teacher')"
								class="w-full"
								:loading="loadingOptions"
							/>
							<Message
								v-if="$form.teacher_id?.invalid"
								severity="error"
								size="small"
								variant="simple"
							>
								{{ $form.teacher_id?.error?.message }}
							</Message>
							<Message
								v-if="errors?.teacher_id"
								severity="error"
								size="small"
								variant="simple"
							>
								{{ Array.isArray(errors?.teacher_id) ? errors?.teacher_id.join(', ') : errors?.teacher_id }}
							</Message>
						</div>
					</div>

					<div class="flex flex-col md:flex-row w-full space-y-4 md:space-y-0 md:space-x-2">
						<div class="flex flex-col w-full md:w-1/3">
							<DateInput
								id="date"
								name="date"
								:label="$t('Date')"
								:placeholder="$t('dd/mm/yyyy')"
								:mandatory="true"
							/>
							<Message
								v-if="$form.date?.invalid"
								severity="error"
								size="small"
								variant="simple"
							>
								{{ $form.date?.error?.message }}
							</Message>
							<Message
								v-if="errors?.date"
								severity="error"
								size="small"
								variant="simple"
							>
								{{ Array.isArray(errors?.date) ? errors?.date.join(', ') : errors?.date }}
							</Message>
						</div>

						<div class="flex flex-col w-full md:w-1/3">
							<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
								{{ $t('Start Time') }}
								<span class="text-red-500">*</span>
							</label>
							<InputText
								name="start_time"
								v-maska="timeMaskOptions"
								:placeholder="$t('HH:MM')"
								class="w-full text-right"
							/>
							<Message
								v-if="$form.start_time?.invalid"
								severity="error"
								size="small"
								variant="simple"
							>
								{{ $form.start_time?.error?.message }}
							</Message>
							<Message
								v-if="errors?.start_time"
								severity="error"
								size="small"
								variant="simple"
							>
								{{ Array.isArray(errors?.start_time) ? errors?.start_time.join(', ') : errors?.start_time }}
							</Message>
						</div>

						<div class="flex flex-col w-full md:w-1/3">
							<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
								{{ $t('End Time') }}
								<span class="text-red-500">*</span>
							</label>
							<InputText
								name="end_time"
								v-maska="timeMaskOptions"
								:placeholder="$t('HH:MM')"
								class="w-full text-right"
							/>
							<Message
								v-if="$form.end_time?.invalid"
								severity="error"
								size="small"
								variant="simple"
							>
								{{ $form.end_time?.error?.message }}
							</Message>
							<Message
								v-if="errors?.end_time"
								severity="error"
								size="small"
								variant="simple"
							>
								{{ Array.isArray(errors?.end_time) ? errors?.end_time.join(', ') : errors?.end_time }}
							</Message>
						</div>
					</div>

					<div class="flex flex-col md:flex-row w-full space-y-4 md:space-y-0 md:space-x-2">
						<div class="flex flex-col w-full md:w-1/3">
							<label for="attendance" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
								{{ $t('Attendance') }}
								<span class="text-red-500">*</span>
							</label>
							<Select
								id="attendance"
								name="attendance"
								:options="attendanceOptions"
								option-label="label"
								option-value="value"
								:placeholder="$t('Select attendance')"
								class="w-full"
							/>
							<Message
								v-if="$form.attendance?.invalid"
								severity="error"
								size="small"
								variant="simple"
							>
								{{ $form.attendance?.error?.message }}
							</Message>
							<Message
								v-if="errors?.attendance"
								severity="error"
								size="small"
								variant="simple"
							>
								{{ Array.isArray(errors?.attendance) ? errors?.attendance.join(', ') : errors?.attendance }}
							</Message>
						</div>

						<div class="flex flex-col w-full md:w-1/3">
							<label for="duration_minutes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
								{{ $t('Duration (minutes)') }}
								<span class="text-red-500">*</span>
							</label>
							<InputText
								id="duration_minutes"
								name="duration_minutes"
								type="number"
								min="1"
								class="w-full"
							/>
							<Message
								v-if="$form.duration_minutes?.invalid"
								severity="error"
								size="small"
								variant="simple"
							>
								{{ $form.duration_minutes?.error?.message }}
							</Message>
							<Message
								v-if="errors?.duration_minutes"
								severity="error"
								size="small"
								variant="simple"
							>
								{{ Array.isArray(errors?.duration_minutes) ? errors?.duration_minutes.join(', ') : errors?.duration_minutes }}
							</Message>
						</div>

						<div class="hidden md:block md:w-1/3"></div>
					</div>

					<div class="flex flex-col w-full">
						<label for="comments" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
							{{ $t('Comments') }}
						</label>
						<Textarea
							id="comments"
							name="comments"
							rows="4"
							class="w-full"
						/>
						<Message
							v-if="$form.comments?.invalid"
							severity="error"
							size="small"
							variant="simple"
						>
							{{ $form.comments?.error?.message }}
						</Message>
						<Message
							v-if="errors?.comments"
							severity="error"
							size="small"
							variant="simple"
						>
							{{ Array.isArray(errors?.comments) ? errors?.comments.join(', ') : errors?.comments }}
						</Message>
					</div>


					<div class="border border-gray-200 dark:border-gray-700 rounded-md p-4 space-y-4">
						<div class="flex items-center justify-between">
							<h3 class="text-base font-semibold text-gray-700 dark:text-gray-200">
								{{ $t('Record Details') }}
							</h3>
							<span class="text-xs text-gray-500">{{ $t('Add class record details before saving.') }}</span>
						</div>

						<div class="grid grid-cols-1 lg:grid-cols-5 w-full gap-4">
							<div class="flex flex-col w-full">
								<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
									{{ $t('Content') }}
								</label>
								<Select
									v-model="detailForm.content_id"
									:options="levelContentsOptions"
									option-label="name"
									option-value="id"
									:placeholder="$t('Select content')"
									class="w-full"
								/>
							</div>

							<div class="flex flex-col w-full">
								<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
									{{ $t('Free Content') }}
								</label>
								<InputText
									v-model="detailForm.free_content"
									:placeholder="$t('Type content')"
									class="w-full"
								/>
							</div>

							<div class="flex flex-col w-full">
								<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
									{{ $t('Activity') }}
									<span class="text-red-500">*</span>
								</label>
								<InputText
									v-model="detailForm.activity"
									:placeholder="$t('Describe activity')"
									class="w-full"
								/>
							</div>

							<div class="flex flex-col w-full">
								<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
									{{ $t('Links') }}
								</label>
								<InputText
									v-model="detailForm.links"
									:placeholder="$t('https://...')"
									class="w-full"
								/>
							</div>

							<div class="flex flex-col w-full">
								<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
									{{ $t('Attachment') }}
								</label>
								<InputText
									type="file"
									accept="*"
									class="w-full"
									@change="onDetailFileChange"
								/>
							</div>
						</div>

						<Message
							v-if="detailFormError"
							severity="error"
							size="small"
							variant="simple"
						>
							{{ detailFormError }}
						</Message>

						<div class="flex justify-end">
							<Button
								type="button"
								:label="$t('Add detail')"
								icon="pi pi-plus"
								class="p-button-primary"
								@click="addRecordDetail"
							/>
						</div>

						<div v-if="classRecordDetails.length" class="overflow-x-auto">
							<table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
								<thead class="bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-200">
									<tr>
										<th class="px-3 py-2 text-left">{{ $t('Content') }}</th>
										<th class="px-3 py-2 text-left">{{ $t('Free Content') }}</th>
										<th class="px-3 py-2 text-left">{{ $t('Activity') }}</th>
										<th class="px-3 py-2 text-left">{{ $t('Links') }}</th>
										<th class="px-3 py-2 text-left">{{ $t('Attachment') }}</th>
										<th class="px-3 py-2 text-left">{{ $t('Actions') }}</th>
									</tr>
								</thead>
								<tbody class="divide-y divide-gray-200 dark:divide-gray-700">
									<tr v-for="detail in classRecordDetails" :key="detail._key" class="bg-white dark:bg-slate-900">
										<td class="px-3 py-2 text-left">{{ detail.content_name }}</td>
										<td class="px-3 py-2 text-left">{{ detail.free_content }}</td>
										<td class="px-3 py-2 text-left">{{ detail.activity }}</td>
										<td class="px-3 py-2 text-left">{{ detail.links }}</td>
										<td class="px-3 py-2 text-left">
											<a v-if="detail.attachment_url" :href="detail.attachment_url" target="_blank" class="text-primary underline">
												{{ detail.attachment_name }}
											</a>
											<span v-else>{{ detail.attachment_name || '-' }}</span>
										</td>
										<td class="px-3 py-2 text-left">
											<Button
												type="button"
												icon="pi pi-trash"
												severity="danger"
												text
												@click="removeRecordDetail(detail._key)"
											/>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
						<div v-else class="text-xs text-gray-500">{{ $t('No details added yet.') }}</div>
					</div>

					<div class="flex justify-end mt-4">
						<SubmitButton :isLoading="isLoading" />
					</div>
				</div>

				<Dialog
					v-model:visible="notTeacherDialogVisible"
					modal
					:draggable="false"
					:closable="false"
					:header="$t('Permission required')"
					class="w-full max-w-lg"
				>
					<p class="text-sm text-gray-700 dark:text-gray-200">{{ notTeacherDialogMessage }}</p>
					<template #footer>
						<Button
							type="button"
							:label="$t('Back')"
							@click="goBackAfterNotTeacher"
						/>
					</template>
				</Dialog>
			</template>
		</PageContainer>
	</Form>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { Form } from '@primevue/forms';
import { zodResolver } from '@primevue/forms/resolvers/zod';
import axios from 'axios';
import { useToast } from 'primevue/usetoast';
import PageContainer from '@/components/layout/pages/PageContainer.vue';
import DateInput from '@/components/form/DateInput.vue';
import Message from 'primevue/message';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Textarea from 'primevue/textarea';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import SubmitButton from '@/components/form/SubmitButton.vue';
import { createClassRecordSchema } from '@/schemas/classRecord';
import { useApiErrorHandler } from '@/composables/useApiErrorHandler';
import { useFormValues } from '@/composables/useFormValues';
import { useFormSubmitter } from '@/composables/useFormSubmitter';

const { t: $t, locale } = useI18n();
const route = useRoute();
const router = useRouter();
const toast = useToast();
const { handleApiError } = useApiErrorHandler();
const { extractFormData } = useFormValues();

const crudAction = route.meta?.crud || 'read';
const creating = crudAction === 'create';
const classRecordId = computed(() => route.params.id);

const paramClassScheduleDetailId = computed(() => {
	const value = route.params.classScheduleDetailId || route.query.class_schedule_detail_id;
	return value ? Number(value) : null;
});

const classRecordData = ref(null);
const selectedClassScheduleDetailId = ref(null);
const classScheduleDetailsOptions = ref([]);
const teachersOptions = ref([]);
const attendanceOptions = ref([]);
const levelContentsOptions = ref([]);
const classRecordDetails = ref([]);
const loadingOptions = ref(false);
const formKey = ref(0);
const preferredTeacherId = ref(null);

const lockedClassScheduleDetailId = ref(null);
const notTeacherDialogVisible = ref(false);
const notTeacherDialogMessage = ref($t("Sorry, you're not a teacher, you can't create class records"));
const detailFormError = ref('');

const createEmptyDetailForm = () => ({
	content_id: null,
	free_content: '',
	activity: '',
	links: '',
	file: null,
});

const detailForm = reactive(createEmptyDetailForm());

const schema = computed(() => createClassRecordSchema($t, locale.value));
const resolver = computed(() => zodResolver(schema.value));

const timeMaskOptions = { mask: '##:##', eager: true };

const { errors, isLoading, setErrors } = useFormSubmitter({
	class_schedule_detail_id: '',
	teacher_id: '',
	date: '',
	start_time: '',
	end_time: '',
	duration_minutes: '',
	attendance: '',
	comments: '',
	general: '',
});

const isClassScheduleDetailLocked = computed(() => Boolean(lockedClassScheduleDetailId.value));

const generateDetailKey = () => (typeof crypto !== 'undefined' && crypto.randomUUID
	? crypto.randomUUID()
	: `detail-${Date.now()}-${Math.random().toString(36).slice(2, 9)}`);

const normalizeRecordDetail = (detail = {}) => ({
	id: detail.id ?? null,
	content_id: detail.content_id ?? null,
	content_name: detail.content_name ?? (levelContentsOptions.value.find((item) => item.id === detail.content_id)?.name || ''),
	free_content: detail.free_content ?? '',
	activity: detail.activity ?? '',
	links: detail.links ?? '',
	attachment_name: detail.attachment_name ?? '',
	attachment_url: detail.attachment_url ?? null,
	file: detail.file ?? null,
	_key: detail._key ?? (detail.id ? `detail-${detail.id}` : generateDetailKey()),
});

const resetDetailForm = () => {
	Object.assign(detailForm, createEmptyDetailForm());
};

const normalizeLinks = (value) => value
	? value
		.split(/[\s,|]+/)
		.map((item) => item.trim())
		.filter(Boolean)
		.join('|')
	: '';

const onDetailFileChange = (event) => {
	detailForm.file = event.target?.files?.[0] || null;
};

const onSessionSelectChange = async (event) => {
	selectedClassScheduleDetailId.value = event.value ?? null;
	await fetchClassScheduleDetailContext(selectedClassScheduleDetailId.value);

	if (!levelContentsOptions.value.some((item) => item.id === detailForm.content_id)) {
		detailForm.content_id = null;
	}
};

const addRecordDetail = () => {
	detailFormError.value = '';

	if (!detailForm.activity?.trim()) {
		detailFormError.value = $t('Activity is required.');
		return;
	}

	const contentName = levelContentsOptions.value.find((item) => item.id === detailForm.content_id)?.name || '';

	classRecordDetails.value = [
		...classRecordDetails.value,
		normalizeRecordDetail({
			content_id: detailForm.content_id,
			content_name: contentName,
			free_content: detailForm.free_content,
			activity: detailForm.activity,
			links: normalizeLinks(detailForm.links),
			attachment_name: detailForm.file?.name || '',
			file: detailForm.file,
		}),
	];

	resetDetailForm();
};

const removeRecordDetail = (_key) => {
	classRecordDetails.value = classRecordDetails.value.filter((detail) => detail._key !== _key);
};

const buildDetailsPayload = () => classRecordDetails.value.map((detail) => ({
	id: detail.id,
	content_id: detail.content_id,
	free_content: detail.free_content,
	activity: detail.activity,
	links: normalizeLinks(detail.links),
}));

const buildSubmitPayload = (values) => {
	const payload = new FormData();
	Object.entries(values).forEach(([key, value]) => {
		if (value !== null && value !== undefined) {
			payload.append(key, value);
		}
	});

	buildDetailsPayload().forEach((detail, index) => {
		Object.entries(detail).forEach(([key, value]) => {
			if (value !== null && value !== undefined && value !== '') {
				payload.append(`details[${index}][${key}]`, value);
			}
		});

		if (classRecordDetails.value[index]?.file) {
			payload.append(`detail_files[${index}]`, classRecordDetails.value[index].file);
		}
	});

	return payload;
};

const fetchClassScheduleDetailContext = async (detailId) => {
	if (!detailId) {
		levelContentsOptions.value = [];
		return;
	}

	const { data } = await axios.post(`/academics/lessons/class-records/class-schedule-details/${detailId}/context`);
	levelContentsOptions.value = data?.data?.level_contents || [];
};

const initialValues = computed(() => {
	const record = classRecordData.value || {};

	return {
		class_schedule_detail_id: selectedClassScheduleDetailId.value,
		teacher_id: record.teacher_id || preferredTeacherId.value || null,
		date: record.date || '',
		start_time: record.start_time || '',
		end_time: record.end_time || '',
		duration_minutes: record.duration_minutes || null,
		attendance: record.attendance || '',
		comments: record.comments || '',
	};
});

const applyFormData = (data) => {
	classScheduleDetailsOptions.value = data?.class_schedule_details || [];
	teachersOptions.value = data?.teachers || [];
	attendanceOptions.value = data?.attendances || [];
	levelContentsOptions.value = data?.level_contents || [];
	preferredTeacherId.value = data?.preferred_teacher_id ? Number(data.preferred_teacher_id) : null;
	lockedClassScheduleDetailId.value = data?.locked_class_schedule_detail_id
		? Number(data.locked_class_schedule_detail_id)
		: (paramClassScheduleDetailId.value || null);

	selectedClassScheduleDetailId.value = lockedClassScheduleDetailId.value || classRecordData.value?.class_schedule_detail_id || null;
};

const fetchCreateFormData = async () => {
	const payload = {};
	if (paramClassScheduleDetailId.value) {
		payload.class_schedule_detail_id = paramClassScheduleDetailId.value;
	}

	const response = await axios.post('/academics/lessons/class-records/form-data', payload);
	applyFormData(response?.data?.data || {});
};

const fetchEditData = async () => {
	const payload = {};
	if (paramClassScheduleDetailId.value) {
		payload.class_schedule_detail_id = paramClassScheduleDetailId.value;
	}

	const response = await axios.post(`/academics/lessons/class-records/${classRecordId.value}/data`, payload);
	const data = response?.data?.data || {};
	classRecordData.value = data.class_record || null;
	applyFormData(data);
	classRecordDetails.value = (data.class_record?.details || []).map((detail) => normalizeRecordDetail(detail));
};

const openNotTeacherDialog = (message) => {
	notTeacherDialogMessage.value = message || $t("Sorry, you're not a teacher, you can't create class records");
	notTeacherDialogVisible.value = true;
};

const goBackAfterNotTeacher = () => {
	notTeacherDialogVisible.value = false;

	if (window.history.length > 1) {
		router.back();
		return;
	}

	router.push({ name: 'academics.classes.class-records.list' });
};

const handleSubmit = async (formData) => {
	errors.value = {};
	const { valid, values } = extractFormData(formData);

	if (!valid) {
		return;
	}

	if (lockedClassScheduleDetailId.value) {
		values.class_schedule_detail_id = lockedClassScheduleDetailId.value;
	}

	if (!lockedClassScheduleDetailId.value && selectedClassScheduleDetailId.value) {
		values.class_schedule_detail_id = selectedClassScheduleDetailId.value;
	}

	isLoading.value = true;

	try {
		const endpoint = creating
			? '/academics/lessons/class-records'
			: `/academics/lessons/class-records/${classRecordId.value}/edit`;

		await axios.post(endpoint, buildSubmitPayload(values));

		toast.add({
			severity: 'success',
			summary: $t('Success'),
			detail: creating ? $t('Class record created successfully.') : $t('Class record updated successfully.'),
			life: 3000,
		});

		router.push({ name: 'academics.classes.class-records.list' });
	} catch (error) {
		const apiError = handleApiError(error);

		if (apiError?.type === 'validation' && apiError.errors) {
			setErrors(apiError.errors);
			toast.add({
				severity: 'error',
				summary: $t('Validation Error'),
				detail: $t('Please correct the errors in the form.'),
				life: 5000,
			});
			return;
		}

		if (apiError?.type === 'permission') {
			openNotTeacherDialog(apiError?.message);
			return;
		}

		setErrors({ general: apiError?.message || $t('An unexpected error occurred. Please try again.') });
		toast.add({
			severity: 'error',
			summary: $t('Error'),
			detail: apiError?.message || $t('An unexpected error occurred. Please try again.'),
			life: 5000,
		});
	} finally {
		isLoading.value = false;
	}
};

onMounted(async () => {
	loadingOptions.value = true;

	try {
		if (creating) {
			await fetchCreateFormData();
		} else {
			await fetchEditData();
		}

		if (selectedClassScheduleDetailId.value) {
			await fetchClassScheduleDetailContext(selectedClassScheduleDetailId.value);
		}

		formKey.value += 1;
	} catch (error) {
		const apiError = handleApiError(error);

		if (apiError?.type === 'permission') {
			openNotTeacherDialog(apiError?.message);
			return;
		}

		setErrors({ general: apiError?.message || $t('Failed to load class record form data.') });
		toast.add({
			severity: 'error',
			summary: $t('Error'),
			detail: apiError?.message || $t('Failed to load class record form data.'),
			life: 5000,
		});
	} finally {
		loadingOptions.value = false;
	}
});
</script>
