(function() {
    const STORAGE_KEY = 'barbershop-theme';
    const root = document.documentElement;

    function getStoredTheme() {
        try {
            const theme = localStorage.getItem(STORAGE_KEY);
            return theme === 'light' || theme === 'dark' ? theme : 'dark';
        } catch (error) {
            return 'dark';
        }
    }

    function saveTheme(theme) {
        try {
            localStorage.setItem(STORAGE_KEY, theme);
        } catch (error) {
            // Si el navegador bloquea localStorage, el tema visual sigue funcionando en la sesión actual.
        }
    }

    function updateToggle(theme) {
        const toggles = document.querySelectorAll('[data-theme-toggle]');

        toggles.forEach((toggle) => {
            const icon = toggle.querySelector('[data-theme-icon]');
            const label = toggle.querySelector('[data-theme-label]');
            const isLight = theme === 'light';

            toggle.setAttribute('aria-pressed', isLight ? 'true' : 'false');
            toggle.setAttribute('title', isLight ? 'Cambiar a modo oscuro' : 'Cambiar a modo claro');

            if(icon) {
                icon.textContent = isLight ? '☀️' : '🌙';
            }

            if(label) {
                label.textContent = isLight ? 'Modo claro' : 'Modo oscuro';
            }
        });
    }

    function applyTheme(theme, persist = true) {
        root.setAttribute('data-theme', theme);
        updateToggle(theme);

        if(persist) {
            saveTheme(theme);
        }
    }

    function toggleTheme() {
        const currentTheme = root.getAttribute('data-theme') === 'light' ? 'light' : 'dark';
        const nextTheme = currentTheme === 'light' ? 'dark' : 'light';
        applyTheme(nextTheme);
    }

    applyTheme(getStoredTheme(), false);

    document.addEventListener('DOMContentLoaded', function() {
        updateToggle(root.getAttribute('data-theme') || getStoredTheme());

        document.querySelectorAll('[data-theme-toggle]').forEach((toggle) => {
            toggle.addEventListener('click', toggleTheme);
        });
    });
})();
