import axios from 'axios'

const axiosInstance = axios.create({
    headers: {
        'Accept': 'application/json',
    },
})

export default axiosInstance
