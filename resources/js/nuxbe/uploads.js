import { getLocale } from './format.js';

export function formatFileSize(bytes) {
    const units = ['B', 'KB', 'MB', 'GB'];
    let size = Number(bytes) || 0;
    let unit = 0;

    while (size >= 1024 && unit < units.length - 1) {
        size /= 1024;
        unit++;
    }

    const decimals = size < 10 && unit > 0 ? 1 : 0;

    const formatted = new Intl.NumberFormat(getLocale(), {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
    }).format(size);

    return `${formatted} ${units[unit]}`;
}

export function escapeHtml(value) {
    return String(value ?? '').replace(
        /[&<>"']/g,
        (character) =>
            ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;',
            })[character],
    );
}

function parseAccept(accept) {
    return accept
        .split(/[,;]/)
        .map((pattern) => pattern.trim().toLowerCase())
        .filter(Boolean);
}

function matchesAccept(file, patterns) {
    const name = (file.name || '').toLowerCase();
    const type = (file.type || '').toLowerCase();

    if (!type) {
        const extensions = patterns.filter((pattern) =>
            pattern.startsWith('.'),
        );

        return (
            extensions.length === 0 ||
            extensions.some((pattern) => name.endsWith(pattern))
        );
    }

    return patterns.some((pattern) => {
        if (pattern.startsWith('.')) {
            return name.endsWith(pattern);
        }

        if (pattern.endsWith('/*')) {
            return type.startsWith(pattern.slice(0, -1));
        }

        return type === pattern;
    });
}

export function validateFiles(files, { maxSize = 0, accept = '' } = {}) {
    const patterns = accept ? parseAccept(accept) : [];

    for (const file of Array.from(files ?? [])) {
        if (maxSize > 0 && file.size > maxSize) {
            return {
                file,
                reason: 'size',
                size: formatFileSize(file.size),
                maxSize: formatFileSize(maxSize),
            };
        }

        if (patterns.length && !matchesAccept(file, patterns)) {
            return {
                file,
                reason: 'type',
                accept: patterns.join(', '),
            };
        }
    }

    return null;
}
