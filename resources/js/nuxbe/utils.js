export function parseNumber(number) {
    let parsed = parseFloat(number);
    if (isNaN(parsed)) parsed = 0;

    const str = parsed.toString();
    const decimalIndex = str.indexOf('.');

    if (decimalIndex === -1) return str + '.00';

    let trimmed = str;
    while (trimmed.endsWith('0')) trimmed = trimmed.slice(0, -1);
    if (trimmed.endsWith('.')) trimmed = trimmed.slice(0, -1);
    if (trimmed.includes('.') && trimmed.split('.')[1].length < 2)
        trimmed += '0';

    return trimmed;
}

export function openDetailModal(url, hideNavigation = true) {
    const urlObj = new URL(url);

    if (!urlObj.searchParams.has('signature')) {
        urlObj.searchParams.set(
            'no-navigation',
            hideNavigation ? 'true' : 'false',
        );
    }

    document.getElementById('detail-modal-iframe').src = urlObj.href;
    window.$tsui.open.modal('detail-modal');
}
