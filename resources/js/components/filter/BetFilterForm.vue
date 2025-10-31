<script setup>
import { reactive, watch } from 'vue';
import SingleSelectFilter from './SingleSelectFilter.vue';

const props = defineProps({
    initialFilters: Object,
    onSubmit: Function,
});

const localFilters = reactive({ ...props.initialFilters });
const today = new Date().toISOString().split('T')[0];

let debounceTimer = null;

const triggerFilter = () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        props.onSubmit(localFilters);
    }, 700);
};

watch(localFilters, () => {
    triggerFilter();
});

const handleReset = () => {
    localFilters.trandate = '';
    localFilters.master = '';
    localFilters.account = '';
    localFilters.channel = '';
};
</script>

<template>
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Filter Transactions</h3>

            <div>
                <button type="button" class="btn btn-primary px-1 btn-sm" @click="handleReset">
                    <i class="las la-eraser fs-2"></i> Reset
                </button>
            </div>
        </div>

        <div class="card-body">
            <form class="row g-3 align-items-end">
                <div class="col-md-3 col-sm-6">
                    <label class="form-label">Date</label>
                    <input type="date" class="form-control" v-model="localFilters.trandate" />
                </div>

                <!-- <div class="col-md-3 col-sm-6">
                    <label class="form-label">Master</label>
                    <input
                        type="text"
                        class="form-control"
                        placeholder="e.g., VIRTUAL"
                        v-model="localFilters.master"
                    />
                </div> -->
                <div class="col-md-3 col-sm-6">
                    <label class="form-label">Master</label>
                    <SingleSelectFilter placeholder="Master" />
                </div>

                <!-- <div class="col-md-3 col-sm-6">
                    <label class="form-label">Account</label>
                    <input
                        type="text"
                        class="form-control"
                        placeholder="e.g., THB7686"
                        v-model="localFilters.account"
                    />
                </div> -->
                <div class="col-md-3 col-sm-6">
                    <label class="form-label">Account</label>
                    <SingleSelectFilter placeholder="Account" />
                </div>

                <!-- <div class="col-md-2 col-sm-6">
                    <label class="form-label">Channel</label>
                    <input
                        type="text"
                        class="form-control"
                        placeholder="e.g., TH"
                        v-model="localFilters.channel"
                    />
                </div> -->
                <div class="col-md-3 col-sm-6">
                    <label class="form-label">Channel</label>
                    <SingleSelectFilter placeholder="Channel" />
                </div>
            </form>
        </div>
    </div>
</template>
