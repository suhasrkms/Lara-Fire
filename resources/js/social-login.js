import { GithubAuthProvider, GoogleAuthProvider, getAuth, signInWithPopup, signOut } from 'firebase/auth';
import { firebaseApp } from './firebase';

const providers = {
    google: () => new GoogleAuthProvider(),
    github: () => {
        const p = new GithubAuthProvider();
        p.addScope('user:email');
        return p;
    },
};

function showError(message) {
    const el = document.querySelector('[data-social-error]');
    if (!el) return;
    el.textContent = message;
    el.classList.remove('d-none');
}

export function initSocialLogin() {
    const form = document.getElementById('social-login-form');

    document.querySelectorAll('[data-social-login]').forEach((button) => {
        button.addEventListener('click', async () => {
            const make = providers[button.dataset.socialLogin];
            if (!make || !form) return;

            button.disabled = true;

            try {
                const auth = getAuth(firebaseApp());
                const result = await signInWithPopup(auth, make());
                form.querySelector('[name="id_token"]').value = await result.user.getIdToken();
                // Laravel owns the session from here on.
                await signOut(auth);
                form.submit();
            } catch (error) {
                button.disabled = false;
                if (error?.code === 'auth/popup-closed-by-user') return;
                if (error?.code === 'auth/account-exists-with-different-credential') {
                    showError('An account already exists with this email using a different sign-in method.');
                    return;
                }
                showError(error?.message ?? 'Sign-in failed.');
                console.error(error);
            }
        });
    });
}
