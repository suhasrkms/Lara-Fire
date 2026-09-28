import { deleteToken, getMessaging, getToken, isSupported, onMessage } from 'firebase/messaging';
import { csrfToken, firebaseApp } from './firebase';

const STORAGE_KEY = 'larafire-push-token';

function store(value) {
    try {
        value ? localStorage.setItem(STORAGE_KEY, value) : localStorage.removeItem(STORAGE_KEY);
    } catch (e) {
        /* ignore */
    }
}

function stored() {
    try {
        return localStorage.getItem(STORAGE_KEY);
    } catch (e) {
        return null;
    }
}

function waitForActive(registration, timeoutMs = 10000) {
    if (registration.active) return Promise.resolve();
    const worker = registration.installing || registration.waiting;
    return new Promise((resolve, reject) => {
        const timer = setTimeout(() => reject(new Error(
            'Service worker did not activate. Open DevTools → Application → Service workers and check /firebase-messaging-sw.js for errors.',
        )), timeoutMs);
        worker?.addEventListener('statechange', () => {
            if (worker.state === 'activated') {
                clearTimeout(timer);
                resolve();
            } else if (worker.state === 'redundant') {
                clearTimeout(timer);
                reject(new Error('Service worker failed to install. Check /firebase-messaging-sw.js in the browser.'));
            }
        });
    });
}

async function api(url, method, token) {
    const res = await fetch(url, {
        method,
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
        },
        body: JSON.stringify({ token }),
    });
    if (!res.ok && res.status !== 204) {
        const data = await res.json().catch(() => ({}));
        throw new Error(data.message ?? `Request failed (${res.status})`);
    }
}

export async function initPush() {
    const enableBtn = document.querySelector('[data-push-enable]');
    const disableBtn = document.querySelector('[data-push-disable]');
    const status = document.querySelector('[data-push-status]');
    const { routes, vapidKey } = window.LaraFire;

    const setState = (on, text) => {
        enableBtn.classList.toggle('d-none', on);
        disableBtn.classList.toggle('d-none', !on);
        status.textContent = text ?? '';
    };

    if (!(await isSupported())) {
        enableBtn.disabled = true;
        status.textContent = 'This browser does not support web push.';
        return;
    }

    const messaging = getMessaging(firebaseApp());

    if (stored() && Notification.permission === 'granted') {
        setState(true, 'Enabled on this device.');
    }

    onMessage(messaging, (payload) => {
        const { title = 'Notification', body = '' } = payload.notification ?? {};
        new Notification(title, { body });
    });

    enableBtn.addEventListener('click', async () => {
        enableBtn.disabled = true;
        status.textContent = 'Requesting permission…';
        try {
            // 1. Permission first, so we can tell the user exactly what's blocking.
            const hint = setTimeout(() => {
                status.textContent = 'No popup? Click the bell / "Notifications blocked" icon in the address bar and choose Allow.';
            }, 5000);
            const permission = await Notification.requestPermission().finally(() => clearTimeout(hint));
            if (permission !== 'granted') {
                throw new Error(permission === 'denied'
                    ? 'Notifications are blocked for this site. Click the lock icon in the address bar → Site settings → Notifications → Allow, then reload.'
                    : 'The permission prompt was dismissed. Click Enable again and choose Allow.');
            }

            // 2. Service worker must be active before the push subscription.
            status.textContent = 'Registering service worker…';
            const registration = await navigator.serviceWorker.register(routes.serviceWorker, {
                scope: '/firebase-cloud-messaging-push-scope',
            });
            await waitForActive(registration);

            // 3. FCM token.
            status.textContent = 'Getting push token…';
            let token;
            try {
                token = await getToken(messaging, { vapidKey, serviceWorkerRegistration: registration });
            } catch (error) {
                // A subscription left over from another VAPID key makes the push service reject us.
                if (error?.name !== 'AbortError' && !String(error?.message).includes('push service')) throw error;
                await (await registration.pushManager.getSubscription())?.unsubscribe();
                try {
                    token = await getToken(messaging, { vapidKey, serviceWorkerRegistration: registration });
                } catch (retryError) {
                    throw new Error(
                        'The browser push service refused to register. On Brave, enable "Use Google services for push messaging"; '
                        + 'otherwise try a normal (non-incognito) window without VPN/ad-block DNS.',
                    );
                }
            }
            await api(routes.pushSubscribe, 'POST', token);
            store(token);
            setState(true, 'Enabled on this device.');
        } catch (error) {
            console.error(error);
            status.textContent = error?.message ?? 'Could not enable notifications.';
        } finally {
            enableBtn.disabled = false;
        }
    });

    disableBtn.addEventListener('click', async () => {
        const token = stored();
        try {
            if (token) await api(routes.pushUnsubscribe, 'DELETE', token);
            await deleteToken(messaging);
        } catch (error) {
            console.error(error);
        }
        store(null);
        setState(false, 'Disabled.');
    });
}
