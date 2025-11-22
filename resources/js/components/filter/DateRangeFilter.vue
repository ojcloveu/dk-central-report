<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';

/*
 * Props & Emits
 */
const props = defineProps({
    modelValue: {
        type: Object,
        default: () => ({ start_date: null, end_date: null }),
    },
    placeholder: {
        type: String,
        default: 'Select Date Range',
    },
});

const emit = defineEmits(['update:modelValue']);

/*
 * Reactive State
 */
const isDropdownOpen = ref(false);
const selectedPreset = ref('');
const tempStartDate = ref(null);
const tempEndDate = ref(null);
const currentMonth1 = ref(new Date());
const currentMonth2 = ref(new Date());

const dateRangeRef = ref(null);

/*
 * Date Utilities
 */
const formatDate = date => {
    if (!date) return null;
    const d = new Date(date);
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

const formatDisplayDate = date => {
    if (!date) return '';
    const d = new Date(date);
    const day = String(d.getDate()).padStart(2, '0');
    const month = d.toLocaleString('en-US', { month: 'short' });
    const year = String(d.getFullYear()).slice(-2);
    return `${day} ${month} ${year}`;
};

const isSameDay = (date1, date2) => {
    if (!date1 || !date2) return false;
    const d1 = new Date(date1);
    const d2 = new Date(date2);
    return (
        d1.getFullYear() === d2.getFullYear() &&
        d1.getMonth() === d2.getMonth() &&
        d1.getDate() === d2.getDate()
    );
};

const isInRange = (date, start, end) => {
    if (!start || !end) return false;
    const d = new Date(date);
    const s = new Date(start);
    const e = new Date(end);
    return d >= s && d <= e;
};

/*
 * Preset Date Ranges
 */
const presets = [
    { label: 'Today', value: 'today' },
    { label: 'Yesterday', value: 'yesterday' },
    { label: 'Last 7 Days', value: 'last7days' },
    { label: 'Last 30 Days', value: 'last30days' },
    { label: 'This Month', value: 'thismonth' },
    { label: 'Last Month', value: 'lastmonth' },
    { label: 'Custom Range', value: 'custom' },
];

const getPresetDates = preset => {
    const today = new Date();
    const start = new Date();
    const end = new Date();

    switch (preset) {
        case 'today':
            return { start_date: formatDate(today), end_date: formatDate(today) };
        case 'yesterday':
            start.setDate(today.getDate() - 1);
            end.setDate(today.getDate() - 1);
            return { start_date: formatDate(start), end_date: formatDate(end) };
        case 'last7days':
            start.setDate(today.getDate() - 6);
            return { start_date: formatDate(start), end_date: formatDate(today) };
        case 'last30days':
            start.setDate(today.getDate() - 29);
            return { start_date: formatDate(start), end_date: formatDate(today) };
        case 'thismonth':
            start.setDate(1);
            return { start_date: formatDate(start), end_date: formatDate(today) };
        case 'lastmonth':
            start.setMonth(today.getMonth() - 1);
            start.setDate(1);
            end.setMonth(today.getMonth());
            end.setDate(0);
            return { start_date: formatDate(start), end_date: formatDate(end) };
        default:
            return { start_date: null, end_date: null };
    }
};

/*
 * Calendar Generation
 */
const generateCalendar = monthDate => {
    const year = monthDate.getFullYear();
    const month = monthDate.getMonth();
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const daysInMonth = lastDay.getDate();
    const startingDayOfWeek = firstDay.getDay();

    const calendar = [];
    let week = [];

    // Fill in previous month's days
    for (let i = 0; i < startingDayOfWeek; i++) {
        const prevMonthDay = new Date(year, month, -startingDayOfWeek + i + 1);
        week.push({ date: prevMonthDay, isCurrentMonth: false });
    }

    // Fill in current month's days
    for (let day = 1; day <= daysInMonth; day++) {
        const date = new Date(year, month, day);
        week.push({ date, isCurrentMonth: true });

        if (week.length === 7) {
            calendar.push(week);
            week = [];
        }
    }

    // Fill in next month's days
    if (week.length > 0) {
        const remainingDays = 7 - week.length;
        for (let i = 1; i <= remainingDays; i++) {
            const nextMonthDay = new Date(year, month + 1, i);
            week.push({ date: nextMonthDay, isCurrentMonth: false });
        }
        calendar.push(week);
    }

    return calendar;
};

const calendar1 = computed(() => generateCalendar(currentMonth1.value));
const calendar2 = computed(() => generateCalendar(currentMonth2.value));

const monthYearLabel1 = computed(() => {
    return currentMonth1.value.toLocaleString('en-US', { month: 'short', year: 'numeric' });
});

const monthYearLabel2 = computed(() => {
    return currentMonth2.value.toLocaleString('en-US', { month: 'short', year: 'numeric' });
});

/*
 * Calendar Navigation
 */
const prevMonth = () => {
    currentMonth1.value = new Date(
        currentMonth1.value.getFullYear(),
        currentMonth1.value.getMonth() - 1,
        1
    );
    currentMonth2.value = new Date(
        currentMonth2.value.getFullYear(),
        currentMonth2.value.getMonth() - 1,
        1
    );
};

const nextMonth = () => {
    currentMonth1.value = new Date(
        currentMonth1.value.getFullYear(),
        currentMonth1.value.getMonth() + 1,
        1
    );
    currentMonth2.value = new Date(
        currentMonth2.value.getFullYear(),
        currentMonth2.value.getMonth() + 1,
        1
    );
};

/*
 * Preset Selection
 */
const selectPreset = preset => {
    selectedPreset.value = preset;
    if (preset === 'custom') {
        tempStartDate.value = null;
        tempEndDate.value = null;
    } else {
        const dates = getPresetDates(preset);
        tempStartDate.value = dates.start_date;
        tempEndDate.value = dates.end_date;
    }
};

/*
 * Date Selection
 */
const selectDate = dateObj => {
    if (!dateObj.isCurrentMonth) return;

    const dateStr = formatDate(dateObj.date);

    if (!tempStartDate.value || (tempStartDate.value && tempEndDate.value)) {
        // Start new selection
        tempStartDate.value = dateStr;
        tempEndDate.value = null;
        selectedPreset.value = 'custom';
    } else if (tempStartDate.value && !tempEndDate.value) {
        // Complete the range
        const start = new Date(tempStartDate.value);
        const selected = new Date(dateStr);

        if (selected < start) {
            tempEndDate.value = tempStartDate.value;
            tempStartDate.value = dateStr;
        } else {
            tempEndDate.value = dateStr;
        }
    }
};

/*
 * Apply & Cancel
 */
const applyDateRange = () => {
    if (tempStartDate.value && tempEndDate.value) {
        emit('update:modelValue', {
            start_date: tempStartDate.value,
            end_date: tempEndDate.value,
        });
        isDropdownOpen.value = false;
    }
};

const cancelSelection = () => {
    tempStartDate.value = props.modelValue.start_date;
    tempEndDate.value = props.modelValue.end_date;
    isDropdownOpen.value = false;
};

/*
 * Display Text
 */
const displayText = computed(() => {
    if (props.modelValue.start_date && props.modelValue.end_date) {
        return `${formatDisplayDate(props.modelValue.start_date)} - ${formatDisplayDate(props.modelValue.end_date)}`;
    }
    return props.placeholder;
});

/*
 * Toggle Dropdown
 */
const toggleDropdown = () => {
    if (!isDropdownOpen.value) {
        tempStartDate.value = props.modelValue.start_date;
        tempEndDate.value = props.modelValue.end_date;

        // Set calendar months
        if (props.modelValue.start_date) {
            currentMonth1.value = new Date(props.modelValue.start_date);
        } else {
            currentMonth1.value = new Date();
        }
        currentMonth2.value = new Date(
            currentMonth1.value.getFullYear(),
            currentMonth1.value.getMonth() + 1,
            1
        );
    }
    isDropdownOpen.value = !isDropdownOpen.value;
};

/*
 * Clear Selection
 */
const clearSelection = event => {
    event.stopPropagation();
    emit('update:modelValue', { start_date: null, end_date: null });
    tempStartDate.value = null;
    tempEndDate.value = null;
    selectedPreset.value = '';
};

/*
 * Outside Click Handling
 */
const handleClickOutside = event => {
    if (dateRangeRef.value && !dateRangeRef.value.contains(event.target)) {
        isDropdownOpen.value = false;
    }
};

/*
 * Lifecycle
 */
onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <div class="date-range-filter" ref="dateRangeRef">
        <div class="date-range-input" @click="toggleDropdown">
            <span :class="{ 'text-muted': !modelValue.start_date }">
                {{ displayText }}
            </span>
            <div class="input-icons">
                <i
                    v-if="modelValue.start_date"
                    class="la la-times-circle text-muted"
                    @click.stop="clearSelection"
                    role="button"
                    title="Clear"
                ></i>
                <i class="la la-calendar"></i>
            </div>
        </div>

        <div v-if="isDropdownOpen" class="date-range-dropdown">
            <div class="date-range-content">
                <!-- Preset Options -->
                <div class="preset-list">
                    <button
                        type="button"
                        v-for="preset in presets"
                        :key="preset.value"
                        :class="['preset-item', { active: selectedPreset === preset.value }]"
                        @click="selectPreset(preset.value)"
                    >
                        {{ preset.label }}
                    </button>
                </div>

                <!-- Calendar View -->
                <div class="calendar-container">
                    <!-- Calendar Header -->
                    <div class="calendar-header">
                        <button type="button" class="nav-btn" @click="prevMonth">
                            <i class="la la-angle-left"></i>
                        </button>
                        <div class="month-labels">
                            <span class="month-label">{{ monthYearLabel1 }}</span>
                            <span class="month-label">{{ monthYearLabel2 }}</span>
                        </div>
                        <button type="button" class="nav-btn" @click="nextMonth">
                            <i class="la la-angle-right"></i>
                        </button>
                    </div>

                    <!-- Calendars -->
                    <div class="calendars">
                        <!-- Calendar 1 -->
                        <div class="calendar">
                            <div class="weekday-header">
                                <div class="weekday">Su</div>
                                <div class="weekday">Mo</div>
                                <div class="weekday">Tu</div>
                                <div class="weekday">We</div>
                                <div class="weekday">Th</div>
                                <div class="weekday">Fr</div>
                                <div class="weekday">Sa</div>
                            </div>
                            <div class="calendar-body">
                                <div
                                    v-for="(week, weekIdx) in calendar1"
                                    :key="weekIdx"
                                    class="week"
                                >
                                    <div
                                        v-for="(dayObj, dayIdx) in week"
                                        :key="dayIdx"
                                        :class="[
                                            'day',
                                            {
                                                'other-month': !dayObj.isCurrentMonth,
                                                selected:
                                                    isSameDay(dayObj.date, tempStartDate) ||
                                                    isSameDay(dayObj.date, tempEndDate),
                                                'in-range': isInRange(
                                                    dayObj.date,
                                                    tempStartDate,
                                                    tempEndDate
                                                ),
                                                today: isSameDay(dayObj.date, new Date()),
                                            },
                                        ]"
                                        @click="selectDate(dayObj)"
                                    >
                                        {{ dayObj.date.getDate() }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Calendar 2 -->
                        <div class="calendar">
                            <div class="weekday-header">
                                <div class="weekday">Su</div>
                                <div class="weekday">Mo</div>
                                <div class="weekday">Tu</div>
                                <div class="weekday">We</div>
                                <div class="weekday">Th</div>
                                <div class="weekday">Fr</div>
                                <div class="weekday">Sa</div>
                            </div>
                            <div class="calendar-body">
                                <div
                                    v-for="(week, weekIdx) in calendar2"
                                    :key="weekIdx"
                                    class="week"
                                >
                                    <div
                                        v-for="(dayObj, dayIdx) in week"
                                        :key="dayIdx"
                                        :class="[
                                            'day',
                                            {
                                                'other-month': !dayObj.isCurrentMonth,
                                                selected:
                                                    isSameDay(dayObj.date, tempStartDate) ||
                                                    isSameDay(dayObj.date, tempEndDate),
                                                'in-range': isInRange(
                                                    dayObj.date,
                                                    tempStartDate,
                                                    tempEndDate
                                                ),
                                                today: isSameDay(dayObj.date, new Date()),
                                            },
                                        ]"
                                        @click="selectDate(dayObj)"
                                    >
                                        {{ dayObj.date.getDate() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Selected Range Display -->
                    <div class="selected-range">
                        <span v-if="tempStartDate && tempEndDate">
                            {{ formatDisplayDate(tempStartDate) }} -
                            {{ formatDisplayDate(tempEndDate) }}
                        </span>
                        <span v-else class="text-muted">Select date range</span>
                    </div>

                    <!-- Action Buttons -->
                    <div class="action-buttons">
                        <button
                            type="button"
                            class="btn btn-outline-secondary"
                            @click="cancelSelection"
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            class="btn btn-danger"
                            @click="applyDateRange"
                            :disabled="!tempStartDate || !tempEndDate"
                        >
                            Apply
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.date-range-filter {
    position: relative;
}

.date-range-input {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 7px 8px;
    border: 1px solid var(--tblr-border-color);
    background-color: var(--tblr-bg-forms);
    cursor: pointer;
    transition: all 0.2s;
    min-height: 36px;
    font-size: 0.875rem;
}

.date-range-input:hover {
    border-color: var(--tblr-primary);
}

.date-range-input:focus-within {
    border-color: var(--tblr-primary);
    box-shadow: 0 0 0 0.2rem rgba(var(--tblr-primary-rgb), 0.25);
}

.date-range-input span {
    font-weight: 500;
    flex: 1;
}

.input-icons {
    display: flex;
    gap: 0.5rem;
    align-items: center;
    margin-left: 0.5rem;
}

.input-icons i {
    font-size: 1rem;
    color: var(--tblr-secondary);
    transition: color 0.2s;
}

.input-icons i:hover {
    color: var(--tblr-body-color);
}

.input-icons .la-times-circle {
    cursor: pointer;
}

.input-icons .la-times-circle:hover {
    color: var(--tblr-danger);
}

.date-range-dropdown {
    position: absolute;
    top: calc(100% + 5px);
    left: 0;
    z-index: 1000;
    background-color: var(--tblr-bg-surface);
    border: 1px solid var(--tblr-border-color);
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    min-width: 650px;
    box-shadow: 0 0 0 0.2rem rgba(var(--tblr-primary-rgb), 0.25);
}

.date-range-content {
    display: flex;
}

/* Preset List */
.preset-list {
    display: flex;
    flex-direction: column;
    border-right: 1px solid var(--tblr-border-color);
    min-width: 150px;
}

.preset-item {
    padding: 0.75rem 1rem;
    border: none;
    background: transparent;
    text-align: left;
    cursor: pointer;
    transition: background-color 0.2s;
    color: var(--tblr-body-color);
    font-size: 0.875rem;
}

.preset-item:hover {
    background-color: var(--tblr-secondary-bg);
}

.preset-item.active {
    background-color: var(--tblr-primary);
    color: white;
}

/* Calendar Container */
.calendar-container {
    flex: 1;
    padding: 1rem;
}

.calendar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.nav-btn {
    background: transparent;
    border: none;
    cursor: pointer;
    padding: 0.25rem 0.5rem;
    color: var(--tblr-body-color);
    font-size: 1.2rem;
}

.nav-btn:hover {
    color: var(--tblr-primary);
}

.month-labels {
    display: flex;
    gap: 4rem;
    flex: 1;
    justify-content: center;
}

.month-label {
    font-weight: 600;
    font-size: 0.875rem;
}

/* Calendars */
.calendars {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

.calendar {
    min-width: 220px;
}

.weekday-header {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 2px;
    margin-bottom: 0.5rem;
}

.weekday {
    text-align: center;
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--tblr-secondary);
    padding: 0.25rem;
}

.calendar-body {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.week {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 6px;
}

.day {
    aspect-ratio: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    border-radius: 4px;
    font-size: 0.875rem;
    transition: all 0.2s;
    color: var(--tblr-body-color);
}

.day:hover:not(.other-month) {
    background-color: var(--tblr-secondary-bg);
}

.day.other-month {
    color: var(--tblr-secondary);
    cursor: default;
}

.day.today {
    font-weight: 700;
    color: var(--tblr-primary);
}

.day.selected {
    background-color: var(--tblr-primary);
    color: white;
    font-weight: 600;
}

.day.in-range:not(.selected) {
    background-color: rgba(var(--tblr-primary-rgb), 0.1);
}

/* Selected Range Display */
.selected-range {
    text-align: center;
    padding: 1rem;
    margin-top: 1rem;
    border-top: 1px solid var(--tblr-border-color);
    font-size: 0.875rem;
    font-weight: 500;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    padding-top: 0.75rem;
    border-top: 1px solid var(--tblr-border-color);
}

.action-buttons .btn {
    padding: 0.375rem 1rem;
    font-size: 0.875rem;
}
</style>
