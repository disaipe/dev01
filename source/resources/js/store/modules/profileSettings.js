import { defineStore } from 'pinia';

import { setCookie, getCookie, deleteCookie } from '../../utils/cookieUtils';

const COMPANY_CONTEXT_COOKIE = 'company-context';

const state = () => ({
    formDisplayType: 'drawer',
    companyContext: getCookie(COMPANY_CONTEXT_COOKIE)
});

const actions = {
    setCompanyContext(companyId) {
        if (this.companyContext !== companyId) {
            this.companyContext = companyId;
        }

        if (this.companyContext) {
            const expires = new Date();
            expires.setFullYear(expires.getFullYear() + 1);

            setCookie(COMPANY_CONTEXT_COOKIE, this.companyContext, { expires });
        } else {
            deleteCookie(COMPANY_CONTEXT_COOKIE);
        }
    }
};

export const useProfilesSettingsStore = defineStore('profileSettings', {
    state,
    actions,
    persist: true
});
