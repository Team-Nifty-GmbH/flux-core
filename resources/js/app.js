import _ from 'lodash';
import axios from 'axios';
// import Echo from 'laravel-echo'
// import Pusher from 'pusher-js';

window._ = _;

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// window.Pusher = Pusher;
//
// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: import.meta.env.VITE_PUSHER_APP_KEY,
//     cluster:import.meta.env.VITE_PUSHER_APP_CLUSTER,
//     wsHost: import.meta.env.VITE_PUSHER_HOST ?? window.location.hostname,
//     forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
//     wsPort: import.meta.env.VITE_PUSHER_PORT ?? 6001,
//     wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
//     enabledTransports: ['ws', 'wss'],
//     disableStats: true,
// });
