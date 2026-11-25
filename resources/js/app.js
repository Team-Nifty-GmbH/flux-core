import axios from 'axios';
import Echo from 'laravel-echo';
import {
    browserSupportsWebAuthn,
    startAuthentication,
    startRegistration,
} from '@simplewebauthn/browser';
import nuxbeAppBridge, {
    loadSharedFiles,
    clearSharedFiles,
} from './nuxbe-bridge.js';

// Import all modules into single bundle
import './components/alpine.js';
import './components/apex-charts.js';
import './components/data-table-floating-head.js';
import './components/widget-live-updates.js';

window.nuxbeAppBridge = nuxbeAppBridge;
window.nuxbeShareTarget = { loadSharedFiles, clearSharedFiles };
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

window.Echo =
    document.head.querySelector('meta[name="ws-broadcaster"]')?.content ===
    'mercure'
        ? new Echo({
              broadcaster: 'mercure',
              host:
                  document.head.querySelector('meta[name="ws-mercure-hub"]')
                      ?.content || undefined,
          })
        : new Echo({ broadcaster: 'null' });
