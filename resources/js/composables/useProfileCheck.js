import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useToast } from 'primevue/usetoast';
import axios from 'axios';

export function useProfileCheck() {
    const { t: $t } = useI18n();
    const toast = useToast();
    const isChecking = ref(false);

    const checkProfile = async (personalId, options = {}) => {
        const {
            endpoint = `/settings/users/profile/${personalId}/profile-data`,
            onSuccess = null,
            showToast = true,
            showErrorToast = false,
        } = options;

        if (!personalId) {
            return null;
        }
        
        isChecking.value = true;

        try {
            const response = await axios.post(endpoint);

            if (response.data.success && response.data.data?.profile) {
                const profileData = response.data.data.profile;

                if (showToast) {
                    toast.add({ 
                        severity: 'success', 
                        summary: $t('Profile Found'), 
                        detail: $t('Profile data has been loaded'),
                        life: 3000 
                    });
                }

                if (onSuccess && typeof onSuccess === 'function') {
                    onSuccess(profileData);
                }

                return profileData;
            }

            return null;
        } catch (error) {
            if (showErrorToast) {
                toast.add({ 
                    severity: 'warn', 
                    summary: $t('Profile Not Found'), 
                    detail: $t('No existing profile found for this ID.'),
                    life: 3000 
                });
            }
            console.log('Profile check error:', error);
            
            return null;

        } finally {
            isChecking.value = false;
        }
    };
    
    return {
        isChecking,
        checkProfile,
    };
}