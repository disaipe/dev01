<template lang='pug'>
.app-wrapper
    el-container
        side-bar-menu

        el-container
            el-header
                .flex.justify-between.w-full
                    //- left side
                    .flex.items-center
                        bread-crumbs

                    //- right side
                    .flex.items-center.space-x-2
                        template(v-if='user.isClient')
                            div(
                                :class='company ? "" : "outline outline-offset-2 outline-2 outline-red-400 rounded"'
                            )
                                el-select(
                                    v-model='company'
                                    placeholder='Организация'
                                    no-data-text='Нет доступа к организациям'
                                    @change='onChangeCompany'
                                )
                                    el-option(
                                        v-for='[key, name] of Object.entries(user.companies)'
                                        :value='key'
                                        :label='name'
                                    )

                        el-dropdown(
                            trigger='click'
                        )
                            .flex.space-x-2.items-center
                                el-avatar(
                                    :class='user.isImpersonating ? "outline outline-2 outline-red-400" : ""'
                                    :src='user.avatar'
                                    shape='square'
                                    :size='32'
                                )
                                    icon(icon='tabler:alien-filled' width='28')
                                .text-gray-400 {{ user.name }}

                            template(#dropdown)
                                el-dropdown-menu
                                    el-dropdown-item
                                        a.flex.items-center.space-x-1.text-orange-500(:href='user.isImpersonating')
                                            icon(icon='tabler:spy' height='16')
                                            span Вернуться в свой аккаунт

                                    el-dropdown-item(@click='openSettingsDrawer')
                                        .flex.items-center.space-x-1
                                            icon(icon='material-symbols:display-settings-outline-rounded' height='16')
                                            span Настройки

                                    el-dropdown-item(v-if='user.hasAdminAccess')
                                        a.flex.items-center.space-x-1(href='/admin')
                                            icon(icon='material-symbols:settings-alert' height='16')
                                            span Управление

                                    el-dropdown-item
                                        el-popconfirm(
                                            title='Завершить сеанс?'
                                            @confirm='logout'
                                            width='200'
                                        )
                                            template(#reference)
                                                .flex.items-center.space-x-1
                                                    icon(icon='tabler:logout' height='16')
                                                    span Выйти
            el-main(class='!pr-1')
                //- RouterTabs
                component.pt-1.pr-4.h-full(:is='isRouteScroll ? "el-scrollbar" : "div"')
                    router-view
                    //router-view(v-slot='{ Component }')
                    //    transition(name='fade-transform' mode='out-in')
                    //        keep-alive(:include='cachedViews')
                    //            component(:is='Component')

                el-drawer(
                    v-model='drawer'
                    title='Настройки'
                )
                    el-scrollbar.pr-4
                        .space-y-6
                            div
                                .text-xs.text-gray-400.mb-1.
                                    Способ отображения формы создания и редактирования записей -
                                    в правом боковом меню или модальном окне
                                el-select(v-model='profileSettings.formDisplayType')
                                    el-option(value='drawer' label='Боковое меню')
                                        .flex.items-center.space-x-2
                                            icon.text-xl(icon='tabler:layout-sidebar-right')
                                            span Боковое меню
                                    el-option(value='modal' label='Модальное окно')
                                        .flex.items-center.space-x-2
                                            icon.text-xl(icon='tabler:app-window')
                                            span Модальное окно

                            div
                                .text-xs.text-gray-400.mb-1.
                                    Региональные настройки для форматирования числовых значений в отчетах
                                el-select(
                                    v-model='profileSettings.numberFormatLocale'
                                    @change='onChangeNumberFormatLocale'
                                )
                                    el-option(
                                        v-for='(label, code) of countryCodes'
                                        :value='code'
                                        :label='label'
                                    )

                                .text-xs.text-gray-400.
                                    Разделитель дробной части "{{currentNumberFormatOptions.decimal}}".
                                    Пример отформатированного числа: {{ toFixed(1234.56) }}
            //- el-footer Footer
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';

import { useTabsStore, useProfilesSettingsStore } from '../store/modules';
import usePage from '../utils/usePage';
import { getCountryCodes, getNumberSeparators, toFixed } from '../utils/localeUtils';
import BreadCrumbs from '../components/breadcrumbs/BreadCrumbs.vue';
import SideBarMenu from './components/SideBarMenu.vue';

const { cachedViews } = useTabsStore();
const profileSettings = useProfilesSettingsStore();

const drawer = ref(false);
const company = ref(profileSettings.companyContext);

const router = useRouter();

const { user } = usePage();

const countryCodes = getCountryCodes();

if (! Object.hasOwn(user.companies, profileSettings.companyContext)) {
    company.value = Object.values(user.companies)[0];
    profileSettings.setCompanyContext(company.value);
}

const isRouteScroll = computed(() => router.currentRoute.value.meta?.scroll !== false);
const currentNumberFormatOptions = computed(() => {
    return getNumberSeparators(profileSettings.numberFormatLocale);
});

const openSettingsDrawer = () => drawer.value = true;

const onChangeCompany = () => profileSettings.setCompanyContext(company.value);

const onChangeNumberFormatLocale = (event) => profileSettings.setNumberFormatLocale(event);

const logout = () => window.location.href = '/logout';
</script>

<style scoped lang='postcss'>
.app-wrapper {
    @apply w-full h-full;

    .el-container {
        @apply h-full;
    }
}

.el-header {
    @apply shadow flex items-center;
}

.el-menu {
    @apply flex-1;
}

.el-main {
    @apply relative w-full overflow-hidden;
}
</style>
