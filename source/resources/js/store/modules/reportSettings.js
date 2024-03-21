import { defineStore } from 'pinia';

const state = () => ({
    company: null,
    reportTemplate: null,
    period: null,
    extended: false,
});

export const useReportSettingsStore = defineStore('reportSettings', {
    state,
    persist: true
});
