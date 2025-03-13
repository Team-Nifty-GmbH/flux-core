self.addEventListener("push", function (e) {
    if (!(self.Notification && self.Notification.permission === "granted")) {
        return;
    }

    if (e.data) {
        let msg = e.data.json();
        e.waitUntil(self.registration.showNotification(msg.title, msg));
    }
});

self.addEventListener("notificationclick", function (e) {
    e.notification.close();

    if (e.notification.data.url) {
        clients.openWindow(e.notification.data.url);
    }
});
