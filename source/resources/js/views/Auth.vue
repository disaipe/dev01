<template lang='pug'>
.page
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
import { ref } from 'vue';

import { baseClient } from '../utils/axiosClient';

export default {
    name: 'AuthPage',
    setup() {
        const email = ref();
        const password = ref();
        const domain = ref();

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
                    const { status, redirect } = response.data;

                    if (status && redirect) {
                        window.location.href = redirect;
                    }
                }
            })
        }

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
