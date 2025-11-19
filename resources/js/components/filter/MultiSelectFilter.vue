<!-- MultiSelectFilter.vue -->
<script setup>
import { ref, watch, nextTick, onMounted, onUnmounted } from 'vue';

/*
 * Props
 */
const props = defineProps({
    // Value props
    items: { type: Array, default: () => [] },
    modelValue: { type: Array, default: () => [] },

    // Config props
    placeholder: { type: String, default: 'Select items' },
    error: { type: String, default: null },
    loading: { type: Boolean, default: false },
    fetchItems: { type: Function, default: () => {} },

    // Pagination props
    hasNextPage: { type: Boolean, default: false },
    currentPage: { type: Number, default: 1 },
});

const emit = defineEmits(['update:modelValue', 'search', 'load-more']);

/*
 * State
 */
const isDropdownOpen = ref(false);
const searchQuery = ref('');
const selectedValues = ref([...props.modelValue]);
const isLoadingMore = ref(false);

const selectRef = ref(null);
const searchInputRef = ref(null);
const listContainerRef = ref(null);

/*
 * Handle Selected Items
 */
const toggleItem = item => {
    const exists = selectedValues.value.includes(item);

    if (exists) {
        selectedValues.value = selectedValues.value.filter(v => v !== item);
    } else {
        selectedValues.value.push(item);
    }

    emit('update:modelValue', selectedValues.value);
};

const removeTag = item => {
    selectedValues.value = selectedValues.value.filter(v => v !== item);
    emit('update:modelValue', selectedValues.value);
};

const clearAll = () => {
    selectedValues.value = [];
    emit('update:modelValue', []);
};

/*
 * watchers
 */
watch(
    () => props.modelValue,
    newValue => {
        selectedValues.value = [...newValue];
    }
);

let searchDebounce = null;
watch(searchQuery, newQuery => {
    clearTimeout(searchDebounce);

    if (isDropdownOpen.value) {
        searchDebounce = setTimeout(() => {
            emit('search', newQuery);
        }, 500);
    }
});

/*
 * Infinite Scroll
 */
const handleScroll = () => {
    if (!listContainerRef.value || isLoadingMore.value || !props.hasNextPage) return;

    const { scrollTop, scrollHeight, clientHeight } = listContainerRef.value;

    if ((scrollTop + clientHeight) / scrollHeight > 0.8) {
        loadMore();
    }
};

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

/*
 * Dropdown open/close
 */
const toggleDropdown = () => {
    isDropdownOpen.value = !isDropdownOpen.value;
};

watch(isDropdownOpen, async open => {
    if (open) {
        emit('search', searchQuery.value);

        await nextTick();
        searchInputRef.value?.focus();
        listContainerRef.value?.addEventListener('scroll', handleScroll);
    } else {
        listContainerRef.value?.removeEventListener('scroll', handleScroll);
    }
});

/*
 * Outside click
 */
const handleClickOutside = e => {
    if (selectRef.value && !selectRef.value.contains(e.target)) {
        isDropdownOpen.value = false;
    }
};

onMounted(() => document.addEventListener('click', handleClickOutside));
onUnmounted(() => document.removeEventListener('click', handleClickOutside));
</script>

<template>
    <div class="singleselect2" ref="selectRef">
        <div class="dropdown-selector">
            <!-- Selected tags -->
            <div
                class="selector-input form-control"
                :class="{ 'form-error-border': error }"
                @click="toggleDropdown"
            >
                <div class="d-flex flex-wrap gap-1">
                    <template v-if="selectedValues.length > 0">
                        <span
                            v-for="item in selectedValues"
                            :key="item"
                            class="badge bg-primary d-flex align-items-center gap-1"
                        >
                            {{ item }}
                            <i class="la la-times" role="button" @click.stop="removeTag(item)"></i>
                        </span>
                    </template>

                    <span v-else class="text-muted">{{ placeholder }}</span>
                </div>

                <i class="la" :class="isDropdownOpen ? 'la-angle-up' : 'la-angle-down'"></i>
            </div>

            <!-- Dropdown list -->
            <div v-if="isDropdownOpen" class="dropdown-menu-list" ref="listContainerRef">
                <div class="p-2">
                    <input
                        ref="searchInputRef"
                        type="text"
                        class="form-control"
                        placeholder="Search..."
                        v-model="searchQuery"
                        @keydown.enter.prevent
                    />

                    <!-- Clear all -->
                    <i
                        v-if="selectedValues.length > 0 && !loading"
                        class="la la-times-circle remove-filter text-muted"
                        role="button"
                        @click.stop="clearAll"
                    ></i>
                </div>

                <!-- Item list -->
                <div class="list-group list-group-flush">
                    <a
                        v-for="item in items"
                        :key="item"
                        href="#"
                        @click.prevent="toggleItem(item)"
                        class="list-group-item list-group-item-action d-flex justify-content-between"
                    >
                        {{ item }}
                        <i
                            v-if="selectedValues.includes(item)"
                            class="la la-check text-success"
                        ></i>
                    </a>

                    <!-- Loading more -->
                    <div v-if="isLoadingMore" class="text-center text-muted p-2 border-top">
                        <i class="la la-spinner la-spin"></i> Loading more...
                    </div>

                    <!-- End -->
                    <div
                        v-else-if="!hasNextPage && items.length > 0"
                        class="text-center text-muted p-2 border-top"
                    >
                        <i class="la la-check-circle"></i> All items loaded
                    </div>

                    <!-- No results -->
                    <div v-if="items.length === 0 && !loading" class="text-center text-muted p-2">
                        No results
                    </div>
                </div>
            </div>

            <span v-if="error" class="invalid-feedback d-block">{{ error }}</span>
        </div>
    </div>
</template>

<style scoped>
/* Same styling as SingleSelectFilter */
.dropdown-selector {
    position: relative;
}

.selector-input {
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
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

.remove-filter {
    position: absolute;
    right: 14px;
    top: 20px;
    cursor: pointer;
}
</style>
