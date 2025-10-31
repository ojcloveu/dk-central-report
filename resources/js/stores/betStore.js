// resources/js/stores/betStore.js
import { defineStore } from 'pinia';
import axios from 'axios';

export const useBetStore = defineStore('bet', {
    state: () => {
        const today = new Date().toISOString().split('T')[0];

        return {
            data: [],
            meta: {},

            loading: false,
            error: null,

            filters: {
                page: 1,
                per_page: 10,
                sort_by: 'trandate',
                sort_dir: 'desc',
                account: '',
                trandate: today,
                master: '',
                channel: '',
            },
        };
    },

    getters: {
        // Getter to check if we have data
        hasBets: state => state.data.length > 0,

        // Getter to prepare URL for current state
        currentApiUrl: state => {
            // Create a clean object with only non-empty filters
            const cleanFilters = Object.keys(state.filters).reduce((acc, key) => {
                const value = state.filters[key];

                // Keep the value if it's not an empty string and not null
                if (value !== '' && value !== null) {
                    acc[key] = value;
                }
                return acc;
            }, {});

            // Construct URLSearchParams from the clean object
            const params = new URLSearchParams(cleanFilters);
            return `/admin/api/bets?${params.toString()}`;
        },
    },

    actions: {
        // Main action to fetch data from the API
        async fetchBets() {
            this.loading = true;
            this.error = null;

            // Build query string from current filters state
            const url = this.currentApiUrl;

            try {
                const response = await axios.get(url);
                const paginationData = response.data;

                // Update state with API response
                this.data = paginationData.data;
                this.meta = {
                    current_page: paginationData.meta.current_page,
                    last_page: paginationData.meta.last_page,
                    per_page: paginationData.meta.per_page,
                    total: paginationData.meta.total,
                    links: paginationData.meta.links,
                };
            } catch (error) {
                console.error('Failed to fetch bet reports:', error);
                this.error = 'Failed to load data. Please check network and permissions.';
            } finally {
                this.loading = false;
            }
        },

        // Action to handle page changes
        setPage(page) {
            this.filters.page = page;
            this.fetchBets();
        },

        // Action to handle sorting
        setSort(sortBy, sortDir) {
            this.filters.sort_by = sortBy;
            this.filters.sort_dir = sortDir;
            this.fetchBets();
        },

        // Action to apply filters
        applyFilters(newFilters) {
            // Reset to page 1 when new filters are applied
            this.filters.page = 1;

            // Merge new filters with existing ones
            this.filters = { ...this.filters, ...newFilters };
            this.fetchBets();
        },

        setPerPage(perPage) {
            this.filters.per_page = perPage;
            this.filters.page = 1; // reset to first page
            this.fetchBets();
        },
    },
});
