<script setup>
import { ref, computed } from 'vue';

// Dummy betting data based on the image
const bettingData = ref([
    {
        id: 1,
        account: 'THB7686',
        channel: 'TH',
        trandate: '28 Oct 2025',
        master: 'VIRTUAL',
        min: 10,
        max: 540,
        count: 162,
        turnover: '$55,424',
        winlose: '$-2,594',
        lp: '-5%',
        selected: false,
    },
    {
        id: 2,
        account: 'TH1524',
        channel: 'TH',
        trandate: '28 Oct 2025',
        master: 'VIRTUAL',
        min: 24,
        max: 1152,
        count: 303,
        turnover: '$21,827',
        winlose: '$-4,058',
        lp: '-19%',
        selected: false,
    },
    {
        id: 3,
        account: 'THA1180',
        channel: 'TH',
        trandate: '28 Oct 2025',
        master: 'ESPORTS',
        min: 21,
        max: 1260,
        count: 482,
        turnover: '$68,699',
        winlose: '$-2,095',
        lp: '-3%',
        selected: false,
    },
    {
        id: 4,
        account: 'TH9486',
        channel: 'TH',
        trandate: '28 Oct 2025',
        master: 'ESPORTS',
        min: 22,
        max: 682,
        count: 290,
        turnover: '$38,966',
        winlose: '$-2,416',
        lp: '-6%',
        selected: false,
    },
    {
        id: 5,
        account: 'TH3717',
        channel: 'TH',
        trandate: '28 Oct 2025',
        master: 'ESPORTS',
        min: 13,
        max: 273,
        count: 75,
        turnover: '$16,483',
        winlose: '$-1,078',
        lp: '-7%',
        selected: false,
    },
    {
        id: 6,
        account: 'THB1772',
        channel: 'TH',
        trandate: '28 Oct 2025',
        master: 'LOTTERY',
        min: 19,
        max: 817,
        count: 282,
        turnover: '$17,218',
        winlose: '$-8,006',
        lp: '-47%',
        selected: false,
    },
    {
        id: 7,
        account: 'THA3811',
        channel: 'TH',
        trandate: '28 Oct 2025',
        master: 'LOTTERY',
        min: 13,
        max: 1014,
        count: 139,
        turnover: '$52,531',
        winlose: '$-16,369',
        lp: '-31%',
        selected: false,
    },
    {
        id: 8,
        account: 'TH1172',
        channel: 'TH',
        trandate: '28 Oct 2025',
        master: 'LOTTERY',
        min: 7,
        max: 196,
        count: 478,
        turnover: '$38,297',
        winlose: '$-10,555',
        lp: '-28%',
        selected: false,
    },
    {
        id: 9,
        account: 'TH6301',
        channel: 'TH',
        trandate: '28 Oct 2025',
        master: 'POKER',
        min: 125,
        max: 18125,
        count: 478,
        turnover: '$32,144',
        winlose: '$-2,909',
        lp: '-9%',
        selected: false,
    },
    {
        id: 10,
        account: 'THA3466',
        channel: 'TH',
        trandate: '28 Oct 2025',
        master: 'SLOTS',
        min: 10,
        max: 470,
        count: 285,
        turnover: '$24,006',
        winlose: '$-1,836',
        lp: '-8%',
        selected: false,
    },
    {
        id: 11,
        account: 'THA3466',
        channel: 'TH',
        trandate: '28 Oct 2025',
        master: 'SLOTS',
        min: 10,
        max: 470,
        count: 285,
        turnover: '$24,006',
        winlose: '$-1,836',
        lp: '-8%',
        selected: false,
    },
]);

const currentPage = ref(1);
const itemsPerPage = ref(20);
const sortBy = ref('');
const sortOrder = ref('asc');

// Computed filtered and sorted data
const filteredData = computed(() => {
    let filtered = bettingData.value;

    // Sorting
    if (sortBy.value) {
        filtered = [...filtered].sort((a, b) => {
            let aVal = a[sortBy.value];
            let bVal = b[sortBy.value];

            // Handle numeric fields
            if (['min', 'max', 'count'].includes(sortBy.value)) {
                aVal = Number(aVal);
                bVal = Number(bVal);
            }

            if (sortOrder.value === 'asc') {
                return aVal > bVal ? 1 : -1;
            } else {
                return aVal < bVal ? 1 : -1;
            }
        });
    }

    return filtered;
});

