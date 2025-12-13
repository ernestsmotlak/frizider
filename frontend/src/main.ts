import {createApp} from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import './assets/main.css'
import router from './router'
import axiosInstance from './api/http'
import { useAuthStore } from './stores/auth'

globalThis.axios = axiosInstance;

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);
app.use(router);

const auth = useAuthStore();
await auth.fetchUser();

app.mount('#app')
