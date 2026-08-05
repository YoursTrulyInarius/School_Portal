document.addEventListener('DOMContentLoaded', function () {
    if (typeof Swal === 'undefined') {
        return;
    }

    if (!window.SWEETALERT_FLASH || !window.SWEETALERT_FLASH.type) {
        return;
    }

    const flash = window.SWEETALERT_FLASH;
    const config = {
        icon: flash.type,
        title: flash.title || '',
        text: flash.text || undefined,
        html: flash.html || undefined,
        footer: flash.footer || undefined,
        timer: flash.timer || undefined,
        showConfirmButton: flash.showConfirmButton !== false,
        allowOutsideClick: flash.allowOutsideClick !== false,
        allowEscapeKey: flash.allowEscapeKey !== false,
        allowEnterKey: flash.allowEnterKey !== false,
    };

    Swal.fire(config).then((result) => {
        if (flash.redirect) {
            if (flash.redirectOnConfirm) {
                if (result.isConfirmed) {
                    window.location.href = flash.redirect;
                }
            } else {
                window.location.href = flash.redirect;
            }
        }
    });
});

function showSwal(type, title, text, options = {}) {
    if (typeof Swal === 'undefined') {
        console.warn('SweetAlert2 is not loaded');
        return;
    }

    return Swal.fire(Object.assign({
        icon: type,
        title: title,
        text: text,
        showConfirmButton: true,
    }, options));
}

function showSuccess(text, title = 'Success', options = {}) {
    return showSwal('success', title, text, Object.assign({
        timer: 2500,
        showConfirmButton: false,
    }, options));
}

function showError(text, title = 'Error', options = {}) {
    return showSwal('error', title, text, options);
}

function showInfo(text, title = 'Info', options = {}) {
    return showSwal('info', title, text, options);
}
