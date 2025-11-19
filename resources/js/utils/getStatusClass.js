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

export const lpBgColor = lp => {
    const color = lp && lp?.color ? lp.color.toLowerCase() : 'secondary';
    return `fw-bold bg-${color}-lt`;
};

export const amountColor = amount => {
    if (String(amount).startsWith('-')) return 'fw-bold text-danger';
    else return 'fw-bold';
};
