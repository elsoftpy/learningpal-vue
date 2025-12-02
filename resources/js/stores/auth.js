import { defineStore } from 'pinia';

import {
    csrfCookie,
    loginRequest,
    registerRequest,
    logoutRequest,
    fetchUser,
} from '../api/auth';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        isAuthenticated: false,
        loading: false,
        csrfLoaded: false,
        ready: false,
    }),

    actions: {
        async ensureCsrf() {
            if (!this.csrfLoaded) {
                await csrfCookie();
                this.csrfLoaded = true;
            }
        },

        async checkAuth() {
            this.loading = true;
            try {
                await this.ensureCsrf();

                const response = await fetchUser();

                this.user = response.data.data.user;
                this.isAuthenticated = true;

                return true;
            } catch (error) {
                this.user = null;
                this.isAuthenticated = false;
                
                return false;
            } finally {
                this.loading = false;
                this.ready = true;
            }
        },

        async login(credentials) {
            this.loading = true;

            try {
                await this.ensureCsrf();

                const response = await loginRequest(credentials);
                this.user = response.data.data.user;
                this.isAuthenticated = true;

                return { success: true, user: this.user };
            } catch (error) {
                this.user = null;
                this.isAuthenticated = false;
                
                return { success: false, error: error.response.data.message || 'Login failed' };
            }
            finally {
                this.loading = false;
            }
        },
        
        async logout() {
            this.loading = true;
            try {
                await logoutRequest();

                this.user = null;
                this.isAuthenticated = false;

            } catch (error) {
                console.error('Logout failed:', error);

            } finally {
                this.loading = false;
            }
        },

        async register(data) {

            this.loading = true;

            try {
                await getCsrfCookie();
                
                const response = await registerRequest(data);

                this.user = response.data.data.user;
                this.isAuthenticated = true;
                
                return { success: true, user: this.user };
            } catch (error) {
                this.user = null;
                this.isAuthenticated = false;

                return { success: false, error: error.response.data.message || 'Registration failed' };
            }
            finally {
                this.loading = false;
            }
        },
        async hasPermission(permission) {
            /* if (!this.isAuthenticated || !this.user || !this.user.permissions) {
                return false;
            } */
            //return this.user.permissions.includes(permission);
            return true; // Placeholder: always return true for now
        }
    },
});