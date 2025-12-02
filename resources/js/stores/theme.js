import { defineStore } from 'pinia';

export const useThemeStore = defineStore('theme', {
    state: () => ({
        isDark: false,
        initialized: false,
    }),

    actions: {
        initialize() {
            // Check for saved theme preference or default to light mode
            const savedTheme = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            
            this.isDark = savedTheme === 'dark' || (!savedTheme && prefersDark);
            this.applyTheme();
            this.initialized = true;
        },

        toggle() {
            this.isDark = !this.isDark;
            this.applyTheme();
        },

        applyTheme() {
            console.log('Applying theme:', this.isDark ? 'dark' : 'light');
            if (this.isDark) {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            } else {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            }
        },
    },

    getters: {
        theme: (state) => (state.isDark ? 'dark' : 'light'),
    },
});