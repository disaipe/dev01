import axios, { type RequestHeaders } from 'redaxios';

const csrfTokenMeta = document.querySelector('meta[name=csrf_token]');
export const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : null;

const headers: RequestHeaders = {
    'X-CSRF-TOKEN': csrfToken || ''
};

export const baseClient = axios.create({ headers });

const api = axios.create({ baseURL: '/api/web', headers });

export function useApi() {
    return api;
}
