import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import {
    browserSupportsWebAuthn,
    startAuthentication,
    startRegistration,
} from '@simplewebauthn/browser';
import nuxbeAppBridge from './nuxbe-bridge.js';

// Import all modules into single bundle
import './components/alpine.js';
import './components/apex-charts.js';

window.nuxbeAppBridge = nuxbeAppBridge;
window.browserSupportsWebAuthn = browserSupportsWebAuthn;
window.startAuthentication = startAuthentication;
window.startRegistration = startRegistration;

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
