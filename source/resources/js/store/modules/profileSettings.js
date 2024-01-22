import { defineStore } from 'pinia';

import { setCookie, getCookie, deleteCookie } from '../../utils/cookieUtils';
import { setNumberFormatLanguage } from '../../utils/localeUtils';

const COMPANY_CONTEXT_COOKIE = 'company-context';

const state = () => ({
    formDisplayType: 'drawer',
    numberFormatLocale: 'ru-RU',
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

            setCookie(COMPANY_CONTEXT_COOKIE, this.companyContext, { expires, SameSite: 'None' });
        } else {
            deleteCookie(COMPANY_CONTEXT_COOKIE);
        }
    },

    setNumberFormatLocale(locale) {
        setNumberFormatLanguage(locale);
    }
};

export const useProfilesSettingsStore = defineStore('profileSettings', {
    state,
    actions,
    persist: {
        afterRestore({ store }) {
            setNumberFormatLanguage(store.numberFormatLocale);
        }
    }
});
