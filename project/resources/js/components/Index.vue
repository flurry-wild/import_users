<template>
    <div class="menu">
        <router-link :to="{ name: 'login' }" class="m-2" v-if="!isLoggedIn">Войти</router-link>
        <router-link :to="{ name: 'main' }" class="m-2" v-if="isLoggedIn">Главная</router-link>
        <a @click.prevent="logout" href="#" v-if="isLoggedIn">Выйти</a>
    </div>

    <br>

    <router-view></router-view>
</template>
<script>
import { AuthMixin } from '@/mixins/AuthMixin';

export default {
    name: 'Index',
    mixins: [AuthMixin],

    data() {
        return {
            isLoggedIn: false
        };
    },
    mounted() {
        this.isLoggedIn = AuthMixin.methods.checkAuthStatus();
    },
    methods: {
        logout() {
            axios.post('/logout').then(res => {
                localStorage.removeItem('x_xsrf_token');

                window.location.href = '/login';
            })
        }
    }
}
</script>
