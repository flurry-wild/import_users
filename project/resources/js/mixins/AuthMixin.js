export const AuthMixin = {
    methods: {
        checkAuthStatus() {
            return localStorage.getItem('x_xsrf_token') !== null;
        }
    }
};

