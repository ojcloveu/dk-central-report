<script setup>
import { getLpClass } from '../utils/getStatusClass';
import Pagination from './Pagination.vue';

const props = defineProps({
    rangeKey: String,
    rangeData: Object,
});

const emit = defineEmits(['changePage', 'changePerPage']);

const rangeLabel = {
    '7d': 'Last 7 days',
    '1m': 'Last 1 month',
    '3m': 'Last 3 months',
}[props.rangeKey];
</script>

<template>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>{{ rangeLabel }}</strong>
            <div>
                <select
                    class="form-select form-select-sm"
                    :value="rangeData.meta.per_page"
                    @change="emit('changePerPage', $event.target.value)"
                >
                    <option value="5">5</option>
                    <option value="10">10</option>
                    <option value="20">20</option>
                </select>
            </div>
        </div>

        <div class="card-body py-0">
            <div v-if="rangeData.data.length" class="table-responsive">
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
                        <tr v-for="row in rangeData.data" :key="row.account">
                            <td>{{ row.account }}</td>
                            <td>{{ row.count }}</td>
                            <td>{{ row.turnover }}</td>
                            <td :class="getLpClass(row.winlose)">{{ row.winlose }}</td>
                            <td :class="getLpClass(row.lp)">{{ row.lp }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-else class="p-3 text-muted">No data</div>
        </div>

        <Pagination
            :itemsPerPage="rangeData.meta.per_page"
            :currentPage="rangeData.meta.current_page"
            :totalPages="rangeData.meta.last_page"
            @update:currentPage="emit('changePage', $event)"
            @update:itemsPerPage="emit('changePerPage', $event)"
        />
    </div>
</template>
