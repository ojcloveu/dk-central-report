<!-- BetFilterForm.vue -->
<script setup>
import { reactive, watch, onMounted } from 'vue';
import { useFilterStore } from '@/stores/filterStore';
import SingleSelectFilter from './SingleSelectFilter.vue';

const props = defineProps({
    initialFilters: Object,
    onSubmit: Function,
});

const localFilters = reactive({ ...props.initialFilters });
const filterStore = useFilterStore();

const today = new Date().toISOString().split('T')[0];

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
                        :loading="filterStore.loading.masters"
                        :fetch-items="filterStore.fetchMasters"
                    />
                </div>

                <div class="col-md-3 col-sm-6">
                    <label class="form-label">Account</label>
                    <SingleSelectFilter
                        v-model="localFilters.account"
                        :items="filterStore.accounts"
                        :placeholder="'Select Account'"
                        :loading="filterStore.loading.accounts"
                        :fetch-items="filterStore.fetchAccounts"
                    />
                </div>

                <div class="col-md-3 col-sm-6">
                    <label class="form-label">Channel</label>
                    <SingleSelectFilter
                        v-model="localFilters.channel"
                        :items="filterStore.channels"
                        :placeholder="'Select Channel'"
                        :loading="filterStore.loading.channels"
                        :fetch-items="filterStore.fetchChannels"
                    />
                </div>
            </form>
        </div>
    </div>
</template>
