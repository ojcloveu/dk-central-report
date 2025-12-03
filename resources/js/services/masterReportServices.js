// resources/js/services/masterReportServices.js
import axios from 'axios';

const masterReportServices = {
    /**
     * Fetches the master report.
     */
    async fetchMasterReport() {
        const url = '/admin/api/master-report';
        const response = await axios.get(url);

        return response.data;
    },

    /**
     * Fetches the master turnover.
     */
    async fetchMasterTurnover() {
        const url = '/admin/api/master-turnover';
        const response = await axios.get(url);

        return response.data;
    },
};

export default masterReportServices;
