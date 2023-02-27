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
                path: 'report_template/:id',
                component: () => import('../views/dashboard/record/ReportTemplate.vue'),
                name: 'ReportTemplateRecord',
                meta: {
                    scroll: false
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
