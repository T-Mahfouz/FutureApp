export type Translatable = Record<string, string>;

export interface User {
    id: number;
    name: string;
    email: string;
    role: 'admin' | 'editor';
}

export interface SeoData {
    meta_title: Translatable | null;
    meta_description: Translatable | null;
    og_image: string | null;
    slug: Translatable;
}

export interface Service {
    id: number;
    title: Translatable;
    slug: Translatable;
    description: Translatable;
    deliverables: Translatable | null;
    icon: string | null;
    sort_order: number;
    is_active: boolean;
    meta_title: Translatable | null;
    meta_description: Translatable | null;
    og_image: string | null;
    media: Media[];
    media_files?: MediaFile[];
    case_studies?: CaseStudy[];
}

export interface Industry {
    id: number;
    title: Translatable;
    slug: Translatable;
    description: Translatable;
    challenges: Translatable | null;
    solutions: Translatable | null;
    icon: string | null;
    sort_order: number;
    is_active: boolean;
    meta_title: Translatable | null;
    meta_description: Translatable | null;
    og_image: string | null;
    media: Media[];
    media_files?: MediaFile[];
    case_studies?: CaseStudy[];
}

export interface TechCategory {
    id: number;
    name: Translatable;
    slug: string;
    sort_order: number;
    items: TechItem[];
}

export interface TechItem {
    id: number;
    name: Translatable;
    logo: string | null;
    url: string | null;
    tech_category_id: number;
    sort_order: number;
    media: Media[];
}

export interface CaseStudy {
    id: number;
    title: Translatable;
    slug: Translatable;
    excerpt: Translatable | null;
    overview: Translatable | null;
    challenge: Translatable | null;
    solution: Translatable | null;
    features: Translatable | null;
    our_role: Translatable | null;
    results: Translatable | null;
    timeline: string | null;
    team_size: string | null;
    live_url: string | null;
    app_store_url: string | null;
    demo_url: string | null;
    year: number | null;
    is_featured: boolean;
    is_active: boolean;
    sort_order: number;
    meta_title: Translatable | null;
    meta_description: Translatable | null;
    og_image: string | null;
    thumbnail: string | null;
    media: Media[];
    media_files?: MediaFile[];
    services?: Service[];
    industries?: Industry[];
    tech_items?: TechItem[];
}

export interface Testimonial {
    id: number;
    name: Translatable;
    position: Translatable | null;
    company: Translatable | null;
    content: Translatable;
    avatar: string | null;
    rating: number | null;
    sort_order: number;
    is_active: boolean;
    media: Media[];
}

export interface Client {
    id: number;
    name: Translatable;
    logo: string | null;
    url: string | null;
    sort_order: number;
    is_active: boolean;
    media: Media[];
    case_studies?: CaseStudy[];
}

export interface TeamMember {
    id: number;
    name: Translatable;
    position: Translatable;
    bio: Translatable | null;
    photo: string | null;
    sort_order: number;
    is_active: boolean;
    media: Media[];
}

export interface CompanyProfile {
    id: number;
    company_name: Translatable;
    tagline: Translatable | null;
    about: Translatable | null;
    mission: Translatable | null;
    vision: Translatable | null;
    values: Translatable | null;
    email: string | null;
    phone: string | null;
    address: Translatable | null;
    whatsapp_link: string | null;
    calendly_link: string | null;
    facebook: string | null;
    twitter: string | null;
    linkedin: string | null;
    instagram: string | null;
    github: string | null;
}

export interface ContactSubmission {
    id: number;
    name: string;
    company: string | null;
    email: string;
    phone: string | null;
    service_id: number | null;
    message: string;
    budget_range: string | null;
    preferred_contact_time: string | null;
    is_read: boolean;
    created_at: string;
    service?: Service;
}

export interface ProcessStep {
    id: number;
    title: Translatable;
    description: Translatable;
    icon: string | null;
    sort_order: number;
}

export interface Media {
    id: number;
    original_url: string;
    preview_url?: string;
    name: string;
    file_name: string;
    mime_type: string;
    size: number;
    custom_properties: {
        alt?: Translatable;
    };
}

export interface MediaFile {
    id: number;
    type: string;
    path: string;
    storage: string;
    alt: Translatable | null;
    mediable_type: string;
    mediable_id: number;
    collection: string;
    sort_order: number;
    url: string;
}

export interface PageProps {
    locale: string;
    locales: Record<string, { name: string; english_name: string; flag: string; rtl: boolean }>;
    currentUrl: string;
    translations: Record<string, Record<string, any>>;
    auth: {
        user: User | null;
    };
    flash: {
        success?: string;
        error?: string;
    };
    companyProfile: CompanyProfile | null;
}

export interface PaginatedData<T> {
    data: T[];
    links: {
        first: string;
        last: string;
        prev: string | null;
        next: string | null;
    };
    meta: {
        current_page: number;
        from: number;
        last_page: number;
        per_page: number;
        to: number;
        total: number;
    };
}
