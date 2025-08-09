<script setup lang="ts">
import { basename, date, extension, tinymceDefaultProps } from '@/ts/app';
import type { Author, Country, Manuscript, RevisionReviewer } from '@/ts/types';
import Modal from '@/vue/components/modal.vue';
import Processing from '@/vue/components/processing.vue';
import Layout from '@/vue/layout.vue';
import EditAuthor from '@/vue/manuscript/edit-author.vue';
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import Editor from '@tinymce/tinymce-vue';
import { parse } from 'qs';
import { computed, ref } from 'vue';

type Props = {
    manuscript: Manuscript;
    countries: Country[];
    reviewers?: RevisionReviewer['reviewer'][];
    similar_research_area_reviewers?: Author[];
    reinvite_reviewer_email_content?: { subject: string, content: string };
}

const { props: { url, auth } } = usePage();
const { manuscript, reviewers, countries } = defineProps<Props>();

const filter = useForm<{ search?: string }>(
    Object.assign({ search: '' }, parse(window.location.search, { ignoreQueryPrefix: true }))
)

const form = useForm({
    minimum_reviews: manuscript?.revision?.minimum_reviews,
});

const authors = computed(() => manuscript.authors?.map(x => x.author)?.filter(x => x));

const groupedEvents = computed(() => manuscript.events?.reduce((grouped, event) => {
    (grouped[date(event.created_at)] = grouped[date(event.created_at)] || []).push(event);
    return grouped;
}, {} as Record<string, Manuscript['events']>))


const activeRevisionIndex = ref(0);
const reInviteReviewerModalOpened = ref(false);
const similarResearchAreaReviewersModalOpened = ref(false);

const addRevisionReviewer = ref([]);

const addReviewerForm = useForm({});
const removeReviewerForm = useForm({});

const reInviteReviewerForm = useForm({
    revision_reviewer: undefined as string | undefined,
    subject: undefined as string | undefined,
    content: undefined as string | undefined,
});

const updateRevisionReviewer = ref<Partial<RevisionReviewer['reviewer']>>();

function addReviewer({ country, ...reviewer }: RevisionReviewer['reviewer']) {
    addReviewerForm.transform(() => ({ ...reviewer, country: country?.id }))
        .post(`/manuscripts/${manuscript.id}/add-reviewer`, { onFinish: clearSearch, preserveScroll: true });
}



function editReviewer(revision_reviewer: Partial<RevisionReviewer['reviewer']>) {
    clearSearch();
    updateRevisionReviewer.value = revision_reviewer;
}

function removeReviewer(revision_reviewer: RevisionReviewer) {
    removeReviewerForm.transform(() => ({ revision_reviewer: revision_reviewer.id }))
        .post(`/manuscripts/${manuscript.id}/remove-reviewer`, { onFinish: clearSearch, preserveScroll: true });
}

function inviteReviewer(revision_reviewer: RevisionReviewer) {
    return router.post(
        `/manuscripts/${manuscript.id}/invite-reviewer`,
        { revision_reviewer: revision_reviewer.id },
        { onSuccess: clearSearch, preserveScroll: true },
    );
}

function previewReInviteReviewer(revision_reviewer: RevisionReviewer) {
    router.reload({
        only: ['reinvite_reviewer_email_content'],
        data: { revision_reviewer: revision_reviewer.id },
        onSuccess: (res) => {
            const mail = res?.props?.reinvite_reviewer_email_content as Props['reinvite_reviewer_email_content'];
            reInviteReviewerForm.subject = (mail?.subject ?? '') as string
            reInviteReviewerForm.content = (mail?.content ?? '') as string
        },
    });

    reInviteReviewerForm.revision_reviewer = revision_reviewer.id;
    reInviteReviewerModalOpened.value = true;
}

function sendReminder(revision_reviewer: RevisionReviewer) {
    return router.post(
        `/manuscripts/${manuscript.id}/remind-reviewer`,
        { revision_reviewer: revision_reviewer.id },
        { onSuccess: clearSearch, preserveScroll: true },
    );
}

function sendReviewer() {

    addReviewerForm.transform(() => ({ ...addRevisionReviewer }))
        .post(`/manuscripts/${manuscript.id}/add-reviewers`, { onFinish: clearSearch, preserveScroll: true });
}

