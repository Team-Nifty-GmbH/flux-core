import _ from 'lodash';
import axios from 'axios';
import Echo from 'laravel-echo'
import Pusher from 'pusher-js';
import Tribute from 'tributejs';

window._ = _;

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

window.Tribute = Tribute;

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: document.head.querySelector('meta[name="pusher-key"]').content,
    cluster: document.head.querySelector('meta[name="pusher-cluster"]').content,
    wsHost: window.location.hostname, // <-- important if you dont build the js file on the prod server
    wsPort: 80, // <-- this ensures that nginx will receive the request
    wssPort: 443, // <-- this ensures that nginx will receive the request
    forceTLS: window.location.protocol === 'https:',
    disableStats: true,
    enabledTransports: ['ws', 'wss'],
});

window.parseNumber = function (number) {
    let parsedNumber = parseFloat(number);
    if (isNaN(parsedNumber)) {
        parsedNumber = 0;
    }
    const trimmedNumber = parsedNumber.toString();
    const decimalIndex = trimmedNumber.indexOf('.');

    if (decimalIndex !== -1) {
        let parsedNumber = trimmedNumber;
        while (parsedNumber.endsWith('0')) {
            parsedNumber = parsedNumber.slice(0, -1);
        }
        if (parsedNumber.endsWith('.')) {
            parsedNumber = parsedNumber.slice(0, -1);
        }
        if (parsedNumber.includes('.') && parsedNumber.split('.')[1].length < 2) {
            parsedNumber = parsedNumber + '0';
        }
        return parsedNumber;
    }

    return trimmedNumber + '.00';
}

window.$openDetailModal = (url, hideNavigation = true) => {
    let urlObj = new URL(url);
    urlObj.searchParams.set('no-navigation', hideNavigation === true ? 'true' : 'false');

    document.getElementById('detail-modal-embed').src = urlObj.href;
    $openModal('detail-modal');
}
