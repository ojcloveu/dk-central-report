/**
 * Date Utilities
 * Reusable date formatting, comparison, and calendar generation functions
 */

/**
 * Formats a date to YYYY-MM-DD format
 */
export const formatDate = date => {
    if (!date) return null;
    const d = new Date(date);
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

/**
 * Formats a date for display (e.g., "22 Nov 25")
 */
export const formatDisplayDate = date => {
    if (!date) return '';
    const d = new Date(date);
    const day = String(d.getDate()).padStart(2, '0');
    const month = d.toLocaleString('en-US', { month: 'short' });
    const year = String(d.getFullYear()).slice(-2);
    return `${day} ${month} ${year}`;
};

/**
 * Checks if two dates are the same day
 * @param {Date|string} date1 - First date
 */
export const isSameDay = (date1, date2) => {
    if (!date1 || !date2) return false;
    const d1 = new Date(date1);
    const d2 = new Date(date2);
    return (
        d1.getFullYear() === d2.getFullYear() &&
        d1.getMonth() === d2.getMonth() &&
        d1.getDate() === d2.getDate()
    );
};

/**
 * Checks if a date is within a range (inclusive)
 */
export const isInRange = (date, start, end) => {
    if (!start || !end) return false;
    const d = new Date(date);
    const s = new Date(start);
    const e = new Date(end);
    return d >= s && d <= e;
};

/**
 * Gets date range for preset options
 */
export const getPresetDates = preset => {
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

/**
 * Generates a calendar grid for a given month
 */
export const generateCalendar = monthDate => {
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

/**
 * Gets month and year label for display
 */
export const getMonthYearLabel = date => {
    return date.toLocaleString('en-US', { month: 'short', year: 'numeric' });
};
