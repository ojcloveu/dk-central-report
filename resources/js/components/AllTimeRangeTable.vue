<script setup>
import { storeToRefs } from 'pinia';
import { useBetStore } from '@/stores/betStore';
import { amountColor, lpBgColor } from '../utils/getStatusClass';
import { computed, ref, onMounted, watch, nextTick } from 'vue';
import { useRangeTableSorting } from '../composables/useRangeTableSorting';
import RangeTableSkeleton from './loading/RangeTableSkeleton.vue';
import SortIcon from './icons/SortIcon.vue';
import ActionButton from './buttons/ActionButton.vue';

const betStore = useBetStore();

const { loading, rangeLoading, externalSummaryCache, externalSummaryLoading } =
    storeToRefs(betStore);
const { hasSelectedAccounts } = storeToRefs(betStore);
const selectAllCheckbox = ref(null);

/*
 * Helper function to safely get the all-time data
 */
const getRangeData = () => betStore.rangesTables.all_time;

/*
 * Use sorting composables with shared state from store
 */
const handleRangeSortClick = column => {
    if (betStore.rangeSort.sort_by === column) {
        // Toggle direction
        betStore.setRangeSort(column, betStore.rangeSort.sort_dir === 'asc' ? 'desc' : 'asc');
    } else {
        betStore.setRangeSort(column, 'asc');
    }
};

const { rangeSort, handleRangeSort, getSortedData } = useRangeTableSorting(
    () => getRangeData(),
    betStore.rangeSort,
    handleRangeSortClick,
    externalSummaryCache
);

/*
 * Client-only pagination state (now from store)
 */

// Get filtered data based on perPage limit
const getFilteredData = () => {
    const sortedData = getSortedData('all_time');
    return sortedData.slice(0, betStore.rangePerPage);
};

// Handle change per page for client filtering
const handleChangePerPage = count => {
    betStore.setRangePerPage(+count);
};

/*
 * Handle refetch bet and period data
 */
const handleRefetchAllTime = () => {
    betStore.refetchBetsAndPeriod();
};

/*
 * Handle close all time report
 */
const handleCloseAllTimeReport = () => {
    betStore.toggleAllTimeReport();
};

const handleRowCheckboxChange = (account, isChecked) => {
    betStore.toggleAccountSelection(account, isChecked);
};

/*
 * Checkbox select/unselect all accounts
 */
const getAllSelected = computed(() => {
    const data = getFilteredData();
    if (data.length === 0) return false;

    // Check at least one account in period data is selected
    return data.every(row => betStore.selectedAccounts.includes(row.account));
});

const getSomeSelected = computed(() => {
    const data = getFilteredData();
    const allSelected = getAllSelected.value;

    // Check at least one account in period data is selected
    return !allSelected && data.some(row => betStore.selectedAccounts.includes(row.account));
});

// Handle select all checkbox change
const handleSelectAll = event => {
    const accounts = getFilteredData().map(row => row.account) || [];
    const selectAll = event.target.checked;

    if (selectAll) {
        // Select all accounts on this page
        const uniqueNewAccounts = accounts.filter(acc => !betStore.selectedAccounts.includes(acc));
        betStore.selectedAccounts.push(...uniqueNewAccounts);
    } else {
        // Deselect only accounts on this page
        betStore.selectedAccounts = betStore.selectedAccounts.filter(
            acc => !accounts.includes(acc)
        );
    }

    betStore.fetchAllTimeData();
};

/*
 * Watch and update indeterminate state for checkbox
 */
watch(
    () => betStore.selectedAccounts,
    async () => {
        await nextTick();
        if (selectAllCheckbox.value) {
            selectAllCheckbox.value.indeterminate = getSomeSelected.value;
        }
    },
    { deep: true }
);

/*
 * Helper functions to get external summary data
 */
const getDeposit = account => {
    return externalSummaryCache.value[account]?.deposits || '$0';
};

const getWithdraw = account => {
    return externalSummaryCache.value[account]?.withdraws || '$0';
};

const getProfit = account => {
    return externalSummaryCache.value[account]?.profit || '$0';
};

/*
 * Set initial indeterminate state on mount
 */
onMounted(async () => {
    await nextTick();
    if (selectAllCheckbox.value) {
        selectAllCheckbox.value.indeterminate = getSomeSelected.value;
    }
});
</script>

