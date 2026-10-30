import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { getNotyf, notifySuccess, notifyError } from './notyf';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        app.use(plugin);
        app.use(ZiggyVue);

        if (typeof window !== 'undefined') {
            getNotyf();
        }

        app.config.globalProperties.$notifySuccess = notifySuccess;
        app.config.globalProperties.$notifyError = notifyError;
        app.config.globalProperties.$notyf = {
            success: notifySuccess,
            error: notifyError,
            get instance() {
                return getNotyf();
            },
        };

        return app.mount(el);
    },
    progress: {
        color: '#10B981',
    },
});
