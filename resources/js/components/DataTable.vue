<script setup>
import { ref, computed, watch } from 'vue';
import Pagination from './Pagination.vue';
import RangeTable from './RangeTable.vue';
import FilterBar from './FilterBar.vue';
import TableHeader from './TableHeader.vue';
import TableRow from './TableRow.vue';

/* -------------------------
   Dummy server data (original)
   ------------------------- */
const originalData = [
    /* ... keep your original array items unchanged ... */
    {
        id: 1,
        account: 'THB7686',
        channel: 'TH',
        trandate: '2025-10-28',
        master: 'VIRTUAL',
        min: 10,
        max: 540,
        count: 162,
        turnover: '$55,424',
        winlose: '$-2,594',
        lp: '-5%',
    },
    {
        id: 2,
        account: 'TH1524',
        channel: 'TH',
        trandate: '2025-10-28',
        master: 'VIRTUAL',
        min: 24,
        max: 1152,
        count: 303,
        turnover: '$21,827',
        winlose: '$-4,058',
        lp: '-19%',
    },
    {
        id: 3,
        account: 'THA1180',
        channel: 'TH',
        trandate: '2025-10-28',
        master: 'ESPORTS',
        min: 21,
        max: 1260,
        count: 482,
        turnover: '$68,699',
        winlose: '$-2,095',
        lp: '-3%',
    },
    {
        id: 4,
        account: 'TH9486',
        channel: 'TH',
        trandate: '2025-10-28',
        master: 'ESPORTS',
        min: 22,
        max: 682,
        count: 290,
        turnover: '$38,966',
        winlose: '$-2,416',
        lp: '-6%',
    },
    {
        id: 5,
        account: 'TH3717',
        channel: 'TH',
        trandate: '2025-10-28',
        master: 'ESPORTS',
        min: 13,
        max: 273,
        count: 75,
        turnover: '$16,483',
        winlose: '$-1,078',
        lp: '-7%',
    },
    {
        id: 6,
        account: 'THB1772',
        channel: 'TH',
        trandate: '2025-10-28',
        master: 'LOTTERY',
        min: 19,
        max: 817,
        count: 282,
        turnover: '$17,218',
        winlose: '$-8,006',
        lp: '-47%',
    },
    {
        id: 7,
        account: 'THA3811',
        channel: 'TH',
        trandate: '2025-10-28',
        master: 'LOTTERY',
        min: 13,
        max: 1014,
        count: 139,
        turnover: '$52,531',
        winlose: '$-16,369',
        lp: '-31%',
    },
    {
        id: 8,
        account: 'TH1172',
        channel: 'TH',
        trandate: '2025-10-28',
        master: 'LOTTERY',
        min: 7,
        max: 196,
        count: 478,
        turnover: '$38,297',
        winlose: '$-10,555',
        lp: '-28%',
    },
    {
        id: 9,
        account: 'TH6301',
        channel: 'TH',
        trandate: '2025-10-28',
        master: 'POKER',
        min: 125,
        max: 18125,
        count: 478,
        turnover: '$32,144',
        winlose: '$-2,909',
        lp: '-9%',
    },
    {
        id: 10,
        account: 'THA3466',
        channel: 'TH',
        trandate: '2025-10-28',
        master: 'SLOTS',
        min: 10,
        max: 470,
        count: 285,
        turnover: '$24,006',
        winlose: '$-1,836',
        lp: '-8%',
    },
    {
        id: 11,
        account: 'THA3466',
        channel: 'TH',
        trandate: '2025-11-28',
        master: 'SLOTS',
        min: 10,
        max: 470,
        count: 285,
        turnover: '$24,006',
        winlose: '$-1,836',
        lp: '-8%',
    },
];

const columns = [
    { field: 'account', label: 'ACCOUNT' },
    { field: 'channel', label: 'CHANNEL' },
    { field: 'trandate', label: 'TRANDATE' },
    { field: 'master', label: 'MASTER' },
    { field: 'min', label: 'MIN' },
    { field: 'max', label: 'MAX' },
    { field: 'count', label: 'COUNT' },
    { field: 'turnover', label: 'TURNOVER' },
    { field: 'winlose', label: 'WINLOSE' },
    { field: 'lp', label: 'LP' },
];

/* -------------------------
   Reactive state
   ------------------------- */
// main table (what API1 returns)
const mainData = ref([]);

// meta for main (Laravel style)
const mainMeta = ref({
    total: 0,
    per_page: 10,
    current_page: 1,
    last_page: 1,
});

// selection (persist across pages)
const selectedIds = ref([]);

// pagination & sorting (we keep these as reactive controls that map to API params)
const currentPage = ref(1);
const itemsPerPage = ref(20);
const sortBy = ref('');
const sortOrder = ref('asc');

