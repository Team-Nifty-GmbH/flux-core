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
        const App = Plugins.App;
        const Browser = Plugins.Browser;
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
                },
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

        // Passkey bridge: WebAuthn cannot run in WKWebView for a multi-tenant
        // app (no Associated Domains entitlement is possible per-tenant), so
        // delegate to the system browser via a PKCE-protected redirect flow.
        if (App && Browser) {
            const PASSKEY_REDIRECT_URI = 'nuxbe://auth-callback';
            const PASSKEY_TIMEOUT_MS = 5 * 60 * 1000;

            const base64UrlEncode = (bytes) => {
                let str = '';
                bytes.forEach((b) => {
                    str += String.fromCharCode(b);
                });

                return btoa(str)
                    .replace(/\+/g, '-')
                    .replace(/\//g, '_')
                    .replace(/=+$/, '');
            };

            const generatePkcePair = async () => {
                const verifierBytes = new Uint8Array(32);
                crypto.getRandomValues(verifierBytes);
                const verifier = base64UrlEncode(verifierBytes);
                const digest = await crypto.subtle.digest(
                    'SHA-256',
                    new TextEncoder().encode(verifier),
                );
                const challenge = base64UrlEncode(new Uint8Array(digest));

                return { verifier, challenge };
            };

            const waitForCallback = () =>
                new Promise((resolve, reject) => {
                    let handle = null;
                    let timer = null;
                    const cleanup = () => {
                        if (handle) handle.remove();
                        if (timer) clearTimeout(timer);
                    };

                    Promise.resolve(
                        App.addListener('appUrlOpen', (event) => {
                            if (
                                !event?.url ||
                                !event.url.startsWith(PASSKEY_REDIRECT_URI)
                            ) {
                                return;
                            }

                            cleanup();
                            try {
                                const u = new URL(event.url);
                                const error = u.searchParams.get('error');
                                if (error) {
                                    reject(new Error(error));
                                    return;
                                }

                                const code = u.searchParams.get('code');
                                if (!code) {
                                    reject(new Error('missing_code'));
                                    return;
                                }

                                resolve(code);
                            } catch (e) {
                                reject(e);
                            }
                        }),
                    ).then((h) => {
                        handle = h;
                    });

                    timer = setTimeout(() => {
                        cleanup();
                        reject(new Error('passkey_bridge_timeout'));
                    }, PASSKEY_TIMEOUT_MS);
                });

            const csrf = () =>
                document.querySelector('meta[name=csrf-token]')?.content || '';

            const exchange = async (code, verifier) => {
                const r = await fetch('/auth/passkey-bridge/exchange', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                    },
                    body: JSON.stringify({
                        code,
                        code_verifier: verifier,
                    }),
                });

                const body = await r.json().catch(() => ({}));

                if (!r.ok) {
                    throw new Error(body.statusMessage || 'exchange_failed');
                }

                return body.data || {};
            };

            bridge.passkeyLogin = async () => {
                try {
                    const { verifier, challenge } = await generatePkcePair();
                    const url = new URL(
                        '/auth/passkey-bridge/login',
                        window.location.origin,
                    );
                    url.searchParams.set('code_challenge', challenge);
                    url.searchParams.set('redirect_uri', PASSKEY_REDIRECT_URI);

                    const callbackPromise = waitForCallback();
                    await Browser.open({
                        url: url.toString(),
                        presentationStyle: 'popover',
                    });

                    const code = await callbackPromise;
                    await Browser.close().catch(() => {});

                    const { magic_login_url: magicLoginUrl } = await exchange(
                        code,
                        verifier,
                    );

                    if (magicLoginUrl) {
                        window.location.href = magicLoginUrl;
                    }

                    return { success: true };
                } catch (error) {
                    await Browser.close().catch(() => {});

                    return {
                        success: false,
                        error: error?.message || 'passkey_login_failed',
                    };
                }
            };

            bridge.passkeyRegister = async () => {
                try {
                    const { verifier, challenge } = await generatePkcePair();
                    const startResp = await fetch(
                        '/auth/passkey-bridge/start-registration',
                        {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'Content-Type': 'application/json',
                                Accept: 'application/json',
                                'X-CSRF-TOKEN': csrf(),
                            },
                            body: JSON.stringify({
                                code_challenge: challenge,
                                redirect_uri: PASSKEY_REDIRECT_URI,
                            }),
                        },
                    );

                    const startBody = await startResp.json().catch(() => ({}));

                    if (!startResp.ok) {
                        throw new Error(
                            startBody.statusMessage || 'start_failed',
                        );
                    }

                    const bridgeUrl = startBody.data?.bridge_url;

                    const callbackPromise = waitForCallback();
                    await Browser.open({
                        url: bridgeUrl,
                        presentationStyle: 'popover',
                    });

                    const code = await callbackPromise;
                    await Browser.close().catch(() => {});
                    await exchange(code, verifier);

                    return { success: true };
                } catch (error) {
                    await Browser.close().catch(() => {});

                    return {
                        success: false,
                        error: error?.message || 'passkey_register_failed',
                    };
                }
            };

            // Override the global helper used by the existing
            // <x-authenticate-passkey> button on the login page so it goes
            // through the bridge when running inside the Capacitor app.
            window.authenticateWithPasskey = async () => bridge.passkeyLogin();
        }

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
