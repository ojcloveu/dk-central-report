<!-- BetFilterForm.vue -->
<script setup>
import { reactive, watch, onMounted } from 'vue';
import { useFilterStore } from '@/stores/filterStore';
import { storeToRefs } from 'pinia';
import SingleSelectFilter from './SingleSelectFilter.vue';

const props = defineProps({
    initialFilters: Object,
    onSubmit: Function,
});

const localFilters = reactive({ ...props.initialFilters });
const filterStore = useFilterStore();

// Get pagination refs from store
const { pagination, loading } = storeToRefs(filterStore);

let debounceTimer = null;
const triggerFilter = () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        props.onSubmit(localFilters);
    }, 700);
};

// Watch for changes in filter form
watch(localFilters, () => {
    triggerFilter();
});

// Reset filters
const handleReset = () => {
    localFilters.trandate = '';
    localFilters.master = '';
    localFilters.account = '';
    localFilters.channel = '';
};

// Handle search for each filter type
const handleSearchMasters = query => {
    filterStore.fetchMasters({ q: query, page: 1 }, false);
};

const handleSearchAccounts = query => {
    filterStore.fetchAccounts({ q: query, page: 1 }, false);
};

const handleSearchChannels = query => {
    filterStore.fetchChannels({ q: query, page: 1 }, false);
};

// Handle load more for each filter type
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

            <div>
                <button type="button" class="btn btn-primary px-1 btn-sm" @click="handleReset">
                    <i class="las la-eraser fs-2"></i> Reset
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
                    <SingleSelectFilter
                        v-model="localFilters.master"
                        :items="filterStore.masters"
                        :placeholder="'Select Master'"
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
