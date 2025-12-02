import axios from 'axios';

const api = axios.create({
    baseURL: '/',                  // or your API base URL
    withCredentials: true,         // REQUIRED for Sanctum
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',  // Forces JSON responses
    },
});

export default api;
