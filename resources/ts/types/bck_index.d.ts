export type Column = {
    label: string;
    orderable: boolean;
    searchable: boolean;
}

export type TData<T> = {
    total: number;
    items: T[];
}

export type Filter = {
    page: number;
    perPage: number;
    search: string;
    order: Record<string, 'asc' | 'desc' | undefined>;
}

export type ResearchArea = {
    id: string;
    research_area: string;
    created_at: string;
    updated_at: string;
}

export type TermAndCondition = {
    id: string;
    term_and_condition: string;
    created_at: string;
    updated_at: string;
}

export type Country = {
    id: string;
    name: string;
    created_at: string;
    updated_at: string;
}

export type GuestAuthor = {
    id: string;
    title: string;
    name: string;
    first_name: string;
    middle_name: string;
    last_name: string;
    email: string;
    highest_qualification: string;
    organization_institution: string;
    country?: Country;
    can_edit?: boolean;
    created_at: string;
    updated_at: string;
    pending_review_count?: number;
    research_areas?: ResearchArea[];
}

export type Author = GuestAuthor & {
    section: Section;
    scopus_id: string;
    orcid_id: string;
    position: string;
    department: string;
    address_line_1: string;
    address_line_2: string;
    city: string;
    state: string;
    postal_code: string;
    research_areas?: ResearchArea[];
    privacy_policy_accepted: boolean;
    subscribed_for_notifications: boolean;
    accept_review_request: boolean;
    roles?: Role[];
    permissions?: Permission[];
}

export type Action = {
    value: string;
    label: string;
}

export type Section = {
    value: string;
    label: string;
}

export type ManuscriptType = {
    value: string;
    label: string;
}

export type ManuscriptFilter = {
    value: string;
    label: string;
}

export type ManuscriptStatus = {
    value: string;
    label: string;
}

export type ReviewDecision = {
    value: string;
    label: string;
}

export type Manuscript = {
    id: string;
    code: string;
    type: ManuscriptType;
    copyright_form: string;
    revision?: Revision;
    author?: Author;
    research_areas?: ResearchArea[];
    term_and_conditions?: TermAndCondition[];
    reviews?: Review[];
    revisions?: Revision[];
    reviewers?: RevisionReviewer[];
    authors?: RevisionAuthor[];
    events?: RevisionEvent[];
    step: Record<string, boolean>,
    created_at: string;
    updated_at: string;
}

export type RevisionReviewer = {
    id: string;
    section: Section;
    created_at: string;
    updated_at: string;
    invited_at?: string;
    accepted_at?: string;
    denied_at?: string;
    invite_count?: number;
    remind_count?: number;
    reviewer: GuestAuthor;
    review?: Review;
}

export type RevisionAuthor = {
    id: string;
    created_at: string;
    updated_at: string;
    author: GuestAuthor;
}

export type RevisionEvent = {
    id: string;
    title: string;
    created_at: string;
    revision?: Revision;
}

export type Revision = {
    id: string;
    code: string;
    title: string;
    abstract: string;
    keywords: string;
    novelty: string;
    comments_to_eic: string;
    comment_reply: string;
    anonymous_file: string;
    source_file: string;
    comment_reply_file: string;
    status: ManuscriptStatus;
    minimum_reviews: number;
    similarity: number;
    pages: number;
    grammar_updated: boolean;
    associate_editor?: Author;
    reviews?: Review[];
    reviewers?: RevisionReviewer[];
    authors?: RevisionAuthor[];
    events?: RevisionEvent[];
    created_at: string;
    updated_at: string;
}

export type Review = {
    id: string;
    comments_to_author: string;
    decision: ReviewDecision;
    comments_to_associate_editor?: string;
    review_report?: string;
    created_at: string;
    updated_at: string;
    revision?: Revision;
    reviewer?: Author;
}

export type Permission = {
    id: string;
    name: string;
    label: string;
}

export type Role = {
    id: string;
    name: string;
    section: Section;
    default: boolean;
    created_at: string;
    updated_at: string;
    permissions?: Permission[];
}

export type HeaderItem = {
    label: string;
    value: string;
}

export type AsideItem = {
    label: string;
    path: string;
    badge?: number;
    active: boolean;
}

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    url: {
        current: string;
        previous: string;
    };
    flash?: {
        type: 'success' | 'error',
        message: string
    };
    auth: {
        user: Author;
    };
    header: {
        items: HeaderItem[];
    };
    aside: {
        items: AsideItem[];
    };
    search?: {
        items: Manuscript[];
    };
};
