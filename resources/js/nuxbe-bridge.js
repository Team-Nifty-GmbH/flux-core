const getBaseUrl = () => {
    if (!window.Capacitor?.isNativePlatform?.()) return '/';
    const platform = window.Capacitor.getPlatform();
    return platform === 'android' ? 'http://localhost' : 'capacitor://localhost';
};

const initializeNativeBridge = async () => {
    try {
        if (!window.Capacitor?.isNativePlatform?.()) {
            return;
        }

        const { Preferences } = window.Capacitor.Plugins;
        const { PushNotifications } = window.Capacitor.Plugins;
        const { App } = window.Capacitor.Plugins;
        const { Camera } = window.Capacitor.Plugins;

        window.nativeBridge = {
            isNative: () => window.Capacitor?.isNativePlatform?.() || false,
            getPlatform: () => window.Capacitor?.getPlatform() || 'web',

            getPreference: async (key) => {
                return await Preferences.get({ key });
            },

            setPreference: async (key, value) => {
                await Preferences.set({ key, value });
            },

            removePreference: async (key) => {
                await Preferences.remove({ key });
            },

            changeServer: async () => {
                window.location.href = getBaseUrl() + '/?reset=1';
                return { success: true };
            },

            capturePhoto: async () => {
                try {
                    const image = await Camera.getPhoto({
                        quality: 90,
                        allowEditing: false,
                        resultType: 'base64',
                        source: 'camera'
                    });

                    return {
                        success: true,
                        base64: image.base64String,
                        format: image.format
                    };
                } catch (error) {
                    console.error('Camera capture failed:', error);
                    return {
                        success: false,
                        error: error.message
                    };
                }
            },

            pickPhoto: async () => {
                try {
                    const image = await Camera.getPhoto({
                        quality: 90,
                        allowEditing: false,
                        resultType: 'base64',
                        source: 'photos'
                    });

                    return {
                        success: true,
                        base64: image.base64String,
                        format: image.format
                    };
                } catch (error) {
                    console.error('Photo picker failed:', error);
                    return {
                        success: false,
                        error: error.message
                    };
                }
            }
        };

        await PushNotifications.addListener('pushNotificationActionPerformed', async (notification) => {
            const data = notification.notification?.data;

            if (data?.url && data?.path) {
                const targetUrl = data.url;
                const targetPath = data.path;
                const currentUrl = window.location.origin;

                if (currentUrl !== targetUrl) {
                    await window.nativeBridge.setPreference('server_url', targetUrl);
                    await window.nativeBridge.setPreference('deep_link_target', targetPath);
                    window.location.href = getBaseUrl() + '/?reset=1';
                } else {
                    navigateToPath(targetPath);
                }
            }
        });

        await App.addListener('resume', async () => {
            await checkAndHandleDeepLink();
        });

        setTimeout(async () => {
            await checkAndHandleDeepLink();
        }, 500);
    } catch (error) {
        console.error('[BRIDGE] Initialization failed:', error);
    }
};

function navigateToPath(targetPath) {
    const waitForLivewire = () => {
        return new Promise((resolve) => {
            if (window.Livewire?.navigate) {
                resolve(true);
            } else {
                const checkInterval = setInterval(() => {
                    if (window.Livewire?.navigate) {
                        clearInterval(checkInterval);
                        resolve(true);
                    }
                }, 50);

                setTimeout(() => {
                    clearInterval(checkInterval);
                    resolve(false);
                }, 5000);
            }
        });
    };

    waitForLivewire().then((livewireReady) => {
        if (livewireReady) {
            window.Livewire.navigate(targetPath);
        } else {
            window.location.href = targetPath;
        }
    });
}

async function checkAndHandleDeepLink() {
    try {
        const deepLinkResult = await window.nativeBridge.getPreference('deep_link_target');

        if (deepLinkResult?.value) {
            await window.nativeBridge.removePreference('deep_link_target');

            const targetPath = deepLinkResult.value;

            navigateToPath(targetPath);
        }
    } catch (error) {
        console.error('[DEEP LINK] Error checking for deep link:', error);
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeNativeBridge);
} else {
    initializeNativeBridge();
}
