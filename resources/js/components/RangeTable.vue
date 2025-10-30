<script setup>
import RangeCard from './RangeCard.vue';

const props = defineProps({
    selectedAccounts: Array,
    rangesTables: Object,
});

const emits = defineEmits(['setRangeItemsPerPage', 'goToRangePage']);
</script>

<template>
    <div class="py-3">
        <h3>Selected Account Period Range</h3>

        <div class="d-flex flex-column gap-3">
            <RangeCard
                v-for="rangeKey in ['7d', '1m', '3m']"
                :key="rangeKey"
                :range-key="rangeKey"
                :range-data="rangesTables[rangeKey]"
                @changePage="page => $emit('goToRangePage', rangeKey, page)"
                @changePerPage="count => $emit('setRangeItemsPerPage', rangeKey, +count)"
            />
        </div>
    </div>
</template>
