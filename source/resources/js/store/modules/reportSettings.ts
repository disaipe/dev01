import { defineStore } from 'pinia';

interface State {
    company: string | undefined | null;
    reportTemplate: string | undefined | null;
    period: string | undefined | null;
}

export const useReportSettingsStore = defineStore('reportSettings', {
    state: (): State => ({
        company: null,
        reportTemplate: null,
        period: null
    }),
    
    persist: true
});
