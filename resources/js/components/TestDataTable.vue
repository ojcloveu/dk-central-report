<script setup>
import { onMounted, ref, reactive } from 'vue';
import { useBetStore } from '../stores/betStore';
import EmptyState from './EmptyState.vue';

const betStore = useBetStore();

// Reactive Local Filter State
const localFilters = reactive({
    trandate: betStore.filters.trandate,
    master: betStore.filters.master,
    account: betStore.filters.account,
    channel: betStore.filters.channel,
});

// Filter Submission Logic
const submitFilters = () => {
    betStore.applyFilters(localFilters);
};

// Filter Reset Logic
const resetFilters = () => {
    // Reset local state
    localFilters.trandate = '';
    localFilters.master = '';
    localFilters.account = '';
    localFilters.channel = '';
    
    submitFilters();
};

// Fetch initial data when the component is mounted
onMounted(() => {
    if (!betStore.hasBets) {
        betStore.fetchBets();
    }
});

// Handle pagination link clicks
const handlePaginationClick = page => {
    // Laravel pagination labels can be 'Previous' or 'Next'
    if (page === '&laquo; Previous') {
        page = betStore.meta.current_page - 1;
    } else if (page === 'Next &raquo;') {
        page = betStore.meta.current_page + 1;
    }
    // Only proceed if page is a valid number
    if (typeof page === 'number' && page >= 1 && page <= betStore.meta.last_page) {
        betStore.setPage(page);
    }
};

// Handle column sorting click
const handleSort = column => {
    let sortDir = 'asc';
    if (betStore.filters.sort_by === column) {
        // If clicking the same column, toggle direction
        sortDir = betStore.filters.sort_dir === 'asc' ? 'desc' : 'asc';
    }
    betStore.setSort(column, sortDir);
};

// Get badge class for master type
const getMasterBadgeClass = (master) => {
    const classes = {
        'VIRTUAL': 'bg-primary-lt',
        'ESPORTS': 'bg-success-lt',
        'LOTTERY': 'bg-warning-lt',
        'POKER': 'bg-info-lt',
        'SLOTS': 'bg-purple-lt',
    };
    return classes[master] || 'bg-secondary-lt';
};

// Expose store state to the template
// const { data, meta, links, loading, error } = storeToRefs(betStore);
</script>

