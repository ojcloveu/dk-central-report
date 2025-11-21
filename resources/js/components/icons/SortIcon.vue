<script setup>
defineProps({
    currentSortBy: {
        type: String,
        required: true,
    },
    columnName: {
        type: String,
        required: true,
    },
    sortDirection: {
        type: String,
        required: true,
        validator: value => ['asc', 'desc'].includes(value),
    },
});
</script>

<template>
    <i
        v-if="currentSortBy === columnName"
        class="las la-sort-amount-down fs-4"
        :class="{ 'rotate-180': sortDirection === 'desc' }"
    ></i>
</template>

<style scoped>
.rotate-180 {
    transform: rotate(180deg);
    display: inline-block;
}
</style>