<template>
    <div class="py-3">
        <div class="mb-3 d-flex justify-content-between align-items-end">
            <div>
                <h2 class="page-title">All Time Report</h2>
                <div class="text-muted mt-1">
                    View all-time betting transactions for selected accounts
                </div>
            </div>

            <div class="d-flex gap-2">
                <!-- Close Button -->
                <ActionButton
                    variant="outline-danger"
                    iconClass="las la-times"
                    label="Close"
                    title="Close All Time Report"
                    @click="handleCloseAllTimeReport"
                />

                <!-- Refresh Button -->
                <ActionButton
                    variant="outline-secondary"
                    iconClass="las la-sync-alt"
                    title="Refresh all-time data"
                    :disabled="loading || rangeLoading"
                    :loading="rangeLoading"
                    :iconOnly="true"
                    @click="handleRefetchAllTime"
                />
            </div>
        </div>

        <!-- Skeleton Loading -->
        <RangeTableSkeleton
            v-if="betStore.rangeLoading"
            :rangePeriods="[{ key: 'all_time', label: 'All Time' }]"
        />

        <div v-else class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <strong>All Time</strong>
                    <span
                        v-if="getRangeData()?.date_range"
                        class="text-muted ms-2"
                        style="font-size: 0.875rem"
                    >
                        ({{ getRangeData()?.date_range?.start_date }} -
                        {{ getRangeData()?.date_range?.end_date }})
                    </span>
                </div>

                <!-- Show record options (Frontend filters display) -->
                <div v-if="getRangeData()?.data?.length">
                    <select
                        class="form-select form-select-sm"
                        :value="betStore.rangePerPage"
                        @change="handleChangePerPage($event.target?.value)"
                    >
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                        <option value="9999">All</option>
                    </select>
                </div>
            </div>

            <!-- Table body -->
            <div class="card-body py-0">
                <div v-if="getRangeData()?.data?.length" class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <!-- Checkbox for select all Accounts -->
                                <th class="w-1">
                                    <input
                                        ref="selectAllCheckbox"
                                        class="form-check-input m-0"
                                        type="checkbox"
                                        :checked="getAllSelected"
                                        @change="handleSelectAll($event)"
                                    />
                                </th>
                                <th
                                    @click="handleRangeSort('account')"
                                    class="sortable cursor-pointer"
                                >
                                    Account
                                    <SortIcon
                                        :currentSortBy="rangeSort.sort_by"
                                        columnName="account"
                                        :sortDirection="rangeSort.sort_dir"
                                    />
                                </th>

                                <th
                                    @click="handleRangeSort('total_count')"
                                    class="sortable text-end cursor-pointer"
                                >
                                    Count
                                    <SortIcon
                                        :currentSortBy="rangeSort.sort_by"
                                        columnName="total_count"
                                        :sortDirection="rangeSort.sort_dir"
                                    />
                                </th>

                                <th
                                    @click="handleRangeSort('total_turnover')"
                                    class="sortable text-end cursor-pointer"
                                >
                                    Turnover
                                    <SortIcon
                                        :currentSortBy="rangeSort.sort_by"
                                        columnName="total_turnover"
                                        :sortDirection="rangeSort.sort_dir"
                                    />
                                </th>

                                <th
                                    @click="handleRangeSort('total_winlose')"
                                    class="sortable text-end cursor-pointer"
                                >
                                    Win/Lose
                                    <SortIcon
                                        :currentSortBy="rangeSort.sort_by"
                                        columnName="total_winlose"
                                        :sortDirection="rangeSort.sort_dir"
                                    />
                                </th>

                                <th
                                    @click="handleRangeSort('total_lp')"
                                    class="sortable text-end cursor-pointer"
                                >
                                    LP
                                    <SortIcon
                                        :currentSortBy="rangeSort.sort_by"
                                        columnName="total_lp"
                                        :sortDirection="rangeSort.sort_dir"
                                    />
                                </th>

                                <!-- External Summary Columns -->
                                <th
                                    @click="handleRangeSort('deposits')"
                                    class="sortable text-end th-bg-muted cursor-pointer"
                                >
                                    Total Deposit
                                    <SortIcon
                                        :currentSortBy="rangeSort.sort_by"
                                        columnName="deposits"
                                        :sortDirection="rangeSort.sort_dir"
                                    />
                                </th>
                                <th
                                    @click="handleRangeSort('withdraws')"
                                    class="sortable text-end th-bg-muted cursor-pointer"
                                >
                                    Total Withdraw
                                    <SortIcon
                                        :currentSortBy="rangeSort.sort_by"
                                        columnName="withdraws"
                                        :sortDirection="rangeSort.sort_dir"
                                    />
                                </th>
                                <th
                                    @click="handleRangeSort('profit')"
                                    class="sortable text-end cursor-pointer"
                                >
                                    Deposit - Withdraw
                                    <SortIcon
                                        :currentSortBy="rangeSort.sort_by"
                                        columnName="profit"
                                        :sortDirection="rangeSort.sort_dir"
                                    />
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="row in getFilteredData()"
                                :key="row?.account"
                                :class="{ 'table-primary-light': true }"
                            >
                                <td>
                                    <input
                                        class="form-check-input m-0"
                                        type="checkbox"
                                        :checked="true"
                                        @change="
                                            handleRowCheckboxChange(
                                                row.account,
                                                $event.target.checked
                                            )
                                        "
                                    />
                                </td>
                                <td>{{ row?.account }}</td>
                                <td class="text-end">{{ row?.total_count }}</td>
                                <td class="text-end" :class="amountColor(row?.total_turnover)">
                                    {{ row?.total_turnover }}
                                </td>
                                <td class="text-end" :class="amountColor(row?.total_winlose)">
                                    {{ row?.total_winlose }}
                                </td>
                                <td class="text-end" :class="lpBgColor(row?.total_lp)">
                                    {{
                                        (
                                            Number(
                                                typeof row?.total_lp === 'number'
                                                    ? row.total_lp
                                                    : row?.total_lp?.percentage
                                            ) || 0
                                        ).toFixed(0)
                                    }}%
                                </td>

                                <!-- Total Deposit -->
                                <td class="text-end text-success fw-bold bg-muted-lt">
                                    <span v-if="externalSummaryLoading">Loading...</span>
                                    <span v-else>{{ getDeposit(row.account) }}</span>
                                </td>
                                <!-- Total Withdraw -->
                                <td class="text-end text-danger fw-bold bg-muted-lt">
                                    <span v-if="externalSummaryLoading">Loading...</span>
                                    <span v-else>{{ getWithdraw(row.account) }}</span>
                                </td>
                                <!-- Deposit - Withdraw -->
                                <td
                                    class="text-end fw-bold"
                                    :class="
                                        Number(getProfit(row.account)) < 0
                                            ? 'bg-red-lt'
                                            : 'bg-green-lt'
                                    "
                                >
                                    <span v-if="externalSummaryLoading">Loading...</span>
                                    <span v-else>{{ getProfit(row.account) }}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="p-3 text-muted">No all-time data available.</div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.th-bg-muted {
    background-color: var(--tblr-muted-lt) !important;
}
</style>
