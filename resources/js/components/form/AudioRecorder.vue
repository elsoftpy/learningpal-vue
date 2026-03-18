<template>
    <div class="w-full border border-slate-200 dark:border-slate-700 rounded-lg p-4 bg-white dark:bg-slate-900 space-y-3">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="text-sm font-medium text-slate-800 dark:text-slate-100">{{ label || $t('Audio Recorder') }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    {{ $t('The microphone is only used after your permission and only while recording.') }}
                </p>
            </div>
            <span class="text-xs font-semibold" :class="isRecording ? 'text-red-500' : 'text-slate-500 dark:text-slate-400'">
                {{ isRecording ? $t('Recording') : $t('Ready') }}
            </span>
        </div>

        <Message v-if="errorMessage" severity="error" size="small" variant="simple">
            {{ errorMessage }}
        </Message>

        <Message v-if="permissionState === 'denied'" severity="warn" size="small" variant="simple">
            {{ $t('Microphone permission was denied. Enable it in your browser settings to record audio.') }}
        </Message>

        <div class="flex flex-wrap gap-2">
            <Button
                type="button"
                icon="pi pi-microphone"
                :label="$t('audio.record')"
                severity="danger"
                :disabled="isRecording || isProcessing"
                @click="startRecording"
            />
            <Button
                type="button"
                icon="pi pi-stop"
                :label="$t('Stop')"
                severity="secondary"
                :disabled="!isRecording || isProcessing"
                @click="stopRecording"
            />
            <Button
                type="button"
                icon="pi pi-trash"
                :label="$t('Discard')"
                severity="contrast"
                outlined
                :disabled="!audioUrl || isRecording || isProcessing"
                @click="discardAudio"
            />
        </div>

        <div class="text-xs text-slate-500 dark:text-slate-400">
            {{ $t('Duration') }}: {{ formattedDuration }}
            <span v-if="isProcessing"> - {{ $t('Preparing audio...') }}</span>
        </div>

        <div v-if="audioUrl" class="space-y-2">
            <audio :src="audioUrl" controls class="w-full" />
            <p class="text-xs text-slate-500 dark:text-slate-400">
                {{ audioFileName }}
            </p>
        </div>
    </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import Button from 'primevue/button';
import Message from 'primevue/message';

const { t: $t } = useI18n();

const props = defineProps({
    label: {
        type: String,
        default: '',
    },
    maxDurationSeconds: {
        type: Number,
        default: 600,
    },
    bitRate: {
        type: Number,
        default: 128,
    },
});

const emit = defineEmits(['update:modelValue', 'recording-complete', 'error']);

const isRecording = ref(false);
const isProcessing = ref(false);
const audioUrl = ref(null);
const audioFile = ref(null);
const errorMessage = ref('');
const permissionState = ref('prompt');
const durationSeconds = ref(0);

let mediaRecorder = null;
let mediaStream = null;
let recordedChunks = [];
let timerInterval = null;
let selectedMimeType = 'audio/webm';

const formattedDuration = computed(() => {
    const mins = String(Math.floor(durationSeconds.value / 60)).padStart(2, '0');
    const secs = String(durationSeconds.value % 60).padStart(2, '0');
    return `${mins}:${secs}`;
});

const audioFileName = computed(() => audioFile.value?.name || '');

const refreshPermissionState = async () => {
    if (!navigator.permissions?.query) {
        return;
    }

    try {
        const status = await navigator.permissions.query({ name: 'microphone' });
        permissionState.value = status.state;
        status.onchange = () => {
            permissionState.value = status.state;
        };
    } catch {
        permissionState.value = 'prompt';
    }
};

const chooseMimeType = () => {
    const candidates = ['audio/mpeg', 'audio/mp3', 'audio/webm;codecs=opus', 'audio/webm', 'audio/ogg;codecs=opus', 'audio/ogg'];
    return candidates.find((mime) => MediaRecorder.isTypeSupported(mime)) || '';
};

const cleanupStream = () => {
    if (mediaStream) {
        mediaStream.getTracks().forEach((track) => track.stop());
        mediaStream = null;
    }
};

const stopTimer = () => {
    if (timerInterval) {
        clearInterval(timerInterval);
        timerInterval = null;
    }
};

const revokeAudioUrl = () => {
    if (audioUrl.value) {
        URL.revokeObjectURL(audioUrl.value);
        audioUrl.value = null;
    }
};

const getFileExtensionFromMimeType = (mimeType) => {
    if (mimeType.includes('mpeg') || mimeType.includes('mp3')) {
        return 'mp3';
    }

    if (mimeType.includes('ogg')) {
        return 'ogg';
    }

    return 'webm';
};

const toBaseMimeType = (mimeType) => (mimeType || 'audio/webm').split(';')[0].trim();

const startRecording = async () => {
    errorMessage.value = '';

    if (!navigator.mediaDevices?.getUserMedia || typeof MediaRecorder === 'undefined') {
        errorMessage.value = $t('This browser does not support microphone recording.');
        emit('error', errorMessage.value);
        return;
    }

    try {
        mediaStream = await navigator.mediaDevices.getUserMedia({ audio: true });
        permissionState.value = 'granted';

        selectedMimeType = chooseMimeType() || 'audio/webm';
        mediaRecorder = selectedMimeType
            ? new MediaRecorder(mediaStream, { mimeType: selectedMimeType })
            : new MediaRecorder(mediaStream);

        recordedChunks = [];

        mediaRecorder.ondataavailable = (event) => {
            if (event.data && event.data.size > 0) {
                recordedChunks.push(event.data);
            }
        };

        mediaRecorder.onstop = async () => {
            stopTimer();
            isRecording.value = false;
            cleanupStream();

            if (!recordedChunks.length) {
                return;
            }

            isProcessing.value = true;
            try {
                const rawMimeType = mediaRecorder?.mimeType || selectedMimeType || 'audio/webm';
                const mimeType = toBaseMimeType(rawMimeType);
                const extension = getFileExtensionFromMimeType(mimeType);
                const sourceBlob = new Blob(recordedChunks, { type: mimeType });
                const audioOutputFile = new File([sourceBlob], `student-production-${Date.now()}.${extension}`, { type: mimeType });
                audioFile.value = audioOutputFile;
                revokeAudioUrl();
                audioUrl.value = URL.createObjectURL(audioOutputFile);
                emit('update:modelValue', audioOutputFile);
                emit('recording-complete', audioOutputFile);
            } catch (error) {
                errorMessage.value = error?.message || $t('Unable to prepare recorded audio.');
                emit('error', errorMessage.value);
            } finally {
                isProcessing.value = false;
                recordedChunks = [];
            }
        };

        mediaRecorder.start();
        isRecording.value = true;
        durationSeconds.value = 0;
        timerInterval = setInterval(() => {
            durationSeconds.value += 1;
            if (durationSeconds.value >= props.maxDurationSeconds && isRecording.value) {
                stopRecording();
            }
        }, 1000);
    } catch {
        permissionState.value = 'denied';
        errorMessage.value = $t('Microphone access is required to record audio.');
        emit('error', errorMessage.value);
        cleanupStream();
    }
};

const stopRecording = () => {
    if (!mediaRecorder || mediaRecorder.state === 'inactive') {
        return;
    }

    mediaRecorder.stop();
};

const discardAudio = () => {
    audioFile.value = null;
    durationSeconds.value = 0;
    errorMessage.value = '';
    revokeAudioUrl();
    emit('update:modelValue', null);
};

onMounted(() => {
    refreshPermissionState();
});

onBeforeUnmount(() => {
    stopTimer();
    if (mediaRecorder && mediaRecorder.state !== 'inactive') {
        mediaRecorder.stop();
    }
    cleanupStream();
    revokeAudioUrl();
});
</script>
