<template>
  <div class="flex flex-col w-full">
    <label v-if="label" :for="id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>

    <Select
        ref="selectRef"
        :id="id"
        v-model="selectedValue"
        :options="options"
        :option-label="optionLabel"
        :option-value="optionValue"
        :loading="loading"
        :placeholder="placeholder"
        :disabled="disabled"
        :invalid="invalid"
        :show-clear="true"
        filter
        :filter-placeholder="filterPlaceholder"
        @filter="onFilter"
        @change="onChange"
        @hide="onHide"
        class="w-full"

    >
      <template #empty>
        <div class="p-3 text-center text-gray-500">
          {{ emptyMessage || $t('No results found') }}
        </div>
      </template>

      <template #loader>
        <div class="flex items-center justify-center p-3">
          <ProgressSpinner style="width: 20px; height: 20px" strokeWidth="4"/>
          <span class="ml-2 text-sm text-gray-600">{{ $t('Loading') }}...</span>
        </div>
      </template>

      <template v-if="$slots.option" #option="slotProps">
        <slot name="option" :option="slotProps.option" />
      </template>

      <template v-if="$slots.value" #value="slotProps">
        <slot name="value" :value="slotProps.value" />
      </template>
    </Select>

    <small v-if="error" class="text-red-500 mt-1">{{ error }}</small>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import Select from 'primevue/select'
import ProgressSpinner from 'primevue/progressspinner'
import axios from 'axios'

const { t: $t } = useI18n()

const props = defineProps({
  id: String,
  name: { type: String, required: true },
  modelValue: { type: [String, Number, Object], default: null },
  label: String,
  placeholder: String,
  apiEndpoint: { type: String, required: true },
  optionLabel: { type: String, default: 'name' },
  optionValue: { type: String, default: 'id' },
  searchParam: { type: String, default: 'search' },
  required: Boolean,
  disabled: Boolean,
  invalid: Boolean,
  error: String,
  emptyMessage: String,
  filterPlaceholder: { type: String, default: 'Search...' },
  debounceTime: { type: Number, default: 300 },
  initialLoad: { type: Boolean, default: true }
})

const emit = defineEmits(['update:modelValue', 'change'])

const options = ref([])
const loading = ref(false)
const selectedValue = ref(props.modelValue)
const selectRef = ref(null)
let debounceTimer = null

// Sync external modelValue
watch(() => props.modelValue, (newVal) => {
  selectedValue.value = newVal
})

// Fetch options from server
const fetchOptions = async (query = '') => {
  loading.value = true
  try {
    const params = query ? { [props.searchParam]: query } : {}
    const response = await axios.post(props.apiEndpoint, { params })
    let fetchedOptions = []

    if (response.data.data) fetchedOptions = response.data.data
    else if (Array.isArray(response.data)) fetchedOptions = response.data

    // Ensure the selected option is always present
    if (selectedValue.value && !fetchedOptions.some(o => o[props.optionValue] === selectedValue.value)) {
      const currentOption = options.value.find(o => o[props.optionValue] === selectedValue.value)
      if (currentOption) fetchedOptions.unshift(currentOption)
    }

    options.value = fetchedOptions
  } catch (err) {
    console.error('Failed to fetch options:', err)
    options.value = []
  } finally {
    loading.value = false
  }
}

// Debounced filter
const onFilter = (event) => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    fetchOptions(event.value)
  }, props.debounceTime)
}

// Handle selection
const onChange = (event) => {
  selectedValue.value = event.value
  emit('update:modelValue', event.value)
  emit('change', event.value)

  // Reset filter input so it's cleared after selection
  if (selectRef.value) {
    selectRef.value.filterValue = ''
    selectRef.value.filteredOptions = null
  }

  // Ensure selected option exists in options array
  if (selectedValue.value && !options.value.some(o => o[props.optionValue] === selectedValue.value)) {
    options.value.unshift({
      [props.optionValue]: selectedValue.value,
      [props.optionLabel]: event.originalEvent?.target?.innerText || 'Selected'
    })
  }
}

const onHide = () => {
  if (!selectRef.value) return;

  // Clear search box
  selectRef.value.filterValue = '';

  // Reset filtered options
  selectRef.value.filteredOptions = null;

  // Reload available options to show full list
  // BUT keep selected item always included
  fetchOptions('');
};

// Initial load
onMounted(() => {
  if (props.initialLoad) fetchOptions()
})
</script>
