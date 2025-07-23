<script setup lang="ts">
import { basename, date, extension } from '@/ts/app';
import type { Manuscript, Review, ReviewDecision, Revision, TermAndCondition } from '@/ts/types';
import Modal from '@/vue/components/modal.vue';
import Processing from '@/vue/components/processing.vue';
import Layout from '@/vue/layout.vue';
import Step from '@/vue/manuscript/step.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

type Props = {
    can_submit?: boolean;
    can_edit_review?: boolean;
    manuscript: Manuscript;
    term_and_conditions?: TermAndCondition[];
    review_decisions?: ReviewDecision[];
}
let sn = 1;
let sno = 1;
let checkauth = 0;
let authorno = 0;
let productionno = 0;
function sectionCheck(e:any){
if( e === 'proofreader' || e === 'formatter' || e === 'reviewer') {
    checkauth += 1;
    return false;
}else {
    checkauth += 1;
    authorno++;
    return true;
}
}

let checkauthentication = 0;
function sectionCheckTeam(e:any){
if( e === 'proofreader' || e === 'formatter') {
    checkauthentication += 1;
    productionno++;
    return true;
}else {
    checkauthentication += 1;
    return false;
}
}


const { props: { url } } = usePage();
const { manuscript, term_and_conditions } = defineProps<Props>();
    // const { manuscript, term_and_conditions, authors_detail } = defineProps<Props>();
       
const authors = computed(() => manuscript.authors?.map(x => x.author)?.filter(x => x));
const reviewers = computed(() => manuscript.revision?.reviewers?.map(x => x.reviewer)?.filter(x => x));

const groupedEvents = computed(() => manuscript.events?.reduce((grouped, event) => {
    (grouped[date(event.created_at)] = grouped[date(event.created_at)] || []).push(event);
    return grouped;
}, {} as Record<string, Manuscript['events']>))

const activeRevisionIndex = ref(0);

const form = useForm({
    reviewers: [],
    term_and_conditions: manuscript.term_and_conditions?.map(item => item.id) || []
})

const updateCommentModalOpened = ref(false);

const updateCommentForm = useForm({
    revision: undefined as unknown as string | undefined,
    review: undefined as unknown as string | undefined,
    comments_to_author: undefined as unknown as string | undefined,
    decision: undefined as unknown as string | undefined,
    comments_to_associate_editor: undefined as unknown as string | undefined,
    review_report: undefined as unknown as string | File | undefined
});

function editReview(revision: Revision, review: Review) {
    updateCommentModalOpened.value = true;

    updateCommentForm.revision = revision.id;
    updateCommentForm.review = review.id;
    updateCommentForm.comments_to_author = review.comments_to_author;
    updateCommentForm.decision = review.decision.value;
    updateCommentForm.comments_to_associate_editor = review.comments_to_associate_editor;
    updateCommentForm.review_report = review.review_report;
}

function transformFormData({ term_and_conditions: _term_and_conditions }: { term_and_conditions: TermAndCondition['id'][] }) {
    return {
        term_and_conditions: term_and_conditions?.map(
            ({ id }) => ({ id, accepted: _term_and_conditions?.includes(id) })
        )
    }
}

function transformUpdateCommentFormData(data: any) {
    if (!(data.review_report instanceof File)) delete data.review_report;
    return data;
}
function checkactions(e:any){
    if(e.status?.value === 'ready-article' || e.status?.value === 'proofreader' || e.status?.value === 'formatter'  || e.status?.value === 'publication' || e.status?.value === 'published'){
        return true;
    }else{
        return false;
    }
}
</script>

