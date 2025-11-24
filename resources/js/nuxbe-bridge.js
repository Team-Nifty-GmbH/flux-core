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
    } catch (error) {
        // Silent fail
    }

    return bridge;
};

// Initialize and export
const nuxbeAppBridge = await initializeNativeBridge();

export default nuxbeAppBridge;
