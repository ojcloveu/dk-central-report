<!-- BetDataTable.vue -->
<script setup>
import { onMounted, reactive } from 'vue';
import { useBetStore } from '../stores/betStore';
import BetPageHeader from './BetPageHeader.vue';
import BetFilterForm from './BetFilterForm.vue';
import BetTableHeader from './table/BetTableHeader.vue';
import BetTableRow from './table/BetTableRow.vue';
import Pagination from './table/Pagination.vue';
import EmptyState from './EmptyState.vue';
import RangeTable from './RangeTable.vue';

const betStore = useBetStore();

const localFilters = reactive({ ...betStore.filters });

const submitFilters = filters => betStore.applyFilters(filters);

onMounted(() => {
    if (!betStore.hasBets) betStore.fetchBets();
});

const handlePaginationClick = page => {
    if (page === '&laquo; Previous') page = betStore.meta.current_page - 1;
    else if (page === 'Next &raquo;') page = betStore.meta.current_page + 1;
    if (typeof page === 'number' && page >= 1 && page <= betStore.meta.last_page)
        betStore.setPage(page);
};

const handlePerPageChange = perPage => {
    betStore.setPerPage(perPage);
};

const handleSort = column => {
    let sortDir = 'asc';
    if (betStore.filters.sort_by === column)
        sortDir = betStore.filters.sort_dir === 'asc' ? 'desc' : 'asc';
    betStore.setSort(column, sortDir);
};

const getMasterBadgeClass = master => {
    const classes = {
        VIRTUAL: 'bg-primary-lt',
        ESPORTS: 'bg-success-lt',
        LOTTERY: 'bg-warning-lt',
        POKER: 'bg-info-lt',
        SLOTS: 'bg-purple-lt',
    };
    return classes[master] || 'bg-secondary-lt';
};

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
        <BetPageHeader :onRefresh="betStore.fetchBets" />
        <BetFilterForm :initialFilters="localFilters" :onSubmit="submitFilters" />

        <div class="card">
            <div v-if="betStore.loading" class="card-body text-center py-5">
                <div class="spinner-border text-primary mb-3"></div>
                <p class="text-muted mb-0">Loading bet reports...</p>
            </div>

            <div v-else-if="betStore.error" class="card-body">
                <div class="alert alert-danger">{{ betStore.error }}</div>
            </div>

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

                <Pagination
                    :meta="betStore.meta"
                    :onPageClick="handlePaginationClick"
                    :onPerPageChange="handlePerPageChange"
                />
            </div>

            <EmptyState
                v-else
                title="No bet reports found"
                subtitle="Try adjusting your search or filter."
                :refresh-action="betStore.fetchBets"
                :show-refresh-button="true"
            />
        </div>

        <RangeTable v-if="betStore.hasSelectedAccounts" class="mt-4" />
    </div>
</template>
