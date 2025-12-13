import {createApp} from 'vue'
import App from './App.vue'
import './assets/main.css'
import router from './router'
import axiosInstance from './api/http'

globalThis.axios = axiosInstance;

createApp(App).use(router).mount('#app')
