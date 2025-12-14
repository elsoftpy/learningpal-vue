<template>
    <RowActionButtons
        :can-edit="canEdit"
        :can-delete="canDelete"
        :edit-label="resolvedEditLabel"
        :delete-label="resolvedDeleteLabel"
        :edit-icon="editIcon"
        :delete-icon="deleteIcon"
        @edit="handleEdit"
        @delete="handleDelete"
    >
        <Button
            v-for="action in visibleAdditionalActions"
            :key="action.key"
            size="small"
            :label="action.label"
            :icon="action.icon"
            :severity="action.severity"
            :outlined="action.outlined"
            :text="action.text"
            :rounded="action.rounded"
            class="ml-1"
            v-bind="action.buttonProps"
            @click="() => action.onClick && action.onClick(data)"
        />
    </RowActionButtons>
</template>
<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { usePermissions } from '@/composables/usePermissions.js';
import RowActionButtons from '@/components/datatable/RowActionButtons.vue';
import Button from 'primevue/button';

const props = defineProps({
    data: { type: Object, default: null },
    editPermission: { type: [String, Array], default: null },
    deletePermission: { type: [String, Array], default: null },
    editLabel: { type: String, default: null },
    deleteLabel: { type: String, default: null },
    editIcon: { type: String, default: 'pi pi-pencil' },
    deleteIcon: { type: String, default: 'pi pi-trash' },
    onEdit: { type: Function, default: null },
    onDelete: { type: Function, default: null },
    additionalActions: {
        type: [Array, Function],
        default: null,
    },
});

const { t: $t } = useI18n();
const { can } = usePermissions();

const resolvedEditLabel = computed(() => props.editLabel ?? $t('Edit'));
const resolvedDeleteLabel = computed(() => props.deleteLabel ?? $t('Delete'));

const canEdit = computed(() => Boolean(props.onEdit) && passesPermission(props.editPermission));
const canDelete = computed(() => Boolean(props.onDelete) && passesPermission(props.deletePermission));

const normalizedAdditionalActions = computed(() => {
    const raw = typeof props.additionalActions === 'function'
        ? props.additionalActions({ data: props.data })
        : props.additionalActions;

    return (raw || []).map((action, index) => ({
        key: action.key ?? `extra-action-${index}`,
        ...action,
    }));
});

const visibleAdditionalActions = computed(() =>
    normalizedAdditionalActions.value.filter((action) => {
        const permissionOk = passesPermission(action.permission);
        const visible = typeof action.visible === 'function'
            ? action.visible({ data: props.data })
            : action.visible !== false;
        return permissionOk && Boolean(action.onClick) && visible;
    })
);

function passesPermission(permission) {
    if (!permission) {
        return true;
    }
    return can(permission);
}

function handleEdit() {
    if (props.onEdit) {
        props.onEdit(props.data);
    }
}

function handleDelete() {
    if (props.onDelete) {
        props.onDelete(props.data);
    }
}
</script>
