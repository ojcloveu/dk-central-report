<script setup>
import { ref, computed, watch, nextTick } from 'vue';

/* -------------------------
   Dummy server data (original)
   ------------------------- */
const originalData = [
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

/* -------------------------
   Reactive state
   ------------------------- */
// main table (what API1 returns)
const mainData = ref([]);

// selection (persist across pages)
const selectedIds = ref([]); // array of selected item ids

// pagination & sorting
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
   Range tables (API2 results)
   - three separate tables for 7d, 1m, 3m
   ------------------------- */
const rangesTables = ref({
    '7d': [],
    '1m': [],
    '3m': [],
});

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
   - accepts params: filters, sort, page, perPage
   - returns Promise that resolves with filtered+sorted slice
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

            // filter (simple contains / equality logic)
            if (date) {
                // date is string; server usually filters by date range; we just equal-match for now
                data = data.filter(r => r.trandate === date);
            }
            if (master) {
                data = data.filter(r => r.master === master);
            }
            if (account) {
                data = data.filter(r => r.account === account);
            }
            if (channel) {
                data = data.filter(r => r.channel === channel);
            }

            // sort
            if (sBy) {
                data.sort((a, b) => {
                    let aVal = a[sBy];
                    let bVal = b[sBy];

                    // normalize numbers if field likely numeric
                    if (typeof aVal === 'string' && aVal.match(/^\$?[\d,]+$/)) {
                        aVal = Number(aVal.replace(/[^0-9.-]+/g, ''));
                        bVal = Number(bVal.replace(/[^0-9.-]+/g, ''));
                    }
                    if (sOrder === 'asc') return aVal > bVal ? 1 : aVal < bVal ? -1 : 0;
                    return aVal < bVal ? 1 : aVal > bVal ? -1 : 0;
                });
            }

            // pagination slice
            const total = data.length;
            const start = (page - 1) * perPage;
            const slice = data.slice(start, start + perPage);

            resolve({ data: slice, total });
        }, 200); // simulate small latency
    });
}

/* -------------------------
   Simulated API 2 (range summaries)
   - accepts list of accounts and range key
   - returns Promise with aggregated rows (one per account)
   - IMPORTANT: this is only called when selection changes
   ------------------------- */
