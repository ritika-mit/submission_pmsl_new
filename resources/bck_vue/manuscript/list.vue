<script setup lang="ts">
import { basename, date, extension, wordCount } from '@/ts/app';
import type { Action, Author, Column, Filter, Manuscript, Revision, TData } from '@/ts/types';
import Modal from '@/vue/components/modal.vue';
import Processing from '@/vue/components/processing.vue';
import Table from '@/vue/components/table.vue';
import Layout from '@/vue/layout.vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import { parse } from 'qs';
import { computed, ref } from 'vue';

type Props = {
    columns: Record<string, Column>;
    data: TData<Manuscript & Revision & { actions: Action[] }>;
    associate_editors?: Author[];
};

const { props: { url, aside } } = usePage();
const { columns, data, associate_editors } = defineProps<Props>();

const title = computed(() => aside.items.find(item => item.active === true)?.label);

const filter = useForm(
    parse(window.location.search, { ignoreQueryPrefix: true }) as unknown as Filter
)

const updateAssociateEditorForm = useForm({
    associate_editor: undefined as unknown as string | undefined
});

const sendToEICForm = useForm({
    comments_to_eic: undefined as unknown as string | undefined
});

const updateSimilarityForm = useForm({
    similarity: undefined as unknown as number | undefined
});

const updatePagesForm = useForm({
    pages: undefined as unknown as number | undefined,
    source_file: undefined as unknown as string | File | undefined,
});

const updateGrammarForm = useForm({
    source_file: undefined as unknown as string | File | undefined,
});

const updateCommentReplyForm = useForm({
    comment_reply: undefined as unknown as string | undefined,
    comment_reply_file: undefined as unknown as string | File | undefined,
});

const updateManuscript = ref<Manuscript>();

const confirmDialogModalAction = ref('');

const assignAssociateEditorModalOpened = ref(false);

const sendToEICModalOpened = ref(false);

const updateSimilarityModalOpened = ref(false);

const updatePagesModalOpened = ref(false);

const updateGrammarModalOpened = ref(false);

const updateCommentReplyModalOpened = ref(false);

function submit(_filter: Filter) {
    filter.transform(() => _filter).get(url.current, {
        preserveState: true,
        preserveScroll: true
    })
}

function action($event: Event, row: Manuscript) {
    const target = $event.target as HTMLSelectElement | HTMLButtonElement;
    const action = target.value;
    target.value = '';

    switch (action) {
        case 'edit':
            return router.visit(`/manuscripts/${row.id}/edit`);

        case 'view':
            return router.visit(`/manuscripts/${row.id}/view`);

        case 'assign-associate-editor':
            router.reload({ only: ['associate_editors'] });
            updateManuscript.value = row;
            assignAssociateEditorModalOpened.value = true;
            updateAssociateEditorForm.associate_editor = row?.revisions?.[row?.revisions.length - 1]?.associate_editor?.id ?? row?.revisions?.[row?.revisions.length - 2]?.associate_editor?.id;
            return;

        case 'send-to-eic':
            updateManuscript.value = row;
            sendToEICModalOpened.value = true;
            return;

        case 'update-similarity':
            updateManuscript.value = row;
            updateSimilarityForm.similarity = row?.revision?.similarity;
            updateSimilarityModalOpened.value = true;
            return;

        case 'update-pages':
            updateManuscript.value = row;
            updatePagesForm.pages = row?.revision?.pages;
            updatePagesModalOpened.value = true;
            return;

        case 'grammar-checked':
            updateManuscript.value = row;
            updateGrammarModalOpened.value = true;
            return;

        case 'update-comment-reply':
            updateManuscript.value = row;
            updateCommentReplyModalOpened.value = true;
            updateCommentReplyForm.comment_reply = row?.revisions?.[row?.revisions.length - 2]?.comment_reply;
            updateCommentReplyForm.comment_reply_file = row?.revisions?.[row?.revisions.length - 2]?.comment_reply_file;
            return;

        case 'minor-revision-required':
        case 'major-revision-required':
        case 'conditionally-accept':
        case 'accept':
        case 'withdraw':
        case 'reject':
        case 'production':
        case 'publication':
        case 'publish':
        case 'delete':
        case 'send-for-similarity-check':
        case 'send-for-pagination':
        case 'send-for-grammar-check':
            updateManuscript.value = row;
            confirmDialogModalAction.value = action;
            return;

        case 'invite-reviewer':
        case 'invite-more-reviewer':
            return router.visit(`/manuscripts/${row.id}/invite`);

        case 'revise':
            return router.visit(`/manuscripts/${row.id}/revise`);

        case 'review':
            return router.visit(`/manuscripts/${row.id}/review`);

        case 'remind-author':
            return router.post(`/manuscripts/${row.id}/remind-author`);

        default:
            throw new Error('Unsupported action');
    }
}

