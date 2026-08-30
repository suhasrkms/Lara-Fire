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
            const registration = await navigator.serviceWorker.register(routes.serviceWorker, {
                scope: '/firebase-cloud-messaging-push-scope',
            });
            const token = await getToken(messaging, { vapidKey, serviceWorkerRegistration: registration });
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
