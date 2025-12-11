import { computed } from 'vue';
import { storeToRefs } from 'pinia';
import { useAuthStore } from '@/stores/auth';

export const usePermissions = () => {
  const auth = useAuthStore();
  const { user } = storeToRefs(auth);

  const can = (permission) => {
    if (!permission) return false;
    
    const required = Array.isArray(permission) ? permission : [permission];
    const owned = user.value?.permissions || [];
    // default behaviour = “any”
    return required.some((perm) => owned.includes(perm));
  };

  const canAll = (permissions) =>
    (permissions || []).every((perm) => can(perm));

  return { can, canAll };
};