<script setup>
/*
 * Define the props this component accepts
 */
const props = defineProps({
    filters: Object,
    onSort: Function,
    sortableColumns: Array,
});
</script>

<template>
    <thead>
        <tr>
            <th class="w-1"><input class="form-check-input m-0" type="checkbox" /></th>
            <th
                v-for="col in sortableColumns"
                :key="col.key"
                @click="onSort(col.key)"
                class="cursor-pointer"
            >
                <div class="d-flex align-items-center">
                    {{ col.label }}
                    <i
                        v-if="filters.sort_by === col.key"
                        class="las la-sort-amount-down fs-4"
                        :class="{ 'rotate-180': filters.sort_dir === 'desc' }"
                    ></i>
                </div>
            </th>
        </tr>
    </thead>
</template>

<style scoped>
.cursor-pointer {
    cursor: pointer;
}
.rotate-180 {
    transform: rotate(180deg);
}
</style>
