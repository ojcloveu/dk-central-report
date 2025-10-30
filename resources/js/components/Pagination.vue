<script setup>
import { computed } from 'vue';

const props = defineProps({
    itemsPerPage: {
        type: Number,
        default: 10,
    },
    totalPages: {
        type: Number,
        required: true,
    },
    currentPage: {
        type: Number,
        default: 1,
    },
});

const emit = defineEmits(['update:itemsPerPage', 'update:currentPage']);

const setItemsPerPage = value => {
    emit('update:itemsPerPage', value);
    emit('update:currentPage', 1);
};

const goToPage = page => {
    if (page >= 1 && page <= props.totalPages) {
        emit('update:currentPage', page);
    }
};
</script>

<template>
    <div class="card-footer d-flex align-items-center">
        <div class="dropdown">
            <a class="btn dropdown-toggle" href="#" data-bs-toggle="dropdown" role="button">
                <span class="me-1">{{ itemsPerPage }}</span>
                <span>records</span>
            </a>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="#" @click.prevent="setItemsPerPage(10)">10 records</a>
                <a class="dropdown-item" href="#" @click.prevent="setItemsPerPage(20)">20 records</a>
                <a class="dropdown-item" href="#" @click.prevent="setItemsPerPage(50)">50 records</a>
                <a class="dropdown-item" href="#" @click.prevent="setItemsPerPage(100)">100 records</a>
            </div>
        </div>

        <ul class="pagination m-0 ms-auto">
            <li
                v-for="page in totalPages"
                :key="page"
                class="page-item"
                :class="{ active: currentPage === page }"
            >
                <a class="page-link cursor-pointer" href="#" @click.prevent="goToPage(page)">{{ page }}</a>
            </li>
        </ul>
    </div>
</template>
