<script setup>
/*
 * Define the props this component accepts
 */
const props = defineProps({
    // Prop to receive the list of periods
    rangePeriods: {
        type: Array,
        required: true,
    },
    // Number of skeleton rows to render in each table
    rowCount: {
        type: Number,
        default: 10,
    },
});

/*
 * The headers correspond to the columns in the actual Range Table
 */
const tableHeaders = ['Account', 'Count', 'Turnover', 'Win/Lose', 'LP'];
</script>

<template>
    <div class="d-flex flex-column gap-3">
        <div v-for="period in rangePeriods" :key="period.key" class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>{{ period.label }}</strong>
            </div>

            <div class="card-body py-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th v-for="header in tableHeaders" :key="header">{{ header }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="n in rowCount" :key="n">
                                <td
                                    v-for="m in tableHeaders.length"
                                    :key="m"
                                    class="skeleton-loading"
                                >
                                    <div
                                        class="placeholder placeholder-wave"
                                        style="height: 1em; width: 60%"
                                    ></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer text-muted text-center">
                <div class="spinner-border spinner-border-sm me-2"></div>
                Loading data...
            </div>
        </div>
    </div>
</template>
