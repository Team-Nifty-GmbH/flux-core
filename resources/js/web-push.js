window.WebPush = (function () {
    'use strict';

    async function checkCurrentSubscription(endpoint) {
        try {
            if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
                return false;
            }

            const registration =
                await navigator.serviceWorker.getRegistration();
            if (!registration) {
                return false;
            }

            const subscription =
                await registration.pushManager.getSubscription();
            if (!subscription) {
                return false;
            }

            return subscription.endpoint === endpoint;
        } catch (error) {
            console.error('Error checking current subscription:', error);
            return false;
        }
    }

    async function checkWebPushSupport() {
        const support = {
            serviceWorker: 'serviceWorker' in navigator,
            pushManager: 'PushManager' in window,
            notification: 'Notification' in window,
            https:
                window.location.protocol === 'https:' ||
                window.location.hostname === 'localhost' ||
                window.location.hostname === '127.0.0.1',
            currentBrowserSubscribed: false,
        };

        support.allSupported =
            support.serviceWorker &&
            support.pushManager &&
            support.notification &&
            support.https;

        if (support.serviceWorker && support.pushManager) {
            try {
                const registration =
                    await navigator.serviceWorker.getRegistration();
                if (registration) {
                    const subscription =
                        await registration.pushManager.getSubscription();
                    support.currentBrowserSubscribed = subscription !== null;
                }
            } catch (err) {
                console.log('Could not check subscription status:', err);
            }
        }

        return support;
    }

    async function initSW(forceResubscribe = false) {
        const support = await checkWebPushSupport();

        if (!support.https) {
            const message = 'Web Push requires HTTPS connection (or localhost)';
            if (window.Livewire) {
                window.Livewire.dispatch('push-error', { message });
            }
            return Promise.reject(message);
        }

        if (!support.serviceWorker) {
            const message = 'Service Workers are not supported in this browser';
            if (window.Livewire) {
                window.Livewire.dispatch('push-error', { message });
            }
            return Promise.reject(message);
        }

        if (!support.pushManager) {
            const message =
                'Push notifications are not supported in this browser';
            if (window.Livewire) {
                window.Livewire.dispatch('push-error', { message });
            }
            return Promise.reject(message);
        }

        if (!support.notification) {
            const message = 'Notifications are not supported in this browser';
            if (window.Livewire) {
                window.Livewire.dispatch('push-error', { message });
            }
            return Promise.reject(message);
        }

        let registration = await navigator.serviceWorker.getRegistration();

        if (!registration) {
            let url = '/pwa-service-worker';
            try {
                registration = await navigator.serviceWorker.register(url);
            } catch (error) {
                console.error('Service Worker registration failed:', error);
                const message =
                    'Failed to register Service Worker. Please check your browser settings.';
                if (window.Livewire) {
                    window.Livewire.dispatch('push-error', { message });
                }
                throw error;
            }
        }

        const existingSubscription =
            await registration.pushManager.getSubscription();
        if (existingSubscription && !forceResubscribe) {
            if (window.Livewire) {
                window.Livewire.dispatch('push-subscription-updated');
            }
            return existingSubscription;
        }

        if (existingSubscription && forceResubscribe) {
            try {
                await existingSubscription.unsubscribe();
            } catch (error) {
                console.error('Error unsubscribing:', error);
            }
        }

        return initPush();
    }

    async function initPush() {
        await navigator.serviceWorker.ready;

        return new Promise(function (resolve, reject) {
            const permissionResult = Notification.requestPermission(
                function (result) {
                    resolve(result);
                },
            );

            if (permissionResult) {
                permissionResult.then(resolve, reject);
            }
        }).then((permissionResult) => {
            if (permissionResult === 'denied') {
                const message =
                    'Notification permission was denied. Please enable notifications in your browser settings.';
                if (window.Livewire) {
                    window.Livewire.dispatch('push-error', { message });
                }
                return Promise.reject(message);
            }
            if (permissionResult !== 'granted') {
                const message = 'Notification permission was not granted';
                if (window.Livewire) {
                    window.Livewire.dispatch('push-error', { message });
                }
                return Promise.reject(message);
            }
            return subscribeUser();
        });
    }

    function subscribeUser() {
        return navigator.serviceWorker.ready
            .then((registration) => {
                const subscribeOptions = {
                    userVisibleOnly: true,
                    applicationServerKey: urlBase64ToUint8Array(
                        document.head.querySelector('meta[name="webpush-key"]')
                            .content,
                    ),
                };

                return registration.pushManager.subscribe(subscribeOptions);
            })
            .then((pushSubscription) => {
                return storePushSubscription(pushSubscription);
            })
            .catch((err) => {
                console.error('Failed to subscribe the user: ', err);
                const message =
                    'Failed to subscribe to push notifications. Please try again.';
                if (window.Livewire) {
                    window.Livewire.dispatch('push-error', { message });
                }
                throw err;
            });
    }

    /**
     * @param {object} pushSubscription
     */
    function storePushSubscription(pushSubscription) {
        const token = document
            .querySelector('meta[name=csrf-token]')
            .getAttribute('content');

        return fetch('/push-subscription', {
            method: 'POST',
            body: JSON.stringify(pushSubscription),
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-Token': token,
            },
        })
            .then((res) => {
                return res.json();
            })
            .then((data) => {
                if (window.Livewire) {
                    window.Livewire.dispatch('push-subscription-updated');
                }
                return data;
            })
            .catch((error) => {
                console.error('Failed to store push subscription:', error);
                throw error;
            });
    }

    /**
     * @param {string} base64String a public vapid key
     */
    function urlBase64ToUint8Array(base64String) {
        let padding = '='.repeat((4 - (base64String.length % 4)) % 4);
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

    return {
        checkCurrentSubscription,
        checkWebPushSupport,
        initSW,
    };
})();
