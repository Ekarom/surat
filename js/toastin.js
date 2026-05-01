/**
 * Toastiin - Modern Notification System
 * Managed by Antigravity AI
 * With Toastr Compatibility Layer
 */

(function() {
    // Inject Styles
    const style = document.createElement('style');
    style.textContent = `
        #toastiin-container {
            position: fixed;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        #toastiin-container.top-right { top: 20px; right: 20px; }
        #toastiin-container.top-left { top: 20px; left: 20px; }
        #toastiin-container.bottom-right { bottom: 20px; right: 20px; }
        #toastiin-container.bottom-left { bottom: 20px; left: 20px; }

        .toastiin-toast {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 280px;
            max-width: 350px;
            padding: 14px 18px;
            border-radius: 12px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            font-size: 14px;
            color: white !important;
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            transform: translateY(-20px);
            pointer-events: auto;
        }

        .toastiin-toast.success { background: #2ecc71; }
        .toastiin-toast.warning { background: #f1c40f; }
        .toastiin-toast.error { background: #e74c3c; }
        .toastiin-toast.info { background: #3498db; }

        .toastiin-toast .icon {
            font-size: 20px;
            flex-shrink: 0;
            color: white !important;
        }

        .toastiin-toast .message {
            flex-grow: 1;
            font-weight: 500;
            color: white !important;
        }

        .toastiin-toast .close-btn {
            background: transparent;
            border: none;
            font-size: 20px;
            color: white !important;
            cursor: pointer;
            opacity: 0.8;
            line-height: 1;
        }

        .toastiin-toast .close-btn:hover { opacity: 1; }
    `;
    document.head.appendChild(style);

    // Global showToast Function
    window.showToast = function(message, type = 'success', duration = 3000) {
        let container = document.getElementById('toastiin-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toastiin-container';
            container.className = 'top-right';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.className = `toastiin-toast ${type}`;
        
        let iconClass = 'fa-check-circle';
        if(type === 'error') iconClass = 'fa-times-circle';
        if(type === 'warning') iconClass = 'fa-exclamation-triangle';
        if(type === 'info') iconClass = 'fa-info-circle';

        toast.innerHTML = `
            <div class="icon"><i class="fas ${iconClass}"></i></div>
            <div class="message">${message}</div>
            <button class="close-btn">&times;</button>
        `;

        container.appendChild(toast);

        // Animate In
        setTimeout(() => {
            toast.style.opacity = '1';
            toast.style.transform = 'translateY(0)';
        }, 10);

        const removeToast = () => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(-20px)';
            setTimeout(() => toast.remove(), 400);
        };

        toast.querySelector('.close-btn').addEventListener('click', removeToast);
        if (duration > 0) setTimeout(removeToast, duration);
    };

    // Toastr Compatibility Layer
    window.toastr = {
        success: (msg) => window.showToast(msg, 'success'),
        error: (msg) => window.showToast(msg, 'error'),
        warning: (msg) => window.showToast(msg, 'warning'),
        info: (msg) => window.showToast(msg, 'info'),
        options: {} // Dummy for options
    };
})();
