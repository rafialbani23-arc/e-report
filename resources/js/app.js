document.addEventListener('DOMContentLoaded', () => {
    const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
    const sidebar = document.querySelector('[data-sidebar]');
    const sidebarBackdrop = document.querySelector('[data-sidebar-backdrop]');
    const sidebarClose = document.querySelector('[data-sidebar-close]');

    if (!sidebarToggle || !sidebar || !sidebarBackdrop || !sidebarClose) {
        return;
    }

    const desktopLayout = window.matchMedia('(min-width: 1024px)');
    let returnFocusToToggle = true;

    const focusableElements = () => [...sidebar.querySelectorAll(
        'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])',
    )].filter((element) => element.getClientRects().length > 0);

    const isOpen = () => !sidebar.classList.contains('hidden') && !desktopLayout.matches;

    const closeSidebar = ({ restoreFocus = true } = {}) => {
        sidebar.classList.add('hidden');
        sidebarBackdrop.classList.add('hidden');
        sidebarToggle.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('overflow-hidden');

        if (restoreFocus && returnFocusToToggle && !desktopLayout.matches) {
            sidebarToggle.focus();
        }
    };

    const openSidebar = () => {
        returnFocusToToggle = true;
        sidebar.classList.remove('hidden');
        sidebarBackdrop.classList.remove('hidden');
        sidebarToggle.setAttribute('aria-expanded', 'true');
        document.body.classList.add('overflow-hidden');
        sidebarClose.focus();
    };

    sidebarToggle.addEventListener('click', () => {
        if (isOpen()) {
            closeSidebar();
            return;
        }

        openSidebar();
    });

    sidebarClose.addEventListener('click', () => closeSidebar());
    sidebarBackdrop.addEventListener('click', () => closeSidebar());

    sidebar.addEventListener('click', (event) => {
        if (event.target.closest('a[href]') && isOpen()) {
            returnFocusToToggle = false;
            closeSidebar({ restoreFocus: false });
        }
    });

    document.addEventListener('keydown', (event) => {
        if (!isOpen()) {
            return;
        }

        if (event.key === 'Escape') {
            event.preventDefault();
            closeSidebar();
            return;
        }

        if (event.key !== 'Tab') {
            return;
        }

        const elements = focusableElements();
        const firstElement = elements[0];
        const lastElement = elements.at(-1);

        if (!firstElement || !lastElement) {
            event.preventDefault();
            sidebar.focus();
        } else if (event.shiftKey && document.activeElement === firstElement) {
            event.preventDefault();
            lastElement.focus();
        } else if (!event.shiftKey && document.activeElement === lastElement) {
            event.preventDefault();
            firstElement.focus();
        }
    });

    desktopLayout.addEventListener('change', (event) => {
        if (event.matches) {
            returnFocusToToggle = false;
            closeSidebar({ restoreFocus: false });
        }
    });
});
