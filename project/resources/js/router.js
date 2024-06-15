import { createWebHashHistory, createRouter } from "vue-router";
import Login from './components/Login.vue';
import Main from './components/Main.vue';
import { AuthMixin } from '@/mixins/AuthMixin';

const routes = [
    { path: '/', component: Main, name: 'main'},
    { path: '/login', component: Login, name: 'login' }
];

const router = createRouter({
    history: createWebHashHistory(),
    routes: routes,
});

router.beforeEach((to, from, next) => {
    const isAuthenticated = AuthMixin.methods.checkAuthStatus();

    if (to.name !== 'login' && !isAuthenticated) {
        next({ name: 'login' });
    } else {
        next();
    }
});

export default router;
