export function formatMoney(
    value: number,
    locale = 'id-ID',
    currency = 'IDR',
): string {
    return new Intl.NumberFormat(locale, {
        style: 'currency',
        currency,
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value);
}

export function formatNumber(value: number, locale = 'en-US'): string {
    return new Intl.NumberFormat(locale).format(value);
}

export function formatDateTime(
    value: string | Date,
    dateStyle: 'medium' | 'long' = 'medium',
    locale = 'en-US',
): string {
    return new Intl.DateTimeFormat(locale, {
        dateStyle,
        timeStyle: 'short',
    }).format(new Date(value));
}
