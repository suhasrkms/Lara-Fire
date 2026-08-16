export function initThemeToggle() {
    document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const root = document.documentElement;
            const next = root.dataset.bsTheme === 'dark' ? 'light' : 'dark';
            root.dataset.bsTheme = next;
            try {
                localStorage.setItem('larafire-theme', next);
            } catch (e) {
                // storage unavailable – theme just won't persist
            }
        });
    });
}
