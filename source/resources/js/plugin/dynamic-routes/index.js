import { defineComponent } from 'vue';
import Reference from '../../views/dashboard/reference/reference.vue';

export default {
    install(app) {
        const router = app.config.globalProperties.$router;
        const routes = app.config.globalProperties.$page.routes;

        for (const route of routes) {
            const component = route.meta.view
                ? import(`../../views/dashboard/reference/${route.meta.view}.vue`)
                : Promise.resolve(defineComponent({
                    ...Reference,
                    name: `Reference${route.name}`
                }));

            router.addRoute('dashboard-root', {
                ...route,
                component: () => component,
                meta: {
                    ...route.meta,
                    title: route.meta?.title || route.name,
                    isReference: true
                }
            });
        }

        // Manually resolve and replace location
        // Without it you will see empty screen on reference routes
        const resolved = router.resolve(window.location.pathname);
        if (resolved.matched.length) {
            router.replace(resolved.href);
        } else {
            router.replace('/');
        }
    }
}
