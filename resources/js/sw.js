self.addEventListener('install', function(event) {
    self.skipWaiting();
});

self.addEventListener('activate', function(event) {
    event.waitUntil(clients.claim());
});

self.addEventListener('push', function (e) {
    if (!(self.Notification && self.Notification.permission === 'granted')) {
        return;
    }

    if (!e.data) {
        return;
    }

    let msg = e.data.json();

    // Extract the notification options properly
    const title = msg.title || 'Notification';
    const options = {};

    // Only add defined values to options (according to MDN spec)
    if (msg.body || msg.message) options.body = msg.body || msg.message;
    if (msg.icon) options.icon = msg.icon;
    if (msg.badge) options.badge = msg.badge;
    if (msg.data) options.data = msg.data;
    if (msg.tag) options.tag = msg.tag;
    if (msg.dir) options.dir = msg.dir;
    if (msg.lang) options.lang = msg.lang;
    if (msg.requireInteraction === true) options.requireInteraction = true;
    if (msg.renotify === true) options.renotify = true;
    if (msg.silent === true) options.silent = true;
    if (msg.vibrate) options.vibrate = msg.vibrate;
    if (msg.image) options.image = msg.image;
    if (msg.actions && Array.isArray(msg.actions))
        options.actions = msg.actions;
    if (msg.timestamp) options.timestamp = msg.timestamp;

    e.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', function (e) {
    e.notification.close();

    let targetPath = null;
    let actionToUse = e.action;

    if (
        !actionToUse &&
        e.notification.actions &&
        e.notification.actions.length > 0
    ) {
        actionToUse = e.notification.actions[0].action;
    }

    if (actionToUse) {
        if (
            actionToUse.startsWith('http://') ||
            actionToUse.startsWith('https://')
        ) {
            targetPath = actionToUse;
        } else if (actionToUse.startsWith('/')) {
            targetPath = actionToUse;
        }
    } else if (e.notification.data && e.notification.data.url) {
        targetPath = e.notification.data.url;
    }

    if (!targetPath) {
        targetPath = '/';
    }

    let targetUrl;
    try {
        if (
            targetPath.startsWith('http://') ||
            targetPath.startsWith('https://')
        ) {
            targetUrl = new URL(targetPath);
        } else {
            targetUrl = new URL(targetPath, self.location.origin);
        }
    } catch (error) {
        targetUrl = new URL('/', self.location.origin);
    }

    const finalUrl = targetUrl.href;

    e.waitUntil(
        clients.matchAll({ type: 'window' }).then(function (clientList) {
            for (let client of clientList) {
                if (
                    client.url.startsWith(self.location.origin) &&
                    'focus' in client
                ) {
                    return client.focus().then(() => {
                        const targetIsRoot =
                            finalUrl === self.location.origin + '/';
                        const alreadyOnTarget = client.url === finalUrl;

                        if (!targetIsRoot && !alreadyOnTarget) {
                            return client.navigate(finalUrl);
                        }
                    });
                }
            }

            if (clients.openWindow) {
                return clients.openWindow(finalUrl);
            }
        }),
    );
});
