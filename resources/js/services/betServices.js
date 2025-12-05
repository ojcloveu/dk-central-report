// resources/js/services/betServices.js
import axios from 'axios';

const betServices = {
    /**
     * Fetches the main paginated bet reports.
     */
    async fetchBets(queryString) {
        if (!queryString) {
            throw new Error('Query string is required for fetchBets.');
        }

        const url = `/admin/api/bets?${queryString}`;
        const response = await axios.get(url);

        return response.data;
    },

    /**
     * Fetches the range table data for a specific period.
     */
    async fetchRangePeriodData(params) {
        if (!params || !params.accounts || !params.period) {
            throw new Error('Accounts and period are required for fetchRangePeriodData.');
        }

        // Use POST to send accounts array in request body
        const response = await axios.post('/admin/api/bet-period', params);

        return response.data;
    },

    /**
     * Fetches external summary data (deposit/withdraw) from DK API
     */
    async fetchExternalSummary(accounts) {
        if (!accounts || accounts.length === 0) {
            throw new Error('Accounts array is required for fetchExternalSummary.');
        }

        // Join unique accounts as comma-separated string
        const accountsString = accounts.join(',');

        const response = await axios.get('/admin/api/external-summary', {
            params: { accounts: accountsString },
        });

        return response.data;
    },
};

export default betServices;
