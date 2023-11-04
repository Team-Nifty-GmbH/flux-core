const swReady = navigator.serviceWorker.ready;

document.addEventListener('DOMContentLoaded', function () {
    initSW();
});

window.initSW = function initSW() {
    if (!"serviceWorker" in navigator) {
        return;
    }

    if (!"PushManager" in window) {
        return;
    }

    let url = document.getElementById('service-worker').getAttribute('href');
    url = new URL(url).pathname
    navigator.serviceWorker.register(
        '/service-worker.js'
    )
        .then(() => {
            initPush();
        });
}

function initPush() {
    if (!swReady) {
        return;
    }

    new Promise(function (resolve, reject) {
        const permissionResult = Notification.requestPermission(function (result) {
            resolve(result);
        });

        if (permissionResult) {
            permissionResult.then(resolve, reject);
        }
    })
        .then((permissionResult) => {
            if (permissionResult !== 'granted') {
                return;
            }
            subscribeUser();
        });
}

/**
 * Subscribe the user to push
 */
function subscribeUser() {
    swReady
        .then((registration) => {
            const subscribeOptions = {
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(
                    document.head.querySelector('meta[name="webpush-key"]').content
                )
            };

            return registration.pushManager.subscribe(subscribeOptions);
        })
        .then((pushSubscription) => {
            storePushSubscription(pushSubscription);
        })
        .catch((err) => {
            console.log('Failed to subscribe the user: ', err);
        });
}

/**
 * send PushSubscription to server with AJAX.
 * @param {object} pushSubscription
 */
function storePushSubscription(pushSubscription) {
    const token = document.querySelector('meta[name=csrf-token]').getAttribute('content');

    fetch('/push-subscription', {
        method: 'POST',
        body: JSON.stringify(pushSubscription),
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-Token': token
        }
    })
        .then((res) => {
            return res.json();
        });
}

/**
 * urlBase64ToUint8Array
 *
 * @param {string} base64String a public vapid key
 */
function urlBase64ToUint8Array(base64String) {
    let padding = '='.repeat((4 - base64String.length % 4) % 4);
    let base64 = (base64String + padding)
        .replace(/-/g, '+')
        .replace(/_/g, '/');

    let rawData = window.atob(base64);
    let outputArray = new Uint8Array(rawData.length);

    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}
