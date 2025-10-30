<script setup>
const props = defineProps({
    dateFilter: String,
    masterFilter: String,
    accountFilter: String,
    channelFilter: String,
    masterOptions: Array,
    accountOptions: Array,
    channelOptions: Array,
    masterSearch: String,
    accountSearch: String,
    channelSearch: String,
});

const emits = defineEmits([
    'update:dateFilter',
    'update:masterFilter',
    'update:accountFilter',
    'update:channelFilter',
    'update:masterSearch',
    'update:accountSearch',
    'update:channelSearch',
    'clear',
]);
</script>

<template>
    <div class="p-3">
        <div class="row g-2 align-items-end">
            <!-- Date -->
            <div class="col-md-2">
                <label class="form-label small">Date</label>
                <input
                    type="date"
                    class="form-control form-control-sm"
                    :value="dateFilter"
                    @input="$emit('update:dateFilter', $event.target.value)"
                />
            </div>

            <!-- Master -->
            <div class="col-md-3">
                <label class="form-label small">Master</label>
                <input
                    class="form-control form-control-sm mb-1"
                    placeholder="Search master..."
                    :value="masterSearch"
                    @input="$emit('update:masterSearch', $event.target.value)"
                />
                <select
                    class="form-select form-select-sm"
                    :value="masterFilter"
                    @change="$emit('update:masterFilter', $event.target.value)"
                >
                    <option value="">All masters</option>
                    <option v-for="m in masterOptions" :key="m" :value="m">{{ m }}</option>
                </select>
            </div>

            <!-- Account -->
            <div class="col-md-3">
                <label class="form-label small">Account</label>
                <input
                    class="form-control form-control-sm mb-1"
                    placeholder="Search account..."
                    :value="accountSearch"
                    @input="$emit('update:accountSearch', $event.target.value)"
                />
                <select
                    class="form-select form-select-sm"
                    :value="accountFilter"
                    @change="$emit('update:accountFilter', $event.target.value)"
                >
                    <option value="">All accounts</option>
                    <option v-for="a in accountOptions" :key="a" :value="a">{{ a }}</option>
                </select>
            </div>

            <!-- Channel -->
            <div class="col-md-2">
                <label class="form-label small">Channel</label>
                <input
                    class="form-control form-control-sm mb-1"
                    placeholder="Search channel..."
                    :value="channelSearch"
                    @input="$emit('update:channelSearch', $event.target.value)"
                />
                <select
                    class="form-select form-select-sm"
                    :value="channelFilter"
                    @change="$emit('update:channelFilter', $event.target.value)"
                >
                    <option value="">All channels</option>
                    <option v-for="c in channelOptions" :key="c" :value="c">{{ c }}</option>
                </select>
            </div>

            <div class="col-md-2 text-end">
                <button class="btn btn-sm btn-secondary" @click="$emit('clear')">Clear</button>
            </div>
        </div>
    </div>
</template>
