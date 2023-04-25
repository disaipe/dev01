<template lang='pug'>
.page
    .flex.flex-col
        .text-center.pb-4
            el-image.w-64(src='/images/logo_lg.png')
        .form
            .space-y-2
                el-input(v-model='email' size='large')
                el-input(v-model='password' size='large' show-password)

                .flex.items-center.justify-between
                    div
                        div(v-show='domains?.length')
                            el-select(v-model='domain' clearable)
                                el-option(
                                    v-for='(name, id) of domains'
                                    :value='id'
                                    :label='name'
                                )

                    el-button(type='primary' @click='doLogin') Войти
</template>

<script>
import { ref, getCurrentInstance } from 'vue';

import { baseClient } from '../utils/axiosClient';

export default {
    name: 'AuthPage',
    setup() {
        const app = getCurrentInstance();
        const { $alert } = app.appContext.config.globalProperties;

        const email = ref();
        const password = ref();
        const domain = ref();
        const domains = ref();

        const storeCredentials = (email, domain) => {
            window.localStorage.setItem('q_auth', btoa(JSON.stringify({
                e: email,
                d: domain
            })));
        };

        const getCredentials = () => {
            const data = window.localStorage.getItem('q_auth');

            if (data) {
                try {
                    const { e, d } = JSON.parse(atob(data));
                    email.value = e;
                    domain.value = d;
                } catch (error) {
                }
            }
        };

        const getDomains = () => {
            const data = window.localStorage.getItem('domains');

            if (data) {
                try {
                    const d = JSON.parse(atob(data));

                    if (typeof(d) === 'object') {
                        domains.value = d;
                    }
                } catch (error) {
                }
            }
        }

        const doLogin = () => {
            baseClient.post('/login', {
                email: email.value,
                password: password.value,
                domain: domain.value
            }, {
                headers: {
                    'Accept': 'application/json'
                }
            }).then((response) => {
                if (response.ok) {
                    const { status, redirect, message } = response.data;

                    if (status && redirect) {
                        storeCredentials(email.value, domain.value);
                        window.location.href = redirect;
                    }

                    if (!status && message) {
                        $alert(message, 'Ошибка', { type: 'error' });
                    }
                }
            }).catch((response) => {
                const { data } = response;

                if (data) {
                    const { message } = data;

                    if (message) {
                        $alert(message, 'Ошибка', { type: 'error' });
                    }
                }
            });
        }

        getCredentials();
        getDomains();

        return {
            email,
            password,
            domain,
            domains,

            doLogin
        };
    }
}
</script>

<style lang='postcss' scoped>
.page {
    @apply relative h-screen flex justify-center items-center overflow-hidden;

    .form {
        @apply p-8 bg-slate-100 rounded-md z-30;
    }
}
</style>
