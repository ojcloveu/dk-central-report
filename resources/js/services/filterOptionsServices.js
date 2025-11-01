// resources/js/services/filterServices.js
import axios from 'axios';

const filterOptionsServices = {
    /**
     * Fetch list of Accounts for filter selection
     */
    async fetchAccounts(payload = {}) {
        try {
            const response = await axios.post('/admin/bet/fetch/accounts', payload);
            return response.data;
        } catch (error) {
            console.error('Failed to fetch accounts:', error);
            throw error;
        }
    },

    /**
     * Fetch list of Channels for filter selection
     */
    async fetchChannels(payload = {}) {
        try {
            const response = await axios.post('/admin/bet/fetch/channels', payload);
            return response.data;
        } catch (error) {
            console.error('Failed to fetch channels:', error);
            throw error;
        }
    },

    /**
     * Fetch list of Masters for filter selection
     */
    async fetchMasters(payload = {}) {
        try {
            const response = await axios.post('/admin/bet/fetch/masters', payload);
            return response.data;
        } catch (error) {
            console.error('Failed to fetch masters:', error);
            throw error;
        }
    },
};

export default filterOptionsServices;
