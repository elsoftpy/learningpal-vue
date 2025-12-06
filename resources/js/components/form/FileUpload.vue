<template>
    <div class="flex flex-col w-full">
        <FileUpload
            :id="id"
            mode="advanced"
            :accept="accept"
            :auto="false"
            :choose-label="$t(buttonLabel || 'Select Image')"
            :custom-upload="true"
            :show-upload-button="false"
            :show-cancel-button="false"
            :multiple="false"
            :max-file-size="maxFileSize"
            @select="onFileSelect"
            @clear="onFileClear"
            :class="containerClass"
        >
            <template #empty v-if="showEmpty">
                <div class="flex flex-col items-center justify-center py-4">
                    <i :class="['text-4xl text-gray-400 mb-2', emptyIcon]"></i>
                    <p class="text-sm text-gray-500">
                        {{ emptyMessage || $t('Drag and drop image here') }}
                    </p>
                </div>
            </template>
            
            <template #content="{ files, removeFileCallback }">
                <div v-if="files.length > 0" class="p-4">
                    <div 
                        v-for="(file, index) in files" 
                        :key="file.name" 
                        class="flex flex-col space-y-2 p-3 border rounded-lg dark:border-gray-700"
                    >
                        <!-- Preview and Info -->
                        <div class="flex items-center space-x-3">
                            <img 
                                v-if="file.objectURL" 
                                :src="file.objectURL" 
                                :alt="file.name"
                                :class="['rounded object-cover', previewClass]"
                            />
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                    {{ file.name }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ formatFileSize(file.size) }}
                                </p>
                            </div>
                        </div>
                        
                        <!-- Status and Remove -->
                        <div class="flex items-center justify-between">
                            <span :class="statusClass">
                                {{ statusText || $t('Pending') }}
                            </span>
                            <Button
                                icon="pi pi-times"
                                @click="onRemove(index, removeFileCallback)"
                                text
                                rounded
                                severity="danger"
                                size="small"
                                :aria-label="$t('Remove file')"
                            />
                        </div>
                    </div>
                </div>
            </template>
        </FileUpload>

        <!-- Error message -->
        <small v-if="error" class="text-red-500 mt-1">
            {{ error }}
        </small>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import FileUpload from 'primevue/fileupload'
import Button from 'primevue/button'

const { t: $t } = useI18n()

const props = defineProps({
    id: {
        type: String,
        default: 'file-upload'
    },
    name: {
        type: String,
        default: 'file[]'
    },
    label: {
        type: String,
        default: ''
    },
    buttonLabel: {
        type: String,
        default: ''
    },
    showEmpty: {
        type: Boolean,
        default: false
    },
    accept: {
        type: String,
        default: 'image/*'
    },
    maxFileSize: {
        type: Number,
        default: 5000000 // 5MB
    },
    emptyMessage: {
        type: String,
        default: ''
    },
    emptyIcon: {
        type: String,
        default: 'pi pi-image'
    },
    statusText: {
        type: String,
        default: ''
    },
    statusClass: {
        type: String,
        default: 'px-2 py-1 rounded-full bg-amber-500 text-xs font-semibold text-white'
    },
    previewClass: {
        type: String,
        default: 'h-12 w-12'
    },
    containerClass: {
        type: String,
        default: 'w-full'
    },
    error: {
        type: String,
        default: ''
    }
})

const emit = defineEmits(['update:modelValue', 'file-select', 'file-remove'])

const selectedFile = ref(null)

const onFileSelect = (event) => {
    if (!event || !event.files) {
        selectedFile.value = null
        emit('update:modelValue', null)
        emit('file-remove')
        return
    }

    const [file] = event.files || []
    if (!file) {
        selectedFile.value = null
        emit('update:modelValue', null)
        emit('file-remove')
        return
    }

    selectedFile.value = file
    emit('update:modelValue', file)
    emit('file-select', file)
}

const onFileClear = () => {
    selectedFile.value = null
    emit('update:modelValue', null)
    emit('file-remove')
}

const onRemove = (index, removeFileCallback) => {
    removeFileCallback(index)
    onFileClear()
}

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes'
    const k = 1024
    const sizes = ['Bytes', 'KB', 'MB', 'GB']
    const i = Math.floor(Math.log(bytes) / Math.log(k))
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i]
}
</script>