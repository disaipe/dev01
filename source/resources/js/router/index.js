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
                component: () => import('../views/dashboard/index.vue'),
                name: 'dashboard',
                meta: {
                    title: 'Главная'
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
