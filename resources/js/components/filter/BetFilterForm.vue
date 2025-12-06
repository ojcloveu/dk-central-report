<script setup>
import { reactive, watch, onMounted, ref, nextTick, computed } from 'vue';
import { storeToRefs } from 'pinia';
import { useFilterStore } from '@/stores/filterStore';
import { useBetStore } from '@/stores/betStore';
import SingleSelectFilter from './SingleSelectFilter.vue';
import MultiSelectFilter from './MultiSelectFilter.vue';
import DateRangeFilter from './DateRangeFilter.vue';
import ActionButton from '../buttons/ActionButton.vue';
import RangeFilterModal from './RangeFilterModal.vue';

/*
 * Props & emits
 */
const props = defineProps({
    initialFilters: Object,
    onSubmit: Function,
    onRefresh: Function,
    rangeLoading: {
        type: Boolean,
        default: false,
    },
});

/*
 * Local reactive state
 */
const localFilters = reactive({
    ...props.initialFilters,
    minRange: { min: null, max: null },
    maxRange: { min: null, max: null },
    countRange: { min: null, max: null },
    turnoverRange: { min: null, max: null },
    winloseRange: { min: null, max: null },
    lPRange: { min: null, max: null },
});
const filterStore = useFilterStore();
const betStore = useBetStore();

const { pagination, loading: filterLoading } = storeToRefs(filterStore);
const { hasSelectedAccounts, loading } = storeToRefs(betStore);

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
watch(
    () => [
        localFilters.trandate,
        localFilters.master,
        localFilters.account,
        localFilters.channel,
        localFilters.minRange,
        localFilters.maxRange,
        localFilters.countRange,
        localFilters.turnoverRange,
        localFilters.winloseRange,
        localFilters.lPRange,
    ],
    () => {
        if (skipNextWatch.value) {
            skipNextWatch.value = false;
            return;
        }
        triggerFilter();
    },
    { deep: true }
);

// Reset filters
const handleReset = () => {
    localFilters.trandate = { start_date: null, end_date: null };
    localFilters.master = [];
    localFilters.account = '';
    localFilters.channel = '';
    localFilters.minRange = { min: null, max: null };
    localFilters.maxRange = { min: null, max: null };
    localFilters.countRange = { min: null, max: null };
    localFilters.turnoverRange = { min: null, max: null };
    localFilters.winloseRange = { min: null, max: null };
    localFilters.lPRange = { min: null, max: null };

    // Also reset the refs
    minRangeRef.value = { min: null, max: null };
    maxRangeRef.value = { min: null, max: null };
    countRangeRef.value = { min: null, max: null };
    turnoverRangeRef.value = { min: null, max: null };
    winLoseRangeRef.value = { min: null, max: null };
    lPRangeRef.value = { min: null, max: null };
};

// Refetch bet and period data
const handleRefetchBetAndPeriod = () => {
    props.onRefresh();
};

// Remove all selected accounts
const handleRemoveSelectedAccounts = () => {
    betStore.clearAllSelectedAccounts();
};

const hasFiltersToReset = computed(() => {
    return (
        localFilters.trandate?.start_date ||
        localFilters.trandate?.end_date ||
        localFilters.master.length > 0 ||
        localFilters.account !== '' ||
        localFilters.channel !== '' ||
        localFilters.minRange?.min !== null ||
        localFilters.minRange?.max !== null ||
        localFilters.maxRange?.min !== null ||
        localFilters.maxRange?.max !== null ||
        localFilters.countRange?.min !== null ||
        localFilters.countRange?.max !== null ||
        localFilters.turnoverRange?.min !== null ||
        localFilters.turnoverRange?.max !== null ||
        localFilters.winloseRange?.min !== null ||
        localFilters.winloseRange?.max !== null ||
        localFilters.lPRange?.min !== null ||
        localFilters.lPRange?.max !== null
    );
});

/*
 * Toggle filters visibility
 */
const showFilters = ref(false);
const toggleFilters = () => {
    showFilters.value = !showFilters.value;
};

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

