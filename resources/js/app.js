import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import nuxbeAppBridge from './nuxbe-bridge.js';

// Import all modules into single bundle
import './components/alpine.js';
import './components/apex-charts.js';
import '../../vendor/team-nifty-gmbh/tall-datatables/resources/js/tall-datatables.js';

window.nuxbeAppBridge = nuxbeAppBridge;

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

window.Pusher = Pusher;

const broadcaster =
    document.head.querySelector('meta[name="ws-broadcaster"]')?.content ||
    'reverb';
const key = document.head.querySelector('meta[name="ws-key"]')?.content;

const effectiveBroadcaster =
    broadcaster === 'log' || !key ? 'null' : broadcaster;

const createBatchAuthorizer = () => {
    let pendingChannels = [];
    let pendingCallbacks = {};
    let batchTimeout = null;
    const BATCH_DELAY = 75;

    const processBatch = () => {
        if (pendingChannels.length === 0) return;

        const channelsToProcess = [...pendingChannels];
        const callbacksToProcess = { ...pendingCallbacks };
        pendingChannels = [];
        pendingCallbacks = {};
        batchTimeout = null;

        const socketId = channelsToProcess[0]?.socketId;

        const uniqueChannels = [
            ...new Map(
                channelsToProcess.map((c) => [c.channelName, c]),
            ).values(),
        ];

        axios
            .post('/broadcasting/auth/batch', {
                socket_id: socketId,
                channels: uniqueChannels.map((c) => ({
                    name: c.channelName,
                    socket_id: c.socketId,
                })),
            })
            .then((response) => {
                Object.entries(response.data).forEach(
                    ([channelName, authData]) => {
                        const callbacks = callbacksToProcess[channelName] || [];
                        callbacks.forEach((callback) => {
                            if (authData.status) {
                                callback(true, authData);
                            } else {
                                callback(null, authData);
                            }
                        });
                    },
                );
            })
            .catch((error) => {
                Object.values(callbacksToProcess)
                    .flat()
                    .forEach((callback) => {
                        callback(true, error);
                    });
            });
    };

    return (channel, options) => {
        return {
            authorize: (socketId, callback) => {
                pendingChannels.push({
                    channelName: channel.name,
                    socketId: socketId,
                });

                if (!pendingCallbacks[channel.name]) {
                    pendingCallbacks[channel.name] = [];
                }
                pendingCallbacks[channel.name].push(callback);

                if (batchTimeout) {
                    clearTimeout(batchTimeout);
                }
                batchTimeout = setTimeout(processBatch, BATCH_DELAY);
            },
        };
    };
};

window.Echo = new Echo({
    broadcaster: effectiveBroadcaster,
    key: key || 'dummy-key',
    wsHost:
        document.head.querySelector('meta[name="ws-host"]')?.content ||
        window.location.hostname,
    wsPort: document.head.querySelector('meta[name="ws-port"]')?.content || 80,
    wssPort:
        document.head.querySelector('meta[name="ws-port"]')?.content || 443,
    forceTLS:
        document.head.querySelector('meta[name="ws-protocol"]')?.content ===
        'https',
    enabledTransports: ['ws', 'wss'],
    authorizer: createBatchAuthorizer(),
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
        if (
            parsedNumber.includes('.') &&
            parsedNumber.split('.')[1].length < 2
        ) {
            parsedNumber = parsedNumber + '0';
        }
        return parsedNumber;
    }

    return trimmedNumber + '.00';
};

window.fileSizeHumanReadable = function (sizeBytes) {
    if (sizeBytes === null || sizeBytes === undefined) {
        return null;
    }

    const units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

    if (sizeBytes <= 0) {
        return '0B';
    }

    let i = 0;
    while (sizeBytes >= 1024 && i < units.length - 1) {
        sizeBytes /= 1024;
        i++;
    }

    const sizeStr = sizeBytes.toFixed(2);

    if (sizeStr.endsWith('.00')) {
        return sizeStr.slice(0, -3) + units[i];
    } else if (sizeStr.endsWith('0')) {
        return sizeStr.slice(0, -1) + units[i];
    }

    return sizeStr + units[i];
};

window.$openDetailModal = (url, hideNavigation = true) => {
    let urlObj = new URL(url);
    urlObj.searchParams.set(
        'no-navigation',
        hideNavigation === true ? 'true' : 'false',
    );

    document.getElementById('detail-modal-iframe').src = urlObj.href;
    $modalOpen('detail-modal');
};
