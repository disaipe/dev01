import { defineStore } from 'pinia';

const state = () => ({
    formDisplayType: 'drawer'
});

const actions = {

};

export const useProfilesSettingsStore = defineStore('profileSettings', {
    state,
    actions,
    persist: true
});
