<script setup>
import DataTable from '../components/DataTable.vue';
import TestDataTable from '../components/TestDataTable.vue';
import { useBetStore } from '../stores/betStore';

const betStore = useBetStore();
</script>

<template>
    <div>
        <!-- <DataTable /> -->
        <TestDataTable />
    </div>
</template>
