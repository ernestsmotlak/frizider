import axios from 'axios'
import router from '../router'
import {useAuthStore} from '../stores/auth'

const axiosInstance = axios.create({
    baseURL: import.meta.env.VITE_API_URL || '',
    headers: {
        'Accept': 'application/json',
    },
    withCredentials: true,
})

axiosInstance.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            const auth = useAuthStore();
            auth.logout();

            if (!router.currentRoute.value.meta.guest) {
                router.push('/login');
            }
        }

        return Promise.reject(error);
    }
);

export default axiosInstance