// filters (these go into API1 params)
const dateFilter = ref('');
const masterFilter = ref('');
const accountFilter = ref('');
const channelFilter = ref('');

// search boxes for each select (UI convenience)
const masterSearch = ref('');
const accountSearch = ref('');
const channelSearch = ref('');

/* -------------------------
   Range tables (API2 results) - now server paginated per range
   structure:
   rangesTables.value = {
      '7d': { data: [], meta: { total, per_page, current_page, last_page } },
      '1m': { ... },
      '3m': { ... }
   }
   and rangeState per-range controls (page, perPage)
   ------------------------- */
const rangesTables = ref({
    '7d': { data: [], meta: { total: 0, per_page: 10, current_page: 1, last_page: 1 } },
    '1m': { data: [], meta: { total: 0, per_page: 10, current_page: 1, last_page: 1 } },
    '3m': { data: [], meta: { total: 0, per_page: 10, current_page: 1, last_page: 1 } },
});

// per-range state convenience accessors
function rangePage(rangeKey) {
    return rangesTables.value[rangeKey].meta.current_page || 1;
}
function rangePerPage(rangeKey) {
    return rangesTables.value[rangeKey].meta.per_page || 10;
}

/* -------------------------
   Utility: unique option lists (from original data)
   ------------------------- */
const masterOptions = computed(() => {
    const set = new Set(originalData.map(d => d.master));
    return Array.from(set);
});
const accountOptions = computed(() => {
    const set = new Set(originalData.map(d => d.account));
    return Array.from(set);
});
const channelOptions = computed(() => {
    const set = new Set(originalData.map(d => d.channel));
    return Array.from(set);
});

/* -------------------------
   Simulated API 1 (main table)
   - returns { data, meta } where meta follows Laravel pagination:
     { total, per_page, current_page, last_page }
   - accepts params: filters, sort, page, perPage
   ------------------------- */
function simulateApi1({
    date,
    master,
    account,
    channel,
    sortBy: sBy,
    sortOrder: sOrder,
    page,
    perPage,
}) {
    return new Promise(resolve => {
        setTimeout(() => {
            let data = [...originalData];

            // filters
            if (date) data = data.filter(r => r.trandate === date);
            if (master) data = data.filter(r => r.master === master);
            if (account) data = data.filter(r => r.account === account);
            if (channel) data = data.filter(r => r.channel === channel);

            // sorting
            if (sBy) {
                data.sort((a, b) => {
                    let aVal = a[sBy];
                    let bVal = b[sBy];

                    if (typeof aVal === 'string' && aVal.match(/^\$?[\d,]+/)) {
                        aVal = Number(String(aVal).replace(/[^0-9.-]+/g, ''));
                        bVal = Number(String(bVal).replace(/[^0-9.-]+/g, ''));
                    }
                    if (sOrder === 'asc') return aVal > bVal ? 1 : aVal < bVal ? -1 : 0;
                    return aVal < bVal ? 1 : aVal > bVal ? -1 : 0;
                });
            }

            const total = data.length;
            const last_page = Math.max(1, Math.ceil(total / perPage));
            const safePage = Math.min(Math.max(1, page), last_page);
            const start = (safePage - 1) * perPage;
            const slice = data.slice(start, start + perPage);

            resolve({
                data: slice,
                meta: {
                    total,
                    per_page: perPage,
                    current_page: safePage,
                    last_page,
                },
            });
        }, 250);
    });
}

/* -------------------------
   Simulated API 2 (range summaries) - now returns { data, meta }
   - accepts list of accounts and rangeKey and page/perPage
   ------------------------- */
