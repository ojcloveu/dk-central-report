// resources/js/stores/betStore.js
import { defineStore } from 'pinia';
import betServices from '../services/betServices';

// Initial state range data
const initialRangeState = {
    '7d': { data: [], meta: { current_page: 1, last_page: 1, per_page: 10 } },
    '1m': { data: [], meta: { current_page: 1, last_page: 1, per_page: 10 } },
    '3m': { data: [], meta: { current_page: 1, last_page: 1, per_page: 10 } },
};

export const useBetStore = defineStore('bet', {
    state: () => {
        const today = new Date().toISOString().split('T')[0];

        return {
            data: [],
            meta: {},

            loading: false,
            error: null,

            // State account select and range data
            selectedAccounts: [],
            rangesTables: initialRangeState,
            rangeLoading: false,

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
            const cleanFilters = Object.keys(state.filters).reduce((acc, key) => {
                const value = state.filters[key];

                // Keep the value if not an empty string and not null
                if (value !== '' && value !== null) {
                    acc[key] = value;
                }
                return acc;
            }, {});

            // Construct URLSearchParams from the clean object
            const params = new URLSearchParams(cleanFilters);
            return `/admin/api/bets?${params.toString()}`;
        },

        hasSelectedAccounts: (state) => state.selectedAccounts.length > 0,
    },

    actions: {
        /**
         * Action to fetch bet reports
         */
        async fetchBets() {
            this.loading = true;
            this.error = null;

            // Build query string from current filters state
            const queryString = this.currentApiUrl.split('?')[1];

            try {
                const paginationData = await betServices.fetchBets(queryString);
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

        /**
         * Action to handle page changes
         */
        setPage(page) {
            this.filters.page = page;
            this.fetchBets();
        },

        /**
         * Action to handle sorting
         */
        setSort(sortBy, sortDir) {
            this.filters.sort_by = sortBy;
            this.filters.sort_dir = sortDir;
            this.fetchBets();
        },

        /**
         * Action to apply filters
         */
        applyFilters(newFilters) {
            this.filters.page = 1;
            this.filters = { ...this.filters, ...newFilters };
            this.fetchBets();
        },

        /**
         * Adds or removes an account from the selected list
         */
        toggleAccountSelection(account, isChecked) {
            if (isChecked) {
                if (!this.selectedAccounts.includes(account)) {
                    this.selectedAccounts.push(account);
                }
            } else {
                this.selectedAccounts = this.selectedAccounts.filter(a => a !== account);
            }
            // Trigger the range data fetch immediately after selection changes
            this.fetchRangeData();
        },

        /**
         * Action fetches data for Range Table based on selected accounts and periods
         */
        async fetchRangeData(period = null, page = 1, per_page = null) {
            if (this.selectedAccounts.length === 0) {
                this.rangesTables = initialRangeState;
                return;
            }

            this.rangeLoading = true;

            const periods = period ? [period] : ['7d', '1m', '3m'];

            for (const p of periods) {
                const currentPerPage = per_page || this.rangesTables[p].meta?.per_page;
                const params = {
                    accounts: this.selectedAccounts.join(','),
                    period: p,
                    page: page,
                    per_page: currentPerPage,
                };

                try {
                    const paginationData = await betServices.fetchRangePeriodData(params);

                    this.rangesTables[p] = {
                        data: paginationData.data,
                        meta: {
                            current_page: paginationData.meta.current_page,
                            last_page: paginationData.meta.last_page,
                            per_page: paginationData.meta.per_page,
                            total: paginationData.meta.total,
                            links: paginationData.meta.links || [],
                        },
                    };
                } catch (error) {
                    console.error(`Error fetching range data for ${p}:`, error);
                }
            }

            this.rangeLoading = false;
        },

        /**
         * Action to handle pagination
         */
        setRangePage(period, page) {
            this.fetchRangeData(period, page, this.rangesTables[period].meta.per_page);
        },

        /**
         * Action to handle per-page change
         */
        setRangeItemsPerPage(period, count, page = 1) {
            this.fetchRangeData(period, page, count);
        },

        setPerPage(perPage) {
            this.filters.per_page = perPage;
            this.filters.page = 1;
            this.fetchBets();
        },

        /**
         * Action clear selections and refresh the main table data
         */
        fetchBetsWithReset() {
            this.selectedAccounts = [];
            this.filters.page = 1;
            this.fetchBets();
            this.rangesTables = initialRangeState; 
        },
    },
});