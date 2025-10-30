<script setup>
const props = defineProps({
    label: String,
    field: String,
    sortBy: String,
    sortOrder: String,
});

const emit = defineEmits(['sort']);
</script>

<template>
    <th>
        <button
            class="table-sort d-flex justify-content-between w-100 border-0 bg-transparent text-start"
            @click="$emit('sort', field)"
        >
            {{ label }}
            <span v-if="sortBy === field">{{ sortOrder === 'asc' ? '↑' : '↓' }}</span>
        </button>
    </th>
</template>
