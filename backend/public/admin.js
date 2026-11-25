document.querySelectorAll('[data-theme-toggle]').forEach((toggle) => {
    const label = toggle.querySelector('[data-theme-label]');

    const updateLabel = () => {
        const isDark = document.documentElement.dataset.theme === 'dark';
        toggle.setAttribute('aria-pressed', String(isDark));
        toggle.setAttribute('aria-label', isDark ? toggle.dataset.lightLabel : toggle.dataset.darkLabel);
        label.textContent = isDark ? toggle.dataset.lightLabel : toggle.dataset.darkLabel;
    };

    toggle.addEventListener('click', () => {
        const theme = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
        document.documentElement.dataset.theme = theme;
        localStorage.setItem('admin-theme', theme);
        updateLabel();
    });

    updateLabel();
});
