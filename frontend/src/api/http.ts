import axios from 'axios'

const axiosInstance = axios.create({
    headers: {
        'Accept': 'application/json',
    },
    withCredentials: true,
})

export default axiosInstance
