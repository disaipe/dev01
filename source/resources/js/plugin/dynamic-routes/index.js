import { defineComponent } from 'vue';
import Reference from '../../views/dashboard/reference/BaseReference.vue';
import Record from '../../views/dashboard/record/BaseRecord.vue';

const resolveDefaultComponent = (component, name) => {
  return Promise.resolve(defineComponent({ ...component, name }));
};

const referenceComponent = (route) => {
    return route.meta.view
        ? import(`../../views/dashboard/reference/${route.meta.view}.vue`)
        : resolveDefaultComponent(Reference,`Reference${route.name}`);
}

const recordComponent = (route) => {
    return route.meta.view
        ? import(`../../views/dashboard/record/${route.meta.view}.vue`)
        : resolveDefaultComponent(Record,`Record${route.name}`);
}

export default {
    install(app) {
        const router = app.config.globalProperties.$router;
        const routes = app.config.globalProperties.$page.routes;

        for (const route of routes) {
            if (Array.isArray(route.children)) {
                for (const childRoute of route.children) {
                    if (childRoute.meta?.isReference) {
                        childRoute.component = () => referenceComponent(childRoute);
                    } else if (childRoute.meta?.isRecord) {
                        childRoute.component = () => recordComponent(childRoute);
                    }
                }
            }

            router.addRoute('dashboard-root', route);
        }

        // Manually resolve and replace location
        // Without it, you will see empty screen on reference routes
        const resolved = router.resolve(window.location.pathname);
        if (resolved.matched.length) {
            router.replace(resolved.href);
        } else {
            router.replace('/');
        }
    }
}
