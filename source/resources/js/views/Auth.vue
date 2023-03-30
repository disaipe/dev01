<template lang='pug'>
.page
    .flex.flex-col
        .text-center.pb-4
            el-image.w-64(src='/images/logo_lg.png')
        .form
            .space-y-2
                el-input(v-model='email' size='large')
                el-input(v-model='password' size='large' show-password )

                .flex.items-center.justify-between
                    el-select(v-model='domain' clearable)
                        el-option(:value='1' label='GW-AD')

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

        return {
            email,
            password,
            domain,

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
