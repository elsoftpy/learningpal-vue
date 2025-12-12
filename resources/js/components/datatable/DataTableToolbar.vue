<template>
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-1">
            <slot name="actions"></slot>
        </div>
        <div class="flex items-center space-x-2">
            <slot name="before-filter"></slot>
            <Button
                v-if="hasActiveFilters"
                size="small"
                severity="secondary"
                icon="pi pi-filter-slash"
                :label="clearLabel"
                @click="$emit('clear-filters')"
            />
            <IconField>
                <InputIcon>
                    <i class="pi pi-search"></i>
                </InputIcon>
                <InputText
                    :value="searchQuery"
                    :placeholder="searchPlaceholder"
                    @input="handleInput"
                />
            </IconField>
        </div>
    </div>
</template>

<script setup>
import Button from 'primevue/button';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputText from 'primevue/inputtext';

const props = defineProps({
    searchQuery: { type: String, default: '' },
    searchPlaceholder: { type: String, default: '' },
    clearLabel: { type: String, default: 'Clear filters' },
    hasActiveFilters: { type: Boolean, default: false },
});

const emit = defineEmits(['update:searchQuery', 'search-input', 'clear-filters']);

const handleInput = (event) => {
    const value = event?.target?.value ?? '';
    emit('update:searchQuery', value);
    emit('search-input', value);
};
</script>
