<script setup>
import { storeToRefs } from 'pinia';
import { useBetStore } from '../stores/betStore';
import { amountColor, lpBgColor } from '../utils/getStatusClass';
import RangeTableSkeleton from './loading/RangeTableSkeleton.vue';
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
    { key: '7d', label: 'Last 7 days' },
    { key: '1m', label: 'Last 1 month' },
    { key: '3m', label: 'Last 3 months' },
];

/*
 * Helper function to safely get the data for a given range key
 */
const getRangeData = key => betStore.rangesTables[key];

/*
 * Handle pagination click
 */
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

/*
 * Handle change per page for record options selection 
 */
const handleChangePerPage = count => {
    rangePeriods.forEach(period => {
        betStore.setRangeItemsPerPage(period.key, +count);
    });
};

/*
 * Global value for record options selection 
 */
const globalPerPage = computed(() => {
    return getRangeData('7d')?.meta?.per_page || 10;
});

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

    // Refetch all 7d, 1m, 3m tables with sort param
    betStore.fetchRangeData(null, 1, null, rangeSort.sort_by, rangeSort.sort_dir);
};

/*
 * Checkbox select/unselect all accounts in a period
 */
const getAllSelected = periodKey =>
    computed(() => {
        const data = getRangeData(periodKey)?.data || [];
        if (data.length === 0) return false;

        // Check if at least one account in period's data is selected
        return data.every(row => betStore.selectedAccounts.includes(row.account));
    });

const getSomeSelected = periodKey =>
    computed(() => {
        const data = getRangeData(periodKey)?.data || [];
        const allSelected = getAllSelected(periodKey).value;

        // Check if at least one account in period's data is selected
        return !allSelected && data.some(row => betStore.selectedAccounts.includes(row.account));
    });

// Handle select all checkbox change for a specific period
const handleSelectAll = (periodKey, event) => {
    const accounts = getRangeData(periodKey)?.data.map(row => row.account) || [];
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

    betStore.fetchRangeData(null, 1, null, rangeSort.sort_by, rangeSort.sort_dir);
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
                <button
                    type="button"
                    class="btn btn-primary px-2"
                    @click="handleRemoveSelectedAccounts"
                    title="Remove all selected accounts"
                    :disabled="!hasSelectedAccounts"
                >
                    <i class="las la-user-circle fs-2 pe-1"></i>
                    Reset selected
                </button>

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
                            :value="globalPerPage"
                            @change="handleChangePerPage($event.target?.value)"
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
                                    <th @click="handleRangeSort('account')" class="sortable">
                                        Account
                                        <span v-if="rangeSort.sort_by === 'account'">
                                            {{ rangeSort.sort_dir === 'asc' ? '▲' : '▼' }}
                                        </span>
                                    </th>

                                    <th @click="handleRangeSort('total_count')" class="sortable">
                                        Count
                                        <span v-if="rangeSort.sort_by === 'total_count'">
                                            {{ rangeSort.sort_dir === 'asc' ? '▲' : '▼' }}
                                        </span>
                                    </th>

                                    <th @click="handleRangeSort('total_turnover')" class="sortable">
                                        Turnover
                                        <span v-if="rangeSort.sort_by === 'total_turnover'">
                                            {{ rangeSort.sort_dir === 'asc' ? '▲' : '▼' }}
                                        </span>
                                    </th>

                                    <th @click="handleRangeSort('total_winlose')" class="sortable">
                                        Win/Lose
                                        <span v-if="rangeSort.sort_by === 'total_winlose'">
                                            {{ rangeSort.sort_dir === 'asc' ? '▲' : '▼' }}
                                        </span>
                                    </th>

                                    <th @click="handleRangeSort('total_lp')" class="sortable">
                                        LP
                                        <span v-if="rangeSort.sort_by === 'total_lp'">
                                            {{ rangeSort.sort_dir === 'asc' ? '▲' : '▼' }}
                                        </span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="row in getRangeData(period?.key).data"
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
