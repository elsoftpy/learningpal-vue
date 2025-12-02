import { defineStore } from 'pinia';

export const useSidebarStore = defineStore('sidebar', {
    state: () => ({
        openModule: null,
        openSubmodule: null,

        activeModule: null,
        activeSubmodule: null,
        activeItem: null,
    }),

    actions: {
        updateFromRoute(route) {
            this.activeModule = route.meta.module || null;
            this.activeSubmodule = route.meta.submodule || null;
            this.activeItem = route.name || null;

            // automatically expand the structure
            this.openModule = this.activeModule;
            this.openSubmodule = this.activeSubmodule;
        },

        toggleModule(module) {
            if (this.openModule === module) {
                this.openModule = null;
                this.openSubmodule = null;
                return;
            }

            this.openModule = module;

            // restore submodule if this module is active
            if (this.activeModule === module) {
                this.openSubmodule = this.activeSubmodule;
            } else {
                this.openSubmodule = null;
            }
        },

        toggleSubmodule(submodule) {
            this.openSubmodule =
                this.openSubmodule === submodule ? null : submodule;
        }
    },
});
