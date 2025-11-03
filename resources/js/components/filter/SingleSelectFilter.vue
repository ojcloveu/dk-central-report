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

    // Pagination props
    hasNextPage: { type: Boolean, default: false },
    currentPage: { type: Number, default: 1 },
});

const emit = defineEmits(['update:modelValue', 'load-more', 'search']);

const isDropdownOpen = ref(false);
const searchQuery = ref('');
const selectedValue = ref(props.modelValue || null);
const isLoadingMore = ref(false);

const selectRef = ref(null);
const searchInputRef = ref(null);
const listContainerRef = ref(null);

const filteredItems = computed(() => props.items);

// Toggle dropdown
const toggleDropdown = () => {
    isDropdownOpen.value = !isDropdownOpen.value;
};

let searchDebounce = null;
watch(searchQuery, newQuery => {
    clearTimeout(searchDebounce);

    if (isDropdownOpen.value) {
        // Debounce the search to prevent excessive requests while typing
        searchDebounce = setTimeout(() => {
            // Emit the search event with the query. This must trigger page 1 fetch in the parent.
            emit('search', newQuery);
        }, 500);
    }
});

// Watch dropdown state
watch(isDropdownOpen, async open => {
    if (open) {
        // If items are empty (initial open) or there is a query (reopening with search history), fetch page 1.
        if (props?.items.length === 0 || searchQuery.value) {
            emit('search', searchQuery.value);
        }

        await nextTick();
        searchInputRef.value?.focus();

        // Setup scroll listener
        if (listContainerRef.value) {
            listContainerRef.value.addEventListener('scroll', handleScroll);
        }
    } else {
        // Remove scroll listener
        if (listContainerRef.value) {
            listContainerRef.value.removeEventListener('scroll', handleScroll);
        }
    }
});

// Handle infinite scroll
const handleScroll = () => {
    if (!listContainerRef.value || isLoadingMore.value || !props.hasNextPage) return;

    const { scrollTop, scrollHeight, clientHeight } = listContainerRef.value;
    const scrollPercentage = (scrollTop + clientHeight) / scrollHeight;

    // Load more when scrolled 80% of the list
    if (scrollPercentage > 0.8) {
        loadMore();
    }
};

// Load more items
const loadMore = async () => {
    if (isLoadingMore.value || !props.hasNextPage || props.loading) return;

    isLoadingMore.value = true;

    try {
        emit('load-more', props.currentPage + 1, searchQuery.value);
    } finally {
        setTimeout(() => {
            isLoadingMore.value = false;
        }, 300);
    }
};

// Select item
const selectItem = item => {
    selectedValue.value = item;
    emit('update:modelValue', item);
    isDropdownOpen.value = false;
};

// Clear selection
const clearSelection = event => {
    event.stopPropagation();
    selectedValue.value = null;
    emit('update:modelValue', null);
    isDropdownOpen.value = false;
    searchQuery.value = '';
};

// Get selected text
const getSelectedText = computed(() => {
    return selectedValue.value || props.placeholder;
});

// Resolving selected state
const resolvingSelected = ref(false);

// Handle click outside
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
    if (listContainerRef.value) {
        listContainerRef.value.removeEventListener('scroll', handleScroll);
    }
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

            <div v-if="isDropdownOpen" class="dropdown-menu-list" ref="listContainerRef">
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

                    <div
                        v-if="loading && items.length === 0"
                        class="d-flex justify-content-center mt-2"
                    >
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

                    <!-- Loading more indicator -->
                    <div
                        v-if="isLoadingMore || (loading && items.length > 0)"
                        class="text-center text-muted p-2 border-top"
                    >
                        <small>
                            <i class="la la-spinner la-spin me-1"></i>
                            Loading more...
                        </small>
                    </div>

                    <!-- End of list indicator -->
                    <div
                        v-else-if="!hasNextPage && items.length > 0"
                        class="text-center text-muted p-2 border-top"
                    >
                        <small>
                            <i class="la la-check-circle me-1"></i>
                            All items loaded
                        </small>
                    </div>

                    <!-- No results -->
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
    scroll-behavior: smooth;
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

/* Smooth scrolling */
.dropdown-menu-list::-webkit-scrollbar {
    width: 6px;
}

.dropdown-menu-list::-webkit-scrollbar-track {
    background: transparent;
}

.dropdown-menu-list::-webkit-scrollbar-thumb {
    background: #cbd5e0;
    border-radius: 3px;
}

.dropdown-menu-list::-webkit-scrollbar-thumb:hover {
    background: #a0aec0;
}
</style>
