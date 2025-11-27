/**
 * Nuxbe Mobile Bridge
 * Provides native mobile functionality when running in Capacitor
 */

const initializeNativeBridge = async () => {
    // Only initialize if Capacitor is available
    if (!window.Capacitor) {
        return null;
    }

    const bridge = {};

    try {
        const Capacitor = window.Capacitor;
        const Plugins = Capacitor.Plugins || {};
        const Camera = Plugins.Camera;
        const CapacitorBarcodeScanner = Plugins.CapacitorBarcodeScanner;
        const Preferences = Plugins.Preferences;
        const PushNotifications = Plugins.PushNotifications;

        // Handle push notification tap - Deep Link Navigation
        if (PushNotifications) {
            PushNotifications.addListener(
                'pushNotificationActionPerformed',
                (notification) => {
                    const data = notification.notification?.data;

                    if (data?.url && data?.path) {
                        const currentOrigin = window.location.origin;
                        const targetOrigin = new URL(data.url).origin;

                        if (currentOrigin === targetOrigin && window.Livewire) {
                            window.Livewire.navigate(data.path);
                        } else {
                            window.location.href =
                                data.url +
                                '/login-mobile?redirect=' +
                                encodeURIComponent(data.path);
                        }
                    }
                }
            );
        }

        // Check if running in native app
        bridge.isNative = () => Capacitor.isNativePlatform();

        // Get platform
        bridge.getPlatform = () => Capacitor.getPlatform();

        // Change server
        bridge.changeServer = async () => {
            try {
                if (Preferences) {
                    await Preferences.remove({ key: 'server_url' });
                }
                // Use correct URL scheme per platform
                const baseUrl =
                    Capacitor.getPlatform() === 'android'
                        ? 'http://localhost'
                        : 'capacitor://localhost';
                window.location.href = baseUrl + '/index.html';
                return { success: true };
            } catch (error) {
                return {
                    success: false,
                    error: error.message,
                };
            }
        };

        // Camera - Capture new photo
        if (Camera) {
            bridge.capturePhoto = async () => {
                try {
                    const image = await Camera.getPhoto({
                        quality: 90,
                        allowEditing: false,
                        resultType: 'base64',
                        source: 'camera',
                    });

                    return {
                        success: true,
                        base64: image.base64String,
                        format: image.format,
                    };
                } catch (error) {
                    return {
                        success: false,
                        error: error.message,
                    };
                }
            };

            // Pick photo from gallery
            bridge.pickPhoto = async () => {
                try {
                    const image = await Camera.getPhoto({
                        quality: 90,
                        allowEditing: false,
                        resultType: 'base64',
                        source: 'photos',
                    });

                    return {
                        success: true,
                        base64: image.base64String,
                        format: image.format,
                    };
                } catch (error) {
                    return {
                        success: false,
                        error: error.message,
                    };
                }
            };
        }

        // Barcode Scanner
        if (CapacitorBarcodeScanner) {
            bridge.scanBarcode = async () => {
                try {
                    const result = await CapacitorBarcodeScanner.scanBarcode({
                        hint: 'ALL',
                        scanButton: false,
                    });

                    if (result.ScanResult) {
                        return {
                            success: true,
                            barcode: result.ScanResult,
                            format: result.format || 'unknown',
                        };
                    }

                    return {
                        success: false,
                        error: 'No barcode detected',
                    };
                } catch (error) {
                    return {
                        success: false,
                        error: error.message,
                    };
                }
            };
        }

        // Status Bar - Update based on dark mode
        const StatusBar = Plugins.StatusBar;
        if (StatusBar) {
            // Ensure StatusBar does NOT overlay WebView (content below status bar)
            StatusBar.setOverlaysWebView({ overlay: false });

            bridge.setDarkMode = async (isDark) => {
                try {
                    if (isDark) {
                        await StatusBar.setStyle({ style: 'DARK' });
                        await StatusBar.setBackgroundColor({
                            color: '#1e293b',
                        });
                    } else {
                        await StatusBar.setStyle({ style: 'LIGHT' });
                        await StatusBar.setBackgroundColor({
                            color: '#ffffff',
                        });
                    }
                    return { success: true };
                } catch (error) {
                    return { success: false, error: error.message };
                }
            };

            bridge.updateStatusBarFromDOM = async () => {
                const isDark =
                    document.documentElement.classList.contains('dark') ||
                    document.body.classList.contains('dark');
                return bridge.setDarkMode(isDark);
            };
        }

        // Watch for dark mode changes automatically
        const observeDarkMode = () => {
            if (!bridge.updateStatusBarFromDOM) return;

            const observer = new MutationObserver((mutations) => {
                for (const mutation of mutations) {
                    if (mutation.attributeName === 'class') {
                        bridge.updateStatusBarFromDOM();
                    }
                }
            });

            observer.observe(document.documentElement, {
                attributes: true,
                attributeFilter: ['class'],
            });
            observer.observe(document.body, {
                attributes: true,
                attributeFilter: ['class'],
            });

            // Initial update
            bridge.updateStatusBarFromDOM();
        };

        // Start observing when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', observeDarkMode);
        } else {
            observeDarkMode();
        }
    } catch (error) {
        // Silent fail
    }

    return bridge;
};

// Initialize and export
const nuxbeAppBridge = await initializeNativeBridge();

export default nuxbeAppBridge;
