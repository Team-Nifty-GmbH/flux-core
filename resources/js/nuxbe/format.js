const badgeClasses = {
    primary:
        'text-primary-600 bg-primary-100 dark:text-primary-400 dark:bg-slate-700',
    secondary:
        'text-secondary-600 bg-secondary-100 dark:text-secondary-400 dark:bg-slate-700',
    slate: 'text-slate-600 bg-slate-100 dark:text-slate-400 dark:bg-slate-700',
    gray: 'text-gray-600 bg-gray-100 dark:text-gray-400 dark:bg-slate-700',
    zinc: 'text-zinc-600 bg-zinc-100 dark:text-zinc-400 dark:bg-slate-700',
    neutral:
        'text-neutral-600 bg-neutral-100 dark:text-neutral-400 dark:bg-slate-700',
    stone: 'text-stone-600 bg-stone-100 dark:text-stone-400 dark:bg-slate-700',
    red: 'text-red-600 bg-red-100 dark:text-red-400 dark:bg-slate-700',
    orange: 'text-orange-600 bg-orange-100 dark:text-orange-400 dark:bg-slate-700',
    amber: 'text-amber-600 bg-amber-100 dark:text-amber-400 dark:bg-slate-700',
    yellow: 'text-yellow-600 bg-yellow-100 dark:text-yellow-400 dark:bg-slate-700',
    lime: 'text-lime-600 bg-lime-100 dark:text-lime-400 dark:bg-slate-700',
    green: 'text-green-600 bg-green-100 dark:text-green-400 dark:bg-slate-700',
    emerald:
        'text-emerald-600 bg-emerald-100 dark:text-emerald-400 dark:bg-slate-700',
    teal: 'text-teal-600 bg-teal-100 dark:text-teal-400 dark:bg-slate-700',
    cyan: 'text-cyan-600 bg-cyan-100 dark:text-cyan-400 dark:bg-slate-700',
    sky: 'text-sky-600 bg-sky-100 dark:text-sky-400 dark:bg-slate-700',
    blue: 'text-blue-600 bg-blue-100 dark:text-blue-400 dark:bg-slate-700',
    indigo: 'text-indigo-600 bg-indigo-100 dark:text-indigo-400 dark:bg-slate-700',
    violet: 'text-violet-600 bg-violet-100 dark:text-violet-400 dark:bg-slate-700',
    purple: 'text-purple-600 bg-purple-100 dark:text-purple-400 dark:bg-slate-700',
    fuchsia:
        'text-fuchsia-600 bg-fuchsia-100 dark:text-fuchsia-400 dark:bg-slate-700',
    pink: 'text-pink-600 bg-pink-100 dark:text-pink-400 dark:bg-slate-700',
    rose: 'text-rose-600 bg-rose-100 dark:text-rose-400 dark:bg-slate-700',
};

function getLocale() {
    return document.documentElement.lang || 'de';
}

function getCurrencyCode() {
    return (
        document
            .querySelector('meta[name="currency-code"]')
            ?.getAttribute('content') ||
        document.body?.dataset?.currencyCode ||
        'EUR'
    );
}

function getTimezone() {
    return (
        document
            .querySelector('meta[name="timezone"]')
            ?.getAttribute('content') || undefined
    );
}

export function money(value, options = {}) {
    if (value === null || value === undefined) return '';

    const colored = options.colored || false;
    const currency = options.currency || getCurrencyCode();

    let formatted;
    try {
        formatted = new Intl.NumberFormat(getLocale(), {
            style: 'currency',
            currency: currency,
            minimumFractionDigits: 2,
        }).format(value);
    } catch {
        formatted = float(value) + ' ' + currency;
    }

    if (!colored) return formatted;

    const color =
        value < 0
            ? 'text-red-500 dark:text-red-700'
            : 'text-emerald-500 dark:text-emerald-700';

    return `<span class="${color} font-semibold">${formatted}</span>`;
}

export function percentage(value) {
    if (value === null || value === undefined) return '';

    return new Intl.NumberFormat(getLocale(), {
        style: 'percent',
        minimumFractionDigits: 2,
    }).format(value);
}

export function date(value) {
    if (!value) return '';

    const options = { year: 'numeric', month: '2-digit', day: '2-digit' };
    const tz = getTimezone();
    if (tz) options.timeZone = tz;

    return new Date(value).toLocaleDateString(getLocale(), options);
}

export function datetime(value) {
    if (!value) return '';

    const options = {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    };
    const tz = getTimezone();
    if (tz) options.timeZone = tz;

    return new Date(value).toLocaleString(getLocale(), options);
}

export function relativeTime(value) {
    if (!value) return '';

    const elapsed =
        Date.now() -
        (typeof value === 'number' ? value : new Date(value).getTime());
    const seconds = elapsed / 1000;
    const formatter = new Intl.RelativeTimeFormat(getLocale(), {
        style: 'narrow',
    });

    if (seconds < 10) return 'now';
    if (seconds < 60) return formatter.format(-Math.round(seconds), 'second');

    const minutes = seconds / 60;
    if (minutes < 60) return formatter.format(-Math.round(minutes), 'minute');

    const hours = minutes / 60;
    if (hours < 24) return formatter.format(-Math.round(hours), 'hour');

    const days = hours / 24;
    if (days < 7) return formatter.format(-Math.round(days), 'day');

    const weeks = days / 7;
    if (weeks < 4) return formatter.format(-Math.round(weeks), 'week');

    const months = days / 30;
    if (months < 12) return formatter.format(-Math.round(months), 'month');

    return formatter.format(-Math.round(days / 365), 'year');
}

export function badge(value, colors) {
    if (!value) return '';

    const color =
        (typeof colors === 'object' ? colors[value] : colors) || 'neutral';
    const classes = badgeClasses[color] || badgeClasses.neutral;

    return `<span class="outline-none inline-flex justify-center items-center group rounded gap-x-1 text-xs font-semibold px-2.5 py-0.5 ${classes}">${value}</span>`;
}

export function state(label, colors) {
    return badge(label, colors);
}

export function fileSize(bytes) {
    if (bytes === null || bytes === undefined) return '';

    const units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
    if (bytes <= 0) return '0B';

    let i = 0;
    let size = bytes;
    while (size >= 1024 && i < units.length - 1) {
        size /= 1024;
        i++;
    }

    const str = size.toFixed(2);
    if (str.endsWith('.00')) return str.slice(0, -3) + units[i];
    if (str.endsWith('0')) return str.slice(0, -1) + units[i];

    return str + units[i];
}

export function int(value) {
    return parseInt(value);
}

export function float(value) {
    if (isNaN(parseFloat(value))) return value;

    try {
        return parseFloat(value).toLocaleString(getLocale());
    } catch {
        return parseFloat(value);
    }
}
