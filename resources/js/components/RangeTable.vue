<script setup>
import { useBetStore } from '../stores/betStore';
import { getLpClass } from '../utils/getStatusClass';

const betStore = useBetStore();

// Define the keys and labels
const rangePeriods = [
    { key: '7d', label: 'Last 7 days' },
    { key: '1m', label: 'Last 1 month' },
    { key: '3m', label: 'Last 3 months' },
];

// Helper function to safely get the data for a given range key
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
</script>

<template>
    <div class="py-3">
        <div class="mb-3">
            <h2 class="page-title">Selected Account Period</h2>
            <div class="text-muted mt-1">
                View last one week, one month and three months betting transactions
            </div>
        </div>

        <div v-if="betStore.rangeLoading" class="text-center py-5">
            <div class="spinner-border text-primary mb-3"></div>
            <p class="text-muted mb-0">Calculating range data...</p>
        </div>

        <div v-else class="d-flex flex-column gap-3">
            <div v-for="period in rangePeriods" :key="period?.key" class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>{{ period?.label }}</strong>

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
                                    <td>{{ row?.total_turnover }}</td>
                                    <td :class="getLpClass(row?.total_winlose)">
                                        {{ row?.total_winlose }}
                                    </td>
                                    <td :class="getLpClass(row?.total_lp)">{{ row?.total_lp }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="p-3 text-muted">No data for this period.</div>
                </div>

                <!-- Pagination -->
                <div
                    v-if="getRangeData(period.key)?.meta"
                    class="card-footer d-flex align-items-center"
                >
                    <p class="m-0 text-muted">
                        Showing
                        <span class="fw-bold">{{
                            getRangeData(period.key).meta?.current_page *
                                getRangeData(period.key).meta.per_page -
                            getRangeData(period.key).meta.per_page +
                            1
                        }}</span>
                        to
                        <span class="fw-bold">{{
                            Math.min(
                                getRangeData(period.key).meta?.current_page *
                                    getRangeData(period.key).meta?.per_page,
                                getRangeData(period.key).meta?.total
                            )
                        }}</span>
                        of
                        <span class="fw-bold">{{ getRangeData(period.key).meta?.total }}</span>
                        entries
                    </p>

                    <ul class="pagination m-0 ms-auto">
                        <li
                            v-for="link in getRangeData(period?.key).links"
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
