<script setup>
import { ref } from 'vue';

const items = ref([
    {
        channel: 'DK',
        dkdk_last: '$14,249.57',
        dkdk_this: '$11,351.15',
        dkao_last: '$11,160.83',
        dkao_this: '$490.64',
    },
    {
        channel: 'VN',
        dkdk_last: '$19,043.22',
        dkdk_this: '$8,742.64',
        dkao_last: '-$20,276.67',
        dkao_this: '$2,882.43',
    },
    {
        channel: 'TH',
        dkdk_last: '$11,496.32',
        dkdk_this: '$14,415.05',
        dkao_last: '$781.39',
        dkao_this: '$2,703.08',
    },
    {
        channel: 'PH',
        dkdk_last: '$2,158.22',
        dkdk_this: '$1,552.37',
        dkao_last: '$180.67',
        dkao_this: '$358.44',
    },
    {
        channel: 'Total',
        dkdk_last: '$46,947.33',
        dkdk_this: '$36,061.21',
        dkao_last: '-$8,153.78',
        dkao_this: '$6,434.59',
    },
]);

const turnoverItem = ref({
    channel: 'Turn Over',
    dkdk_last: '$3,408,519.00',
    dkdk_this: '$2,773,926.00',
    dkao_last: '$314,840.00',
    dkao_this: '$288,465.00',
});
</script>

<template>
    <!-- Title -->
    <div class="mb-3 d-flex justify-content-between align-items-end">
        <div>
            <h2 class="page-title">Master Report</h2>
            <div class="text-muted mt-1">
                View and manage master, channel and turnover transactions
            </div>
        </div>
    </div>

    <!-- Master Report -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Master Report</h3>
        </div>
        <div class="card-body py-0 px-2">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th></th>
                            <th colspan="2" class="fw-bold fs-5 text-center border-end">DKDK</th>
                            <th colspan="2" class="fw-bold fs-5 text-center">DKAO</th>
                        </tr>
                        <tr>
                            <th class="fw-bold text-start border-end">Channel</th>
                            <th class="fw-bold text-end border-end">
                                Last Month <span class="text-primary">(01/Dec/25 - 31/Dec/25)</span>
                            </th>
                            <th class="fw-bold text-end border-end">
                                This Month <span class="text-primary">(01/Dec/25 - 31/Dec/25)</span>
                            </th>
                            <th class="fw-bold text-end border-end">
                                Last Month <span class="text-primary">(01/Dec/25 - 31/Dec/25)</span>
                            </th>
                            <th class="fw-bold text-end">
                                This Month <span class="text-primary">(01/Dec/25 - 31/Dec/25)</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(item, index) in items"
                            :key="index"
                            :class="{ 'fw-bold': item.channel === 'Total' }"
                        >
                            <td class="text-start fw-bold">
                                <span :class="{ 'badge bg-primary-lt': item.channel !== 'Total' }">
                                    {{ item.channel }}
                                </span>
                            </td>
                            <td class="text-end">{{ item.dkdk_last }}</td>
                            <td class="text-end">{{ item.dkdk_this }}</td>
                            <td class="text-end">{{ item.dkao_last }}</td>
                            <td class="text-end">{{ item.dkao_this }}</td>
                        </tr>
                    </tbody>

                    <!-- Turn Over -->
                    <tfoot>
                        <!-- Spacer Row -->
                        <tr class="border-0">
                            <td colspan="5" class="border-0 py-3"></td>
                        </tr>
                        <!-- Turnover Row -->
                        <tr class="fw-bold bg-primary-lt">
                            <td class="text-start fw-bold border-top">
                                {{ turnoverItem.channel }}
                            </td>
                            <td class="text-end border-top">{{ turnoverItem.dkdk_last }}</td>
                            <td class="text-end border-top">{{ turnoverItem.dkdk_this }}</td>
                            <td class="text-end border-top">{{ turnoverItem.dkao_last }}</td>
                            <td class="text-end border-top">{{ turnoverItem.dkao_this }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Optional: Add custom styles if Bootstrap isn't enough */
th {
    background-color: #f8f9fa;
}
</style>