<template>
    <div>
        <!-- Page Header -->
        <div class="page-header d-print-none mb-3">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">TEST Bet Report</h2>
                    <div class="text-muted mt-1">View and manage all betting transactions</div>
                </div>

                <div class="col-auto ms-auto">
                    <div class="btn-list">
                        <button class="btn btn-icon" aria-label="Refresh" @click="betStore.fetchBets()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
                                <path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4"></path>
                                <path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4"></path>
                            </svg>
                        </button>
                        <div class="dropdown">
                            <button class="btn dropdown-toggle" data-bs-toggle="dropdown">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon me-1">
                                    <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2"></path>
                                    <path d="M7 11l5 5l5 -5"></path>
                                    <path d="M12 4l0 12"></path>
                                </svg>
                                Export
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon me-2">
                                        <path d="M14 3v4a1 1 0 0 0 1 1h4"></path>
                                        <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"></path>
                                    </svg>
                                    Export as CSV
                                </a>
                                <a class="dropdown-item" href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon me-2">
                                        <path d="M14 3v4a1 1 0 0 0 1 1h4"></path>
                                        <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"></path>
                                    </svg>
                                    Export as Excel
                                </a>
                                <a class="dropdown-item" href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon me-2">
                                        <path d="M14 3v4a1 1 0 0 0 1 1h4"></path>
                                        <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"></path>
                                    </svg>
                                    Export as PDF
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter -->
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Filter Transactions</h3>
            </div>

            <div class="card-body">
                <form @submit.prevent="submitFilters" class="row g-3 align-items-end">
                    
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label">Date</label>
                        <input type="date" class="form-control" v-model="localFilters.trandate" />
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <label class="form-label">Master (Partial Match)</label>
                        <input type="text" class="form-control" placeholder="e.g., VIRTUAL" v-model="localFilters.master" />
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <label class="form-label">Account (Partial Match)</label>
                        <input type="text" class="form-control" placeholder="e.g., THB7686" v-model="localFilters.account" />
                    </div>

                    <div class="col-md-2 col-sm-6">
                        <label class="form-label">Channel (Exact Match)</label>
                        <input type="text" class="form-control" placeholder="e.g., TH" v-model="localFilters.channel" />
                    </div>

                    <div class="d-flex justify-content-end">
                      <button type="submit" class="btn btn-primary w-100 me-2">
                          <i class="las la-search fs-2"></i>
                      </button>
                      <button type="button" class="btn btn-secondary w-100" @click="resetFilters">
                          <i class="las la-eraser fs-2"></i>
                      </button>
                  </div>
                </form>
            </div>
        </div>

        <!-- Main Card -->
        <div class="card">
            <!-- Loading State -->
            <div v-if="betStore.loading" class="card-body text-center py-5">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="text-muted mb-0">Loading bet reports...</p>
            </div>

            <!-- Error State -->
            <div v-else-if="betStore.error" class="card-body">
                <div class="alert alert-danger d-flex align-items-center" role="alert">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon alert-icon me-2">
                        <path d="M12 9v4"></path>
                        <path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z"></path>
                        <path d="M12 16h.01"></path>
                    </svg>
                    <div>{{ betStore.error }}</div>
                </div>
            </div>

            <!-- Data Table -->
            <div v-else-if="betStore.hasBets">
                <div class="table-responsive">
                    <table class="table table-vcenter table-hover card-table">
                        <thead>
                            <tr>
                                <th class="w-1">
                                    <input class="form-check-input m-0" type="checkbox" aria-label="Select all">
                                </th>
                                <th class="cursor-pointer user-select-none" @click="handleSort('account')">
                                    <div class="d-flex align-items-center">
                                        Account
                                        <i v-if="betStore.filters.sort_by === 'account'" class="las la-sort-amount-down fs-4" :class="{ 'rotate-180': betStore.filters.sort_dir === 'desc' }"></i>
                                    </div>
                                </th>
                                <th class="cursor-pointer user-select-none" @click="handleSort('channel')">
                                    <div class="d-flex align-items-center">
                                        Channel
                                        <i v-if="betStore.filters.sort_by === 'channel'" class="las la-sort-amount-down fs-4" :class="{ 'rotate-180': betStore.filters.sort_dir === 'desc' }"></i>
                                    </div>
                                </th>
                                <th class="cursor-pointer user-select-none" @click="handleSort('trandate')">
                                    <div class="d-flex align-items-center">
                                        Date
                                        <i v-if="betStore.filters.sort_by === 'trandate'" class="las la-sort-amount-down fs-4" :class="{ 'rotate-180': betStore.filters.sort_dir === 'desc' }"></i>
                                    </div>
                                </th>
                                <th class="cursor-pointer user-select-none" @click="handleSort('master')">
                                    <div class="d-flex align-items-center">
                                        Master
                                        <i v-if="betStore.filters.sort_by === 'master'" class="las la-sort-amount-down fs-4" :class="{ 'rotate-180': betStore.filters.sort_dir === 'desc' }"></i>
                                    </div>
                                </th>
                                <th class="text-end cursor-pointer user-select-none" @click="handleSort('min')">
                                    <div class="d-flex align-items-center justify-content-end">
                                        Min
                                        <i v-if="betStore.filters.sort_by === 'min'" class="las la-sort-amount-down fs-4" :class="{ 'rotate-180': betStore.filters.sort_dir === 'desc' }"></i>
                                    </div>
                                </th>
                                <th class="text-end cursor-pointer user-select-none" @click="handleSort('max')">
                                    <div class="d-flex align-items-center justify-content-end">
                                        Max
                                        <i v-if="betStore.filters.sort_by === 'max'" class="las la-sort-amount-down fs-4" :class="{ 'rotate-180': betStore.filters.sort_dir === 'desc' }"></i>
                                    </div>
                                </th>
                                <th class="text-end cursor-pointer user-select-none" @click="handleSort('count')">
                                    <div class="d-flex align-items-center justify-content-end">
                                        Count
                                        <i v-if="betStore.filters.sort_by === 'count'" class="las la-sort-amount-down fs-4" :class="{ 'rotate-180': betStore.filters.sort_dir === 'desc' }"></i>
                                    </div>
                                </th>
                                <th class="text-end cursor-pointer user-select-none" @click="handleSort('turnover')">
                                    <div class="d-flex align-items-center justify-content-end">
                                        Turnover
                                        <i v-if="betStore.filters.sort_by === 'turnover'" class="las la-sort-amount-down fs-4" :class="{ 'rotate-180': betStore.filters.sort_dir === 'desc' }"></i>
                                    </div>
                                </th>
                                <th class="text-end cursor-pointer user-select-none" @click="handleSort('winlose')">
                                    <div class="d-flex align-items-center justify-content-end">
                                        Win/Lose
                                        <i v-if="betStore.filters.sort_by === 'winlose'" class="las la-sort-amount-down fs-4" :class="{ 'rotate-180': betStore.filters.sort_dir === 'desc' }"></i>
                                    </div>
                                </th>
                                <th class="text-end cursor-pointer user-select-none" @click="handleSort('lp')">
                                    <div class="d-flex align-items-center justify-content-end">
                                        LP
                                        <i v-if="betStore.filters.sort_by === 'lp'" class="las la-sort-amount-down fs-4" :class="{ 'rotate-180': betStore.filters.sort_dir === 'desc' }"></i>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="bet in betStore.data" :key="bet.id">
                                <td>
                                    <input class="form-check-input m-0" type="checkbox" :aria-label="`Select bet ${bet.id}`">
                                </td>
                                <td>
                                    <div class="fw-bold">{{ bet.account }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-azure-lt">{{ bet.channel }}</span>
                                </td>
                                <td>
                                    <div class="text-muted">{{ new Date(bet.trandate).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) }}</div>
                                </td>
                                <td>
                                    <span class="badge" :class="getMasterBadgeClass(bet.master)">
                                        {{ bet.master }}
                                    </span>
                                </td>
                                <td class="text-end">{{ bet.min }}</td>
                                <td class="text-end">{{ bet.max.toLocaleString() }}</td>
                                <td class="text-end">{{ bet.count }}</td>
                                <td class="text-end">
                                    <div class="fw-bold">${{ parseFloat(bet.turnover).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</div>
                                </td>
                                <td class="text-end">
                                    <span 
                                        class="fw-bold"
                                        :class="{
                                            'text-success': parseFloat(bet.winlose) > 0,
                                            'text-danger': parseFloat(bet.winlose) < 0,
                                            'text-muted': parseFloat(bet.winlose) === 0
                                        }"
                                    >
                                        {{ parseFloat(bet.winlose) > 0 ? '+' : '' }}${{ parseFloat(bet.winlose).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <span 
                                        class="fw-bold"
                                        :class="{
                                            'text-success': parseFloat(bet.lp) > 0,
                                            'text-danger': parseFloat(bet.lp) < 0,
                                            'text-muted': parseFloat(bet.lp) === 0
                                        }"
                                    >
                                        {{ parseFloat(bet.lp) > 0 ? '+' : '' }}{{ parseFloat(bet.lp).toFixed(2) }}%
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footer with Pagination -->
                <div class="card-footer d-flex align-items-center">
                    <p class="m-0 text-muted">
                        Showing 
                        <span class="fw-bold">{{ betStore.meta.current_page * betStore.meta.per_page - betStore.meta.per_page + 1 }}</span>
                        to 
                        <span class="fw-bold">{{ Math.min(betStore.meta.current_page * betStore.meta.per_page, betStore.meta.total) }}</span>
                        of 
                        <span class="fw-bold">{{ betStore.meta.total }}</span>
                        entries
                    </p>
                    <ul class="pagination m-0 ms-auto">
                        <li 
                            v-for="link in betStore.links" 
                            :key="link.label"
                            class="page-item"
                            :class="{ 
                                active: link.active, 
                                disabled: link.url === null 
                            }"
                        >
                            <a 
                                class="page-link mx-1" 
                                href="#"
                                @click.prevent="handlePaginationClick(link.page || link.label)"
                                v-html="link.label"
                            ></a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else>
                <EmptyState
                    title="No bet reports found"
                    subtitle="Try adjusting your search or filter to find what you're looking for."
                    :refresh-action="betStore.fetchBets"
                    :show-refresh-button="true"
                />
            </div>
        </div>
    </div>
</template>

<style scoped>
.cursor-pointer {
    cursor: pointer;
}

.rotate-180 {
    transform: rotate(180deg);
}

.card-table {
    margin-bottom: 0;
}

.table thead th {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    color: #6c757d;
    background-color: #f8f9fa;
}

.empty-icon {
    width: 4rem;
    height: 4rem;
    border-radius: 50%;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
}

.empty-icon .icon {
    width: 2rem;
    height: 2rem;
    color: #6c757d;
}

.btn-list {
    gap: 0.25rem;
}

.bg-primary-lt {
    background-color: #d3e5f7 !important;
    color: #206bc4;
}

.bg-success-lt {
    background-color: #d2f4dd !important;
    color: #2fb344;
}

.bg-warning-lt {
    background-color: #fef0cd !important;
    color: #f59f00;
}

.bg-info-lt {
    background-color: #d3eef8 !important;
    color: #4299e1;
}

.bg-purple-lt {
    background-color: #ede3fe !important;
    color: #ae3ec9;
}
</style>
