// resources/js/stores/filterStore.js
import { defineStore } from 'pinia';
import filterOptionsServices from '../services/filterOptionsServices';

export const useFilterStore = defineStore('filter', {
    state: () => ({
        accounts: [],
        channels: [],
        masters: [],

        loading: {
            accounts: false,
            channels: false,
            masters: false,
        },

        errors: {
            accounts: null,
            channels: null,
            masters: null,
        },
    }),

    getters: {
        hasAccounts: state => state.accounts.length > 0,
        hasChannels: state => state.channels.length > 0,
        hasMasters: state => state.masters.length > 0,
    },

    actions: {
        /**
         * Fetch Account options
         */
        async fetchAccounts(payload = {}) {
            this.loading.accounts = true;
            this.errors.accounts = null;

            try {
                const response = await filterOptionsServices.fetchAccounts(payload);
                const results = response.results || response.data || [];

                this.accounts = results.map(item => item.text || item.id || item.account || item);
            } catch (error) {
                this.errors.accounts = 'Failed to fetch account list.';
            } finally {
                this.loading.accounts = false;
            }
        },

        /**
         * Fetch Channel options
         */
        async fetchChannels(payload = {}) {
            this.loading.channels = true;
            this.errors.channels = null;

            try {
                const response = await filterOptionsServices.fetchChannels(payload);
                const results = response.results || response.data || [];

                this.channels = results.map(item => item.text || item.id || item.channel || item);
            } catch (error) {
                this.errors.channels = 'Failed to fetch channel list.';
            } finally {
                this.loading.channels = false;
            }
        },

        /**
         * Fetch Master options
         */
        async fetchMasters(payload = {}) {
            this.loading.masters = true;
            this.errors.masters = null;

            try {
                const response = await filterOptionsServices.fetchMasters(payload);
                const results = response.results || response.data || [];

                this.masters = results.map(item => item.text || item.id || item.master || item);
            } catch (error) {
                this.errors.masters = 'Failed to fetch master list.';
            } finally {
                this.loading.masters = false;
            }
        },

        /**
         * Fetch all filters together (parallel requests)
         */
        async fetchAllFilters(payload = {}) {
            this.loading.accounts = this.loading.channels = this.loading.masters = true;

            try {
                const [accountsResp, channelsResp, mastersResp] = await Promise.all([
                    filterOptionsServices.fetchAccounts(payload),
                    filterOptionsServices.fetchChannels(payload),
                    filterOptionsServices.fetchMasters(payload),
                ]);

                const mapResults = (resp, key) => {
                    const results = resp.results || resp.data || [];
                    return results.map(item => item.text || item.id || item[key] || item);
                };

                this.accounts = mapResults(accountsResp, 'account');
                this.channels = mapResults(channelsResp, 'channel');
                this.masters = mapResults(mastersResp, 'master');
            } catch (error) {
                console.error('Failed to fetch all filter data:', error);
            } finally {
                this.loading.accounts = this.loading.channels = this.loading.masters = false;
            }
        },
    },
});
