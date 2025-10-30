<script setup>
import { getLpClass, getMasterBadgeClass } from '../utils/getStatusClass';

const props = defineProps({
    item: Object,
    selected: Boolean,
});

const emit = defineEmits(['toggle']);
</script>

<template>
    <tr>
        <td>
            <input
                type="checkbox"
                class="form-check-input m-0 align-middle"
                :checked="selected"
                @change="emit('toggle', item.id)"
            />
        </td>
        <td class="text-dark fw-bold">{{ item.account }}</td>
        <td>{{ item.channel }}</td>
        <td>{{ item.trandate }}</td>
        <td>
            <span class="badge" :class="getMasterBadgeClass(item.master)">
                {{ item.master }}
            </span>
        </td>
        <td>{{ item.min }}</td>
        <td>{{ item.max.toLocaleString() }}</td>
        <td>{{ item.count }}</td>
        <td class="fw-bold">{{ item.turnover }}</td>
        <td class="fw-bold" :class="getLpClass(item.winlose)">{{ item.winlose }}</td>
        <td class="fw-bold" :class="getLpClass(item.lp)">{{ item.lp }}</td>
    </tr>
</template>
