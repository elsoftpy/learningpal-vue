import axios from '../axios';

export async function csrfCookie() {
    return axios.get('/sanctum/csrf-cookie');
}

export async function loginRequest(credentials) {
  return axios.post('/auth/login', credentials);
}

export async function registerRequest(data) {
  return axios.post('/auth/register', data);
}

export async function logoutRequest() {
  return axios.post('/auth/logout');
}

export async function fetchUser() {
  return axios.get('/auth/me');
}

export async function checkAuth() {
  try {
    await fetchUser();
    return true;
  } catch (error) {
    return false;
  }
}
