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
 */
export function useRangeTableSorting(
    getRangeData,
    rangeSort,
    handleRangeSortFn,
    externalSummaryCache
) {
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

            // Handling for external summary fields (deposits, withdraws, profit)
            if (['deposits', 'withdraws', 'profit'].includes(rangeSort.sort_by)) {
                const aSummary = externalSummaryCache?.value?.[a.account];
                const bSummary = externalSummaryCache?.value?.[b.account];

                aVal = parseCurrency(aSummary?.[rangeSort.sort_by] || '$0');
                bVal = parseCurrency(bSummary?.[rangeSort.sort_by] || '$0');
                return rangeSort.sort_dir === 'asc' ? aVal - bVal : bVal - aVal;
            }

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

            // Handling total_count (convert to number if string)
            if (rangeSort.sort_by === 'total_count') {
                aVal = parseFloat(String(aVal).replace(/,/g, '')) || 0;
                bVal = parseFloat(String(bVal).replace(/,/g, '')) || 0;
                return rangeSort.sort_dir === 'asc' ? aVal - bVal : bVal - aVal;
            }

            // Handling numeric values
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

    // Sorting Handler - use the passed function from store
    const handleRangeSort = handleRangeSortFn;

    return {
        rangeSort,
        handleRangeSort,
        getSortedData,
    };
}
