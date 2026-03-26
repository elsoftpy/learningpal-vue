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
        clearAuthState() {
            this.user = null;
            this.isAuthenticated = false;
        },

        async ensureCsrf(force = false) {
            if (force || !this.csrfLoaded) {
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
                this.clearAuthState();
                
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
                this.isAuthenticated = true;
                this.user = response.data.data.user;

                return {success: true, user: response.data.data.user};

            } catch (error) {
                this.clearAuthState();
                
                throw error;

            } finally {
                this.loading = false;
            }
        },
        
        async logout() {
            this.loading = true;
            try {
                await this.ensureCsrf(true);
                await logoutRequest();

                this.clearAuthState();

            } catch (error) {
                const status = error?.response?.status;

                // If the session or CSRF token already expired, treat logout as complete
                // and let the UI continue to the login screen.
                if (status === 401 || status === 419) {
                    this.clearAuthState();
                    this.ready = true;
                    this.csrfLoaded = false;
                    return;
                }
                
                throw error;

            } finally {
                this.loading = false;
                this.csrfLoaded = false;
            }
        },

        async register(data) {

            this.loading = true;

            try {
                await this.ensureCsrf();
                
                const response = await registerRequest(data);
                this.isAuthenticated = true;
                this.user = response.data.data.user;

                return {success: true, user: this.user};
                
            } catch (error) {
                this.clearAuthState();

                throw error;
            }
            finally {
                this.loading = false;
            }
        },
        
        setUser(user) {
            this.user = user;
        }
    },
});