function similarResearchAreaReviewers() {
    router.reload({ only: ['similar_research_area_reviewers'] })
    similarResearchAreaReviewersModalOpened.value = true;
}

function submitSearch() {
    addReviewerForm.clearErrors();
    removeReviewerForm.clearErrors();

    filter.get(url.current, {
        only: ['reviewers'],
        preserveState: true,
        preserveScroll: true,
    })
}

function submitReviewerSearch() {
    
    addReviewerForm.clearErrors();
    removeReviewerForm.clearErrors();

    filter.get(url.current, {
        only: ['similar_research_area_reviewers'],
        preserveState: true,
        preserveScroll: true,
    })
}

function clearSearch() {
    filter.search = undefined;
    window.history.replaceState(null, '', window.location.pathname)
}

</script>

<template>
    <Layout title="Invite Reviewer">
        <div class="flex items-center mb-2">
            <Link :href="url.previous">
            <i class="bi bi-chevron-compact-left text-2xl"></i>
            </Link>
            <h4 class="text-2xl">Invite Reviewer</h4>
            <button type="submit" class="ml-auto bg-primary-500 text-white py-2 px-4" form="edit-form"
                :disabled="form.processing">
                <Processing v-if="form.processing" class="h-3 my-1.5 w-auto mx-auto text-white" />
                <template v-else><i class="bi bi-arrow-up mr-1"></i> Save</template>
            </button>
        </div>
        <div class="border p-4 md:p-8 rounded shadow bg-white">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
                <div>
                    <label class="block mb-1 font-semibold">Paper ID</label>
                    <span class="border block p-2.5 rounded">{{ manuscript.code || '-' }}</span>
                </div>
                <div>
                    <label class="block mb-1 font-semibold">Manuscript Type</label>
                    <span class="border block p-2.5 rounded">{{ manuscript.type.label || '-' }}</span>
                </div>
                <div>
                    <label for="minimum_reviews" class="block mb-1 font-semibold"># Reviews Required</label>
                    <input type="text" v-model="form.minimum_reviews" id="minimum_reviews"
                        placeholder="Enter # reviews required" autocomplete="off" form="edit-form" />
                    <div v-if="form.errors?.minimum_reviews" class="text-sm text-red-700">{{ form.errors.minimum_reviews }}
                    </div>
                </div>
            </div>
            <div class="mb-4">
                <label class="block mb-1 font-semibold">Title</label>
                <span class="border block p-2.5 rounded">{{ manuscript.revision?.title || '-' }}</span>
            </div>
            <div class="mb-4">
                <label class="block mb-1 font-semibold">Abstract</label>
                <span class="border block p-2.5 rounded">{{ manuscript.revision?.abstract || '-' }}</span>
            </div>
            <div class="mb-4">
                <label class="block mb-1 font-semibold">Keywords</label>
                <span class="border block p-2.5 rounded">{{ manuscript.revision?.keywords || '-' }}</span>
            </div>
            <div class="mb-4">
                <label class="block mb-1 font-semibold">What is novelty in this article?</label>
                <span class="border block p-2.5 rounded">{{ manuscript.revision?.novelty || '-' }}</span>
            </div>
            <div class="mb-4" v-if="manuscript.research_areas">
                <label class="block mb-1 font-semibold">Broad Research Areas of Article</label>
                <ul class="list-disc ml-6">
                    <li v-for="item in manuscript.research_areas" :key="`research-area-${item.id}`">{{ item.research_area }}
                    </li>
                </ul>
            </div>
            <div class="mb-4" v-if="manuscript.revision?.anonymous_file">
                <label class="block mb-1 font-semibold">Anonymous File</label>
                <i class="bi bi-filetype-pdf text-xl mr-2"></i>
                <a :href="manuscript.revision?.anonymous_file" target="_blank" class="text-primary-400">{{
                    basename(manuscript.revision?.anonymous_file) }}</a>
            </div>
            <div class="mb-4" v-if="manuscript.revision?.source_file">
                <label class="block mb-1 font-semibold">Source File</label>
                <i class="bi bi-filetype-docx text-xl mr-2"></i>
                <a :href="manuscript.revision?.source_file" target="_blank" class="text-primary-400">{{
                    basename(manuscript.revision?.source_file) }}</a>
            </div>
            <div class="mb-4" v-if="manuscript.copyright_form">
                <label class="block mb-1 font-semibold">Copyright Form</label>
                <i class="bi text-xl mr-2" :class="`bi-filetype-${extension(manuscript.copyright_form)}`"></i>
                <a :href="manuscript.copyright_form" target="_blank" class="text-primary-400">{{
                    basename(manuscript.copyright_form) }}</a>
            </div>
            <div class="mb-4">
                <label class="block mb-1 font-semibold">Reviewers from Author</label>
                <div class="overflow-y-auto rounded">
                    <table class="table text-left text-sm">
                        <thead class="uppercase">
                            <tr>
                                <th>S. No.</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Highest Qualification</th>
                                <th>Organization/Institution</th>
                                <th>Country</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template
                                v-if="manuscript.revision?.reviewers?.filter(x => (x.section.value === 'reviewer' || x.section.value === 'author')) && manuscript.revision?.reviewers?.filter(x => (x.section.value === 'reviewer' || x.section.value === 'author')).length > 0">
                                <tr v-for="(revision_reviewer, i) in manuscript.revision?.reviewers?.filter(x => (x.section.value === 'reviewer' || x.section.value === 'author'))"
                                    :key="`row-${i}`">
                                    <td>{{ i + 1 }}</td>
                                    <td>
                                        <div class="relative">
                                            <span>{{ revision_reviewer.reviewer.title }} {{ revision_reviewer.reviewer.name
                                                ?? '-' }}</span>
                                            <span v-if="revision_reviewer.reviewer.pending_review_count"
                                                class="absolute inline-flex items-center justify-center w-5 h-5 text-xs bg-blue-200 rounded-full -top-2 -right-2">{{
                                                    revision_reviewer.reviewer.pending_review_count }}</span>
                                        </div>
                                    </td>
                                    <td>{{ revision_reviewer.reviewer.email ?? '-' }}</td>
                                    <td>{{ revision_reviewer.reviewer.highest_qualification ?? '-' }}</td>
                                    <td>{{ revision_reviewer.reviewer.organization_institution ?? '-' }}</td>
                                    <td>{{ revision_reviewer.reviewer.country?.name ?? '-' }}</td>
                                    <td>
                                        <div v-if="revision_reviewer.review" class="text-center">
                                            <div class="truncate text-green-500">Reviewed On</div>
                                            <div class="truncate text-gray-500">({{
                                                date(revision_reviewer.review.created_at) }})</div>
                                        </div>
                                        <div v-else-if="revision_reviewer.accepted_at" class="text-center">
                                            <div class="truncate text-green-500">Agreed for Review</div>
                                            <div class="truncate text-gray-500 mb-1">({{ date(revision_reviewer.accepted_at)
                                            }})</div>
                                            <button type="button" @click.prevent="sendReminder(revision_reviewer)"
                                                class="relative text-primary-400 py-0.5 w-full">
                                                <span>Send Reminder</span>
                                                <span v-if="revision_reviewer.remind_count"
                                                    class="absolute inline-flex items-center justify-center w-5 h-5 text-xs bg-blue-200 rounded-full -top-2 -right-2">{{
                                                        revision_reviewer.remind_count }}</span>
                                            </button>
                                        </div>
                                        <div v-else-if="revision_reviewer.denied_at" class="text-center">
                                            <div class="truncate text-red-500">Denied for Review</div>
                                            <div class="truncate text-gray-500">({{ date(revision_reviewer.denied_at) }})
                                            </div>
                                        </div>
                                        <div v-else-if="revision_reviewer.invited_at" class="text-center">
                                            <div class="truncate">Invited On</div>
                                            <div class="truncate text-gray-500 mb-1">({{ date(revision_reviewer.invited_at)
                                            }})</div>
                                            <button type="button"
                                                @click.prevent="previewReInviteReviewer(revision_reviewer)"
                                                class="relative text-primary-400 py-0.5 w-full">
                                                <span>Reinvite</span>
                                                <span v-if="revision_reviewer.invite_count"
                                                    class="absolute inline-flex items-center justify-center w-5 h-5 text-xs bg-blue-200 rounded-full -top-2 -right-2">{{
                                                        revision_reviewer.invite_count }}</span>
                                            </button>
                                        </div>
                                        <div v-else class="text-center">
                                            <button type="button" @click.prevent="inviteReviewer(revision_reviewer)"
                                                class="text-primary-400 py-0.5 w-full">Invite</button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            <template v-else>
                                <tr>
                                    <td colspan="7" class="py-2 text-center">
                                        <p class="text-gray-800 text-base">No reviewers found.</p>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mb-4 group relative">
                <div class="flex">
                    <label class="block mb-1 font-semibold">Reviewers from Associate Editor</label>
                    <a href="#" class="text-primary-400 text-sm ml-auto"   v-if="['drmrswami@yahoo.com', 'reviewingeditor@ijmems.in'].includes(auth.user.email)"
 @click.prevent="similarResearchAreaReviewers()">Add
                        Reviewer from Journal Office</a>
                </div>
                <form @submit.prevent="submitSearch" id="search-form" novalidate>
                    <div class="relative">
                        <div v-if="filter.processing"
                            class="flex absolute inset-y-0 right-0 items-center pr-3 pointer-events-none">
                            <Processing class="h-3 w-auto text-primary-500" />
                        </div>
                        <input type="search" v-model="filter.search" placeholder="Enter reviewer email address to add"
                            autocomplete="off" @input="submitSearch" />
                    </div>
                    <div v-for="row of addReviewerForm.errors" class="text-sm text-red-700">{{ row }}</div>
                    <div v-for="row of removeReviewerForm.errors" class="text-sm text-red-700">{{ row }}</div>
                </form>
                <ul v-if="reviewers && reviewers.length > 0"
                    class="absolute z-10 max-h-60 w-full overflow-x-hidden overflow-y-auto overflow-ellipsis rounded-lg border bg-white shadow-lg group-focus-within:block">
                    <li class="cursor-pointer border-t p-2.5 hover:bg-gray-100" v-for="(row, i) in reviewers"
                        :key="`row-${i}`" @click="addReviewer(row)">
                        <p>{{ row.title }} {{ row.name }}</p>
                        <p class="text-gray-500 text-sm">{{ row.email }}</p>
                        <p class="text-gray-500 text-sm">{{ row.highest_qualification }}</p>
                        <p class="text-gray-500 text-sm">{{ row.organization_institution }}</p>
                        <p class="text-gray-500 text-sm">{{ row.country?.name }}</p>
                    </li>
                </ul>
                <p v-else-if="filter.search" class="text-sm text-gray-500 mt-2">Reviewer not found. <a href="#"
                        class="text-primary-400" @click.prevent="editReviewer({ email: filter.search })">Click here to add
                        manually.</a></p>
            </div>
            <form @submit.prevent="form.post(url.current)" id="edit-form" class="mb-4" novalidate>
                <div class="overflow-y-auto rounded">
                    <table class="table text-left text-sm">
                        <thead class="uppercase">
                            <tr>
                                <th>S. No.</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Highest Qualification</th>
                                <th>Organization/Institution</th>
                                <th>Country</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template
                                v-if="manuscript.revision?.reviewers?.filter(x => x.section.value === 'associate-editor').length === 0">
                                <tr>
                                    <td :colspan="7" class="py-7 text-center">
                                        <h6 class="text-xl text-gray-800">No reviewers added.</h6>
                                        <p class="text-sm text-gray-700">Try to add reviewer using search by email.</p>
                                    </td>
                                </tr>
                            </template>
                            <template v-else>
                                <tr v-for="(revision_reviewer, i) in manuscript.revision?.reviewers?.filter(x => x.section.value === 'associate-editor')"
                                    :key="`row-${i}`">
                                    <td>{{ i + 1 }}</td>
                                    <td>
                                        <div class="relative">
                                            <span>{{ revision_reviewer.reviewer.title }} {{ revision_reviewer.reviewer.name
                                                ?? '-' }}</span>
                                            <span v-if="revision_reviewer.reviewer.pending_review_count"
                                                class="absolute inline-flex items-center justify-center w-5 h-5 text-xs bg-blue-200 rounded-full -top-2 -right-2">{{
                                                    revision_reviewer.reviewer.pending_review_count }}</span>
                                        </div>
                                    </td>
                                    <td>{{ revision_reviewer.reviewer.email ?? '-' }}</td>
                                    <td>{{ revision_reviewer.reviewer.highest_qualification ?? '-' }}</td>
                                    <td>{{ revision_reviewer.reviewer.organization_institution ?? '-' }}</td>
                                    <td>{{ revision_reviewer.reviewer.country?.name ?? '-' }}</td>
                                    <td>
                                        <div v-if="revision_reviewer.review" class="text-center">
                                            <div class="truncate text-green-500">Reviewed On</div>
                                            <div class="truncate text-gray-500">({{
                                                date(revision_reviewer.review.created_at) }})</div>
                                        </div>
                                        <div v-else-if="revision_reviewer.accepted_at" class="text-center">
                                            <div class="truncate text-green-500">Agreed for Review</div>
                                            <div class="truncate text-gray-500 mb-1">({{ date(revision_reviewer.accepted_at)
                                            }})</div>
                                            <button type="button" @click.prevent="sendReminder(revision_reviewer)"
                                                class="relative text-primary-400 py-0.5 w-full">
                                                <span>Send Reminder</span>
                                                <span v-if="revision_reviewer.remind_count"
                                                    class="absolute inline-flex items-center justify-center w-5 h-5 text-xs bg-blue-200 rounded-full -top-2 -right-2">{{
                                                        revision_reviewer.remind_count }}</span>
                                            </button>
                                        </div>
                                        <div v-else-if="revision_reviewer.denied_at" class="text-center mb-1">
                                            <div class="truncate text-red-500">Denied for Review</div>
                                            <div class="truncate text-gray-500">({{ date(revision_reviewer.denied_at) }})
                                            </div>
                                        </div>
                                        <div v-else-if="revision_reviewer.invited_at" class="text-center">
                                            <div class="truncate">Invited On</div>
                                            <div class="truncate text-gray-500 mb-1">({{ date(revision_reviewer.invited_at)
                                            }})</div>
                                            <button type="button"
                                                @click.prevent="previewReInviteReviewer(revision_reviewer)"
                                                class="relative text-primary-400 py-0.5 w-full">
                                                <span>Reinvite</span>
                                                <span v-if="revision_reviewer.invite_count"
                                                    class="absolute inline-flex items-center justify-center w-5 h-5 text-xs bg-blue-200 rounded-full -top-2 -right-2">{{
                                                        revision_reviewer.invite_count }}</span>
                                            </button>
                                        </div>
                                        <template v-else>
                                            <div class="text-center mb-1" v-if="revision_reviewer.reviewer.can_edit">
                                                <a href="#" @click.prevent="editReviewer(revision_reviewer.reviewer)"
                                                    class="text-primary-400">Edit</a>
                                            </div>
                                            <div class="text-center mb-1">
                                                <a href="#" @click.prevent="removeReviewer(revision_reviewer)"
                                                    class="text-red-400">Remove</a>
                                            </div>
                                            <div class="text-center">
                                                <button type="button" @click.prevent="inviteReviewer(revision_reviewer)"
                                                    class="text-primary-400 py-0.5 w-full">Invite</button>
                                            </div>
                                        </template>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                <div v-for="row of form.errors" class="text-sm text-red-700">{{ row }}</div>
            </form>
            <div class="mt-4" v-if="manuscript.author">
                <label class="block mb-1 font-semibold">Corresponding Author</label>
                <div class="overflow-y-auto rounded">
                    <table class="table text-left text-sm">
                        <thead class="uppercase">
                            <tr>
                                <th>S. No.</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Highest Qualification</th>
                                <th>Organization/Institution</th>
                                <th>Country</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ 1 }}</td>
                                <td>{{ manuscript.author.title }} {{ manuscript.author.name ?? '-' }}</td>
                                <td>{{ manuscript.author.email ?? '-' }}</td>
                                <td>{{ manuscript.author.highest_qualification ?? '-' }}</td>
                                <td>{{ manuscript.author.organization_institution ?? '-' }}</td>
                                <td>{{ manuscript.author.country?.name ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-4" v-if="authors">
                <label class="block mb-1 font-semibold">Co-authors</label>
                <div class="overflow-y-auto rounded">
                    <table class="table text-left text-sm">
                        <thead class="uppercase">
                            <tr>
                                <th>S. No.</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Highest Qualification</th>
                                <th>Organization/Institution</th>
                                <th>Country</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-if="authors.length > 0">
                                <tr v-for="(row, i) in authors" :key="`row-${i}`">
                                    <td>{{ i + 1 }}</td>
                                    <td>{{ row.title }} {{ row.name ?? '-' }}</td>
                                    <td>{{ row.email ?? '-' }}</td>
                                    <td>{{ row.highest_qualification ?? '-' }}</td>
                                    <td>{{ row.organization_institution ?? '-' }}</td>
                                    <td>{{ row.country?.name ?? '-' }}</td>
                                </tr>
                            </template>
                            <template v-else>
                                <tr>
                                    <td colspan="7" class="py-2 text-center">
                                        <p class="text-gray-800 text-base">No co-authors found.</p>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
            <template v-if="manuscript.revisions && manuscript.revisions.length > 0">
                <div class="mt-4">
                    <label class="block mb-1 font-semibold">Revisions</label>
                    <div class="mb-2.5 border-b border-gray-200">
                        <ul class="flex text-center">
                            <li class="w-full" v-for="(revision, i) in manuscript.revisions" :key="`revision-${i}`">
                                <a href="#" class="inline-block border w-full py-2.5 text-gray-900 border-b-2 rounded-t"
                                    :class="`${activeRevisionIndex === i ? 'text-white bg-primary-500 border-primary-600' : ''}`"
                                    @click.prevent="activeRevisionIndex = i">{{ revision.code }}</a>
                            </li>
                        </ul>
                    </div>
                    <template v-for="(revision, i) in manuscript.revisions" :key="`revision-${i}`">
                        <div :class="`${activeRevisionIndex === i ? '' : 'hidden'}`">
                            <template v-if="revision.reviews && revision.reviews.length > 0">
                                <div class="mt-4">
                                    <label class="block mb-1 font-semibold">Reviewers comments</label>
                                    <div class="my-3 rounded" :class="{ 'bg-gray-200 bg-opacity-20': i % 2 === 0 }"
                                        v-for="(review, i) in revision.reviews" :key="`review-${i}`">
                                        <div class="p-2.5 bg-gradient-to-r border-l-4 rounded"
                                            :class="{ 'accept': 'border-l-green-500', 'minor-revision-required': 'border-l-yellow-500', 'major-revision-required': 'border-l-orange-500', 'reject': 'border-l-red-500' }[review.decision.value]">
                                            <div class="grid grid-cols-1"
                                                :class="`${review.reviewer ? 'md:grid-cols-3' : 'md:grid-cols-2'}`">
                                                <div v-if="review.reviewer">
                                                    <label class="block mb-1 font-semibold">Reviewer:</label>
                                                    <p class="mb-3 font-sm">{{ review.reviewer.name ?? '-' }}</p>
                                                </div>
                                                <div :class="{ 'md:text-center': !!review.reviewer }">
                                                    <label class="block mb-1 font-semibold">Decision:</label>
                                                    <p class="mb-3 font-bold">{{ review.decision.label ?? '-' }}</p>
                                                </div>
                                                <div class="md:text-right">
                                                    <label class="block mb-1 font-semibold">Reviewed on:</label>
                                                    <p class="mb-3 font-sm">{{ date(review.created_at) }}</p>
                                                </div>
                                            </div>
                                            <label class="block mb-1 font-semibold">Comments to Author:</label>
                                            <p class="mb-3 text-sm">{{ review.comments_to_author ?? '-' }}</p>
                                            <template v-if="review.comments_to_associate_editor">
                                                <label class="block mb-1 font-semibold">Comments to Associate
                                                    Editor:</label>
                                                <p class="text-sm">{{ review.comments_to_associate_editor ?? '-' }}</p>
                                            </template>
                                            <div class="mt-3" v-if="review.review_report">
                                                <label class="block mb-1 font-semibold">Review Report:</label>
                                                <i class="bi text-xl mr-2"
                                                    :class="`bi-filetype-${extension(review.review_report)}`"></i>
                                                <a :href="review.review_report" target="_blank" class="text-primary-400">{{
                                                    basename(review.review_report) }}</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <template v-else>
                                <div class="mt-4">
                                    <div class="p-2.5">
                                        <p class="text-gray-800 text-center">No reviewers comments found.</p>
                                    </div>
                                </div>
                            </template>
                            <div class="mt-4">
                                <hr />
                            </div>
                            <div class="mt-4">
                                <label class="block mb-1 font-semibold">Associate Editor Comment:</label>
                                <p class="text-sm">{{ revision.comments_to_eic ?? '-' }}</p>
                            </div>
                            <div class="mt-4">
                                <hr />
                            </div>
                            <div class="mt-4">
                                <label class="block mb-1 font-semibold">Author Reply to Reviewers and Editors
                                    comments:</label>
                                <p class="text-sm">{{ revision.comment_reply ?? '-' }}</p>
                            </div>
                            <template v-if="manuscript.revision?.id !== revision.id">
                                <div class="mt-4" v-if="revision.anonymous_file">
                                    <label class="block mb-1 font-semibold">Anonymous File</label>
                                    <i class="bi bi-filetype-pdf text-xl mr-2"></i>
                                    <a :href="revision.anonymous_file" target="_blank" class="text-primary-400">{{
                                        basename(revision.anonymous_file) }}</a>
                                </div>
                                <div class="mt-4" v-if="revision.source_file">
                                    <label class="block mb-1 font-semibold">Source File</label>
                                    <i class="bi bi-filetype-docx text-xl mr-2"></i>
                                    <a :href="revision.source_file" target="_blank" class="text-primary-400">{{
                                        basename(revision.source_file) }}</a>
                                </div>
                            </template>
                            <div class="mt-4" v-if="revision.comment_reply_file">
                                <label class="block mb-1 font-semibold">Comments Reply File</label>
                                <i class="bi bi-filetype-pdf text-xl mr-2"></i>
                                <a :href="revision.comment_reply_file" target="_blank" class="text-primary-400">{{
                                    basename(revision.comment_reply_file) }}</a>
                            </div>
                        </div>
                    </template>
                </div>
            </template>
            <template v-if="groupedEvents">
                <div class="mt-4">
                    <hr />
                </div>
                <div class="mt-4">
                    <label class="block mb-1 font-semibold">Events</label>
                    <ol class="relative border-l border-gray-200">
                        <template v-for="(events, created_at) of groupedEvents">
                            <li v-if="events && events.length" class="ml-4 mb-4">
                                <div class="absolute w-3 h-3 bg-primary-500 rounded-full mt-1.5 -left-1.5"></div>
                                <time class="mb-1 text-sm font-normal leading-none text-gray-400">{{ created_at }}</time>
                                <ul class="list-disc ml-3 pl-1 marker:text-gray-400">
                                    <li v-for="(event, i) of events" class="text-gray-900" :key="`event-${i}`">
                                        <span v-if="event.title === 'Submitted'">{{ event.revision?.code }} &nbsp;</span>
                                        <span>{{ event.title }}</span>
                                    </li>
                                </ul>
                            </li>
                        </template>
                    </ol>
                </div>
            </template>
        </div>
        <Modal v-if="updateRevisionReviewer !== undefined" title="Add Reviewer" @close="updateRevisionReviewer = undefined">
            <EditAuthor :action="`/manuscripts/${manuscript.id}/add-reviewer-manually`" :author="updateRevisionReviewer"
                :countries="countries" @onSuccess="updateRevisionReviewer = undefined" />
        </Modal>
        <Modal v-if="similarResearchAreaReviewersModalOpened && similar_research_area_reviewers"
            title="Reviewers from Journal Office" size="xxxl" @close="similarResearchAreaReviewersModalOpened = false">
            <div class="p-3">
                <div class="overflow-auto rounded" style="height:800px;">
                    <div class="flex items-center mb-2" style="height:40px;">
                        <button type="submit" @click="sendReviewer()" class="ml-auto py-2 px-4 rounded"
                            style="position: fixed;right: 90px; background: blueviolet;color: #fff;">Add Reviewer</button>

                            <form @submit.prevent="submitReviewerSearch" id="search-form" novalidate>
                                <div class="relative">
                                    <div v-if="filter.processing" class="flex absolute inset-y-0 right-0 items-center pr-3 pointer-events-none">
                                        <Processing class="h-3 w-auto text-primary-500" />
                                    </div>
                                    <input style="width: 438px; margin-top: 1px; border: 1px solid blueviolet; font-weight: 600;" type="search" v-model="filter.search" placeholder="Enter reviewer email address or first name or last name to add" autocomplete="off" @input="submitReviewerSearch" />
                                </div>
                                <div v-for="row of addReviewerForm.errors" class="text-sm text-red-700">{{ row }}</div>
                                <div v-for="row of removeReviewerForm.errors" class="text-sm text-red-700">{{ row }}</div>
                            </form>
                    </div>
                    <table class="table text-left text-sm">
                        <thead class="uppercase">
                            <tr>
                                <th>S. No.</th>
                                <th>Name<br />Email</th>
                                <th>Area of Interest</th>
                                <th>Highest Qualification<br />Organization/Institution</th>
                                <th>Country</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-if="similar_research_area_reviewers.length > 0">
                                <tr v-for="(row, i) in similar_research_area_reviewers" :key="`row-${i}`">
                                    <td>{{ i + 1 }}</td>
                                    <td>
                                        <div class="relative">
                                            <div class="truncate max-w-xs">{{ row.title }} {{ row.name ?? '-' }}</div>
                                            <div class="truncate max-w-xs text-gray-500">{{ row.email ?? '-' }}</div>
                                            <span v-if="row.pending_review_count"
                                                class="absolute inline-flex items-center justify-center w-5 h-5 text-xs bg-blue-200 rounded-full -top-2 -right-2">{{
                                                    row.pending_review_count }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <!-- <div class="truncate max-w-xs text-gray-500">{{ row.research_areas  ?? '-' }}</div> -->
                                        <div class="text-gray-500">{{ row.research_areas?.map(item =>
                                            item.research_area.trim()).join(', ') ?? '-' }}</div>

                                    </td>

                                    <td>
                                        <div class="truncate max-w-xs">{{ row.highest_qualification ?? '-' }}</div>
                                        <div class="truncate max-w-xs">{{ row.organization_institution ?? '-' }}</div>
                                    </td>
                                    <td class="truncate max-w-[6rem]" :title="row.country?.name">{{ row.country?.name ?? '-'
                                    }}</td>
                                    <td class="text-center">
                                        <input type="checkbox" v-model="addRevisionReviewer" :value="row">
                                    </td>
                                </tr>
                            </template>
                            <template v-else>
                                <tr>
                                    <td colspan="7" class="py-2 text-center">
                                        <p class="text-gray-800 text-base">No reviewers found.</p>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </Modal>
        <Modal v-if="reInviteReviewerModalOpened && !!reInviteReviewerForm.content" title="Reinvite Reviewer" size="xl"
            @close="(reInviteReviewerModalOpened = false, reInviteReviewerForm.content = '')">
            <div class="p-3">
                <div class="mb-4">
                    <label for="subject" class="block mb-1 font-semibold">Subject</label>
                    <input type="text" v-model="reInviteReviewerForm.subject" id="subject" placeholder="Enter email subject"
                        autocomplete="off" />
                    <div v-if="reInviteReviewerForm.errors?.subject" class="text-sm text-red-700">{{
                        reInviteReviewerForm.errors.subject }}</div>
                </div>
                <div class="mb-4">
                    <label for="Body" class="block mb-1 font-semibold">Body</label>
                    <Editor v-bind="tinymceDefaultProps" v-model="reInviteReviewerForm.content" />
                    <div v-if="reInviteReviewerForm.errors?.content" class="text-sm text-red-700">{{
                        reInviteReviewerForm.errors.content }}</div>
                </div>
            </div>
            <div class="py-2 flex justify-center space-x-1">
                <Processing v-if="reInviteReviewerForm.processing" class="h-3 my-1.5 w-auto mx-aut" />
                <template v-else>
                    <button type="button" class="text-sm py-2 px-4 bg-primary-500 text-white"
                        @click.prevent="reInviteReviewerForm.post(`/manuscripts/${manuscript?.id}/reinvite-reviewer`, { onSuccess: () => reInviteReviewerModalOpened = false })">Send</button>
                    <button type="button" class="text-sm py-2 px-4"
                        @click.prevent="reInviteReviewerModalOpened = false">Cancel</button>
                </template>
            </div>
        </Modal>
    </Layout>
</template>

<style scoped lang="scss"></style>
