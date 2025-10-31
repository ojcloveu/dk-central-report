<script setup>
import { computed } from 'vue';

const props = defineProps({
    bet: Object,
    getMasterBadgeClass: Function,
});

const lpBgColor = computed(() => `bg-${props.bet.lp.color.toLowerCase()}-lt`);

const amountColor = computed(() => amount => {
    if (String(amount).startsWith('-')) return 'text-danger';
    else return 'text-success';
});
</script>

<template>
    <tr>
        <td><input class="form-check-input m-0" type="checkbox" /></td>
        <td>
            <div class="fw-bold">{{ bet.account }}</div>
        </td>
        <td>
            <span class="badge bg-azure-lt">{{ bet.channel }}</span>
        </td>
        <td class="text-nowrap">
            {{
                new Date(bet.trandate).toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric',
                })
            }}
        </td>
        <td>
            <span class="badge" :class="getMasterBadgeClass(bet.master)">{{ bet.master }}</span>
        </td>
        <td class="text-end">{{ bet.min }}</td>
        <td class="text-end">{{ bet.max.toLocaleString() }}</td>
        <td class="text-end">{{ bet.count }}</td>
        <td class="text-end fw-bold">{{ bet.turnover }}</td>
        <td class="text-end fw-bold" :class="amountColor(bet.winlose)">
            {{ bet.winlose }}
        </td>
        <td class="text-end fw-bold" :class="lpBgColor">
            {{ parseFloat(bet.lp.percentage).toFixed(2) }}%
        </td>
    </tr>
</template>
