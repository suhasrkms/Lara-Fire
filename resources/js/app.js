import * as bootstrap from 'bootstrap';
import { initThemeToggle } from './theme';

window.bootstrap = bootstrap;

initThemeToggle();

// Firebase JS SDK is only loaded on pages that need it.
if (document.querySelector('[data-social-login]')) {
    import('./social-login').then((m) => m.initSocialLogin());
}

if (document.querySelector('[data-push-enable]')) {
    import('./push').then((m) => m.initPush());
}