<template>
    <Layout title="View Manuscript">
        <div class="flex items-center mb-2">
            <Link :href="url.previous">
            <i class="bi bi-chevron-compact-left text-2xl"></i>
            </Link>
            <h4 class="text-2xl">View Manuscript</h4>
            <button v-if="can_submit" type="submit" class="ml-auto bg-primary-500 text-white py-2 px-4" form="edit-form" :disabled="form.processing">
                <Processing v-if="form.processing" class="h-3 my-1.5 w-auto mx-auto text-white" />
                <template v-else><i class="bi bi-arrow-up mr-1"></i> Submit</template>
            </button>
        </div>
        <div class="border p-4 md:p-8 rounded shadow bg-white">
            <Step v-if="manuscript?.step" v-bind="manuscript.step" active="submit" />
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
                <div>
                    <label class="block mb-1 font-semibold">Paper ID</label>
                    <span class="border block p-2.5 rounded">{{ manuscript.code || '-' }}</span>
                </div>
                <div>
                    <label class="block mb-1 font-semibold">Manuscript Type</label>
                    <span class="border block p-2.5 rounded">{{ manuscript.type.label || '-' }}</span>
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
            <div class="mt-4" v-if="manuscript.research_areas">
                <label class="block mb-1 font-semibold">Broad Research Areas of Article</label>
                <ul class="list-disc ml-6">
                    <li v-for="item in manuscript.research_areas" :key="`research-area-${item.id}`">{{ item.research_area }}</li>
                </ul>
            </div>
            <div class="mt-4" v-if="manuscript.revision?.anonymous_file">
                <label class="block mb-1 font-semibold">Anonymous File</label>
                <i class="bi bi-filetype-pdf text-xl mr-2"></i>
                <a :href="manuscript.revision?.anonymous_file" target="_blank" class="text-primary-400">{{ basename(manuscript.revision?.anonymous_file) }}</a>
            </div>
            <div class="mt-4" v-if="manuscript.revision?.source_file">
                <label class="block mb-1 font-semibold">Source File</label>
                <i class="bi bi-filetype-docx text-xl mr-2"></i>
                <a :href="manuscript.revision?.source_file" target="_blank" class="text-primary-400">{{ basename(manuscript.revision?.source_file) }}</a>
            </div>
            <div class="mt-4" v-if="manuscript.copyright_form">
                <label class="block mb-1 font-semibold">Copyright Form</label>
                <i class="bi text-xl mr-2" :class="`bi-filetype-${extension(manuscript.copyright_form)}`"></i>
                <a :href="manuscript.copyright_form" target="_blank" class="text-primary-400">{{ basename(manuscript.copyright_form) }}</a>
            </div>
            <div class="mt-4" v-if="reviewers">
                <label class="block mb-1 font-semibold">Reviewers</label>
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
                            <template v-if="reviewers.length > 0">
                                <tr v-for="(row, i) in reviewers" :key="`row-${i}`">
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
                                        <p class="text-gray-800 text-base">No reviewers found.</p>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                <div v-if="form.errors?.reviewers" class="text-sm text-red-700">{{ form.errors.reviewers }}</div>
            </div>
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
                                    <template v-if="sectionCheck(row?.section?.value)">
                                    <td>{{ sn++ }}</td>
                                    <td>{{ row.title }} {{ row.name ?? '-' }}</td>
                                    <td>{{ row.email ?? '-' }}</td>
                                    <td>{{ row.highest_qualification ?? '-' }}</td>
                                    <td>{{ row.organization_institution ?? '-' }}</td>
                                    <td>{{ row.country?.name ?? '-' }}</td>
                                    </template>
                                </tr>
                                
                            </template>
                            <template v-if="authorno === 0">
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
            <div class="mt-4" v-if="authors && checkactions(manuscript?.revision)">
                <label class="block mb-1 font-semibold">Production Team</label>
                <div class="overflow-y-auto rounded">
                    <table class="table text-left text-sm">
                        <thead class="uppercase">
                            <tr>
                                <th>S. No.</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Highest Qualification</th>
                                <th>Organization/Institution</th>
                                <th>Section</th>
                                <th>Country</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-if="authors.length > 0">
                                <tr v-for="(row, i) in authors" :key="`row-${i}`">
                                    <template v-if="sectionCheckTeam(row?.section?.value)">
                                    <td>{{ sno++ }}</td>
                                    <td>{{ row.title }} {{ row.name ?? '-' }}</td>
                                    <td>{{ row.email ?? '-' }}</td>
                                    <td>{{ row.highest_qualification ?? '-' }}</td>
                                    <td>{{ row.organization_institution ?? '-' }}</td>
                                    <td>{{ row.section?.label ?? '-' }}</td>
                                    <td>{{ row.country?.name ?? '-' }}</td>
                                    </template>
                                </tr>
                                
                            </template>
                            <template v-if="productionno === 0">
                                <tr>
                                    <td colspan="7" class="py-2 text-center">
                                        <p class="text-gray-800 text-base">No Production Team found.</p>
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
                                <a href="#" class="inline-block border w-full py-2.5 text-gray-900 border-b-2 rounded-t" :class="`${activeRevisionIndex === i ? 'text-white bg-primary-500 border-primary-600' : ''}`" @click.prevent="activeRevisionIndex = i">{{ revision.code }}</a>
                            </li>
                        </ul>
                    </div>
                    <template v-for="(revision, i) in manuscript.revisions" :key="`revision-${i}`">
                        <div :class="`${activeRevisionIndex === i ? '' : 'hidden'}`">
                            <template v-if="revision.reviews && revision.reviews.length > 0">
                                <div class="mt-4">
                                    <label class="block mb-1 font-semibold">Reviewers comments</label>
                                    <div class="my-3 rounded" :class="{ 'bg-gray-200 bg-opacity-20': i % 2 === 0 }" v-for="(review, i) in revision.reviews" :key="`review-${i}`">
                                        <div class="p-2.5 bg-gradient-to-r border-l-4 rounded" :class="{ 'accept': 'border-l-green-500', 'minor-revision-required': 'border-l-yellow-500', 'major-revision-required': 'border-l-orange-500', 'reject': 'border-l-red-500' }[review.decision.value]">
                                            <div class="flex items-center justify-between">
                                                <div v-if="review.reviewer">
                                                    <label class="block mb-1 font-semibold">Reviewer:</label>
                                                    <p class="mb-3 font-sm">{{ review.reviewer.name ?? '-' }}</p>
                                                </div>
                                                <div>
                                                    <label class="block mb-1 font-semibold">Decision:</label>
                                                    <p class="mb-3 font-bold">{{ review.decision.label ?? '-' }}</p>
                                                </div>
                                                <!-- <div>
                                                    <label class="block mb-1 font-semibold">Reviewed on:</label>
                                                    <p class="mb-3 font-sm">{{ date(review.created_at) }}</p>
                                                </div> -->
                                                <div v-if="can_edit_review">
                                                    <label class="block mb-1 font-semibold">Action:</label>
                                                    <button type="button" @click.prevent="editReview(revision, review)" class="text-primary-400 text-sm inline-block py-0.5 px-3">Edit</button>
                                                </div>
                                            </div>
                                            <label class="block mb-1 font-semibold">Comments to Author:</label>
                                            <p class="mb-3 text-sm">{{ review.comments_to_author ?? '-' }}</p>
                                            <template v-if="review.comments_to_associate_editor">
                                                <label class="block mb-1 font-semibold">Comments to Associate Editor:</label>
                                                <p class="text-sm">{{ review.comments_to_associate_editor ?? '-' }}</p>
                                            </template>
                                            <div class="mt-3" v-if="review.review_report">
                                                <label class="block mb-1 font-semibold">Review Report:</label>
                                                <i class="bi text-xl mr-2" :class="`bi-filetype-${extension(review.review_report)}`"></i>
                                                <a :href="review.review_report" target="_blank" class="text-primary-400">{{ basename(review.review_report) }}</a>
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
                                <label class="block mb-1 font-semibold">Author Reply to Reviewers and Editors comments:</label>
                                <p class="text-sm">{{ revision.comment_reply ?? '-' }}</p>
                            </div>
                            <template v-if="manuscript.revision?.id !== revision.id">
                                <div class="mt-4" v-if="revision.anonymous_file">
                                    <label class="block mb-1 font-semibold">Anonymous File</label>
                                    <i class="bi bi-filetype-pdf text-xl mr-2"></i>
                                    <a :href="revision.anonymous_file" target="_blank" class="text-primary-400">{{ basename(revision.anonymous_file) }}</a>
                                </div>
                                <div class="mt-4" v-if="revision.source_file">
                                    <label class="block mb-1 font-semibold">Source File</label>
                                    <i class="bi bi-filetype-docx text-xl mr-2"></i>
                                    <a :href="revision.source_file" target="_blank" class="text-primary-400">{{ basename(revision.source_file) }}</a>
                                </div>
                            </template>
                            <div class="mt-4" v-if="revision.comment_reply_file">
                                <label class="block mb-1 font-semibold">Comments Reply File</label>
                                <i class="bi bi-filetype-pdf text-xl mr-2"></i>
                                <a :href="revision.comment_reply_file" target="_blank" class="text-primary-400">{{ basename(revision.comment_reply_file) }}</a>
                            </div>
                        </div>
                    </template>
                </div>
            </template>
            <form @submit.prevent="form.transform(transformFormData).post(`/manuscripts/${manuscript.id}/submit`)" id="edit-form" novalidate>
                <div class="mt-4" v-if="term_and_conditions">
                    <label class="block mb-1 font-semibold">Terms and Conditions</label>
                    <div v-for="(row, i) in term_and_conditions" :key="`row-${i}`" class="flex items-center mt-3">
                        <input v-model="form.term_and_conditions" type="checkbox" :id="`term-and-condition-${row.id}`" class="w-4 h-4" :value="row.id" :disabled="!can_submit" />
                        <label :for="`term-and-condition-${row.id}`" class="ml-2 text-sm">{{ row.term_and_condition }}</label>
                    </div>
                </div>
                <div class="mt-3">
                    <template v-for="(row, key) of form.errors">
                        <div v-if="key.includes('term_and_conditions.')" class="text-sm text-red-700">{{ row }}</div>
                    </template>
                </div>
            </form>
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
        <div v-if="can_submit" class="flex mt-3">
            <a class="py-2 px-4 rounded" role="button" :href="url.previous"><i class="bi bi-chevron-compact-left"></i> Previous</a>
            <button type="submit" class="ml-auto bg-primary-500 text-white py-2 px-4" form="edit-form" :disabled="form.processing">
                <Processing v-if="form.processing" class="h-3 my-1.5 w-auto mx-auto text-white" />
                <template v-else>Submit <i class="bi bi-chevron-compact-right"></i></template>
            </button>
        </div>
        <Modal v-if="updateCommentModalOpened" title="Update Comment" size="xl" @close="updateCommentModalOpened = false">
            <div class="p-3">
                <div class="mb-4">
                    <label for="comments_to_author" class="block font-semibold">Comments to Author</label>
                    <p class="text-sm text-red-400 mb-1">Here, kindly give the comments to the authors. It is also a humble request to the reviewer, kindly do not suggest his/her own articles for citations in the comments.</p>
                    <textarea v-model="updateCommentForm.comments_to_author" id="comments_to_author" placeholder="Enter comments to author" rows="6"></textarea>
                    <div v-if="updateCommentForm.errors?.comments_to_author" class="text-sm text-red-700">{{ updateCommentForm.errors.comments_to_author }}</div>
                </div>
                <div class="mb-4">
                    <label class="block mb-1 font-semibold">Decision</label>
                    <div class="max-h-72 overflow-y-auto">
                        <div class="flex items-center p-2" v-for="review_decision in review_decisions" :key="`review-decision-${review_decision.value}`">
                            <input v-model="updateCommentForm.decision" type="radio" :id="`review-decision-${review_decision.value}`" class="w-4 h-4" :value="review_decision.value" />
                            <label :for="`review-decision-${review_decision.value}`" class="ml-2 text-sm">{{ review_decision.label }}</label>
                        </div>
                    </div>
                    <div v-if="updateCommentForm.errors?.decision" class="text-sm text-red-700">{{ updateCommentForm.errors.decision }}</div>
                </div>
                <div class="mb-4">
                    <label for="comments_to_associate_editor" class="block mb-1 font-semibold">Comments to Associate Editor</label>
                    <textarea v-model="updateCommentForm.comments_to_associate_editor" id="comments_to_associate_editor" placeholder="Enter comments to author" rows="6"></textarea>
                    <div v-if="updateCommentForm.errors?.comments_to_associate_editor" class="text-sm text-red-700">{{ updateCommentForm.errors.comments_to_associate_editor }}</div>
                </div>
                <div class="mb-4">
                    <label class="block mb-1 font-semibold" for="review_report">Review Report</label>
                    <template v-if="typeof updateCommentForm.review_report === 'string'">
                        <div class="flex">
                            <i class="bi text-3xl mr-2" :class="`bi-filetype-${extension(updateCommentForm.review_report!)}`"></i>
                            <div>
                                <p><a :href="updateCommentForm.review_report" target="_blank" class="text-primary-400">{{ basename(updateCommentForm.review_report) }}</a></p>
                                <p><a href="#" @click.prevent="updateCommentForm.review_report = undefined" class="text-red-400 text-sm">Remove</a></p>
                            </div>
                        </div>
                    </template>
                    <template v-else>
                        <input class="block w-full" id="review_report" type="file" accept=".doc,.docx,.jpg,.jpeg,.png,.gif,.pdf,image/jpg,image/jpeg,image/png,image/gif,application/pdf" @input="$event => updateCommentForm.review_report = ($event.target! as HTMLInputElement).files![0]" />
                        <p class="text-sm text-gray-500">.docx, .doc, .jpg, .jpeg, .png, .gif, .pdf format only</p>
                    </template>
                    <div v-if="updateCommentForm.errors?.review_report" class="text-sm text-red-700">{{ updateCommentForm.errors.review_report }}</div>
                </div>
                <div class="py-2 flex justify-center space-x-1">
                    <Processing v-if="updateCommentForm.processing" class="h-3 my-1.5 w-auto mx-auto" />
                    <template v-else>
                        <button type="button" class="text-sm py-2 px-4 bg-primary-500 text-white" @click.prevent="updateCommentForm.transform(transformUpdateCommentFormData).post(`/manuscripts/${manuscript?.id}/update-comment`, { forceFormData: true, onSuccess: () => updateCommentModalOpened = false })">Update</button>
                        <button type="button" class="text-sm py-2 px-4" @click.prevent="updateCommentModalOpened = false">Cancel</button>
                    </template>
                </div>
            </div>
        </Modal>
    </Layout>
</template>

<style scoped lang="scss"></style>
