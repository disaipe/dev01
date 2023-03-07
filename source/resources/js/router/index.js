import { createRouter, createWebHistory } from 'vue-router';

import Layout from '../layout/index.vue';

const routes = [
    {
        path: '/',
        redirect: '/dashboard'
    },
    {
        path: '/dashboard',
        component: Layout,
        name: 'dashboard-root',
        meta: {
            title: 'Главная',
        },
        children: [
            {
                path: '',
                component: () => import('../views/dashboard/Index.vue'),
                name: 'dashboard',
                meta: {
                    title: 'Главная'
                }
            },
            {
                path: 'invoice',
                component: () => import('../views/dashboard/report/InvoiceReport.vue'),
                name: 'report-invoice',
                meta: {
                    title: 'Отчет'
                }
            }
        ]
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

export default router;