function simulateApi2(accounts = [], rangeKey = '7d') {
    return new Promise(resolve => {
        setTimeout(() => {
            // produce dummy aggregation per account
            // apply a simple multiplier depending on range to vary numbers
            const multiplier = rangeKey === '7d' ? 1 : rangeKey === '1m' ? 4 : 12;
            const rows = accounts.map(acc => {
                // base find in originalData (first match)
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

            resolve(rows);
        }, 250); // slightly longer response
    });
}

/* -------------------------
   Fetch main table (calls simulateApi1)
   - called on filter / sort / page / perPage change
   ------------------------- */
const totalMainRows = ref(0);
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
    mainData.value = res.data.map(d => ({ ...d })); // copy
    totalMainRows.value = res.total;

    // ensure the checkboxes are in sync (persist selected)
    // we don't touch selectedIds here because selection persists overall
}

/* -------------------------
   Watchers to trigger fetchMainTable when user changes filters/sort/pagination
   (this mimics server-driven main table usage)
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
        // whenever these change, call API1
        fetchMainTable();
    },
    { immediate: true }
);

/* -------------------------
   Selection handling
   - toggling checkboxes updates selectedIds
   - watch selectedIds to call API2 for ranges
   ------------------------- */
function toggleSelect(id) {
    const idx = selectedIds.value.indexOf(id);
    if (idx === -1) {
        selectedIds.value.push(id);
    } else {
        selectedIds.value.splice(idx, 1);
    }
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
   Build / refresh 3 range tables whenever selection changes
   - only depends on selectedAccounts, not filters/sort
   ------------------------- */
let lastRangeRequestKey = 0;
async function refreshRangeTables() {
    const accounts = selectedAccounts.value.slice();

    // quick short-circuit if no selection
    if (accounts.length === 0) {
        rangesTables.value['7d'] = [];
        rangesTables.value['1m'] = [];
        rangesTables.value['3m'] = [];
        return;
    }

    const requestKey = ++lastRangeRequestKey;

    // fetch them in parallel
    const [r7, r1m, r3m] = await Promise.all([
        simulateApi2(accounts, '7d'),
        simulateApi2(accounts, '1m'),
        simulateApi2(accounts, '3m'),
    ]);

    // only set results if this is the latest request (avoid race)
    if (requestKey === lastRangeRequestKey) {
        rangesTables.value['7d'] = r7;
        rangesTables.value['1m'] = r1m;
        rangesTables.value['3m'] = r3m;
    }
}

watch(
    selectedIds,
    () => {
        // whenever selection changes, regenerate the 3 tables
        refreshRangeTables();
    },
    { deep: true }
);

/* -------------------------
   Sorting helper invoked by UI
   - updates sortBy & sortOrder (which triggers fetchMainTable watcher)
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
   Pagination helpers
   ------------------------- */
function setItemsPerPage(count) {
    itemsPerPage.value = count;
    currentPage.value = 1;
}

function goToPage(page) {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
}

const totalPages = computed(() => {
    return Math.max(1, Math.ceil(totalMainRows.value / itemsPerPage.value));
});

/* -------------------------
   Computed: paginatedData is provided by the server (mainData)
   ------------------------- */
const paginatedData = computed(() => mainData.value);

/* -------------------------
   Utility classes (copied from your original)
   ------------------------- */
function getLpClass(lp) {
    const value = parseFloat(String(lp).replace('%', '').replace('$', '').replace(',', '')) || 0;
    if (value < 0) return 'text-danger';
    if (value > 0) return 'text-success';
    return '';
}

function getMasterBadgeClass(master) {
    switch (master) {
        case 'VIRTUAL':
            return 'bg-primary-lt';
        case 'ESPORTS':
            return 'bg-success-lt';
        case 'LOTTERY':
            return 'bg-warning-lt';
        case 'POKER':
            return 'bg-info-lt';
        case 'SLOTS':
            return 'bg-purple-lt';
        default:
            return 'bg-secondary-lt';
    }
}

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

/* initial fetch (already triggered by the watch with immediate: true) */
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
                        <a href="#" class="btn btn-icon" aria-label="Button">...</a>
                        <div class="dropdown">
                            <a href="#" class="btn dropdown-toggle" data-bs-toggle="dropdown"
                                >Download</a
                            >
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="#">Export CSV</a>
                                <a class="dropdown-item" href="#">Export Excel</a>
                                <a class="dropdown-item" href="#">Export PDF</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters row (date, master, account, channel) -->
        <div class="p-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small">Date</label>
                    <input type="date" class="form-control form-control-sm" v-model="dateFilter" />
                </div>

                <div class="col-md-3">
                    <label class="form-label small">Master</label>
                    <input
                        class="form-control form-control-sm mb-1"
                        placeholder="Search master..."
                        v-model="masterSearch"
                    />
                    <select class="form-select form-select-sm" v-model="masterFilter">
                        <option value="">All masters</option>
                        <option v-for="m in filteredMasterOptions" :key="m" :value="m">
                            {{ m }}
                        </option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label small">Account</label>
                    <input
                        class="form-control form-control-sm mb-1"
                        placeholder="Search account..."
                        v-model="accountSearch"
                    />
                    <select class="form-select form-select-sm" v-model="accountFilter">
                        <option value="">All accounts</option>
                        <option v-for="a in filteredAccountOptions" :key="a" :value="a">
                            {{ a }}
                        </option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label small">Channel</label>
                    <input
                        class="form-control form-control-sm mb-1"
                        placeholder="Search channel..."
                        v-model="channelSearch"
                    />
                    <select class="form-select form-select-sm" v-model="channelFilter">
                        <option value="">All channels</option>
                        <option v-for="c in filteredChannelOptions" :key="c" :value="c">
                            {{ c }}
                        </option>
                    </select>
                </div>

                <div class="col-md-2 text-end">
                    <button
                        class="btn btn-sm btn-secondary"
                        @click="
                            () => {
                                dateFilter = '';
                                masterFilter = '';
                                accountFilter = '';
                                channelFilter = '';
                                masterSearch = '';
                                accountSearch = '';
                                channelSearch = '';
                            }
                        "
                    >
                        Clear
                    </button>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div id="betting-table">
            <div class="table-responsive">
                <table class="table table-vcenter table-hover">
                    <thead>
                        <tr class="text-muted">
                            <th class="w-1"></th>

                            <th>
                                <button
                                    class="table-sort d-flex justify-content-between w-100 border-0 bg-transparent text-start"
                                    @click="sortTable('account')"
                                >
                                    ACCOUNT
                                    <span v-if="sortBy === 'account'">{{
                                        sortOrder === 'asc' ? '↑' : '↓'
                                    }}</span>
                                </button>
                            </th>

                            <th>
                                <button
                                    class="table-sort d-flex justify-content-between w-100 border-0 bg-transparent text-start"
                                    @click="sortTable('channel')"
                                >
                                    CHANNEL
                                    <span v-if="sortBy === 'channel'">{{
                                        sortOrder === 'asc' ? '↑' : '↓'
                                    }}</span>
                                </button>
                            </th>

                            <th>
                                <button
                                    class="table-sort d-flex justify-content-between w-100 border-0 bg-transparent text-start"
                                    @click="sortTable('trandate')"
                                >
                                    TRANDATE
                                    <span v-if="sortBy === 'trandate'">{{
                                        sortOrder === 'asc' ? '↑' : '↓'
                                    }}</span>
                                </button>
                            </th>

                            <th>
                                <button
                                    class="table-sort d-flex justify-content-between w-100 border-0 bg-transparent text-start"
                                    @click="sortTable('master')"
                                >
                                    MASTER
                                    <span v-if="sortBy === 'master'">{{
                                        sortOrder === 'asc' ? '↑' : '↓'
                                    }}</span>
                                </button>
                            </th>

                            <th>
                                <button
                                    class="table-sort d-flex justify-content-between w-100 border-0 bg-transparent text-start"
                                    @click="sortTable('min')"
                                >
                                    MIN
                                    <span v-if="sortBy === 'min'">{{
                                        sortOrder === 'asc' ? '↑' : '↓'
                                    }}</span>
                                </button>
                            </th>

                            <th>
                                <button
                                    class="table-sort d-flex justify-content-between w-100 border-0 bg-transparent text-start"
                                    @click="sortTable('max')"
                                >
                                    MAX
                                    <span v-if="sortBy === 'max'">{{
                                        sortOrder === 'asc' ? '↑' : '↓'
                                    }}</span>
                                </button>
                            </th>

                            <th>
                                <button
                                    class="table-sort d-flex justify-content-between w-100 border-0 bg-transparent text-start"
                                    @click="sortTable('count')"
                                >
                                    COUNT
                                    <span v-if="sortBy === 'count'">{{
                                        sortOrder === 'asc' ? '↑' : '↓'
                                    }}</span>
                                </button>
                            </th>

                            <th>TURNOVER</th>
                            <th>WINLOSE</th>
                            <th>LP</th>
                        </tr>
                    </thead>

                    <tbody class="table-tbody">
                        <tr v-for="item in paginatedData" :key="item.id">
                            <td>
                                <input
                                    :checked="isSelected(item.id)"
                                    @change="() => toggleSelect(item.id)"
                                    class="form-check-input m-0 align-middle"
                                    type="checkbox"
                                    :aria-label="`Select ${item.account}`"
                                />
                            </td>
                            <td class="text-dark fw-bold">{{ item.account }}</td>
                            <td>{{ item.channel }}</td>
                            <td>{{ item.trandate }}</td>
                            <td>
                                <span class="badge" :class="getMasterBadgeClass(item.master)">{{
                                    item.master
                                }}</span>
                            </td>
                            <td>{{ item.min }}</td>
                            <td>{{ item.max.toLocaleString() }}</td>
                            <td>{{ item.count }}</td>
                            <td class="fw-bold">{{ item.turnover }}</td>
                            <td class="fw-bold" :class="getLpClass(item.winlose)">
                                {{ item.winlose }}
                            </td>
                            <td class="fw-bold" :class="getLpClass(item.lp)">{{ item.lp }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Footer with Pagination -->
            <div class="card-footer d-flex align-items-center">
                <div class="dropdown">
                    <a class="btn dropdown-toggle" data-bs-toggle="dropdown">
                        <span class="me-1">{{ itemsPerPage }}</span>
                        <span>records</span>
                    </a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" @click.prevent="setItemsPerPage(10)">10 records</a>
                        <a class="dropdown-item" @click.prevent="setItemsPerPage(20)">20 records</a>
                        <a class="dropdown-item" @click.prevent="setItemsPerPage(50)">50 records</a>
                        <a class="dropdown-item" @click.prevent="setItemsPerPage(100)"
                            >100 records</a
                        >
                    </div>
                </div>

                <ul class="pagination m-0 ms-auto">
                    <li
                        v-for="page in totalPages"
                        :key="page"
                        class="page-item"
                        :class="{ active: currentPage === page }"
                    >
                        <a class="page-link cursor-pointer" @click.prevent="goToPage(page)">{{
                            page
                        }}</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div>
        <!-- Generated range tables (API2 results) -->
        <div v-if="selectedAccounts.length" class="py-3">
            <h3 class="">Selected Account Period Range</h3>

            <div class="d-flex flex-column gap-3">
                <div v-for="(rangeKey, idx) in ['7d', '1m', '3m']" :key="rangeKey">
                    <div class="card">
                        <div class="card-header">
                            <strong>{{
                                rangeKey === '7d'
                                    ? 'Last 7 days'
                                    : rangeKey === '1m'
                                      ? 'Last 1 month'
                                      : 'Last 3 months'
                            }}</strong>
                        </div>
                        <div class="card-body py-0">
                            <div
                                v-if="rangesTables[rangeKey] && rangesTables[rangeKey].length"
                                class="table-responsive"
                            >
                                <table class="table table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>Account</th>
                                            <th>Count</th>
                                            <th>Turnover</th>
                                            <th>Win/Lose</th>
                                            <th>LP</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="row in rangesTables[rangeKey]"
                                            :key="row.account"
                                        >
                                            <td>{{ row.account }}</td>
                                            <td>{{ row.count }}</td>
                                            <td>{{ row.turnover }}</td>
                                            <td :class="getLpClass(row.winlose)">
                                                {{ row.winlose }}
                                            </td>
                                            <td :class="getLpClass(row.lp)">{{ row.lp }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div v-else class="p-3 text-muted">
                                No data (select accounts to generate tables)
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
