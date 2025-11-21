<script setup>
import { useBetStore } from '@/stores/betStore';
import { computed, ref, onMounted, watch } from 'vue';

/*
 * Define the props this component accepts
 */
const props = defineProps({
    filters: Object,
    onSort: Function,
    sortableColumns: Array,
});

const betStore = useBetStore();
const selectAllCheckbox = ref(null);

/*
 * Check if all current accounts are selected
 */
const allSelected = computed(() => {
    if (betStore.data.length === 0) return false;
    return betStore.data.every(bet => betStore.selectedAccounts.includes(bet.account));
});

/*
 * Check if some accounts are selected
 */
const someSelected = computed(() => {
    if (betStore.selectedAccounts.length === 0) return false;
    return (
        !allSelected.value &&
        betStore.data.some(bet => betStore.selectedAccounts.includes(bet.account))
    );
});

/*
 * Handle select all checkbox change
 */
const handleSelectAll = event => {
    betStore.toggleAllAccountsSelection(event.target.checked);
}

/*
 * Watch for changes in someSelected to update state
 */
watch(someSelected, newValue => {
    if (selectAllCheckbox.value) {
        selectAllCheckbox.value.indeterminate = newValue;
    }
});

/*
 * Set initial indeterminate state on mount
 */
onMounted(() => {
    if (selectAllCheckbox.value) {
        selectAllCheckbox.value.indeterminate = someSelected.value;
    }
});
</script>

<template>
    <thead>
        <tr>
            <!-- Checkbox for select all Accounts -->
            <th class="w-1">
                <input
                    ref="selectAllCheckbox"
                    class="form-check-input m-0"
                    type="checkbox"
                    :checked="allSelected"
                    @change="handleSelectAll"
                />
            </th>
            <!-- Column headers -->
            <th
                v-for="col in sortableColumns"
                :key="col.key"
                @click="onSort(col.key)"
                class="cursor-pointer"
            >
                <div
                    class="d-flex align-items-center"
                    :class="{ 'justify-content-end': !['account', 'channel', 'master'].includes(col.key) }"
                >
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
