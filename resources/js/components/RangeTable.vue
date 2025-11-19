<script setup>
import { storeToRefs } from 'pinia';
import { useBetStore } from '../stores/betStore';
import { amountColor, lpBgColor } from '../utils/getStatusClass';
import RangeTableSkeleton from './loading/RangeTableSkeleton.vue';

const betStore = useBetStore();

const { loading, rangeLoading } = storeToRefs(betStore);

/*
 * Define the keys and labels
 */
const rangePeriods = [
    { key: '7d', label: 'Last 7 days' },
    { key: '1m', label: 'Last 1 month' },
    { key: '3m', label: 'Last 3 months' },
];

/*
 * Helper function to safely get the data for a given range key
 */
const getRangeData = key => betStore.rangesTables[key];

const handlePaginationClick = (rangeKey, linkLabel) => {
    if (typeof linkLabel === 'number') {
        betStore.setRangePage(rangeKey, linkLabel);
        return;
    }

    const meta = getRangeData(rangeKey)?.meta;
    if (!meta) return;

    if (linkLabel.includes('Previous')) {
        betStore.setRangePage(rangeKey, meta.current_page - 1);
    } else if (linkLabel.includes('Next')) {
        betStore.setRangePage(rangeKey, meta.current_page + 1);
    }
};

const handleChangePerPage = (rangeKey, count) => {
    betStore.setRangeItemsPerPage(rangeKey, +count);
};

const handleRefetchBetAndPeriod = () => {
    betStore.fetchBetsWithReset();
};
</script>

<template>
    <div class="py-3">
        <div class="mb-3 d-flex justify-content-between align-items-end">
            <div>
                <h2 class="page-title">Selected Account Period</h2>
                <div class="text-muted mt-1">
                    View last one week, one month and three months betting transactions
                </div>
            </div>

            <!-- Refresh Button -->
            <button
                type="button"
                class="btn btn-icon btn-outline-secondary"
                aria-label="Refresh"
                @click="handleRefetchBetAndPeriod"
                title="Refresh the data table and Reset filters"
                :disabled="loading || rangeLoading"
            >
                <i class="las la-sync-alt fs-2" :class="{ 'fa-spin': loading }"></i>
            </button>
        </div>

        <!-- Skeleton Loading -->
        <RangeTableSkeleton v-if="betStore.rangeLoading" :rangePeriods="rangePeriods" />

        <div v-else class="d-flex flex-column gap-3">
            <div v-for="period in rangePeriods" :key="period?.key" class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>{{ period?.label }}</strong>

                    <!-- Show record options -->
                    <div v-if="getRangeData(period?.key)?.meta">
                        <select
                            class="form-select form-select-sm"
                            :value="getRangeData(period.key).meta?.per_page"
                            @change="handleChangePerPage(period?.key, $event.target?.value)"
                        >
                            <option value="10">10</option>
                            <option value="20">20</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>

                <!-- Table body -->
                <div class="card-body py-0">
                    <div v-if="getRangeData(period.key)?.data?.length" class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Account</th>
                                    <th>Count</th>
                                    <th>Turnover</th>
                                    <th>Win/Lose</th>
                                    <th>LP</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="row in getRangeData(period?.key).data"
                                    :key="row?.account"
                                >
                                    <td>{{ row?.account }}</td>
                                    <td>{{ row?.total_count }}</td>
                                    <td :class="amountColor(row?.total_turnover)">
                                        {{ row?.total_turnover }}
                                    </td>
                                    <td :class="amountColor(row?.total_winlose)">
                                        {{ row?.total_winlose }}
                                    </td>
                                    <td :class="lpBgColor(row?.total_lp)">
                                        {{ row?.total_lp.percentage }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="p-3 text-muted">No data for this period.</div>
                </div>

                <!-- Pagination -->
                <div
                    v-if="getRangeData(period.key)?.meta"
                    class="card-footer d-flex flex-column flex-sm-row align-items-center"
                >
                    <ul
                        class="pagination m-0 mt-2 mt-sm-0 w-auto w-sm-auto justify-content-center justify-content-md-end flex-grow-1"
                    >
                        <li
                            v-for="link in getRangeData(period?.key).meta?.links"
                            :key="link?.label"
                            class="page-item"
                            :class="{ active: link?.active, disabled: !link.url }"
                        >
                            <a
                                href="#"
                                class="page-link mx-1"
                                @click.prevent="
                                    handlePaginationClick(period?.key, link?.page || link?.label)
                                "
                                v-html="link?.label"
                            ></a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>
