<!-- SingleSelectFilter.vue -->
<script setup>
import { nextTick, watch } from 'vue';
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    // Value props
    items: { type: Array, default: () => [] },
    modelValue: { type: [String, Number, null], default: null },

    // Config props
    placeholder: { type: String, default: 'Select an Item' },
    error: { type: String, default: null },
    loading: { type: Boolean, default: false },
    fetchItems: { type: Function, default: () => {} },
});
const emit = defineEmits(['update:modelValue']);

const isDropdownOpen = ref(false);
const searchQuery = ref('');
const selectedValue = ref(props.modelValue || null);

const selectRef = ref(null);
const searchInputRef = ref(null);

// Placeholder items for the list
const filteredItems = computed(() => {
    if (!searchQuery.value) return props.items;
    const query = searchQuery.value.toLowerCase();
    return props.items.filter(item => item.toLowerCase().includes(query));
});

// Placeholder functions
const toggleDropdown = () => {
    isDropdownOpen.value = !isDropdownOpen.value;
};

watch(isDropdownOpen, async open => {
    if (open) {
        if (props?.items.length === 0) {
            props.fetchItems();
        }

        await nextTick();
        searchInputRef.value?.focus();
    } else {
        searchQuery.value = '';
    }
});

const selectItem = item => {
    selectedValue.value = item;
    emit('update:modelValue', item);
    isDropdownOpen.value = false;
};

const clearSelection = event => {
    event.stopPropagation();
    selectedValue.value = null;
    emit('update:modelValue', null);
    isDropdownOpen.value = false;
    searchQuery.value = '';
};

const getSelectedText = computed(() => {
    return selectedValue.value || props.placeholder;
});

// A simple function to simulate the resolution state
const resolvingSelected = ref(false);

// Handle the global click
const handleClickOutside = event => {
    if (selectRef.value && !selectRef.value.contains(event.target)) {
        isDropdownOpen.value = false;
    }
};

// Setup event listeners
onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <div class="singleselect2" ref="selectRef">
        <div class="dropdown-selector">
            <div
                class="selector-input form-control"
                :class="{ 'form-error-border': error }"
                @click="toggleDropdown"
                role="button"
            >
                <template v-if="selectedValue">
                    <span
                        v-if="resolvingSelected"
                        class="text-muted small d-inline-flex align-items-center"
                    >
                        <i class="la la-spinner la-spin me-1"></i>
                        Loading...
                    </span>
                    <span v-else class="selected-text">
                        {{ getSelectedText }}
                    </span>
                </template>
                <span v-else class="text-muted">{{ placeholder }}</span>

                <i class="la" :class="isDropdownOpen ? 'la-angle-up' : 'la-angle-down'"></i>
            </div>

            <div v-if="isDropdownOpen" class="dropdown-menu-list">
                <div class="p-2">
                    <div class="position-relative">
                        <input
                            ref="searchInputRef"
                            type="text"
                            class="form-control pe-1"
                            placeholder="Search..."
                            v-model="searchQuery"
                            @keydown.enter.prevent
                        />

                        <!-- Remove filter value -->
                        <i
                            v-if="selectedValue && !loading"
                            class="la la-times-circle remove-filter text-muted"
                            @click.stop="clearSelection"
                            role="button"
                            title="Clear"
                        ></i>
                    </div>

                    <div v-if="loading" class="d-flex justify-content-center mt-2">
                        <small class="text-muted">
                            <i class="la la-spinner la-spin"></i>
                            Fetching data...
                        </small>
                    </div>
                </div>

                <div class="list-group list-group-flush">
                    <a
                        v-for="item in filteredItems"
                        :key="item"
                        href="#"
                        class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                        @click.prevent="selectItem(item)"
                    >
                        {{ item }}
                        <i v-if="selectedValue === item" class="la la-check text-success"></i>
                    </a>

                    <div
                        v-if="filteredItems.length === 0 && !loading"
                        class="text-center text-muted p-2"
                    >
                        No results
                    </div>
                </div>
            </div>

            <span v-if="error" class="invalid-feedback d-block">{{ error }}</span>
        </div>
    </div>
</template>

<style scoped>
.selected-text {
    font-weight: 500;
}

.dropdown-selector {
    position: relative;
}

.dropdown-selector .selector-input {
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
    transition: all 0.2s;
}

.dropdown-selector .dropdown-menu-list {
    position: absolute;
    width: 100%;
    z-index: 1000;
    margin-top: 5px;
    border: 1px solid var(--tblr-border-color);
    border-radius: 6px;
    background-color: var(--tblr-light);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    max-height: 250px;
    overflow-y: auto;
}

/* Dark theme variant for the dropdown menu */
[data-bs-theme='dark'] .dropdown-selector .dropdown-menu-list {
    background-color: var(--tblr-body-bg);
    border-color: var(--tblr-border-color);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
}

.dropdown-selector .dropdown-menu-list .list-group-item {
    cursor: pointer;
    transition: background-color 0.2s;
    color: var(--tblr-body-color);
    background-color: var(--tblr-light);
}

.dropdown-selector .dropdown-menu-list .list-group-item:hover {
    background-color: var(--tblr-secondary-bg);
}

.remove-filter {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
}

/* Dark theme variant for list items */
[data-bs-theme='dark'] .dropdown-selector .dropdown-menu-list .list-group-item {
    background-color: var(--tblr-body-bg);
    color: var(--tblr-body-color);
}

[data-bs-theme='dark'] .dropdown-selector .dropdown-menu-list .list-group-item:hover {
    background-color: var(--tblr-dark);
}
</style>
