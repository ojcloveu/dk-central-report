import { defineStore } from 'pinia';
import filterOptionsServices from '../services/filterOptionsServices';

export const useFilterStore = defineStore('filter', {
    state: () => ({
        accounts: [],
        channels: [],
        masters: [],

        // Pagination metadata
        pagination: {
            accounts: {
                currentPage: 1,
                hasNextPage: false,
                perPage: 5,
                total: 0,
            },
            channels: {
                currentPage: 1,
                hasNextPage: false,
                perPage: 5,
                total: 0,
            },
            masters: {
                currentPage: 1,
                hasNextPage: false,
                perPage: 5,
                total: 0,
            },
        },

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
        async fetchAccounts(payload = {}, append = false) {
            this.loading.accounts = true;
            this.errors.accounts = null;

            try {
                const page = payload.page || 1;
                const response = await filterOptionsServices.fetchAccounts({ ...payload, page });

                // Handle Laravel pagination response
                const results = response.data || [];
                const mappedResults = results.map(
                    item => item.account || item
                );

                // Append or replace items
                if (append) {
                    this.accounts = [...this.accounts, ...mappedResults];
                } else {
                    this.accounts = mappedResults;
                }

                // Update pagination metadata
                this.pagination.accounts = {
                    currentPage: response.current_page || 1,
                    hasNextPage: !!response.next_page_url,
                    perPage: response.per_page || 5,
                    total: response.total || results.length,
                };
            } catch (error) {
                this.errors.accounts = 'Failed to fetch account list.';
                console.error('Fetch accounts error:', error);
            } finally {
                this.loading.accounts = false;
            }
        },

        /**
         * Fetch Channel options
         */
        async fetchChannels(payload = {}, append = false) {
            this.loading.channels = true;
            this.errors.channels = null;

            try {
                const page = payload.page || 1;
                const response = await filterOptionsServices.fetchChannels({ ...payload, page });

                const results = response.data || [];
                const mappedResults = results.map(
                    item => item.channel_name || item.id || item
                );

                if (append) {
                    this.channels = [...this.channels, ...mappedResults];
                } else {
                    this.channels = mappedResults;
                }

                this.pagination.channels = {
                    currentPage: response.current_page || 1,
                    hasNextPage: !!response.next_page_url,
                    perPage: response.per_page || 5,
                    total: response.total || results.length,
                };
            } catch (error) {
                this.errors.channels = 'Failed to fetch channel list.';
                console.error('Fetch channels error:', error);
            } finally {
                this.loading.channels = false;
            }
        },

        /**
         * Fetch Master options
         */
        async fetchMasters(payload = {}, append = false) {
            this.loading.masters = true;
            this.errors.masters = null;

            try {
                const page = payload.page || 1;
                const response = await filterOptionsServices.fetchMasters({ ...payload, page });

                const results = response.data || [];
                const mappedResults = results.map(
                    item => item.master || item
                );

                if (append) {
                    this.masters = [...this.masters, ...mappedResults];
                } else {
                    this.masters = mappedResults;
                }

                this.pagination.masters = {
                    currentPage: response.current_page || 1,
                    hasNextPage: !!response.next_page_url,
                    perPage: response.per_page || 5,
                    total: response.total || results.length,
                };
            } catch (error) {
                this.errors.masters = 'Failed to fetch master list.';
                console.error('Fetch masters error:', error);
            } finally {
                this.loading.masters = false;
            }
        },

        /**
         * Load more accounts (for infinite scroll)
         */
        async loadMoreAccounts(page, query = '') {
            if (!this.pagination.accounts.hasNextPage || this.loading.accounts) return;
            await this.fetchAccounts({ page, q: query }, true);
        },

        /**
         * Load more channels (for infinite scroll)
         */
        async loadMoreChannels(page, query = '') {
            if (!this.pagination.channels.hasNextPage || this.loading.channels) return;
            await this.fetchChannels({ page, q: query }, true);
        },

        /**
         * Load more masters (for infinite scroll)
         */
        async loadMoreMasters(page, query = '') {
            if (!this.pagination.masters.hasNextPage || this.loading.masters) return;
            await this.fetchMasters({ page, q: query }, true);
        },

        /**
         * Reset a specific filter
         */
        resetFilter(filterType) {
            if (filterType === 'accounts') {
                this.accounts = [];
                this.pagination.accounts = {
                    currentPage: 1,
                    hasNextPage: false,
                    perPage: 5,
                    total: 0,
                };
            } else if (filterType === 'channels') {
                this.channels = [];
                this.pagination.channels = {
                    currentPage: 1,
                    hasNextPage: false,
                    perPage: 5,
                    total: 0,
                };
            } else if (filterType === 'masters') {
                this.masters = [];
                this.pagination.masters = {
                    currentPage: 1,
                    hasNextPage: false,
                    perPage: 5,
                    total: 0,
                };
            }
        },

        /**
         * Fetch all filters together (parallel requests)
         */
        async fetchAllFilters(payload = {}) {
            this.loading.accounts = this.loading.channels = this.loading.masters = true;

            try {
                const [accountsResp, channelsResp, mastersResp] = await Promise.all([
                    filterOptionsServices.fetchAccounts({ ...payload, page: 1 }),
                    filterOptionsServices.fetchChannels({ ...payload, page: 1 }),
                    filterOptionsServices.fetchMasters({ ...payload, page: 1 }),
                ]);

                const mapResults = (resp, key) => {
                    const results = resp.data || [];
                    return results.map(item => item[key] || item);
                };

                this.accounts = mapResults(accountsResp, 'account');
                this.channels = mapResults(channelsResp, 'channel_name'); 
                this.masters = mapResults(mastersResp, 'master');

                // Update pagination metadata
                this.pagination.accounts = {
                    currentPage: accountsResp.current_page || 1,
                    hasNextPage: !!accountsResp.next_page_url,
                    perPage: accountsResp.per_page || 5,
                    total: accountsResp.total || 0,
                };
                this.pagination.channels = {
                    currentPage: channelsResp.current_page || 1,
                    hasNextPage: !!channelsResp.next_page_url,
                    perPage: channelsResp.per_page || 5,
                    total: channelsResp.total || 0,
                };
                this.pagination.masters = {
                    currentPage: mastersResp.current_page || 1,
                    hasNextPage: !!mastersResp.next_page_url,
                    perPage: mastersResp.per_page || 5,
                    total: mastersResp.total || 0,
                };
            } catch (error) {
                console.error('Failed to fetch all filter data:', error);
            } finally {
                this.loading.accounts = this.loading.channels = this.loading.masters = false;
            }
        },
    },
});
