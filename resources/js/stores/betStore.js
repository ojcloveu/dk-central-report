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
    all_time: { data: [] },
};

// Initial cache state for account data per period
const initialCacheState = {
    tm: {},
    '1m': {},
    '3m': {},
    all_time: {},
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
            showAllTimeReport: false,
            rangePerPage: 10,
            rangeSort: {
                sort_by: 'account',
                sort_dir: 'asc',
            },

            // Cache for account data per period
            accountDataCache: initialCacheState,

            // External summary data (deposit/withdraw/profit) cache by account
            externalSummaryCache: {},
            externalSummaryLoading: false,

            // Highlighted accounts
            highlightedAccounts: {},

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
                    if (value.length > 0) {
                        params.append(key, value.join(','));
                    }
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
         * Action to handle per-page change
         */
        setPerPage(perPage) {
            this.filters.per_page = perPage;
            this.filters.page = 1;
            this.fetchBets();
        },

        /**
         * Action to set range per page
         */
        setRangePerPage(perPage) {
            this.rangePerPage = perPage;
        },

        /**
         * Action to set range sort
         */
        setRangeSort(sortBy, sortDir) {
            this.rangeSort.sort_by = sortBy;
            this.rangeSort.sort_dir = sortDir;
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
        async toggleAccountSelection(account, isChecked) {
            if (isChecked) {
                if (!this.selectedAccounts.includes(account)) {
                    this.rangeLoading = true;
                    this.selectedAccounts.push(account);
                    // Fetch data only for the new account if not cached
                    await this.fetchAccountData(account);
                    // Fetch external summary only if account is not cached
                    if (!this.externalSummaryCache[account]) {
                        await this.fetchExternalSummary();
                    }
                }
            } else {
                // Remove from selected accounts
                this.selectedAccounts = this.selectedAccounts.filter(a => a !== account);
            }
            // Rebuild display from cache
            this.rebuildRangeTablesFromCache();
            this.rangeLoading = false;
        },

        /**
         * Action to select/deselect all accounts currently displayed
         */
        async toggleAllAccountsSelection(selectAll) {
            const currentAccounts = this.data.map(bet => bet.account);

            if (selectAll) {
                this.rangeLoading = true;
                // Find accounts that need to be added
                const uniqueNewAccounts = currentAccounts.filter(
                    acc => !this.selectedAccounts.includes(acc)
                );
                this.selectedAccounts = [...this.selectedAccounts, ...uniqueNewAccounts];

                // Fetch data only for uncached accounts
                await this.fetchMultipleAccountsData(uniqueNewAccounts);
                // Fetch external summary for new accounts
                await this.fetchExternalSummary();
            } else {
                // Only remove accounts visible on the current page
                this.selectedAccounts = this.selectedAccounts.filter(
                    acc => !currentAccounts.includes(acc)
                );
            }

            // Rebuild display from cache
            this.rebuildRangeTablesFromCache();
            this.rangeLoading = false;
        },

        /**
         * Action to clear all selected accounts and cache
         */
        clearAllSelectedAccounts() {
            this.selectedAccounts = [];
            this.rangesTables = initialRangeState;
            this.accountDataCache = initialCacheState;
            this.externalSummaryCache = {};
            this.showAllTimeReport = false;
            this.highlightedAccounts = {};
        },

        /**
         * Fetch data for a single account across all periods and add to cache
         */
        async fetchAccountData(account) {
            const periods = ['tm', '1m', '3m'];

            for (const period of periods) {
                // Skip if already cached
                if (this.accountDataCache[period][account]) {
                    continue;
                }

                try {
                    const params = {
                        accounts: account,
                        period: period,
                    };

                    const response = await betServices.fetchRangePeriodData(params);

                    // Cache the account data
                    if (response.data && response.data.length > 0) {
                        this.accountDataCache[period][account] = response.data[0];
                    }

                    // Store date_range if not already stored
                    if (!this.rangesTables[period].date_range && response.date_range) {
                        this.rangesTables[period].date_range = response.date_range;
                    }
                } catch (error) {
                    console.error(
                        `Error fetching data for account ${account}, period ${period}:`,
                        error
                    );
                }
            }
        },

        /**
         * Fetch data for multiple accounts and add to cache
         */
        async fetchMultipleAccountsData(accounts) {
            if (!accounts || accounts.length === 0) return;

            const periods = ['tm', '1m', '3m'];

            for (const period of periods) {
                // Filter out already cached accounts
                const uncachedAccounts = accounts.filter(
                    acc => !this.accountDataCache[period][acc]
                );

                if (uncachedAccounts.length === 0) continue;

                try {
                    const params = {
                        accounts: uncachedAccounts,
                        period: period,
                    };

                    const response = await betServices.fetchRangePeriodData(params);

                    // Cache each account's data
                    if (response.data) {
                        response.data.forEach(accountData => {
                            this.accountDataCache[period][accountData.account] = accountData;
                        });
                    }

                    // Store date_range if not already stored
                    if (!this.rangesTables[period].date_range && response.date_range) {
                        this.rangesTables[period].date_range = response.date_range;
                    }
                } catch (error) {
                    console.error(`Error fetching data for period ${period}:`, error);
                }
            }
        },

        /**
         * Rebuild range tables from cache based on selected accounts
         */
        rebuildRangeTablesFromCache() {
            const periods = ['tm', '1m', '3m'];
            const uniqueAccounts = [...new Set(this.selectedAccounts)];

            periods.forEach(period => {
                const data = uniqueAccounts
                    .map(account => this.accountDataCache[period][account])
                    .filter(Boolean);

                this.rangesTables[period] = {
                    data: data,
                    date_range: this.rangesTables[period].date_range || null,
                };
            });
        },

        /**
         * Fetch data for account(s) - fetches all selected accounts
         */
        async fetchAccountsData(accounts) {
            // Normalize input to array
            const accountsArray = Array.isArray(accounts) ? accounts : [accounts];

            if (!accountsArray || accountsArray.length === 0) return;

            this.rangeLoading = true;
            const periods = ['tm', '1m', '3m'];

            for (const period of periods) {
                try {
                    const params = {
                        accounts: accountsArray,
                        period: period,
                    };

                    const response = await betServices.fetchRangePeriodData(params);

                    this.rangesTables[period] = {
                        data: response.data || [],
                        date_range: response.date_range || null,
                    };
                } catch (error) {
                    console.error(`Error fetching data for period ${period}:`, error);
                }
            }

            this.rangeLoading = false;
        },

        /**
         * Fetch range data for selected accounts
         */
        async fetchRangeData() {
            if (this.selectedAccounts.length === 0) {
                this.rangesTables = initialRangeState;
                return;
            }

            await this.fetchAccountsData(this.selectedAccounts);
        },

        /**
         * Action clear selections and refresh the main table data
         */
        fetchBetsWithReset() {
            this.filters = getInitialFilters();
            this.selectedAccounts = [];
            this.rangesTables = initialRangeState;
            this.accountDataCache = initialCacheState;
            this.fetchBets();
        },

        /**
         * Action to refetch bets and period data
         */
        refetchBetsAndPeriod() {
            this.fetchBets();
            this.fetchRangeData();
        },

        /**
         * Toggle All Time Report visibility
         */
        toggleAllTimeReport() {
            this.showAllTimeReport = !this.showAllTimeReport;

            // Fetch all-time data when showing first time
            if (this.showAllTimeReport && this.selectedAccounts.length > 0) {
                this.fetchAllTimeData();
            }
        },

        /**
         * Fetch all-time data for selected accounts
         */
        async fetchAllTimeData() {
            if (this.selectedAccounts.length === 0) {
                this.rangesTables.all_time = { data: [] };
                return;
            }

            this.rangeLoading = true;
            const period = 'all_time';

            // Filter out, cached accounts
            const uncachedAccounts = this.selectedAccounts.filter(
                acc => !this.accountDataCache[period][acc]
            );

            // If all accounts are cached, rebuild from cache
            if (uncachedAccounts.length === 0) {
                const uniqueAccounts = [...new Set(this.selectedAccounts)];
                const data = uniqueAccounts
                    .map(account => this.accountDataCache[period][account])
                    .filter(Boolean);

                this.rangesTables[period] = {
                    data: data,
                    date_range: this.rangesTables[period].date_range || null,
                };
                this.rangeLoading = false;
                return;
            }

            try {
                const params = {
                    accounts: uncachedAccounts,
                    period: period,
                };

                const response = await betServices.fetchRangePeriodData(params);

                // Cache each account data
                if (response.data) {
                    response.data.forEach(accountData => {
                        this.accountDataCache[period][accountData.account] = accountData;
                    });
                }

                // Store date_range
                if (response.date_range) {
                    this.rangesTables[period].date_range = response.date_range;
                }

                // Rebuild display from cache for all selected accounts
                const uniqueAccounts = [...new Set(this.selectedAccounts)];
                const data = uniqueAccounts
                    .map(account => this.accountDataCache[period][account])
                    .filter(Boolean);

                this.rangesTables[period] = {
                    data: data,
                    date_range: response.date_range || null,
                };
            } catch (error) {
                console.error(`Error fetching all-time data:`, error);
            } finally {
                this.rangeLoading = false;
            }
        },

        /**
         * Fetch external summary data (deposit/withdraw/profit) from DK API
         */
        async fetchExternalSummary() {
            if (this.selectedAccounts.length === 0) {
                console.warn('No accounts selected for external summary');
                return;
            }

            // Filter out accounts that already cached
            const uniqueAccounts = [...new Set(this.selectedAccounts)];
            const uncachedAccounts = uniqueAccounts.filter(
                account => !this.externalSummaryCache[account]
            );

            // If all accounts cached, no fetch
            if (uncachedAccounts.length === 0) {
                return;
            }

            this.externalSummaryLoading = true;

            try {
                const response = await betServices.fetchExternalSummary(uncachedAccounts);

                if (response.status) {
                    // Cache only accounts found in the API response
                    const dataArray = Array.isArray(response.data) ? response.data : [];

                    dataArray.forEach(item => {
                        if (item.username) {
                            this.externalSummaryCache[item.username] = {
                                deposits: item.deposits,
                                withdraws: item.withdraws,
                                profit: item.profit,
                            };
                        }
                    });
                } else {
                    console.error('Failed to fetch external summary:', response.message);
                }
            } catch (error) {
                console.error('Error fetching external summary:', error);
            } finally {
                this.externalSummaryLoading = false;
            }
        },

        /**
         * Toggle highlight for an account with a random color
         */
        toggleAccountHighlight(account) {
            if (this.highlightedAccounts[account]) {
                delete this.highlightedAccounts[account];
            } else {
                // Generate a random pastel color
                const hue = Math.floor(Math.random() * 360);
                this.highlightedAccounts[account] = `hsl(${hue}, 70%, 85%)`;
            }
        },
    },
});
