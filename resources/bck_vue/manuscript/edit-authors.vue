<script setup lang="ts">
import type { Country, Manuscript, RevisionAuthor } from '@/ts/types';
import Modal from '@/vue/components/modal.vue';
import Processing from '@/vue/components/processing.vue';
import Layout from '@/vue/layout.vue';
import EditAuthor from '@/vue/manuscript/edit-author.vue';
import Step from '@/vue/manuscript/step.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { parse } from 'qs';
import { ref } from 'vue';

type Props = {
    manuscript: Manuscript;
    countries: Country[];
    authors?: RevisionAuthor['author'][];
}

const { props: { url } } = usePage();
const { manuscript, authors, countries } = defineProps<Props>();

const filter = useForm<{ search?: string }>(
    Object.assign({ search: '' }, parse(window.location.search, { ignoreQueryPrefix: true }))
)

const form = useForm({
    action: 'submit',
})

const addAuthorForm = useForm({})
const removeAuthorForm = useForm({})

const updateRevisionAuthor = ref<Partial<RevisionAuthor['author']>>();

function addAuthor({ country, ...author }: RevisionAuthor['author']) {
    addAuthorForm.transform(() => ({ ...author, country: country?.id }))
        .post(`/manuscripts/${manuscript.id}/add-author`, { onFinish: clearSearch });
}

function editAuthor(revision_author: Partial<RevisionAuthor['author']>) {
    clearSearch();
    updateRevisionAuthor.value = revision_author;
}

function removeAuthor(revision_author: RevisionAuthor) {
    removeAuthorForm.transform(() => ({ revision_author: revision_author.id }))
        .post(`/manuscripts/${manuscript.id}/remove-author`, { onFinish: clearSearch });
}

function submitSearch() {
    addAuthorForm.clearErrors();
    removeAuthorForm.clearErrors();

    filter.get(url.current, {
        only: ['authors'],
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
    <Layout title="Edit Author Details">
        <div class="flex items-center mb-2">
            <Link :href="url.previous">
            <i class="bi bi-chevron-compact-left text-2xl"></i>
            </Link>
            <h4 class="text-2xl">Edit Author Details</h4>
            <button type="submit" class="ml-auto bg-primary-500 text-white py-2 px-4" form="edit-form" :disabled="form.processing" @click="form.action = 'submit'">
                <Processing v-if="form.processing" class="h-3 my-1.5 w-auto mx-auto text-white" />
                <template v-else><i class="bi bi-arrow-up mr-1"></i> Save</template>
            </button>
        </div>
        <div class="border p-4 md:p-8 rounded shadow bg-white">
            <Step v-if="manuscript?.step" v-bind="manuscript.step" active="author" />
            <div class="mb-4 group relative">
                <form @submit.prevent="submitSearch" id="search-form" novalidate>
                    <label class="block mb-1 font-semibold">Find Co-author</label>
                    <div class="relative">
                        <div v-if="filter.processing" class="flex absolute inset-y-0 right-0 items-center pr-3 pointer-events-none">
                            <Processing class="h-3 w-auto text-primary-500" />
                        </div>
                        <input type="search" v-model="filter.search" placeholder="Enter author email address to add" autocomplete="off" @input="submitSearch" />
                    </div>
                    <div v-for="row of addAuthorForm.errors" class="text-sm text-red-700">{{ row }}</div>
                    <div v-for="row of removeAuthorForm.errors" class="text-sm text-red-700">{{ row }}</div>
                </form>
                <ul v-if="authors && authors.length > 0" class="absolute z-10 max-h-60 w-full overflow-x-hidden overflow-y-auto overflow-ellipsis rounded-lg border bg-white shadow-lg group-focus-within:block">
                    <li class="cursor-pointer border-t p-2.5 hover:bg-gray-100" v-for="(row, i) in authors" :key="`row-${i}`" @click="addAuthor(row)">
                        <p>{{ row.title }} {{ row.name }}</p>
                        <p class="text-gray-500 text-sm">{{ row.email }}</p>
                        <p class="text-gray-500 text-sm">{{ row.highest_qualification }}</p>
                        <p class="text-gray-500 text-sm">{{ row.organization_institution }}</p>
                        <p class="text-gray-500 text-sm">{{ row.country?.name }}</p>
                    </li>
                </ul>
                <p v-else-if="filter.search && filter.search.length > 0" class="text-sm text-gray-500 mt-2">Author not found. <a href="#" class="text-primary-400" @click.prevent="editAuthor({ email: filter.search })">Click here to add manually.</a></p>
            </div>
            <div class="mb-4">
                <p class="text-gray-500 text-sm">All co-authors must be added here. (You can add up to 20 authors required for submission). Kindly provide author order and it can be changed later on at the time of proofread if the manuscript is accepted for publication.</p>
            </div>
            <form @submit.prevent="form.post(url.current)" id="edit-form" novalidate>
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
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-if="manuscript.revision?.authors?.length === 0">
                                <tr>
                                    <td :colspan="7" class="py-7 text-center">
                                        <h6 class="text-xl text-gray-800">No authors added.</h6>
                                        <p class="text-sm text-gray-700">Try to add author using search by email.</p>
                                    </td>
                                </tr>
                            </template>
                            <template v-else>
                                <template v-for="(revision_author, i) in manuscript.revision?.authors" :key="`row-${i}`">
                                    <tr v-if="revision_author.author">
                                        <td>{{ i + 1 }}</td>
                                        <td>{{ revision_author.author.title }} {{ revision_author.author.name ?? '-' }}</td>
                                        <td>{{ revision_author.author.email ?? '-' }}</td>
                                        <td>{{ revision_author.author.highest_qualification ?? '-' }}</td>
                                        <td>{{ revision_author.author.organization_institution ?? '-' }}</td>
                                        <td>{{ revision_author.author.country?.name ?? '-' }}</td>
                                        <td>
                                            <div class="text-center" v-if="revision_author.author.can_edit">
                                                <a href="#" @click.prevent="editAuthor(revision_author.author)" class="text-primary-400">Edit</a>
                                            </div>
                                            <div class="text-center">
                                                <a href="#" @click.prevent="removeAuthor(revision_author)" class="text-red-400">Remove</a>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </template>
                        </tbody>
                    </table>
                </div>
                <div v-for="row of form.errors" class="text-sm text-red-700">{{ row }}</div>
            </form>
        </div>
        <div class="flex mt-3">
            <a class="py-2 px-4 rounded" role="button" :href="url.previous"><i class="bi bi-chevron-compact-left"></i> Previous</a>
            <button type="submit" class="ml-auto bg-primary-500 text-white py-2 px-4" form="edit-form" :disabled="form.processing" @click="form.action = 'submit&next'">
                <Processing v-if="form.processing" class="h-3 my-1.5 w-auto mx-auto text-white" />
                <template v-else>Save & Next <i class="bi bi-chevron-compact-right"></i></template>
            </button>
        </div>
        <Modal v-if="updateRevisionAuthor !== undefined" title="Add Author" @close="updateRevisionAuthor = undefined">
            <EditAuthor :action="`/manuscripts/${manuscript.id}/add-author-manually`" :author="updateRevisionAuthor" :countries="countries" @onSuccess="updateRevisionAuthor = undefined" />
        </Modal>
    </Layout>
</template>

<style scoped lang="scss"></style>
