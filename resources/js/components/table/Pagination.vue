<script setup>
const props = defineProps({
    meta: Object,
    onPageClick: Function,
    onPerPageChange: Function,
});

const handlePerPageChange = event => {
    const newPerPage = parseInt(event.target.value);
    props.onPerPageChange?.(newPerPage);
};
</script>

<template>
    <div class="card-footer d-flex align-items-center">
        <div class="me-2">
            <select
                class="form-select form-select-sm"
                :value="meta.per_page"
                @change="handlePerPageChange"
            >
                <option value="10">10</option>
                <option value="20">20</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
        <p class="m-0 text-muted">
            Showing
            <span class="fw-bold">{{ meta.current_page * meta.per_page - meta.per_page + 1 }}</span>
            to
            <span class="fw-bold">{{
                Math.min(meta.current_page * meta.per_page, meta.total)
            }}</span>
            of
            <span class="fw-bold">{{ meta.total }}</span>
            entries
        </p>
        <ul class="pagination m-0 ms-auto">
            <li
                v-for="link in meta.links"
                :key="link.label"
                class="page-item"
                :class="{ active: link.active, disabled: !link.url }"
            >
                <a
                    href="#"
                    class="page-link mx-1"
                    @click.prevent="onPageClick(link.page || link.label)"
                    v-html="link.label"
                ></a>
            </li>
        </ul>
    </div>
</template>
