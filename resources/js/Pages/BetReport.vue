<script setup>
import DataTable from '../components/DataTable.vue';

// Props automatically passed from Laravel
defineProps({
    message: String,
    user: Object,
});
</script>

<template>
    <div>
        <DataTable />
    </div>
</template>
