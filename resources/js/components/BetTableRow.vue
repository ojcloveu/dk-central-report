<script setup>
import { computed } from 'vue';

const props = defineProps({
    bet: Object,
    getMasterBadgeClass: Function,
});

const lpBgColor = computed(() => `bg-${props.bet.lp.color}-lt`);
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
        <td>
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
        <td class="text-end fw-bold">
            ${{ parseFloat(bet.turnover).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
        </td>
        <td
            class="text-end fw-bold"
            :class="{
                'text-success': parseFloat(bet.winlose) > 0,
                'text-danger': parseFloat(bet.winlose) < 0,
                'text-muted': parseFloat(bet.winlose) === 0,
            }"
        >
            {{ parseFloat(bet.winlose) > 0 ? '+' : '' }}${{
                parseFloat(bet.winlose).toLocaleString('en-US', { minimumFractionDigits: 2 })
            }}
        </td>
        <td
            class="text-end fw-bold"
            :class="lpBgColor"
        >
            {{ parseFloat(bet.lp.percentage).toFixed(2) }}%
        </td>
    </tr>
</template>