// Pagination
const paginatedData = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    const end = start + itemsPerPage.value;
    return filteredData.value.slice(start, end);
});

const totalPages = computed(() => {
    return Math.ceil(filteredData.value.length / itemsPerPage.value);
});

// Methods
const sortTable = field => {
    if (sortBy.value === field) {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortBy.value = field;
        sortOrder.value = 'asc';
    }
};

const setItemsPerPage = count => {
    itemsPerPage.value = count;
    currentPage.value = 1;
};

const goToPage = page => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
};

const getLpClass = lp => {
    const value = parseFloat(lp);
    if (value < 0) {
        return 'text-danger';
    } else if (value > 0) {
        return 'text-success';
    }
    return '';
};

const getMasterBadgeClass = master => {
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
};
</script>

<template>
    <div class="card-table">
        <!-- Header -->
        <div class="card-header">
            <div class="row w-100">
                <div class="col">
                    <h3 class="card-title mb-0">Betting Report</h3>
                    <p class="text-secondary m-0">View all betting transactions and statistics.</p>
                </div>
                <div class="col-md-auto col-sm-12">
                    <div class="ms-auto d-flex flex-wrap btn-list">
                        <!-- More Options Button -->
                        <a href="#" class="btn btn-icon" aria-label="Button">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                class="icon"
                            >
                                <path d="M5 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path>
                                <path d="M12 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path>
                                <path d="M19 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path>
                            </svg>
                        </a>
                        <!-- Download Dropdown -->
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
                                    v-model="item.selected"
                                    class="form-check-input m-0 align-middle"
                                    type="checkbox"
                                    :aria-label="`Select ${item.account}`"
                                />
                            </td>
                            <td class="text-dark fw-bold">{{ item.account }}</td>
                            <td>{{ item.channel }}</td>
                            <td>{{ item.trandate }}</td>
                            <td>
                                <span class="badge" :class="getMasterBadgeClass(item.master)">
                                    {{ item.master }}
                                </span>
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
                        <a class="page-link cursor-pointer" @click.prevent="goToPage(page)">
                            {{ page }}
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<style scoped>
.card-table {
    border-radius: 0.5rem;
    box-shadow:
        0 1px 3px 0 rgba(0, 0, 0, 0.1),
        0 1px 2px 0 rgba(0, 0, 0, 0.06);
}

.card-header {
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    padding: 1rem 1.5rem;
}

.btn-list {
    gap: 0.5rem;
}

.table-sort {
    cursor: pointer;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    padding: 0;
}

.table-sort:hover {
    color: var(--tbs-primary);
}

.cursor-pointer {
    cursor: pointer;
}

.table {
    margin-bottom: 0;
}

.table thead {
    background: #f8f9fa;
    border-bottom: 2px solid #e9ecef;
}

.table thead tr th {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    color: #6c757d;
    border-bottom: none;
    padding: 0.75rem 1rem;
}

.table tbody tr {
    background: #fff;
    border-bottom: 1px solid #f0f0f0;
    transition: background-color 0.15s ease;
}

.table tbody tr:hover {
    background: #f8f9fa;
}

.table tbody td {
    padding: 0.75rem 1rem;
    vertical-align: middle;
}

.card-footer {
    background: #f8f9fa;
    border-top: 1px solid #e9ecef;
    padding: 0.75rem 1.5rem;
}

.btn-link {
    font-size: 0.875rem;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    color: #4299e1;
}

.btn-link:hover {
    color: #2b6cb0;
}

.btn-link svg {
    width: 16px;
    height: 16px;
}

.text-danger {
    color: #d63939 !important;
}

.text-success {
    color: #2fb344 !important;
}

.badge {
    font-weight: 500;
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
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

.pagination .page-item.active .page-link {
    background-color: #206bc4;
    border-color: #206bc4;
}

.pagination .page-link:hover {
    background-color: #f8f9fa;
    border-color: #dee2e6;
}
</style>
