let notyfInstance = null;

export const getNotyf = () => {
    if (typeof window === 'undefined') return null;

    if (!notyfInstance && window.Notyf) {
        notyfInstance = new window.Notyf({
            duration: 3500,
            position: {
                x: 'right',
                y: 'top',
            },
            dismissible: true,
            types: [
                {
                    type: 'success',
                    background: '#059669', // Emerald
                    message: 'Berhasil Disimpan',
                    dismissible: true,
                    icon: {
                        className: 'notyf__icon--success',
                        tagName: 'i',
                    },
                },
                {
                    type: 'error',
                    background: '#e11d48', // Rose
                    message: 'Gagal Disimpan',
                    dismissible: true,
                    icon: {
                        className: 'notyf__icon--error',
                        tagName: 'i',
                    },
                },
            ],
        });

        // Attach globally to window for convenience
        window.notyf = notyfInstance;
        window.$notyf = notyfInstance;
    }

    return notyfInstance;
};

export const notifySuccess = (message) => {
    const notyf = getNotyf();
    if (notyf) {
        notyf.open({
            type: 'success',
            message: message || 'Berhasil Disimpan',
        });
    }
};

export const notifyError = (message) => {
    const notyf = getNotyf();
    if (notyf) {
        notyf.open({
            type: 'error',
            message: message || 'Gagal Disimpan',
        });
    }
};

export default {
    getNotyf,
    notifySuccess,
    notifyError,
};

