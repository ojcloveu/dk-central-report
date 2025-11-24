<script setup>
import { storeToRefs } from 'pinia';
import { useBetStore } from '../stores/betStore';
import { amountColor, lpBgColor } from '../utils/getStatusClass';
import RangeTableSkeleton from './loading/RangeTableSkeleton.vue';
import SortIcon from './icons/SortIcon.vue';
import ActionButton from './buttons/ActionButton.vue';
import { computed, ref, onMounted, watch, reactive, nextTick } from 'vue';

const betStore = useBetStore();

const { loading, rangeLoading } = storeToRefs(betStore);
const { hasSelectedAccounts } = storeToRefs(betStore);
const selectAllCheckboxes = ref({});

const setSelectAllRef = (periodKey, el) => {
    if (el) {
        selectAllCheckboxes.value[periodKey] = el;
    }
};

/*
 * Define the keys and labels
 */
const rangePeriods = [
    { key: 'tm', label: 'This month' },
    { key: '1m', label: 'Previous month' },
    { key: '3m', label: 'Last 3 months' },
];

/*
 * Helper function to safely get the data for a given range key
 */
const getRangeData = key => betStore.rangesTables[key];

/*
 * Client-only pagination state
 */
const perPage = ref(10);

/*
 * Client filtering
 */
// Get filtered data based on perPage limit
const getFilteredData = periodKey => {
    const data = getRangeData(periodKey)?.data || [];
    return data.slice(0, perPage.value);
};

// Handle change per page for client filtering
const handleChangePerPage = count => {
    perPage.value = +count;
};

/*
 * Handle refetch bet and period data
 */
const handleRefetchBetAndPeriod = () => {
    betStore.refetchBetsAndPeriod();
};

/*
 * Handle remove all selected accounts
 */
const handleRemoveSelectedAccounts = () => {
    betStore.clearAllSelectedAccounts();
};

const handleRowCheckboxChange = (account, isChecked) => {
    betStore.toggleAccountSelection(account, isChecked);
};

// Sorting state for RangeTable
const rangeSort = reactive({
    sort_by: 'account',
    sort_dir: 'asc',
});

// Handle sorting when click table column
const handleRangeSort = column => {
    if (rangeSort.sort_by === column) {
        // toggle direction
        rangeSort.sort_dir = rangeSort.sort_dir === 'asc' ? 'desc' : 'asc';
    } else {
        rangeSort.sort_by = column;
        rangeSort.sort_dir = 'asc';
    }

    // Refetch all tm, 1m, 3m tables with sort param
    betStore.fetchRangeData(null, 1, null, rangeSort.sort_by, rangeSort.sort_dir);
};

/*
 * Checkbox select/unselect all accounts in a period
 */
const getAllSelected = periodKey =>
    computed(() => {
        const data = getFilteredData(periodKey);
        if (data.length === 0) return false;

        // Check if at least one account in period's data is selected
        return data.every(row => betStore.selectedAccounts.includes(row.account));
    });

const getSomeSelected = periodKey =>
    computed(() => {
        const data = getFilteredData(periodKey);
        const allSelected = getAllSelected(periodKey).value;

        // Check if at least one account in period's data is selected
        return !allSelected && data.some(row => betStore.selectedAccounts.includes(row.account));
    });

// Handle select all checkbox change for a specific period
const handleSelectAll = (periodKey, event) => {
    const accounts = getFilteredData(periodKey).map(row => row.account) || [];
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

    betStore.fetchRangeData(null, null, null, rangeSort.sort_by, rangeSort.sort_dir);
};

/*
 * Watch and update indeterminate state for all checkboxes
 */
watch(
    () => betStore.selectedAccounts,
    async () => {
        await nextTick();
        rangePeriods.forEach(period => {
            const checkboxEl = selectAllCheckboxes.value[period.key];
            if (checkboxEl) {
                checkboxEl.indeterminate = getSomeSelected(period.key).value;
            }
        });
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
    rangePeriods.forEach(period => {
        const checkboxEl = selectAllCheckboxes.value[period.key];
        if (checkboxEl) {
            checkboxEl.indeterminate = getSomeSelected(period.key).value;
        }
    });
});
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

            <div class="d-flex gap-2">
                <!-- Remove selected accounts Button -->
                <ActionButton
                    variant="primary"
                    iconClass="las la-user-circle"
                    label="Reset selected"
                    title="Remove all selected accounts"
                    :disabled="!hasSelectedAccounts"
                    @click="handleRemoveSelectedAccounts"
                />

                <!-- Refresh Button -->
                <ActionButton
                    variant="outline-secondary"
                    iconClass="las la-sync-alt"
                    title="Refresh the data table and Reset filters"
                    :disabled="loading || rangeLoading"
                    :loading="loading"
                    :iconOnly="true"
                    @click="handleRefetchBetAndPeriod"
                />
            </div>
        </div>

        <!-- Skeleton Loading -->
        <RangeTableSkeleton v-if="betStore.rangeLoading" :rangePeriods="rangePeriods" />

        <div v-else class="d-flex flex-column gap-3">
            <div v-for="period in rangePeriods" :key="period?.key" class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <strong>{{ period?.label }}</strong>
                        <span
                            v-if="getRangeData(period?.key)?.date_range"
                            class="text-muted ms-2"
                            style="font-size: 0.875rem"
                        >
                            ({{ getRangeData(period?.key)?.date_range?.start_date }} -
                            {{ getRangeData(period?.key)?.date_range?.end_date }})
                        </span>
                    </div>

                    <!-- Show record options (Frontend filters display) -->
                    <div v-if="getRangeData(period?.key)?.data?.length">
                        <select
                            class="form-select form-select-sm"
                            :value="perPage"
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
                    <div v-if="getRangeData(period.key)?.data?.length" class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <!-- Checkbox for select all Accounts -->
                                    <th class="w-1">
                                        <input
                                            :ref="el => setSelectAllRef(period.key, el)"
                                            class="form-check-input m-0"
                                            type="checkbox"
                                            :checked="getAllSelected(period.key).value"
                                            @change="handleSelectAll(period.key, $event)"
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
                                    v-for="row in getFilteredData(period?.key)"
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
                                            Number(getDummyDeposit()) - Number(getDummyWithdraw()) <
                                            0
                                                ? 'bg-red-lt'
                                                : 'bg-green-lt'
                                        "
                                    >
                                        ${{
                                            (
                                                Number(getDummyDeposit()) -
                                                Number(getDummyWithdraw())
                                            ).toFixed(0)
                                        }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="p-3 text-muted">No data for this period.</div>
                </div>
            </div>
        </div>
    </div>
</template>
