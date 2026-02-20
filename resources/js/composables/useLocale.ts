import { computed, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import type { PageProps } from '@/types';

const RTL_LOCALES = ['ar', 'ur'];

// Global reactive locale state persisted to localStorage
const currentLocale = ref('en');
let initialized = false;

export function useLocale() {
    const page = usePage<PageProps>();

    // Initialize from localStorage or URL on first use
    if (!initialized) {
        const stored = localStorage.getItem('locale');
        const urlLocale = window.location.pathname.split('/')[1];
        const supportedCodes = Object.keys(page.props.locales || {});

        if (stored && supportedCodes.includes(stored)) {
            currentLocale.value = stored;
        } else if (supportedCodes.includes(urlLocale)) {
            currentLocale.value = urlLocale;
        } else {
            currentLocale.value = 'en';
        }

        // Apply dir/lang to document
        document.documentElement.lang = currentLocale.value;
        document.documentElement.dir = RTL_LOCALES.includes(currentLocale.value) ? 'rtl' : 'ltr';
        initialized = true;
    }

    const locale = computed(() => currentLocale.value);
    const isRtl = computed(() => RTL_LOCALES.includes(currentLocale.value));
    const dir = computed(() => isRtl.value ? 'rtl' : 'ltr');
    const languages = computed(() => page.props.locales || {});
    const allTranslations = computed(() => page.props.translations || {});

    /**
     * Translate a model's translatable field.
     * Data comes as { en: "...", ar: "...", fr: "..." }
     */
    function tf(field: Record<string, string> | string | null | undefined): string {
        if (!field) return '';
        if (typeof field === 'string') return field;
        return field[currentLocale.value] || field['en'] || Object.values(field)[0] || '';
    }

    /**
     * Translate a UI string key (e.g., 'nav.home', 'contact.send').
     */
    function ui(key: string): string {
        const translations = allTranslations.value[currentLocale.value] || allTranslations.value['en'] || {};
        const keys = key.split('.');
        let result: any = translations;
        for (const k of keys) {
            if (result && typeof result === 'object') {
                result = result[k];
            } else {
                result = undefined;
                break;
            }
        }
        if (typeof result === 'string') return result;

        // Fallback to English
        const enTranslations = allTranslations.value['en'] || {};
        let fallback: any = enTranslations;
        for (const k of keys) {
            if (fallback && typeof fallback === 'object') {
                fallback = fallback[k];
            } else {
                fallback = undefined;
                break;
            }
        }
        return typeof fallback === 'string' ? fallback : key;
    }

    /**
     * Switch locale instantly without page reload.
     */
    function switchLocale(newLocale: string) {
        currentLocale.value = newLocale;
        localStorage.setItem('locale', newLocale);

        // Update HTML attributes
        document.documentElement.lang = newLocale;
        document.documentElement.dir = RTL_LOCALES.includes(newLocale) ? 'rtl' : 'ltr';

        // Update URL locale prefix without page reload
        const currentPath = window.location.pathname;
        const supportedCodes = Object.keys(page.props.locales || {});
        const localeRegex = new RegExp(`^\\/(${supportedCodes.join('|')})`);
        const newPath = currentPath.replace(localeRegex, `/${newLocale}`);
        if (newPath !== currentPath) {
            window.history.replaceState({}, '', newPath + window.location.search);
        }
    }

    function localizedUrl(path: string): string {
        return `/${currentLocale.value}${path}`;
    }

    function localizedSlug(slugField: Record<string, string> | string | null | undefined): string {
        if (!slugField) return '';
        if (typeof slugField === 'string') return slugField;
        return slugField[currentLocale.value] || slugField['en'] || Object.values(slugField)[0] || '';
    }

    return { locale, isRtl, dir, languages, tf, ui, switchLocale, localizedUrl, localizedSlug };
}
