import { computed } from 'vue';
import { useRoute } from 'vue-router';

export function useRouteActive(propsRoute, explicitBaseRoute = null) {
    const route = useRoute();

    const getRouteName = (route) => {
  
        if (!route) {
            return '';
        }
        
        if (typeof route === 'string') {
            return route;
        }

        if (route && typeof route === 'object' && 'name' in route) {
            return route.name?.toString() || '';
        }

        return '';
    };

    const inferredBaseRoute = computed(() => {
        if (explicitBaseRoute) {
            return explicitBaseRoute;
        }

        const routeName = getRouteName(propsRoute);

        if (!routeName) {
            return '';
        }

        const parts = routeName.split('.');

        if (parts.length <= 1) {
            return routeName;
        }

        parts.pop();

        return parts.join('.');
    });

    const isActive = computed(() => {
        const current = route.name?.toString() || ''
        return current.startsWith(inferredBaseRoute.value);
    });

    return {
        inferredBaseRoute,
        isActive,
    };
}