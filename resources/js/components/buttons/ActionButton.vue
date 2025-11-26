<script setup>
defineProps({
    variant: {
        type: String,
        default: 'primary',
        validator: value =>
            ['primary', 'secondary', 'outline-secondary', 'outline-danger', 'danger', 'success'].includes(value),
    },
    iconClass: {
        type: String,
        required: true,
    },
    label: {
        type: String,
        default: '',
    },
    title: {
        type: String,
        default: '',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    iconOnly: {
        type: Boolean,
        default: false,
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

defineEmits(['click']);
</script>

<template>
    <button
        type="button"
        :class="['btn', iconOnly ? 'btn-icon' : 'px-2', `btn-${variant}`]"
        :title="title"
        :disabled="disabled"
        :aria-label="iconOnly ? title : undefined"
        @click="$emit('click')"
    >
        <i :class="[iconClass, 'fs-2', { 'fa-spin': loading }, iconOnly ? '' : 'pe-1']"></i>
        <span v-if="!iconOnly">{{ label }}</span>
    </button>
</template>
