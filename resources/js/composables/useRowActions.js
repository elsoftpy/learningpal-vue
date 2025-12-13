import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useToast } from 'primevue/usetoast';
import { useI18n } from 'vue-i18n';
import axios from 'axios';

export function useRowActions(options = {}) {
    const {
        editRouteName,
        deleteEndpoint,
        onDeleteSuccess,
        deleteMethod = 'post',
        buildDeleteUrl,
        messages = {},
    } = options;

    const { t } = useI18n();
    const deleteDialogVisible = ref(false);
    const itemIdToDelete = ref(null);
    const isDeleting = ref(false);
    const router = useRouter();
    const toast = useToast();

    if (!editRouteName && !deleteEndpoint) {
        throw new Error('At least one of editRouteName or deleteEndpoint must be provided to useRowActions');
    }

    function handleEdit(id) {
        if (!editRouteName) {
            console.error('Edit route name is not provided.');
            
            return;
        }

        router.push({ name: editRouteName, params: { 'userId': id } });
    }

    function handleDelete(id) {
        if (!deleteEndpoint) {
            console.error('Delete endpoint is not provided.');
         
            return;
        }
        
        itemIdToDelete.value = id;
        deleteDialogVisible.value = true;
    }

    function getDeleteUrl(id) {
        if (buildDeleteUrl) {
            return buildDeleteUrl(id);
        }

        if (deleteEndpoint.includes(':id')) {
            return deleteEndpoint.replace(':id', id);
        }

        if (deleteEndpoint.includes('{id}')) {
            return deleteEndpoint.replace('{id}', id);
        }

        return `${deleteEndpoint}/${id}`;
    }

    async function confirmDelete() {
        if (!itemIdToDelete.value) {
            return;
        }

        isDeleting.value = true;
        
        try {
            const url = getDeleteUrl(itemIdToDelete.value);

            if (deleteMethod.toLowerCase() === 'delete') {
                await axios.delete(url);
            } else {
                await axios.post(url);
            }

            deleteDialogVisible.value = false;
            const deletedId = itemIdToDelete.value;
            itemIdToDelete.value = null;

            toast.add({
                severity: 'success',
                summary: messages.successTitle || t('Success'),
                detail: messages.successMessage || t('Item deleted successfully.'),
                life: 3000,
            });

            if (onDeleteSuccess) {
                onDeleteSuccess(deletedId);
            }
        } catch (error) {
            toast.add({
                severity: 'error',
                summary: messages.errorTitle || t('Error'),
                detail: messages.errorMessage || error.response?.data?.message || t('Failed to delete the item.'),
                life: 5000,
            });

            deleteDialogVisible.value = false;
            itemIdToDelete.value = null;
        } finally {
            isDeleting.value = false;
        }
    }

    function cancelDelete() {
        deleteDialogVisible.value = false;
        itemIdToDelete.value = null;
    }

    return {
        handleEdit,
        handleDelete,
        confirmDelete,
        cancelDelete,
        deleteDialogVisible,
        itemIdToDelete,
        isDeleting,
    };
}