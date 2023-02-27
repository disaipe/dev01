<template lang='pug'>
.router-tabs-container
    el-scrollbar(always)
        .wrapper
            router-link.view-link(
                v-for='view of visitedViews'
                :to='{ path: view.path, query: view.query, fullPath: view.fullPath }'
                :class='isActive(view) ? "active" : ""'
                tag ='span'
            )
                .inline-flex.items-center.space-x-1
                    div {{ view.title }}

                    el-link.icon-close(
                        icon='Close'
                        :underline='false'
                        @click.stop.prevent='closeView(view)'
                    )
</template>

<script>
import { toRef, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';

import { useTabsStore} from '../../store/modules/tabs';

export default {
    name: 'RouterTabs',
    setup() {
        const route = useRoute();
        const router = useRouter();

        const store = useTabsStore();
        const { addView, delView } = useTabsStore();
        const visitedViews = toRef(store, 'visitedViews');

        watch(route, () => {
            addView(route);
        });

        addView(route);

        const toLastView = () => {
            if (Array.isArray(visitedViews) && visitedViews.value.length) {
                const [latestView] = visitedViews.value.splice(-1);

                if (latestView) {
                    router.push(latestView.fullPath);
                    return;
                }
            }

            router.push('/dashboard');
        };
        const isActive = (view) => view.path === route.path;
        const closeView = (view) => {
            delView(view);

            isActive(view) && toLastView();
        };

        return {
            visitedViews,
            isActive,
            closeView
        }
    }
}
</script>

<style lang='postcss' scoped>
.router-tabs-container {
    @apply h-9 w-full;

    .wrapper {
        @apply flex items-center space-x-1;

        .view-link {
            @apply
                shrink-0
                h-6 px-2
                inline-block relative
                border border-slate-200
                bg-white
                text-sm cursor-pointer;

            &.active {
                @apply text-slate-600 bg-sky-300;

                &:before {
                    @apply w-2 h-2 mr-1 inline-block relative bg-white rounded-full;
                    content: '';
                }

                .icon-close {
                    @apply text-white;
                }
            }

            .icon-close {
                font-size: 12px;
            }
        }
    }
}
</style>
