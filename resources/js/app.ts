import { createApp, h } from 'vue';
import type { DefineComponent } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { plugin as formkitPlugin, defaultConfig } from '@formkit/vue';
import { ar, en, fr, tr, it, ru, zh, hi, es, pt, id as idLocale } from '@formkit/i18n';

createInertiaApp({
    title: (title) => title ? `${title} - Around Solutions` : 'Around Solutions',
    resolve: (name) => {
        const pages = import.meta.glob<DefineComponent>('./Pages/**/*.vue', { eager: true });
        return pages[`./Pages/${name}.vue`];
    },
    setup({ el, App, props, plugin }) {
        const locale = (props.initialPage.props.locale as string) || 'en';
        const stored = localStorage.getItem('locale');
        const initialLocale = stored || locale;

        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(formkitPlugin, defaultConfig({
                locales: { ar, en, fr, tr, it, ru, zh, hi, es, pt, id: idLocale },
                locale: initialLocale,
            }))
            .mount(el);
    },
    progress: {
        color: '#dc2626',
    },
});
