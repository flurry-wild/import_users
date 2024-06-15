import './bootstrap';

import {createApp} from 'vue/dist/vue.esm-bundler';

import Index from "./components/Index.vue";
import router from './router';

import PrimeVue from 'primevue/config';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';

import 'bootstrap';
import 'primevue/resources/themes/saga-blue/theme.css';
import 'primevue/resources/primevue.min.css';
import 'primeicons/primeicons.css';
import 'primeflex/primeflex.css';


const app = createApp({
    components: {
        Index
    }
});

app.component('Button', Button);
app.component('InputText', InputText);

app.use(router);
app.use(PrimeVue);

app.mount("#app");
