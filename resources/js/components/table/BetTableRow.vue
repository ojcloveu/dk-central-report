<script setup>
import { useBetStore } from '@/stores/betStore';
import { computed } from 'vue';
import { amountColor, lpBgColor } from '../../utils/getStatusClass';

/*
 * Define the props this component accepts
 */
const props = defineProps({
    bet: Object,
    getMasterBadgeClass: Function,
});

const betStore = useBetStore();

/*
 * Check if the current account is selected
 */
const isSelected = computed(() => betStore.selectedAccounts.includes(props.bet.account));

/*
 * Handler to call the store action
 */
const handleCheckboxChange = event => {
    betStore.toggleAccountSelection(props.bet.account, event.target.checked);
};
</script>

<template>
    <tr :class="{ 'table-primary-light': isSelected }">
        <td>
            <input
                class="form-check-input m-0"
                type="checkbox"
                :checked="isSelected"
                @change="handleCheckboxChange"
            />
        </td>
        <td>
            <div class="fw-bold">{{ bet?.account }}</div>
        </td>
        <td>
            <span class="badge bg-azure-lt">{{ bet?.channel }}</span>
        </td>
        <td class="text-end text-nowrap">
            {{
                new Date(bet?.trandate).toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric',
                })
            }}
        </td>
        <td>
            <span class="badge" :class="getMasterBadgeClass(bet?.master)">{{ bet?.master }}</span>
        </td>
        <td class="text-end">{{ bet?.min }}</td>
        <td class="text-end">{{ bet?.max.toLocaleString() }}</td>
        <td class="text-end">{{ bet?.count }}</td>
        <td class="text-end fw-bold">{{ bet?.turnover }}</td>
        <td class="text-end fw-bold" :class="amountColor(bet?.winlose)">
            {{ bet?.winlose }}
        </td>
        <td class="text-end fw-bold" :class="lpBgColor(bet?.lp)">
            {{ bet?.lp?.percentage }}%
        </td>
    </tr>
</template>