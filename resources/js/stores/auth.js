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
                this.isAuthenticated = true;
                this.user = response.data.data.user;

                return {success: true, user: this.user};

            } catch (error) {
                this.user = null;
                this.isAuthenticated = false;
                
                throw error;

            } finally {
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
                
                throw error;

            } finally {
                this.loading = false;
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
                this.user = null;
                this.isAuthenticated = false;

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