/*
 * Range filter refs
 */
const minRangeRef = ref({
    min: null,
    max: null,
});

const maxRangeRef = ref({
    min: null,
    max: null,
});

const winLoseRangeRef = ref({
    min: null,
    max: null,
});

const turnoverRangeRef = ref({
    min: null,
    max: null,
});

const lPRangeRef = ref({
    min: null,
    max: null,
});

const countRangeRef = ref({
    min: null,
    max: null,
});

/*
 * Sync range filter refs with localFilters
 */
watch(
    minRangeRef,
    newVal => {
        localFilters.minRange = { ...newVal };
    },
    { deep: true }
);

watch(
    maxRangeRef,
    newVal => {
        localFilters.maxRange = { ...newVal };
    },
    { deep: true }
);

watch(
    countRangeRef,
    newVal => {
        localFilters.countRange = { ...newVal };
    },
    { deep: true }
);

watch(
    turnoverRangeRef,
    newVal => {
        localFilters.turnoverRange = { ...newVal };
    },
    { deep: true }
);

watch(
    winLoseRangeRef,
    newVal => {
        localFilters.winloseRange = { ...newVal };
    },
    { deep: true }
);

watch(
    lPRangeRef,
    newVal => {
        localFilters.lPRange = { ...newVal };
    },
    { deep: true }
);
</script>

