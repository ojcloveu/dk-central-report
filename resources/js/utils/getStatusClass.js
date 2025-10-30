export const getMasterBadgeClass = master => {
    switch (master) {
        case 'VIRTUAL':
            return 'bg-primary-lt';
        case 'ESPORTS':
            return 'bg-success-lt';
        case 'LOTTERY':
            return 'bg-warning-lt';
        case 'POKER':
            return 'bg-info-lt';
        case 'SLOTS':
            return 'bg-purple-lt';
        default:
            return 'bg-secondary-lt';
    }
};

export const getLpClass = lp => {
    const value = parseFloat(String(lp).replace('%', '').replace('$', '').replace(',', '')) || 0;
    if (value < 0) return 'text-danger';
    if (value > 0) return 'text-success';
    return '';
};