function transformUpdateCommentReplyFormData(data: any) {
    if (!(data.comment_reply_file instanceof File)) delete data.comment_reply_file;
    return data;
}
</script>

<template>
    <Layout :title="title">
        <div class="flex items-center mb-4">
            <h4 class="text-2xl">{{ title }}</h4>
        </div>
        <Table :columns="columns" :data="data" :filter="filter.data()" :processing="filter.processing" @filterChanged="submit">
            <template #index="{ index, filter }">{{ ((filter.page - 1) * filter.perPage) + index + 1 }}</template>
            <template #type="{ row }">{{ row.type.label }}</template>
            <template #title="{ row }">
                <a v-if="row.revision?.anonymous_file" :href="row.revision.anonymous_file" target="_blank" class="text-primary-400 hover:underline">{{ row.title }}</a>
                <template v-else>{{ row.title }}</template>
            </template>
            <template #other="{ row }">
                <template v-if="row.revision?.similarity || row.revision?.pages || row.revision?.grammar_updated || row.revision?.source_file">
                    <div class="whitespace-nowrap mb-1" v-if="row.revision?.similarity"><span class="font-semibold">Similarity</span>: {{ row.revision.similarity }}%</div>
                    <div class="whitespace-nowrap mb-1" v-if="row.revision?.pages"><span class="font-semibold"># of Pages</span> : {{ row.revision.pages }}</div>
                    <div class="whitespace-nowrap mb-1" v-if="row.revision?.grammar_updated"><i class="bi bi-clipboard-check"></i> <span class="font-semibold">Grammar Updated</span></div>
                    <div class="whitespace-nowrap mb-1" v-if="row.revision?.source_file">
                        <a :href="row.revision.source_file" target="_blank" class="text-primary-400 hover:underline"><i class="bi" :class="`bi-filetype-${extension(row.revision.source_file)}`"></i> Source File</a>
                    </div>
                    <div class="whitespace-nowrap mb-1" v-if="row.copyright_form">
                        <a :href="row.copyright_form" target="_blank" class="text-primary-400 hover:underline"><i class="bi" :class="`bi-filetype-${extension(row.copyright_form)}`"></i> Copyright Form</a>
                    </div>
                </template>
                <template v-else>-</template>
            </template>
            <template #events="{ row }">
                <template v-if="row.events && row.events.length">
                    <div class="whitespace-nowrap mb-2">
                        <p class="font-semibold">{{ row.events[0].title }}</p>
                        <p>{{ date(row.events[0].created_at) }}</p>
                    </div>
                </template>
                <template v-else>-</template>
            </template>
            <template #created_at="{ row }">{{ row.created_at ? date(row.created_at) : '-' }}</template>
            <template #updated_at="{ row }">{{ date(row.updated_at) }}</template>
            <template #action="{ row }">
                <select v-if="row.actions.length > 1" class="py-1.5" @change="$event => action($event, row)">
                    <option value="">Select</option>
                    <option v-for="action in row.actions" :value="action.value">{{ action.label }}</option>
                </select>
                <button v-else-if="row.actions.length" type="button" :value="row.actions[0].value" @click.prevent="$event => action($event, row)" class="text-primary-400 py-0.5 w-full">{{ row.actions[0].label }}</button>
            </template>
        </Table>
        <Modal v-if="confirmDialogModalAction" title="Confirm" size="sm" @close="confirmDialogModalAction = ''">
            <div class="p-3">
                <h4 class="text-center text-gray-500">Are you sure to take decision?</h4>
                <h4 class="text-center text-lg py-2 underline font-bold">{{ confirmDialogModalAction.replace(/-/g, ' ').toUpperCase() }}</h4>
                <div class="py-2 flex justify-center space-x-1">
                    <Processing v-if="updateAssociateEditorForm.processing" class="h-3 my-1.5 w-auto mx-auto" />
                    <template v-else>
                        <button type="button" class="text-sm py-2 px-4 bg-primary-500 text-white" @click.prevent="updateAssociateEditorForm.post(`/manuscripts/${updateManuscript?.id}/${confirmDialogModalAction}`, { onSuccess: () => confirmDialogModalAction = '' })">
                            {{ ['send-for-similarity-check', 'send-for-pagination', 'grammar-checked'].includes(confirmDialogModalAction)
                                ? 'Update & Forward'
                                : 'Continue' }}
                        </button>
                        <button type="button" class="text-sm py-2 px-4" @click.prevent="confirmDialogModalAction = ''">Cancel</button>
                    </template>
                </div>
            </div>
        </Modal>
        <Modal v-if="updateSimilarityModalOpened" title="Update Similarity" size="sm" @close="updateSimilarityModalOpened = false">
            <div class="p-3">
                <div class="px-3 mb-3">
                    <label for="similarity" class="block mb-1 font-semibold">Similarity (%)</label>
                    <input type="text" v-model="updateSimilarityForm.similarity" id="similarity" placeholder="Enter similarity (%)" autocomplete="off" />
                    <div v-if="updateSimilarityForm.errors?.similarity" class="text-sm text-red-700">{{ updateSimilarityForm.errors.similarity }}</div>
                </div>
                <div class="py-2 flex justify-center space-x-1">
                    <Processing v-if="updateSimilarityForm.processing" class="h-3 my-1.5 w-auto mx-auto" />
                    <template v-else>
                        <button type="button" class="text-sm py-2 px-4 bg-primary-500 text-white" @click.prevent="updateSimilarityForm.post(`/manuscripts/${updateManuscript?.id}/update-similarity`, { onSuccess: () => updateSimilarityModalOpened = false })">Update & Forward</button>
                        <button type="button" class="text-sm py-2 px-4" @click.prevent="updateSimilarityModalOpened = false">Cancel</button>
                    </template>
                </div>
            </div>
        </Modal>
        <Modal v-if="updatePagesModalOpened" title="Update Pages" size="sm" @close="updatePagesModalOpened = false">
            <div class="p-3">
                <div class="px-3 mb-3">
                    <label for="pages" class="block mb-1 font-semibold">Pages</label>
                    <input type="text" v-model="updatePagesForm.pages" id="pages" placeholder="Enter pages" autocomplete="off" />
                    <div v-if="updatePagesForm.errors?.pages" class="text-sm text-red-700">{{ updatePagesForm.errors.pages }}</div>
                </div>
                <div class="px-3 mb-3">
                    <label class="block mb-1 font-semibold" for="source_file">Source File</label>
                    <input class="block w-full" id="source_file" type="file" accept=".doc,.docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" @input="$event => updatePagesForm.source_file = ($event.target! as HTMLInputElement).files![0]" />
                    <p class="text-sm text-gray-500">.docx format only, with full author's details, their affiliation identifications and acknowledgements</p>
                    <div v-if="updatePagesForm.errors?.source_file" class="text-sm text-red-700">{{ updatePagesForm.errors.source_file }}</div>
                </div>
                <div class="py-2 flex justify-center space-x-1">
                    <Processing v-if="updatePagesForm.processing" class="h-3 my-1.5 w-auto mx-auto" />
                    <template v-else>
                        <button type="button" class="text-sm py-2 px-4 bg-primary-500 text-white" @click.prevent="updatePagesForm.post(`/manuscripts/${updateManuscript?.id}/update-pages`, { forceFormData: true, onSuccess: () => updatePagesModalOpened = false })">Update & Forward</button>
                        <button type="button" class="text-sm py-2 px-4" @click.prevent="updatePagesModalOpened = false">Cancel</button>
                    </template>
                </div>
            </div>
        </Modal>
        <Modal v-if="updateGrammarModalOpened" title="Update Grammar" size="sm" @close="updateGrammarModalOpened = false">
            <div class="p-3">
                <div class="px-3 mb-3">
                    <label class="block mb-1 font-semibold" for="source_file">Source File</label>
                    <input class="block w-full" id="source_file" type="file" accept=".doc,.docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" @input="$event => updateGrammarForm.source_file = ($event.target! as HTMLInputElement).files![0]" />
                    <p class="text-sm text-gray-500">.docx format only, with full author's details, their affiliation identifications and acknowledgements</p>
                    <div v-if="updateGrammarForm.errors?.source_file" class="text-sm text-red-700">{{ updateGrammarForm.errors.source_file }}</div>
                </div>
                <div class="py-2 flex justify-center space-x-1">
                    <Processing v-if="updateGrammarForm.processing" class="h-3 my-1.5 w-auto mx-auto" />
                    <template v-else>
                        <button type="button" class="text-sm py-2 px-4 bg-primary-500 text-white" @click.prevent="updateGrammarForm.post(`/manuscripts/${updateManuscript?.id}/grammar-checked`, { forceFormData: true, onSuccess: () => updateGrammarModalOpened = false })">Update & Forward</button>
                        <button type="button" class="text-sm py-2 px-4" @click.prevent="updateGrammarModalOpened = false">Cancel</button>
                    </template>
                </div>
            </div>
        </Modal>
        <Modal v-if="updateCommentReplyModalOpened" title="Update Comment Reply" @close="updateCommentReplyModalOpened = false">
            <div class="p-3">
                <div class="px-3 mb-3">
                    <label for="comment_reply" class="block mb-1 font-semibold">Reply to reviewers and Editors comments</label>
                    <textarea v-model="updateCommentReplyForm.comment_reply" id="comment_reply" placeholder="Enter manuscript comment reply" rows="6"></textarea>
                    <p class="text-sm text-gray-500 text-right">{{ 200 - wordCount(updateCommentReplyForm.comment_reply || '') }} words left.</p>
                    <p class="text-sm text-gray-500">The author must reply to each comment of all reviewers. Also, reply to each editor's common comments too. Upload a pdf file of all comment replies.</p>
                    <div v-if="updateCommentReplyForm.errors?.comment_reply" class="text-sm text-red-700">{{ updateCommentReplyForm.errors.comment_reply }}</div>
                </div>
                <div class="px-3 mb-3">
                    <label class="block mb-1 font-semibold" for="comment_reply_file">Comments Reply File</label>
                    <template v-if="typeof updateCommentReplyForm.comment_reply_file === 'string'">
                        <div class="flex">
                            <i class="bi bi-filetype-pdf text-3xl mr-2"></i>
                            <div>
                                <p><a :href="updateCommentReplyForm.comment_reply_file" target="_blank" class="text-primary-400">{{ basename(updateCommentReplyForm.comment_reply_file) }}</a></p>
                                <p><a href="#" @click.prevent="updateCommentReplyForm.comment_reply_file = undefined" class="text-red-400 text-sm">Remove</a></p>
                            </div>
                        </div>
                    </template>
                    <template v-else>
                        <input class="block w-full" id="comment_reply_file" type="file" accept=".pdf,application/pdf" @input="$event => updateCommentReplyForm.comment_reply_file = ($event.target! as HTMLInputElement).files![0]" />
                        <p class="text-sm text-gray-500">The author must reply to each comment of all reviewers. Also, reply to each editor's common comments too. Upload a .pdf file of all comment replies</p>
                    </template>
                    <div v-if="updateCommentReplyForm.errors?.comment_reply_file" class="text-sm text-red-700">{{ updateCommentReplyForm.errors.comment_reply_file }}</div>
                </div>
                <div class="py-2 flex justify-center space-x-1">
                    <Processing v-if="updateCommentReplyForm.processing" class="h-3 my-1.5 w-auto mx-auto" />
                    <template v-else>
                        <button type="button" class="text-sm py-2 px-4 bg-primary-500 text-white" @click.prevent="updateCommentReplyForm.transform(transformUpdateCommentReplyFormData).post(`/manuscripts/${updateManuscript?.id}/update-comment-reply`, { forceFormData: true, onSuccess: () => updateCommentReplyModalOpened = false })">Update</button>
                        <button type="button" class="text-sm py-2 px-4" @click.prevent="updateCommentReplyModalOpened = false">Cancel</button>
                    </template>
                </div>
            </div>
        </Modal>
        <Modal v-if="sendToEICModalOpened" title="Send to EIC" @close="sendToEICModalOpened = false">
            <div class="p-3">
                <h4 class="text-center text-lg"></h4>
                <div class="mb-2">
                    <label for="comments_to_eic" class="block mb-1 font-semibold">Comments to EIC</label>
                    <textarea v-model="sendToEICForm.comments_to_eic" id="keywords" placeholder="Enter comments to eic" rows="4"></textarea>
                    <div v-if="sendToEICForm.errors?.comments_to_eic" class="text-sm text-red-700">{{ sendToEICForm.errors.comments_to_eic }}</div>
                </div>
                <div class="py-2 flex justify-center space-x-1">
                    <Processing v-if="sendToEICForm.processing" class="h-3 my-1.5 w-auto mx-auto" />
                    <template v-else>
                        <button type="button" class="text-sm py-2 px-4 bg-primary-500 text-white" @click.prevent="sendToEICForm.post(`/manuscripts/${updateManuscript?.id}/send-to-eic`, { onSuccess: () => sendToEICModalOpened = false })">Send</button>
                    </template>
                </div>
            </div>
        </Modal>
        <Modal v-if="assignAssociateEditorModalOpened && associate_editors" title="Assign Associate Editor" size="xl" @close="assignAssociateEditorModalOpened = false">
            <div class="p-3">
                <div class="overflow-y-auto rounded">
                    <table class="table text-left text-sm">
                        <thead class="uppercase">
                            <tr>
                                <th></th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Highest Qualification</th>
                                <th>Organization/Institution</th>
                                <th>Country</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-if="associate_editors.length > 0">
                                <label v-for="(row, i) in associate_editors" :key="`row-${i}`" :for="`associate-editor-${row.id}`" role="table-row" class="table-row">
                                    <td class="align-middle">
                                        <input type="radio" v-model="updateAssociateEditorForm.associate_editor" :id="`associate-editor-${row.id}`" class="w-4 h-4" :value="row.id" />
                                    </td>
                                    <td>{{ row.name ?? '-' }}</td>
                                    <td>{{ row.email ?? '-' }}</td>
                                    <td>{{ row.highest_qualification ?? '-' }}</td>
                                    <td>{{ row.organization_institution ?? '-' }}</td>
                                    <td>{{ row.country?.name ?? '-' }}</td>
                                </label>
                            </template>
                            <template v-else>
                                <tr>
                                    <td colspan="7" class="py-2 text-center">
                                        <p class="text-gray-800 text-base">No associate editors found.</p>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                <div v-for="row of updateAssociateEditorForm.errors" class="text-sm text-red-700">{{ row }}</div>
                <div class="py-2 flex justify-center space-x-1">
                    <Processing v-if="updateAssociateEditorForm.processing" class="h-3 my-1.5 w-auto mx-aut" />
                    <template v-else>
                        <button type="button" class="text-sm py-2 px-4 bg-primary-500 text-white" @click.prevent="updateAssociateEditorForm.post(`/manuscripts/${updateManuscript?.id}/assign-associate-editor`, { onSuccess: () => assignAssociateEditorModalOpened = false })">Save</button>
                        <button type="button" class="text-sm py-2 px-4" @click.prevent="assignAssociateEditorModalOpened = false">Cancel</button>
                    </template>
                </div>
            </div>
        </Modal>
    </Layout>
</template>

<style scoped lang="scss"></style>