<template>
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Filter Transactions</h3>

            <div class="btn-list">
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

                <!-- Reset Button -->
                <ActionButton
                    variant="primary"
                    iconClass="las la-sliders-h"
                    label="Reset Filter"
                    title="Reset all filters to default"
                    :disabled="!hasFiltersToReset"
                    @click="handleReset"
                />

                <!-- Refresh Button -->
                <ActionButton
                    variant="outline-secondary"
                    iconClass="las la-sync-alt"
                    title="Refresh the data table and Reset filters"
                    :disabled="rangeLoading"
                    :loading="loading"
                    :iconOnly="true"
                    @click="handleRefetchBetAndPeriod"
                />

                <!-- Toggle Filters Button -->
                <ActionButton
                    variant="outline-secondary"
                    :iconClass="showFilters ? 'las la-angle-up' : 'las la-angle-down'"
                    label="Toggle Filters"
                    title="Toggle filters"
                    :iconOnly="true"
                    @click="toggleFilters"
                />
            </div>
        </div>

        <transition name="fade">
            <div v-if="showFilters" class="p-2 card-body">
                <form class="d-flex flex-column gap-2">
                    <!-- Basic Filters Section -->
                    <div class="filter-section">
                        <h5 class="fs-4 mb-2"><i class="las la-filter me-2"></i>Basic Filters</h5>
                        <div class="row g-2 align-items-end">
                            <!-- Date Range filter -->
                            <div class="col-md-3 col-sm-6">
                                <label class="fs-5" for="trandate">Date</label>
                                <DateRangeFilter
                                    v-model="localFilters.trandate"
                                    :placeholder="'Select Date Range'"
                                />
                            </div>

                            <!-- Master filter -->
                            <div class="col-md-3 col-sm-6">
                                <label class="fs-5" for="master">Master</label>
                                <MultiSelectFilter
                                    v-model="localFilters.master"
                                    :items="filterStore.masters"
                                    :placeholder="'Select Master(s)'"
                                    :loading="filterLoading.masters"
                                    :fetch-items="() => filterStore.fetchMasters({ page: 1 })"
                                    :has-next-page="pagination.masters.hasNextPage"
                                    :current-page="pagination.masters.currentPage"
                                    @search="handleSearchMasters"
                                    @load-more="handleLoadMoreMasters"
                                />
                            </div>

                            <!-- Account filter -->
                            <div class="col-md-3 col-sm-6">
                                <label class="fs-5" for="account">Account</label>
                                <SingleSelectFilter
                                    v-model="localFilters.account"
                                    :items="filterStore.accounts"
                                    :placeholder="'Select Account'"
                                    :loading="filterLoading.accounts"
                                    :fetch-items="() => filterStore.fetchAccounts({ page: 1 })"
                                    :has-next-page="pagination.accounts.hasNextPage"
                                    :current-page="pagination.accounts.currentPage"
                                    @search="handleSearchAccounts"
                                    @load-more="handleLoadMoreAccounts"
                                />
                            </div>

                            <!-- Channel filter -->
                            <div class="col-md-3 col-sm-6">
                                <label class="fs-5" for="channel">Channel</label>
                                <SingleSelectFilter
                                    v-model="localFilters.channel"
                                    :items="filterStore.channels"
                                    :placeholder="'Select Channel'"
                                    :loading="filterLoading.channels"
                                    :fetch-items="() => filterStore.fetchChannels({ page: 1 })"
                                    :has-next-page="pagination.channels.hasNextPage"
                                    :current-page="pagination.channels.currentPage"
                                    @search="handleSearchChannels"
                                    @load-more="handleLoadMoreChannels"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Range Filters Section -->
                    <div class="filter-section">
                        <h5 class="fs-4 mb-2">
                            <i class="las la-sliders-h me-2"></i>Range Filters
                        </h5>
                        <div class="row g-2 align-items-end">
                            <!-- MIN Range filter -->
                            <div class="col-md-3 col-sm-6">
                                <label class="fs-5" for="minRange">MIN</label>
                                <RangeFilterModal
                                    v-model="minRangeRef"
                                    label="MIN Range"
                                    :step="1"
                                    :placeholder="{ min: 'Min', max: 'Max' }"
                                />
                            </div>

                            <!-- MAX Range filter -->
                            <div class="col-md-3 col-sm-6">
                                <label class="fs-5" for="maxRange">MAX</label>
                                <RangeFilterModal
                                    v-model="maxRangeRef"
                                    label="MAX Range"
                                    :step="1"
                                    :placeholder="{ min: 'Min', max: 'Max' }"
                                />
                            </div>

                            <!-- Count Range filter -->
                            <div class="col-md-3 col-sm-6">
                                <label class="fs-5" for="countRange">Count</label>
                                <RangeFilterModal
                                    v-model="countRangeRef"
                                    label="Count Range"
                                    :step="1"
                                    :placeholder="{ min: 'Min', max: 'Max' }"
                                />
                            </div>

                            <!-- Turnover Range filter -->
                            <div class="col-md-3 col-sm-6">
                                <label class="fs-5" for="turnoverRange">Turnover</label>
                                <RangeFilterModal
                                    v-model="turnoverRangeRef"
                                    label="Turnover Range"
                                    :step="1"
                                    :placeholder="{ min: 'Min', max: 'Max' }"
                                />
                            </div>

                            <!-- Win/Lose Range filter -->
                            <div class="col-md-3 col-sm-6">
                                <label class="fs-5" for="winLoseRange">Win/Lose</label>
                                <RangeFilterModal
                                    v-model="winLoseRangeRef"
                                    label="Win/Lose Range"
                                    :step="1"
                                    :placeholder="{ min: 'Min', max: 'Max' }"
                                />
                            </div>

                            <!-- LP Range filter -->
                            <div class="col-md-3 col-sm-6">
                                <label class="fs-5" for="lPRange">LP</label>
                                <RangeFilterModal
                                    v-model="lPRangeRef"
                                    label="LP Range"
                                    :step="1"
                                    :placeholder="{ min: 'Min', max: 'Max' }"
                                />
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </transition>
    </div>
</template>

<style scoped>
/* Smooth transition for filter collapse */
.fade-enter-active,
.fade-leave-active {
    transition: all 0.2s ease;
    overflow: hidden;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

/* Filter section styling */
.filter-section {
    padding: 1rem;
    background-color: rgba(0, 0, 0, 0.02);
    border: 1px solid rgba(0, 0, 0, 0.08);
    border-radius: 0.5rem;
    transition: all 0.2s ease;
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .filter-section {
        border-color: var(--tblr-bg-forms);
    }
}
</style>
