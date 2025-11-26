<!-- BetDataTable.vue -->
<script setup>
import { onMounted, reactive } from 'vue';
import { useBetStore } from '../stores/betStore';
import BetPageHeader from './BetPageHeader.vue';
import BetFilterForm from './filter/BetFilterForm.vue';
import BetTableHeader from './table/BetTableHeader.vue';
import BetTableRow from './table/BetTableRow.vue';
import Pagination from './table/Pagination.vue';
import EmptyState from './EmptyState.vue';
import RangeTable from './RangeTable.vue';
import AllTimeRangeTable from './AllTimeRangeTable.vue';
import BetTableSkeleton from './loading/BetTableSkeleton.vue';
import { storeToRefs } from 'pinia';
import ActionButton from './buttons/ActionButton.vue';

/*
 * State Management & Initialization
 */
const betStore = useBetStore();
const localFilters = reactive({ ...betStore.filters });

const { loading, rangeLoading } = storeToRefs(betStore);

onMounted(() => {
    if (!betStore.hasBets) betStore.fetchBets();
});

/*
 * Filter Submission & Refresh
 */
const submitFilters = filters => betStore.applyFilters(filters);

/*
 * Pagination Handling
 */
const handlePaginationClick = page => {
    if (page === '&laquo; Previous') page = betStore.meta.current_page - 1;
    else if (page === 'Next &raquo;') page = betStore.meta.current_page + 1;
    if (typeof page === 'number' && page >= 1 && page <= betStore.meta.last_page)
        betStore.setPage(page);
};

const handlePerPageChange = perPage => {
    betStore.setPerPage(perPage);
};

/*
 * Sorting Logic
 */
const handleSort = column => {
    let sortDir = 'asc';
    if (betStore.filters.sort_by === column)
        sortDir = betStore.filters.sort_dir === 'asc' ? 'desc' : 'asc';
    betStore.setSort(column, sortDir);
};

/*
 * Table Styling Helpers
 */
const getMasterBadgeClass = master => {
    const classes = {
        DKAO: 'bg-info-lt',
        DKDK: 'bg-purple-lt',
    };
    return classes[master] || 'bg-secondary-lt';
};

/*
 * Sortable Column Configuration
 */
const sortableColumns = [
    { key: 'account', label: 'Account' },
    { key: 'channel', label: 'Channel' },
    { key: 'trandate', label: 'Date' },
    { key: 'master', label: 'Master' },
    { key: 'min', label: 'Min' },
    { key: 'max', label: 'Max' },
    { key: 'count', label: 'Count' },
    { key: 'turnover', label: 'Turnover' },
    { key: 'winlose', label: 'Win/Lose' },
    { key: 'lp', label: 'LP' },
];
</script>

<template>
    <div>
        <!-- Page Header -->
        <BetPageHeader />

        <!-- Filter Form -->
        <BetFilterForm
            :initialFilters="localFilters"
            :onSubmit="submitFilters"
            :onRefresh="betStore.refetchBetsAndPeriod"
            :rangeLoading="loading || rangeLoading"
        />

        <!-- Bet Data Table Section -->
        <div class="card">
            <!-- Table Loading State -->
            <div v-if="betStore.loading" class="card-body p-0">
                <BetTableSkeleton />
            </div>

            <!-- Error State -->
            <div v-else-if="betStore.error" class="card-body">
                <div class="alert alert-danger">{{ betStore.error }}</div>
            </div>

            <!-- Table Data -->
            <div v-else-if="betStore.hasBets">
                <div class="table-responsive">
                    <table class="table table-vcenter table-hover card-table">
                        <BetTableHeader
                            :filters="betStore.filters"
                            :onSort="handleSort"
                            :sortable-columns="sortableColumns"
                        />
                        <tbody>
                            <BetTableRow
                                v-for="bet in betStore.data"
                                :key="bet.id"
                                :bet="bet"
                                :getMasterBadgeClass="getMasterBadgeClass"
                            />
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Controls -->
                <Pagination
                    :meta="betStore.meta"
                    :onPageClick="handlePaginationClick"
                    :onPerPageChange="handlePerPageChange"
                />
            </div>

            <!-- Empty State -->
            <EmptyState
                v-else
                title="No bet reports found"
                subtitle="Try adjusting your search or filter."
                :refresh-action="betStore.fetchBets"
                :show-refresh-button="true"
            />
        </div>

        <!-- Range Table (Visible when accounts are selected) -->
        <RangeTable v-if="betStore.hasSelectedAccounts" class="mt-4" />

        <!-- All Time Report Button -->
        <div v-if="betStore.hasSelectedAccounts" class="mt-3 d-flex justify-content-end">
            <ActionButton
                variant="primary"
                iconClass="las la-chart-bar"
                :label="betStore.showAllTimeReport ? 'Hide All Time Report' : 'All Time Report'"
                title="All Time Report"
                :disabled="betStore.rangeLoading && betStore.showAllTimeReport"
                :loading="betStore.rangeLoading && betStore.showAllTimeReport"
                @click="betStore.toggleAllTimeReport"
            />
        </div>

        <!-- All Time Range Table -->
        <AllTimeRangeTable v-if="betStore.showAllTimeReport" />
    </div>
</template>
