<template>
    <div class="container">
        <div class="col-md-6">
            <InputText v-model="email" type="email" placeholder="email" class="m-2"/>
            <InputText v-model="password" type="password" placeholder="пароль" class="m-2"/>
            <Button @click.prevent="login" label="Войти" class="m-2"></Button>
        </div>
    </div>
</template>
<script>
export default {
    name: "Login",

    data() {
        return {
            email: null,
            password: null
        }
    },

    methods: {
        login() {
            axios.get('/sanctum/csrf-cookie').then(response => {
                //console.log(response);

                axios.post('/login', { email: this.email, password: this.password }).then(r => {
                    if (r.status === 204) {
                        localStorage.setItem('x_xsrf_token', this.getCookie('XSRF-TOKEN'));
                    }

                    this.$router.push({name:'main'});
                });
            });
        },
        getCookie(name) {
            const value = `; ${document.cookie}`;
            const parts = value.split(`; ${name}=`);
            if (parts.length === 2) {
                return parts.pop().split(';').shift();
            }
        }
    }
}
</script>
