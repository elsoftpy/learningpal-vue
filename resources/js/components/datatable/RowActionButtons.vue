<template>
    <div class="flex space-x-1 space-y-1">
        <Button
            v-if="canEdit"
            :label="editLabel"
            :icon="editIcon"
            size="small"
            @click="$emit('edit')"
        />
        <Button
            v-if="canDelete"
            :label="deleteLabel"
            :icon="deleteIcon"
            size="small"
            class="p-button-danger"
            @click="$emit('delete')"
        />
        <slot />
    </div>
</template>

<script setup>
import Button from 'primevue/button';

const props = defineProps({
    canEdit: { type: Boolean, default: false },
    canDelete: { type: Boolean, default: false },
    editLabel: { type: String, default: 'Edit' },
    deleteLabel: { type: String, default: 'Delete' },
    editIcon: { type: String, default: 'pi pi-pencil' },
    deleteIcon: { type: String, default: 'pi pi-trash' },
});
</script>
