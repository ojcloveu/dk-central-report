<script setup>
import { ref, computed, watch } from 'vue';

/**
 * Props
 */
const props = defineProps({
    modelValue: {
        type: Object,
        default: () => ({ min: null, max: null }),
    },
    label: {
        type: String,
        default: 'Range Filter',
    },
    min: {
        type: Number,
        default: 0,
    },
    max: {
        type: Number,
        default: 100,
    },
    step: {
        type: Number,
        default: 1,
    },
    placeholder: {
        type: Object,
        default: () => ({ min: 'Min', max: 'Max' }),
    },
});

/**
 * Emits
 */
const emit = defineEmits(['update:modelValue']);

/**
 * Local state
 */
const isOpen = ref(false);
const minValue = ref(props.modelValue?.min ?? null);
const maxValue = ref(props.modelValue?.max ?? null);

/**
 * Watch for external changes to modelValue
 */
watch(
    () => props.modelValue,
    newValue => {
        if (newValue) {
            minValue.value = newValue.min ?? null;
            maxValue.value = newValue.max ?? null;
        }
    },
    { deep: true }
);

/**
 * Toggle dropdown
 */
const toggleDropdown = () => {
    if (!isOpen.value) {
        // Reset to current values when opening
        minValue.value = props.modelValue?.min ?? null;
        maxValue.value = props.modelValue?.max ?? null;
    }
    isOpen.value = !isOpen.value;
};

/**
 * Close dropdown
 */
const closeDropdown = () => {
    isOpen.value = false;
};

/**
 * Apply the filter
 */
const applyFilter = () => {
    emit('update:modelValue', {
        min: minValue.value,
        max: maxValue.value,
    });
    closeDropdown();
};

/**
 * Clear the filter
 */
const clearFilter = () => {
    minValue.value = null;
    maxValue.value = null;
    emit('update:modelValue', { min: null, max: null });
    closeDropdown();
};

/**
 * Display text for the button
 */
const displayText = computed(() => {
    if (props.modelValue.min !== null || props.modelValue.max !== null) {
        const min = props.modelValue.min !== null ? props.modelValue.min : '';
        const max = props.modelValue.max !== null ? props.modelValue.max : '';
        return `${min} — ${max}`;
    }
    return `${props.label}`;
});

/**
 * Check if filter has value
 */
const hasValue = computed(() => {
    return props.modelValue.min !== null || props.modelValue.max !== null;
});
</script>

<template>
    <div class="range-filter-modal">
        <!-- Trigger Button -->
        <button
            type="button"
            class="form-select text-start"
            :class="{ 'has-value': hasValue, 'is-open': isOpen }"
            @click="toggleDropdown"
        >
            <span class="filter-text text-muted">{{ displayText }}</span>
            <i :class="{ rotated: isOpen }" class="la la-angle-down dropdown-arrow"></i>
        </button>

        <!-- Dropdown Content -->
        <div v-if="isOpen" class="dropdown-content" @click.stop>
            <div class="dropdown-header">
                <strong>{{ label }}</strong>
                <button
                    type="button"
                    class="btn-close"
                    @click="closeDropdown"
                    aria-label="Close"
                ></button>
            </div>

            <div class="dropdown-body">
                <div class="range-inputs">
                    <div class="range-input-group">
                        <input
                            type="number"
                            class="form-control py-1"
                            :step="step"
                            v-model.number="minValue"
                            :placeholder="placeholder.min"
                        />
                    </div>
                    <span class="range-separator">—</span>
                    <div class="range-input-group">
                        <input
                            type="number"
                            class="form-control py-1"
                            :step="step"
                            v-model.number="maxValue"
                            :placeholder="placeholder.max"
                        />
                    </div>
                </div>
            </div>

            <div class="dropdown-footer">
                <button
                    type="button"
                    class="btn btn-primary btn-md"
                    @click="applyFilter"
                    :disabled="minValue === null && maxValue === null"
                >
                    Apply
                </button>
            </div>
        </div>

        <!-- Backdrop -->
        <div v-if="isOpen" class="dropdown-backdrop" @click="closeDropdown"></div>
    </div>
</template>

<style scoped>
.range-filter-modal {
    position: relative;
}

.form-select {
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
    background-color: var(--tblr-bg-forms);
    border: var(--tblr-border-width) solid var(--tblr-border-color);
    background-image: none; /* Remove default arrow */
    padding-right: 2.5rem; /* Make room for custom arrow */
}

.form-select:hover {
    border-color: var(--tblr-primary);
}

.form-select.has-value {
    background-color: var(--tblr-primary-lt);
    border-color: var(--tblr-primary);
}

.filter-text {
    flex: 1;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.dropdown-arrow {
    position: absolute;
    right: 0.75rem;
    transition: transform 0.2s ease;
    pointer-events: none;
}

.dropdown-arrow.rotated {
    transform: rotate(180deg);
}

.dropdown-content {
    position: absolute;
    top: calc(100% + 4px);
    left: 0;
    right: 0;
    background-color: var(--tblr-body-bg);
    border: var(--tblr-border-width) solid var(--tblr-border-color);
    border-radius: var(--tblr-border-radius);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    z-index: 1050;
    min-width: 280px;
}

.dropdown-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 1rem;
    border-bottom: var(--tblr-border-width) solid var(--tblr-border-color);
}

.dropdown-body {
    padding: 1rem;
}

.dropdown-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-top: var(--tblr-border-width) solid var(--tblr-border-color);
}

.dropdown-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 1040;
    background: transparent;
}

.btn-close {
    background: transparent;
    border: none;
    font-size: 1.5rem;
    line-height: 1;
    cursor: pointer;
    opacity: 0.5;
}

.btn-close:hover {
    opacity: 1;
}

/* Range Input Styles */
.range-inputs {
    display: flex;
    align-items: center;
    gap: 8px;
}

.range-input-group {
    flex: 1;
}

.range-input-group .form-control {
    text-align: center;
}

.range-separator {
    color: #6c757d;
    font-weight: 500;
    flex-shrink: 0;
}
</style>
