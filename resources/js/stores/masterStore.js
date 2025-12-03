// resources/js/stores/masterStore.js
import { defineStore } from 'pinia';
import masterReportServices from '../services/masterReportServices';

export const masterStore = defineStore('master', {
    state: () => ({
        masterReport: null,
        masterTurnOver: null,
    }),
    actions: {
        /*
         * Fetches the master report.
         */
        async fetchMasterReport() {
            const response = await masterReportServices.fetchMasterReport();
            console.log("response master: ", response);
            this.masterReport = response;
        },

        /*
         * Fetches the master turnover.
         */
        async fetchMasterTurnOver() {
          const response = await masterReportServices.fetchMasterTurnover();
          console.log("response turnover: ", response);
          this.masterTurnOver = response;
        }
    },
});
