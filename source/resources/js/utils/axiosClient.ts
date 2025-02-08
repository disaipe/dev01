import axios, { type RawAxiosRequestHeaders } from 'axios';

const csrfTokenMeta = document.querySelector('meta[name=csrf_token]');
export const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : null;

const headers: RawAxiosRequestHeaders = {
    'X-CSRF-TOKEN': csrfToken || ''
};

export const baseClient = axios.create({ headers });

const api = axios.create({ baseURL: '/api/web', headers });

export function useApi() {
    return api;
}
