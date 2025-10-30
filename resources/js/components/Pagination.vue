<script setup>
const props = defineProps({
    meta: Object,
    links: Array,
    onPageClick: Function,
});
</script>

<template>
    <div class="card-footer d-flex align-items-center">
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
                v-for="link in links"
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
