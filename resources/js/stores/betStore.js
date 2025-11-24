// resources/js/stores/betStore.js
import { defineStore } from 'pinia';
import betServices from '../services/betServices';

// Helper function to get the current date in YYYY-MM-DD format
const getTodayDate = () => new Date().toISOString().split('T')[0];

// Helper function to define and return the initial filter state
const getInitialFilters = () => ({
    page: 1,
    per_page: 10,
    sort_by: 'trandate',
    sort_dir: 'desc',
    account: '',
    trandate: { start_date: null, end_date: null },
    master: [],
    channel: '',
});

// Initial state range data
const initialRangeState = {
    tm: { data: [] },
    '1m': { data: [] },
    '3m': { data: [] },
};

export const useBetStore = defineStore('bet', {
    state: () => {
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
                ...getInitialFilters(),
                trandate: { start_date: getTodayDate(), end_date: getTodayDate() },
            },
        };
    },

    getters: {
        // Getter to check if we have data
        hasBets: state => state.data.length > 0,

        // Getter to prepare URL for current state
        currentApiUrl: state => {
            const params = new URLSearchParams();

            Object.entries(state.filters).forEach(([key, value]) => {
                if (value === '' || value === null) return;

                // Handle date range object
                if (key === 'trandate' && typeof value === 'object') {
                    if (value.start_date) params.append('start_date', value.start_date);
                    if (value.end_date) params.append('end_date', value.end_date);
                } else if (Array.isArray(value)) {
                    value.forEach(v => params.append(key, v));
                } else {
                    params.append(key, value);
                }
            });

            return `/admin/api/bets?${params.toString()}`;
        },

        hasSelectedAccounts: state => state.selectedAccounts.length > 0,
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
         * Action to select/deselect all accounts currently displayed
         */
        toggleAllAccountsSelection(selectAll) {
            const currentAccounts = this.data.map(bet => bet.account);

            if (selectAll) {
                // Merge current accounts with selected accounts
                const uniqueNewAccounts = currentAccounts.filter(
                    acc => !this.selectedAccounts.includes(acc)
                );
                this.selectedAccounts = [...this.selectedAccounts, ...uniqueNewAccounts];
            } else {
                // Only remove accounts visible on the current page
                this.selectedAccounts = this.selectedAccounts.filter(
                    acc => !currentAccounts.includes(acc)
                );
            }
            this.fetchRangeData();
        },

        /**
         * Action to clear all selected accounts and refresh range data.
         */
        clearAllSelectedAccounts() {
            this.selectedAccounts = [];
            this.rangesTables = initialRangeState;
            this.fetchRangeData();
        },

        /**
         * Action fetches data for Range Table based on selected accounts and periods
         */
        async fetchRangeData(
            period = null,
            page = null,
            per_page = null,
            sort_by = null,
            sort_dir = null
        ) {
            if (this.selectedAccounts.length === 0) {
                this.rangesTables = initialRangeState;
                return;
            }

            this.rangeLoading = true;

            const periods = period ? [period] : ['tm', '1m', '3m'];

            for (const p of periods) {
                const params = {
                    accounts: this.selectedAccounts.join(','),
                    period: p,
                };

                // Add sort param if provided
                if (sort_by) {
                    params.sort_by = sort_by;
                }
                if (sort_dir) {
                    params.sort_dir = sort_dir;
                }

                try {
                    const response = await betServices.fetchRangePeriodData(params);

                    this.rangesTables[p] = {
                        data: response.data,
                        date_range: response.date_range || null,
                    };
                } catch (error) {
                    console.error(`Error fetching range data for ${p}:`, error);
                }
            }

            this.rangeLoading = false;
        },

        /**
         * Action to handle per-page change
         */
        setPerPage(perPage) {
            this.filters.per_page = perPage;
            this.filters.page = 1;
            this.fetchBets();
        },

        /**
         * Action clear selections and refresh the main table data
         */
        fetchBetsWithReset() {
            this.filters = getInitialFilters();
            this.selectedAccounts = [];
            this.rangesTables = initialRangeState;
            this.fetchBets();
        },

        /**
         * Action to refetch bets and period data
         */
        refetchBetsAndPeriod() {
            this.fetchBets();
            this.fetchRangeData();
        },
    },
});
