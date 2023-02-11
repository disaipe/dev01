import { defineStore } from 'pinia';

function componentFromRoute(route) {
    return route.matched[route.matched.length - 1].components.default;
}

const state = () => ({
    visitedViews: [],
    cachedViews: []
});

const actions = {
    addView(view) {
        if (!this.visitedViews.some((v) => v.path === view.path)) {
            this.visitedViews.push(
                Object.assign({}, view, {
                    title: view?.meta?.title || 'no-name'
                })
            );
        }

        const name = componentFromRoute(view)?.name;

        if (name && !this.cachedViews.includes(name)) {
            this.cachedViews.push(name);
        }
    },

    delView(view) {
        let idx = this.visitedViews.findIndex((v) => v.path === view.path);
        idx > -1 && this.visitedViews.splice(idx, 1);

        const name = componentFromRoute(view)?.name;

        if (name) {
            idx = this.cachedViews.indexOf(name);
            idx > -1 && this.cachedViews.splice(idx, 1);
        }
    }
};

export const useTabsStore = defineStore('tabs', {
    state,
    actions,
    persist: {
        paths: ['visitedViews', 'cachedViews']
    }
});
