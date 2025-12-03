<script setup>
import { onMounted, watch } from 'vue';
import { masterStore } from '@/stores/masterStore';
import MasterDataTable from '@/components/MasterDataTable.vue';
import MasterChart from '@/components/charts/MasterChart.vue';

const master = masterStore();

// Fetch data when this page loads
onMounted(async () => {
    await master.fetchMasterReport();
    await master.fetchMasterTurnOver();
});
</script>

<template>
    <div class="d-flex flex-column gap-3">
        <MasterDataTable />
        <MasterChart />
    </div>
</template>
