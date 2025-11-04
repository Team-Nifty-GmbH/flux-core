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
        const { Capacitor } = window.Capacitor;
        const { Camera } = window.CapacitorPlugins || {};
        const { CapacitorBarcodeScanner } = window.CapacitorPlugins || {};

        // Check if running in native app
        bridge.isNative = () => Capacitor.isNativePlatform();

        // Get platform
        bridge.getPlatform = () => Capacitor.getPlatform();

        // Server wechseln
        bridge.changeServer = async () => {
            try {
                const { Preferences } = window.CapacitorPlugins || {};
                if (Preferences) {
                    await Preferences.remove({ key: 'server_url' });
                    window.location.href = 'capacitor://localhost/index.html';
                    return { success: true };
                }
                return {
                    success: false,
                    error: 'Preferences plugin not available',
                };
            } catch (error) {
                return {
                    success: false,
                    error: error.message,
                };
            }
        };

        // Camera - Foto aufnehmen
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

            // Foto aus Galerie
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