function simulateApi2(accounts = [], rangeKey = '7d', page = 1, perPage = 10) {
    return new Promise(resolve => {
        setTimeout(() => {
            const multiplier = rangeKey === '7d' ? 1 : rangeKey === '1m' ? 4 : 12;
            const rows = accounts.map(acc => {
                const base = originalData.find(d => d.account === acc) || {
                    account: acc,
                    channel: 'N/A',
                    master: 'N/A',
                    min: 0,
                    max: 0,
                    count: 0,
                    turnover: '$0',
                    winlose: '$0',
                    lp: '0%',
                };

                const count = Math.max(
                    1,
                    Math.round((base.count || 1) * multiplier * (0.8 + Math.random() * 0.6))
                );
                const turnoverNumber = Math.round(
                    (Number(String(base.turnover).replace(/[^0-9.-]+/g, '')) || 1000) *
                        multiplier *
                        (0.6 + Math.random() * 1.4)
                );
                const winloseNumber = Math.round(
                    -Math.abs(turnoverNumber * (0.02 + Math.random() * 0.2))
                );

                return {
                    account: acc,
                    channel: base.channel,
                    master: base.master,
                    count,
                    min: Math.round((base.min || 0) * (0.9 + Math.random() * 1.2)),
                    max: Math.round((base.max || 0) * (0.9 + Math.random() * 1.2)),
                    turnover: `$${turnoverNumber.toLocaleString()}`,
                    winlose: `$${winloseNumber.toLocaleString()}`,
                    lp: `${Math.round((winloseNumber / Math.max(1, turnoverNumber)) * 100)}%`,
                };
            });

            const total = rows.length;
            const last_page = Math.max(1, Math.ceil(total / perPage));
            const safePage = Math.min(Math.max(1, page), last_page);
            const start = (safePage - 1) * perPage;
            const slice = rows.slice(start, start + perPage);

            resolve({
                data: slice,
                meta: { total, per_page: perPage, current_page: safePage, last_page },
            });
        }, 300);
    });
}

/* -------------------------
   Fetch main table (calls simulateApi1)
   - reads and stores meta
   ------------------------- */
async function fetchMainTable() {
    const params = {
        date: dateFilter.value,
        master: masterFilter.value,
        account: accountFilter.value,
        channel: channelFilter.value,
        sortBy: sortBy.value,
        sortOrder: sortOrder.value,
        page: currentPage.value,
        perPage: itemsPerPage.value,
    };

    const res = await simulateApi1(params);
    mainData.value = res.data.map(d => ({ ...d }));
    mainMeta.value = { ...res.meta };

    // sync paging control (if server changed current_page or per_page)
    currentPage.value = mainMeta.value.current_page;
    itemsPerPage.value = mainMeta.value.per_page;
}

/* -------------------------
   Range fetch helpers
   - fetchRange(rangeKey, page?, perPage?)
   - fetchAllRanges()
   ------------------------- */
let lastRangeFetchKey = 0;
async function fetchRange(rangeKey, page = null, perPage = null) {
    const accounts = selectedAccounts.value.slice();
    if (accounts.length === 0) {
        rangesTables.value[rangeKey] = {
            data: [],
            meta: {
                total: 0,
                per_page: rangesTables.value[rangeKey].meta.per_page || 10,
                current_page: 1,
                last_page: 1,
            },
        };
        return;
    }

    // use existing meta paging if not provided
    const usePage = page ?? rangesTables.value[rangeKey].meta.current_page ?? 1;
    const usePerPage = perPage ?? rangesTables.value[rangeKey].meta.per_page ?? 10;

    const requestKey = ++lastRangeFetchKey;
    const res = await simulateApi2(accounts, rangeKey, usePage, usePerPage);

    // avoid race conditions
    if (requestKey === lastRangeFetchKey) {
        rangesTables.value[rangeKey] = {
            data: res.data.map(r => ({ ...r })),
            meta: { ...res.meta },
        };
    }
}

async function fetchAllRanges() {
    await Promise.all(
        ['7d', '1m', '3m'].map(k => fetchRange(k, 1, rangesTables.value[k].meta.per_page))
    );
}

/* -------------------------
   Watchers to trigger fetchMainTable when user changes filters/sort/pagination
   ------------------------- */
watch(
    [
        dateFilter,
        masterFilter,
        accountFilter,
        channelFilter,
        sortBy,
        sortOrder,
        currentPage,
        itemsPerPage,
    ],
    () => {
        fetchMainTable();
    },
    { immediate: true }
);

/* -------------------------
   Selection handling
   ------------------------- */
function toggleSelect(id) {
    const idx = selectedIds.value.indexOf(id);
    if (idx === -1) selectedIds.value.push(id);
    else selectedIds.value.splice(idx, 1);
}
function isSelected(id) {
    return selectedIds.value.includes(id);
}

// compute selected accounts list (unique)
const selectedAccounts = computed(() => {
    const accounts = [];
    for (const id of selectedIds.value) {
        const item = originalData.find(d => d.id === id) || mainData.value.find(d => d.id === id);
        if (item && !accounts.includes(item.account)) accounts.push(item.account);
    }
    return accounts;
});

/* -------------------------
   When selection changes -> refresh range tables (server side)
   ------------------------- */
watch(
    selectedIds,
    () => {
        // reset range pages to 1 for better UX
        for (const k of ['7d', '1m', '3m']) {
            rangesTables.value[k].meta.current_page = 1;
        }
        fetchAllRanges();
    },
    { deep: true }
);

/* -------------------------
   Sorting helper invoked by UI
   ------------------------- */
