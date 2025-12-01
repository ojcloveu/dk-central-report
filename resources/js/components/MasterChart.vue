<script setup>
import { ref } from 'vue';
import VueApexCharts from 'vue3-apexcharts';

const chartOptions = {
    chart: {
        type: 'bar',
        height: 350,
        toolbar: {
            show: false,
        },
    },
    plotOptions: {
        bar: {
            horizontal: false,
            columnWidth: '55%',
            endingShape: 'rounded',
        },
    },
    dataLabels: {
        enabled: false,
    },
    stroke: {
        show: true,
        width: 2,
        colors: ['transparent'],
    },
    xaxis: {
        categories: ['DK', 'VN', 'TH', 'PH', 'Total'],
    },
    yaxis: {
        title: {
            text: '',
        },
    },
    fill: {
        opacity: 1,
    },
    tooltip: {
        y: {
            formatter: function (val) {
                return '$ ' + val.toLocaleString();
            },
        },
    },
    colors: ['#008FFB', '#FF4560'],
    legend: {
        position: 'top',
    },
};

const seriesDKDK = ref([
    {
        name: 'Last Month',
        data: [14249.57, 19043.22, 11496.32, 2158.22, 46947.33],
    },
    {
        name: 'This Month',
        data: [11351.15, 8742.64, 14415.05, 1552.37, 36061.21],
    },
]);

const seriesDKAO = ref([
    {
        name: 'Last Month',
        data: [11160.83, -20276.67, 781.39, 180.67, -8153.78],
    },
    {
        name: 'This Month',
        data: [490.64, 2882.43, 2703.08, 358.44, 6434.59],
    },
]);
</script>

<template>
    <div class="row row-cards my-3">
        <!-- Title -->
        <div class="d-flex justify-content-between align-items-end">
            <div>
                <h2 class="page-title">Master Chart</h2>
                <div class="text-muted mt-1">
                    View and manage master, channel and turnover transactions
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title text-center w-100">Senior DKDK</h3>
                </div>
                <div class="card-body">
                    <VueApexCharts
                        type="bar"
                        height="350"
                        :options="chartOptions"
                        :series="seriesDKDK"
                    />
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title text-center w-100">Senior DKAO</h3>
                </div>
                <div class="card-body">
                    <VueApexCharts
                        type="bar"
                        height="350"
                        :options="chartOptions"
                        :series="seriesDKAO"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Override ApexCharts text colors to be theme-aware */
:deep(.apexcharts-text) {
    fill: var(--tblr-body-color) !important;
}

:deep(.apexcharts-legend-text) {
    color: var(--tblr-body-color) !important;
}

:deep(.apexcharts-xaxis-label),
:deep(.apexcharts-yaxis-label) {
    fill: var(--tblr-body-color) !important;
}
</style>
