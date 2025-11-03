<!-- BetPageHeader.vue -->
<script setup>
const props = defineProps({
    onRefresh: Function,
});
</script>

<template>
    <div class="page-header d-print-none mb-3">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Bet Report</h2>
                <div class="text-muted mt-1">View and manage all betting transactions</div>
            </div>

            <div class="col-auto ms-auto">
                <div class="btn-list">
                    <!-- Refresh table button -->
                    <button class="btn btn-icon btn-primary" aria-label="Refresh" @click="onRefresh">
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
                            <path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4"></path>
                            <path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
