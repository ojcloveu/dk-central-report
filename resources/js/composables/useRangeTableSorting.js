// composables/useRangeTableSorting.js
import { reactive } from 'vue';

/**
 * Helper to parse currency strings($)
 * @returns {number}
 */
const parseCurrency = value => {
    if (typeof value === 'number') return value;
    if (!value) return 0;

    // Remove '$' and ',' then parse
    return parseFloat(String(value).replace(/[$,]/g, ''));
};

/**
 * Helper to extract the numeric LP percentage value from total_lp field
 * @returns {number}
 */
const getLpPercentageValue = lpValue => {
    if (typeof lpValue === 'object' && lpValue?.percentage !== undefined) {
        return parseFloat(lpValue.percentage || 0);
    }
    return parseFloat(lpValue || 0);
};

/**
 * Composable for handling client-side sorting for the Range Table data
 * @returns {{ rangeSort: object, handleRangeSort: function, getSortedData: function }}
 */
export function useRangeTableSorting(getRangeData) {
    // Sorting state (reactive)
    const rangeSort = reactive({
        sort_by: 'account',
        sort_dir: 'asc',
    });

    // Main Sorting Function
    const getSortedData = periodKey => {
        // Use helper function to get the raw data
        const data = getRangeData(periodKey)?.data || [];
        if (data.length === 0) return [];

        // Create a copy to sort
        const sortedData = [...data];

        sortedData.sort((a, b) => {
            let aVal = a[rangeSort.sort_by];
            let bVal = b[rangeSort.sort_by];

            // Handling for currency values ('winlose', 'turnover')
            if (['total_winlose', 'total_turnover'].includes(rangeSort.sort_by)) {
                aVal = parseCurrency(aVal);
                bVal = parseCurrency(bVal);
                return rangeSort.sort_dir === 'asc' ? aVal - bVal : bVal - aVal;
            }

            // Handling total_lp (which object with a 'percentage' key)
            if (rangeSort.sort_by === 'total_lp') {
                aVal = getLpPercentageValue(aVal);
                bVal = getLpPercentageValue(bVal);
                return rangeSort.sort_dir === 'asc' ? aVal - bVal : bVal - aVal;
            }

            // Handling numeric values (total_count)
            if (typeof aVal === 'number' && typeof bVal === 'number') {
                return rangeSort.sort_dir === 'asc' ? aVal - bVal : bVal - aVal;
            }

            // Fallback to String values (account)
            const aStr = String(aVal || '').toLowerCase();
            const bStr = String(bVal || '').toLowerCase();

            if (rangeSort.sort_dir === 'asc') {
                return aStr.localeCompare(bStr);
            } else {
                return bStr.localeCompare(aStr);
            }
        });

        return sortedData;
    };

    // Sorting Handler
    const handleRangeSort = column => {
        if (rangeSort.sort_by === column) {
            // toggle direction
            rangeSort.sort_dir = rangeSort.sort_dir === 'asc' ? 'desc' : 'asc';
        } else {
            rangeSort.sort_by = column;
            rangeSort.sort_dir = 'asc';
        }
    };

    return {
        rangeSort,
        handleRangeSort,
        getSortedData,
    };
}
