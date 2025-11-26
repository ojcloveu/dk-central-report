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

const { loading, rangeLoading } = storeToRefs(betStore);
const { hasSelectedAccounts } = storeToRefs(betStore);
const selectAllCheckbox = ref(null);

/*
 * Helper function to safely get the all-time data
 */
const getRangeData = () => betStore.rangesTables.all_time;

/*
 * Use sorting composables
 */
const { rangeSort, handleRangeSort, getSortedData } = useRangeTableSorting(() => getRangeData());

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
 * Dummy data random
 */
const getDummyDeposit = () => {
    return (Math.random() * 10000).toFixed(2);
};
const getDummyWithdraw = () => {
    return (Math.random() * 8000).toFixed(2);
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

                                <!-- Dummy column -->
                                <th class="text-end">Total Deposit</th>
                                <th class="text-end">Total Withdraw</th>
                                <th class="text-end">Deposit - Withdraw</th>
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

                                <!-- Dummy column deposit -->
                                <td class="text-end text-success fw-bold bg-muted-lt">
                                    ${{ Number(getDummyDeposit()).toFixed(0) }}
                                </td>
                                <!-- Dummy column withdraw -->
                                <td class="text-end text-danger fw-bold bg-muted-lt">
                                    ${{ Number(getDummyWithdraw()).toFixed(0) }}
                                </td>
                                <!-- Dummy column deposit - withdraw -->
                                <td
                                    class="text-end fw-bold"
                                    :class="
                                        Number(getDummyDeposit()) - Number(getDummyWithdraw()) < 0
                                            ? 'bg-red-lt'
                                            : 'bg-green-lt'
                                    "
                                >
                                    ${{
                                        (
                                            Number(getDummyDeposit()) - Number(getDummyWithdraw())
                                        ).toFixed(0)
                                    }}
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
