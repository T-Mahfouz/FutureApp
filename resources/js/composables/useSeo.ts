import { usePage } from '@inertiajs/vue3';
import type { PageProps, Translatable } from '@/types';
import { computed } from 'vue';
import { useLocale } from './useLocale';

export function useSeo() {
    const page = usePage<PageProps>();
    const { tf } = useLocale();

    /**
     * Build SEO data from a model that has Translatable meta fields.
     * meta_title and meta_description are now Translatable objects (or null).
     */
    function buildSeo(entity: {
        meta_title?: Translatable | null;
        meta_description?: Translatable | null;
        og_image?: string | null;
    }) {
        return {
            title: entity.meta_title ? tf(entity.meta_title) : '',
            description: entity.meta_description ? tf(entity.meta_description) : '',
            og_image: entity.og_image || null,
        };
    }

    return { buildSeo };
}
