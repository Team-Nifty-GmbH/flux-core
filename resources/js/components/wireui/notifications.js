// import the original module
import { timer } from '@/notifications/timer'
import uuid from '@/utils/uuid'
import originalNotifications from '@/components/notifications';

const customNotifications = () => {
    const notifications = originalNotifications();

    return {
        ...notifications,
        proccessNotification(notification) {
            notification.id = notification.id ?? uuid();

            if (notification.timeout) {
                notification.timer = timer(
                    notification.timeout,
                    () => {
                        notification.onClose();
                        notification.onTimeout();
                        this.removeNotification(notification.id);
                    },
                    (percentage) => {
                        const progressBar = document.getElementById(`timeout.bar.${notification.id}`);

                        if (!progressBar) return;

                        progressBar.style.width = `${percentage}%`;
                    }
                );
            }

            // If the notifications array has a notification with the same id, update it
            const index = this.notifications.findIndex((item) => item.id === notification.id);
            if (~index) {
                this.notifications.splice(index, 1, notification);
            } else {
                this.notifications.push(notification);
            }

            if (notification.icon) {
                this.$nextTick(() => {
                    this.fillNotificationIcon(notification);
                });
            }
        },
    };
};

export default customNotifications;
