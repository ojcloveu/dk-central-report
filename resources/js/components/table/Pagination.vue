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
    <div class="card-footer d-flex gap-2 flex-column flex-md-row align-items-center">
        <!-- Pagination controls -->
        <div class="d-inline-flex align-items-center">
            <div>
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
            <p class="m-0 text-muted text-center text-sm-start ms-1">entries per page</p>
        </div>

        <!-- Pagination links -->
        <ul
            class="pagination m-0 mt-2 mt-sm-0 w-auto w-sm-auto justify-content-center justify-content-md-end flex-grow-1"
        >
            <li
                v-for="link in meta.links"
                :key="link.label"
                class="page-item"
                :class="{ active: link.active, disabled: !link.url }"
            >
                <a
                    href="#"
                    class="page-link"
                    @click.prevent="onPageClick(link.page || link.label)"
                    v-html="link.label"
                ></a>
            </li>
        </ul>
    </div>
</template>