function sortTable(field) {
    if (sortBy.value === field) {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortBy.value = field;
        sortOrder.value = 'asc';
    }
}

/* -------------------------
   Pagination helpers for main
   ------------------------- */
const totalPages = computed(() => {
    return (
        mainMeta.value.last_page ||
        Math.max(
            1,
            Math.ceil((mainMeta.value.total || 0) / (mainMeta.value.per_page || itemsPerPage.value))
        )
    );
});

/* -------------------------
   Pagination helpers for ranges
   ------------------------- */
function setRangeItemsPerPage(rangeKey, count) {
    rangesTables.value[rangeKey].meta.per_page = count;
    rangesTables.value[rangeKey].meta.current_page = 1;
    fetchRange(rangeKey, 1, count);
}
function goToRangePage(rangeKey, page) {
    const last = rangesTables.value[rangeKey].meta.last_page || 1;
    if (page >= 1 && page <= last)
        fetchRange(rangeKey, page, rangesTables.value[rangeKey].meta.per_page);
}

/* -------------------------
   Computed: paginatedData is provided by the server (mainData)
   ------------------------- */
const paginatedData = computed(() => mainData.value);

/* -------------------------
   Small helpers for filter selects (searchable)
   ------------------------- */
const filteredMasterOptions = computed(() =>
    masterOptions.value.filter(m => m.toLowerCase().includes(masterSearch.value.toLowerCase()))
);
const filteredAccountOptions = computed(() =>
    accountOptions.value.filter(a => a.toLowerCase().includes(accountSearch.value.toLowerCase()))
);
const filteredChannelOptions = computed(() =>
    channelOptions.value.filter(c => c.toLowerCase().includes(channelSearch.value.toLowerCase()))
);

/* -------------------------
   Clear filters
   ------------------------- */
function clearFilters() {
    dateFilter.value = '';
    masterFilter.value = '';
    accountFilter.value = '';
    channelFilter.value = '';
    masterSearch.value = '';
    accountSearch.value = '';
    channelSearch.value = '';
}

/* -------------------------
   Refresh helper (used by Refresh button)
   ------------------------- */
function refreshAll() {
    // keep selection; just re-fetch main and ranges
    fetchMainTable();
    fetchAllRanges();
}
</script>

<template>
    <div class="card">
        <!-- Header -->
        <div class="card-header">
            <div class="row w-100">
                <div class="col">
                    <h3 class="card-title mb-0">Betting Report</h3>
                    <p class="text-secondary m-0">View all betting transactions and statistics.</p>
                </div>

                <div class="col-md-auto col-sm-12">
                    <div class="ms-auto d-flex flex-wrap btn-list">
                        <!-- small contextual button (kept) -->
                        <a href="#" class="btn btn-icon" aria-label="Button">...</a>

                        <!-- REPLACED download + settings with single Refresh button -->
                        <button class="btn btn-primary" @click="refreshAll" type="button">
                            Refresh
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters row (date, master, account, channel) -->
        <FilterBar
            v-model:dateFilter="dateFilter"
            v-model:masterFilter="masterFilter"
            v-model:accountFilter="accountFilter"
            v-model:channelFilter="channelFilter"
            v-model:masterSearch="masterSearch"
            v-model:accountSearch="accountSearch"
            v-model:channelSearch="channelSearch"
            :masterOptions="filteredMasterOptions"
            :accountOptions="filteredAccountOptions"
            :channelOptions="filteredChannelOptions"
            @clear="clearFilters"
        />
        <!-- Table -->
        <div id="betting-table">
            <div class="table-responsive">
                <table class="table table-vcenter table-hover">
                    <thead>
                        <tr>
                            <th></th>
                            <TableHeader
                                v-for="col in columns"
                                :key="col.field"
                                v-bind="col"
                                :sortBy="sortBy"
                                :sortOrder="sortOrder"
                                @sort="sortTable"
                            />
                        </tr>
                    </thead>

                    <tbody class="table-tbody">
                        <TableRow
                            v-for="item in paginatedData"
                            :key="item.id"
                            :item="item"
                            :selected="isSelected(item.id)"
                            @toggle="toggleSelect"
                        />
                    </tbody>
                </table>
            </div>

            <!-- Footer with Pagination (main uses meta from server) -->
            <Pagination
                v-model:itemsPerPage="itemsPerPage"
                v-model:currentPage="currentPage"
                :total-pages="totalPages"
            />
        </div>
    </div>

    <!-- Range tables -->
    <RangeTable
        :selectedAccounts="selectedAccounts"
        :rangesTables="rangesTables"
        @setRangeItemsPerPage="setRangeItemsPerPage"
        @goToRangePage="goToRangePage"
    />
</template>

<style scoped></style>
