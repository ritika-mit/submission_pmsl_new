<script setup lang="ts">
import { basename, wordCount } from '@/ts/app';
import type { Manuscript } from '@/ts/types';
import Processing from '@/vue/components/processing.vue';
import Layout from '@/vue/layout.vue';
import Step from '@/vue/manuscript/step.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

type Props = {
    manuscript?: Manuscript;
}

const { props: { url } } = usePage();
const { manuscript } = defineProps<Props>();

const form = useForm({
    title: manuscript?.revision?.title,
    abstract: manuscript?.revision?.abstract,
    keywords: manuscript?.revision?.keywords,
    novelty: manuscript?.revision?.novelty,
    comment_reply: undefined as unknown as string | undefined,
    anonymous_file: undefined as unknown as string | File | undefined,
    source_file: undefined as unknown as string | File | undefined,
    comment_reply_file: undefined as unknown as string | File | undefined,
})

function transformFormData(data: any) {
    if (!(data.source_file instanceof File)) delete data.source_file;
    if (!(data.anonymous_file instanceof File)) delete data.anonymous_file;
    if (!(data.copyright_form instanceof File)) delete data.copyright_form;
    return data
}
</script>

<template>
    <Layout title="Revise Manuscript">
        <div class="flex items-center mb-2">
            <Link :href="url.previous">
            <i class="bi bi-chevron-compact-left text-2xl"></i>
            </Link>
            <h4 class="text-2xl">Revise Manuscript</h4>
            <button class="ml-auto bg-primary-500 text-white py-2 px-4" form="edit-form" type="submit" :disabled="form.processing">
                <Processing v-if="form.processing" class="h-3 my-1.5 w-auto mx-auto text-white" />
                <template v-else><i class="bi bi-arrow-up mr-1"></i> Submit Revision</template>
            </button>
        </div>
        <form @submit.prevent="form.transform(transformFormData).post(url.current, { forceFormData: true })" id="edit-form" class="border p-4 md:p-8 rounded shadow bg-white" novalidate>
            <Step v-if="manuscript?.step" v-bind="manuscript.step" />
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
                <div>
                    <label class="block mb-1 font-semibold">Paper ID</label>
                    <span class="border block p-2.5 rounded">{{ manuscript?.code || '-' }}</span>
                </div>
                <div>
                    <label class="block mb-1 font-semibold">Manuscript Type</label>
                    <span class="border block p-2.5 rounded">{{ manuscript?.type.label || '-' }}</span>
                </div>
            </div>
            <div class="mb-4">
                <div class="flex">
                    <label for="title" class="block mb-1 font-semibold">Title</label>
                    <span class="text-sm text-gray-500 ml-auto">0-20 words</span>
                </div>
                <input type="text" v-model="form.title" id="title" placeholder="Enter manuscript title" autocomplete="off" />
                <p class="text-sm text-gray-500 text-right">{{ 20 - wordCount(form.title || '') }} words left.</p>
                <div v-if="form.errors?.title" class="text-sm text-red-700">{{ form.errors.title }}</div>
            </div>
            <div class="mb-4">
                <div class="flex">
                    <label for="abstract" class="block mb-1 font-semibold">Abstract</label>
                    <span class="text-sm text-gray-500 ml-auto">0-200 words</span>
                </div>
                <textarea v-model="form.abstract" id="abstract" placeholder="Enter manuscript abstract" rows="5"></textarea>
                <p class="text-sm text-gray-500 text-right">{{ 200 - wordCount(form.abstract || '') }} words left.</p>
                <div v-if="form.errors?.abstract" class="text-sm text-red-700">{{ form.errors.abstract }}</div>
            </div>
            <div class="mb-4">
                <label for="keywords" class="block mb-1 font-semibold">Keywords</label>
                <textarea v-model="form.keywords" id="keywords" placeholder="Enter manuscript keywords" rows="1"></textarea>
                <p class="text-sm text-gray-500">Provide 3-5 keywords comma(,) separated which are directly related to your research manuscript</p>
                <div v-if="form.errors?.keywords" class="text-sm text-red-700">{{ form.errors.keywords }}</div>
            </div>
            <div class="mb-4">
                <label for="novelty" class="block mb-1 font-semibold">What is novelty in this article?</label>
                <textarea v-model="form.novelty" id="novelty" placeholder="Enter manuscript novelty" rows="2"></textarea>
                <p class="text-sm text-gray-500 text-right">{{ 60 - wordCount(form.novelty || '') }} words left.</p>
                <p class="text-sm text-gray-500">Kindly describe within 2-4 points (maximum 60 words).</p>
                <div v-if="form.errors?.novelty" class="text-sm text-red-700">{{ form.errors.novelty }}</div>
            </div>
            <div class="mb-4" v-if="manuscript?.research_areas">
                <label class="block mb-1 font-semibold">Broad Research Areas of Article</label>
                <ul class="list-disc ml-6">
                    <li v-for="item in manuscript?.research_areas" :key="`research-area-${item.id}`">{{ item.research_area }}</li>
                </ul>
            </div>
            <div class="mb-4">
                <label for="comment_reply" class="block mb-1 font-semibold">Reply to reviewers and Editors comments</label>
                <textarea v-model="form.comment_reply" id="comment_reply" placeholder="Enter manuscript comment reply" rows="6"></textarea>
                <p class="text-sm text-gray-500 text-right">{{ 200 - wordCount(form.comment_reply || '') }} words left.</p>
                <p class="text-sm text-gray-500">The author must reply to each comment of all reviewers. Also, reply to each editor's common comments too. Upload a pdf file of all comment replies.</p>
                <div v-if="form.errors?.comment_reply" class="text-sm text-red-700">{{ form.errors.comment_reply }}</div>
            </div>
            <div class="mb-4">
                <label class="block mb-1 font-semibold" for="comment_reply_file">Comments Reply File</label>
                <template v-if="typeof form.comment_reply_file === 'string'">
                    <div class="flex">
                        <i class="bi bi-filetype-pdf text-3xl mr-2"></i>
                        <div>
                            <p><a :href="form.comment_reply_file" target="_blank" class="text-primary-400">{{ basename(form.comment_reply_file) }}</a></p>
                            <p><a href="#" @click.prevent="form.comment_reply_file = undefined" class="text-red-400 text-sm">Remove</a></p>
                        </div>
                    </div>
                </template>
                <template v-else>
                    <input class="block w-full" id="comment_reply_file" type="file" accept=".pdf,application/pdf" @input="$event => form.comment_reply_file = ($event.target! as HTMLInputElement).files![0]" />
                    <p class="text-sm text-gray-500">The author must reply to each comment of all reviewers. Also, reply to each editor's common comments too. Upload a .pdf file of all comment replies</p>
                </template>
                <div v-if="form.errors?.comment_reply_file" class="text-sm text-red-700">{{ form.errors.comment_reply_file }}</div>
            </div>
            <div class="mb-4">
                <label class="block mb-1 font-semibold" for="anonymous_file">Anonymous File</label>
                <template v-if="typeof form.anonymous_file === 'string'">
                    <div class="flex">
                        <i class="bi bi-filetype-pdf text-3xl mr-2"></i>
                        <div>
                            <p><a :href="form.anonymous_file" target="_blank" class="text-primary-400">{{ basename(form.anonymous_file) }}</a></p>
                            <p><a href="#" @click.prevent="form.anonymous_file = undefined" class="text-red-400 text-sm">Remove</a></p>
                        </div>
                    </div>
                </template>
                <template v-else>
                    <input class="block w-full" id="anonymous_file" type="file" accept=".pdf,application/pdf" @input="$event => form.anonymous_file = ($event.target! as HTMLInputElement).files![0]" />
                    <p class="text-sm text-gray-500">.pdf format only, without author's details, their affiliation identifications and acknowledgements</p>
                </template>
                <div v-if="form.errors?.anonymous_file" class="text-sm text-red-700">{{ form.errors.anonymous_file }}</div>
            </div>
            <div class="mb-4">
                <label class="block mb-1 font-semibold" for="source_file">Source File</label>
                <template v-if="typeof form.source_file === 'string'">
                    <div class="flex">
                        <i class="bi bi-filetype-docx text-3xl mr-2"></i>
                        <div>
                            <p><a :href="form.source_file" target="_blank" class="text-primary-400">{{ basename(form.source_file) }}</a></p>
                            <p><a href="#" @click.prevent="form.source_file = undefined" class="text-red-400 text-sm">Remove</a></p>
                        </div>
                    </div>
                </template>
                <template v-else>
                    <input class="block w-full" id="source_file" type="file" accept=".doc,.docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" @input="$event => form.source_file = ($event.target! as HTMLInputElement).files![0]" />
                    <p class="text-sm text-gray-500">.docx format only, with full author's details, their affiliation identifications and acknowledgements</p>
                </template>
                <div v-if="form.errors?.source_file" class="text-sm text-red-700">{{ form.errors.source_file }}</div>
            </div>
        </form>
        <div class="flex mt-3">
            <button type="submit" class="ml-auto bg-primary-500 text-white py-2 px-4" form="edit-form" :disabled="form.processing">
                <Processing v-if="form.processing" class="h-3 my-1.5 w-auto mx-auto text-white" />
                <template v-else><i class="bi bi-arrow-up mr-1"></i> Submit Revision</template>
            </button>
        </div>
    </Layout>
</template>

<style scoped lang="scss"></style>
