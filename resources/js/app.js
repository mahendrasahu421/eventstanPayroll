import './bootstrap';

const TOAST_CONTAINER_ID = 'app-toast-container';
const TOAST_DEFAULT_DURATION = 4000;

const toastTheme = {
    success: {
        border: '#16a34a',
        background: '#ecfdf5',
        foreground: '#14532d',
        icon: 'bi-check-circle-fill',
    },
    error: {
        border: '#dc2626',
        background: '#fef2f2',
        foreground: '#7f1d1d',
        icon: 'bi-x-circle-fill',
    },
    warning: {
        border: '#d97706',
        background: '#fffbeb',
        foreground: '#78350f',
        icon: 'bi-exclamation-triangle-fill',
    },
    info: {
        border: '#2563eb',
        background: '#eff6ff',
        foreground: '#1e3a8a',
        icon: 'bi-info-circle-fill',
    },
};

function ensureToastContainer() {
    let container = document.getElementById(TOAST_CONTAINER_ID);

    if (container) {
        return container;
    }

    container = document.createElement('div');
    container.id = TOAST_CONTAINER_ID;
    container.className = 'app-toast-container';
    container.setAttribute('aria-live', 'polite');
    container.setAttribute('aria-atomic', 'true');
    document.body.appendChild(container);

    return container;
}

function normalizeToastType(type) {
    return Object.prototype.hasOwnProperty.call(toastTheme, type) ? type : 'info';
}

function hideToast(toast) {
    if (!toast || toast.dataset.dismissed === '1') {
        return;
    }

    toast.dataset.dismissed = '1';
    toast.classList.add('app-toast--hide');

    window.setTimeout(() => {
        toast.remove();
    }, 220);
}

function buildToast(message, type = 'info', options = {}) {
    const toastType = normalizeToastType(type);
    const config = toastTheme[toastType];
    const duration = Number.isFinite(options.duration) ? options.duration : TOAST_DEFAULT_DURATION;
    const title = options.title || toastType.charAt(0).toUpperCase() + toastType.slice(1);

    const toast = document.createElement('div');
    toast.className = `app-toast app-toast--${toastType}`;
    toast.style.borderColor = config.border;
    toast.style.background = config.background;
    toast.style.color = config.foreground;

    const header = document.createElement('div');
    header.className = 'app-toast__header';

    const label = document.createElement('div');
    label.className = 'app-toast__title';
    label.innerHTML = `<i class="bi ${config.icon}"></i><span>${title}</span>`;

    const closeButton = document.createElement('button');
    closeButton.type = 'button';
    closeButton.className = 'app-toast__close';
    closeButton.setAttribute('aria-label', 'Close notification');
    closeButton.innerHTML = '&times;';
    closeButton.addEventListener('click', () => hideToast(toast));

    header.append(label, closeButton);

    const body = document.createElement('div');
    body.className = 'app-toast__body';
    body.textContent = message;

    toast.append(header, body);

    if (duration > 0) {
        window.setTimeout(() => hideToast(toast), duration);
    }

    return toast;
}

function showToast(message, type = 'info', options = {}) {
    if (!message) {
        return null;
    }

    const container = ensureToastContainer();
    const toast = buildToast(String(message), type, options);
    container.appendChild(toast);

    window.requestAnimationFrame(() => {
        toast.classList.add('app-toast--show');
    });

    return toast;
}

function emitInitialToasts() {
    const flashToasts = Array.isArray(window.__FLASH_TOASTS) ? window.__FLASH_TOASTS : [];

    flashToasts.forEach((toast) => {
        if (!toast) {
            return;
        }

        showToast(toast.message, toast.type, {
            title: toast.title,
            duration: toast.duration,
        });
    });
}

window.AppToast = {
    show: showToast,
    success: (message, options = {}) => showToast(message, 'success', options),
    error: (message, options = {}) => showToast(message, 'error', options),
    warning: (message, options = {}) => showToast(message, 'warning', options),
    info: (message, options = {}) => showToast(message, 'info', options),
};

window.addEventListener('app:toast', (event) => {
    const detail = event.detail || {};
    showToast(detail.message, detail.type, detail);
});

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', emitInitialToasts, { once: true });
} else {
    emitInitialToasts();
}
