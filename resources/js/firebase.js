import { getApps, initializeApp } from 'firebase/app';

export function firebaseApp() {
    const config = window.LaraFire?.firebase ?? {};

    if (!config.apiKey) {
        throw new Error('Firebase web config is missing. Set FIREBASE_WEB_* in .env.');
    }

    return getApps()[0] ?? initializeApp(config);
}

export function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}
