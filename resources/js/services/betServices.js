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
        
        const response = await axios.get('/admin/api/bet-period', {
            params: params,
        });

        return response.data;
    },
};

export default betServices;
