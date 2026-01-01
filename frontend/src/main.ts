import {createApp} from 'vue'
import {createPinia} from 'pinia'
import App from './App.vue'
import './assets/main.css'
import router from './router'
import axiosInstance from './api/http'
import {useAuthStore} from './stores/auth'
import toastPlugin from './plugins/toast'
import confirmPlugin from './plugins/confirm'
import {formatTime} from "./utils/formatTime.ts";

globalThis.axios = axiosInstance;

const app = createApp(App);
const pinia = createPinia();

app.config.globalProperties.$formatTime = formatTime;

app.use(pinia);
app.use(router);
app.use(toastPlugin);
app.use(confirmPlugin);

const auth = useAuthStore();
await auth.fetchUser();

app.mount('#app')
