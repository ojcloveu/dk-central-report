<script setup>
import { reactive, watch, onMounted, ref, nextTick, computed } from 'vue';
import { storeToRefs } from 'pinia';
import { useFilterStore } from '@/stores/filterStore';
import SingleSelectFilter from './SingleSelectFilter.vue';
import MultiSelectFilter from './MultiSelectFilter.vue';

/*
 * Props & emits
 */
const props = defineProps({
    initialFilters: Object,
    onSubmit: Function,
    onRefresh: Function,
});

/*
 * Local reactive state
 */
const localFilters = reactive({ ...props.initialFilters });
const filterStore = useFilterStore();
const { pagination, loading } = storeToRefs(filterStore);

/*
 * Helpers: debounce for filter submission
 */
let debounceTimer = null;
const triggerFilter = () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        props.onSubmit(localFilters);
    }, 700);
};

/*
 * Reset filter & Refresh
 */
let skipNextWatch = ref(false);

// Watch for changes in filter form
watch(localFilters, () => {
    if (skipNextWatch.value) { 
        skipNextWatch.value = false; 
        return;
    }
    triggerFilter();
});

// Reset filters
const handleReset = () => {
    localFilters.trandate = '';
    localFilters.master = [];
    localFilters.account = '';
    localFilters.channel = '';
};

const handleRefreshAndReset = () => {
    skipNextWatch.value = true;

    handleReset();
    props.onRefresh();
};

const hasFiltersToReset = computed(() => {
    return (
        localFilters.trandate !== '' ||
        localFilters.master.length > 0 ||
        localFilters.account !== '' ||
        localFilters.channel !== ''
    );
});

/*
 * Filter search and pagination handlers
 */
const handleSearchMasters = query => {
    filterStore.fetchMasters({ q: query, page: 1 }, false);
};

const handleSearchAccounts = query => {
    filterStore.fetchAccounts({ q: query, page: 1 }, false);
};

const handleSearchChannels = query => {
    filterStore.fetchChannels({ q: query, page: 1 }, false);
};

const handleLoadMoreMasters = (page, query) => {
    filterStore.loadMoreMasters(page, query);
};

const handleLoadMoreAccounts = (page, query) => {
    filterStore.loadMoreAccounts(page, query);
};

const handleLoadMoreChannels = (page, query) => {
    filterStore.loadMoreChannels(page, query);
};
</script>

<template>
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Filter Transactions</h3>

            <div class="btn-list">
                <!-- Reset Button -->
                <button
                    type="button"
                    class="btn btn-primary px-2"
                    @click="handleReset"
                    title="Reset all filters to default"
                    :disabled="!hasFiltersToReset"
                >
                    <i class="las la-eraser fs-2"></i> Reset
                </button>

                <!-- Refresh Button -->
                <button
                    type="button"
                    class="btn btn-icon btn-outline-secondary"
                    aria-label="Refresh"
                    @click="handleRefreshAndReset"
                    title="Refresh the data table and Reset filters"
                    :disabled="loading.masters || loading.accounts || loading.channels"
                >
                    <i class="las la-sync-alt fs-2" :class="{ 'fa-spin': loading }"></i>
                </button>
            </div>
        </div>

        <div class="card-body">
            <form class="row g-3 align-items-end">
                <div class="col-md-3 col-sm-6">
                    <label class="form-label">Date</label>
                    <input type="date" class="form-control" v-model="localFilters.trandate" />
                </div>

                <div class="col-md-3 col-sm-6">
                    <label class="form-label">Master</label>
                    <MultiSelectFilter
                        v-model="localFilters.master" 
                        :items="filterStore.masters"
                        :placeholder="'Select Master(s)'"
                        :loading="loading.masters"
                        :fetch-items="() => filterStore.fetchMasters({ page: 1 })"
                        :has-next-page="pagination.masters.hasNextPage"
                        :current-page="pagination.masters.currentPage"
                        @search="handleSearchMasters"
                        @load-more="handleLoadMoreMasters"
                    />
                </div>

                <div class="col-md-3 col-sm-6">
                    <label class="form-label">Account</label>
                    <SingleSelectFilter
                        v-model="localFilters.account"
                        :items="filterStore.accounts"
                        :placeholder="'Select Account'"
                        :loading="loading.accounts"
                        :fetch-items="() => filterStore.fetchAccounts({ page: 1 })"
                        :has-next-page="pagination.accounts.hasNextPage"
                        :current-page="pagination.accounts.currentPage"
                        @search="handleSearchAccounts"
                        @load-more="handleLoadMoreAccounts"
                    />
                </div>

                <div class="col-md-3 col-sm-6">
                    <label class="form-label">Channel</label>
                    <SingleSelectFilter
                        v-model="localFilters.channel"
                        :items="filterStore.channels"
                        :placeholder="'Select Channel'"
                        :loading="loading.channels"
                        :fetch-items="() => filterStore.fetchChannels({ page: 1 })"
                        :has-next-page="pagination.channels.hasNextPage"
                        :current-page="pagination.channels.currentPage"
                        @search="handleSearchChannels"
                        @load-more="handleLoadMoreChannels"
                    />
                </div>
            </form>
        </div>
    </div>
</template>
