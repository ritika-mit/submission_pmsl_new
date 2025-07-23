<script setup lang="ts">
import { basename, extension, wordCount } from '@/ts/app';
import type { Manuscript, ResearchArea } from '@/ts/types';
import Processing from '@/vue/components/processing.vue';
import Layout from '@/vue/layout.vue';
import Step from '@/vue/manuscript/step.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { reactive } from 'vue';

type Props = {
    can_submit?: boolean;
    types: Manuscript['type'][];
    manuscript?: Manuscript;
    research_areas: ResearchArea[];
}

const { props: { url } } = usePage();
const { types, manuscript, research_areas } = defineProps<Props>();

const files_keys = ['anonymous_file', 'source_file', 'copyright_form'] as const;
const files = reactive<Record<string, File | undefined>>({});

const form = useForm({
    type: manuscript?.type?.value,
    title: manuscript?.revision?.title,
    abstract: manuscript?.revision?.abstract,
    keywords: manuscript?.revision?.keywords,
    novelty: manuscript?.revision?.novelty,
    research_areas: manuscript?.research_areas?.map(item => item.id) || [],
    anonymous_file: manuscript?.revision?.anonymous_file,
    source_file: manuscript?.revision?.source_file,
    copyright_form: manuscript?.copyright_form,
    action: 'submit',
})

const isDisabled = (id: string) => {
    return form.research_areas.length >= 3 && !form.research_areas.includes(id);
};

function setFile($event: Event, key: typeof files_keys[number]) {
    const target = $event.target as HTMLInputElement;
    files[key] = target.files?.item(0) ?? undefined;
}

function transformFormData(data: any) {
    files_keys.forEach(key => {
        delete data[key];
        if (files[key] instanceof File) data[key] = files[key];
    });

    return data;
}


</script>

<template>
    <Layout :title="manuscript?.id ? 'Edit Manuscript' : 'Create Manuscript'">
        <div class="flex items-center mb-2">
            <Link :href="url.previous">
            <i class="bi bi-chevron-compact-left text-2xl"></i>
            </Link>
            <h4 class="text-2xl">{{ manuscript?.id ? 'Edit Manuscript' : 'Create Manuscript' }}</h4>
            <button type="submit" class="ml-auto bg-primary-500 text-white py-2 px-4" form="edit-form" :disabled="form.processing" @click="form.action = 'submit'">
                <Processing v-if="form.processing" class="h-3 my-1.5 w-auto mx-auto text-white" />
                <template v-else><i class="bi bi-arrow-up mr-1"></i> Save</template>
            </button>
        </div>
        <form @submit.prevent="form.transform(transformFormData).post(url.current, { forceFormData: true })" id="edit-form" class="border p-4 md:p-8 rounded shadow bg-white" novalidate>
            <Step v-if="manuscript?.step" v-bind="manuscript.step" active="basic" />
            <div class="mb-4">
                <label class="block mb-1 font-semibold">Manuscript Type</label>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <label :for="`type-${index}`" class="flex items-center border rounded p-2.5 bg-white" v-for="(item, index) in types">
                        <input :id="`type-${index}`" type="radio" v-model="form.type" name="type" class="w-4 h-4" :value="item.value" />
                        <span class="ml-2">{{ item.label }}</span>
                    </label>
                </div>
                <div v-if="form.errors?.type" class="text-sm text-red-700">{{ form.errors.type }}</div>
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
                <div class="flex items-center space-x-2">
                    <label for="keywords" class="block mb-1 font-semibold">Keywords</label>
                    <p class="text-sm text-gray-500">Provide 3-5 keywords comma(,) separated which are directly related to your research manuscript</p>
                </div>
                <textarea v-model="form.keywords" id="keywords" placeholder="Enter manuscript keywords" rows="1"></textarea>
                <div v-if="form.errors?.keywords" class="text-sm text-red-700">{{ form.errors.keywords }}</div>
            </div>
            <div class="mb-4">
                <div class="flex items-center space-x-2">
                    <label for="novelty" class="block mb-1 font-semibold">What is novelty in this article?</label>
                    <p class="text-sm text-gray-500">Kindly describe within 2-4 points (maximum 60 words).</p>
                </div>
                <textarea v-model="form.novelty" id="novelty" placeholder="Enter manuscript novelty" rows="2"></textarea>
                <p class="text-sm text-gray-500 text-right">{{ 60 - wordCount(form.novelty || '') }} words left.</p>
                <div v-if="form.errors?.novelty" class="text-sm text-red-700">{{ form.errors.novelty }}</div>
            </div>
            <div class="mb-4">
                <div class="flex items-center space-x-2">
                    <label class="font-semibold">Broad Research Areas of Article</label>
                    <p class="text-sm text-gray-500">
                        (Kindly select 3 broad research areas of article)
                    </p>
                </div>
                <div class="max-h-72 overflow-y-auto">
                    <div class="flex items-center p-2" v-for="item in research_areas" :key="`research-area-${item.id}`">
                        <input v-model="form.research_areas" type="checkbox" :id="`research-area-${item.id}`" class="w-4 h-4" :value="item.id" :disabled="isDisabled(item.id)" />
                        <label :for="`research-area-${item.id}`" class="ml-2 text-sm">{{ item.research_area }}</label>
                    </div>
                </div>
                <div v-if="form.errors?.research_areas" class="text-sm text-red-700">{{ form.errors.research_areas }}</div>
            </div>
            <div class="mb-4">
                <div class="flex items-center space-x-2">
                    <label class="block mb-1 font-semibold" for="anonymous_file">Anonymous File</label>
                    <p class="text-sm text-gray-500">.pdf format only, without author's details, their affiliation identifications and acknowledgements</p>
                </div>
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
                    <input class="block w-full" id="anonymous_file" type="file" accept=".pdf,application/pdf" @input="setFile($event, 'anonymous_file')" />
                </template>
                <div v-if="form.errors?.anonymous_file" class="text-sm text-red-700">{{ form.errors.anonymous_file }}</div>
            </div>
            <div class="mb-4">
                <div class="flex items-center space-x-2">
                    <label class="block mb-1 font-semibold" for="source_file">Source File</label>
                    <p class="text-sm text-gray-500">.docx format only, with full author's details, their affiliation identifications and acknowledgements</p>
                </div>
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
                    <input class="block w-full" id="source_file" type="file" accept=".doc,.docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" @input="setFile($event, 'source_file')" />
                </template>
                <div v-if="form.errors?.source_file" class="text-sm text-red-700">{{ form.errors.source_file }}</div>
            </div>
            <div>
                <div class="flex items-center space-x-2">
                    <label class="block mb-1 font-semibold" for="copyright_form">Copyright Form</label>
                    <p class="text-sm text-gray-500">.jpg, .jpeg, .png, .gif, .pdf format only, which can be found at the link <a href="https://www.ijmems.in/assets/copyright-ijmems.pdf" target="_blank" class="text-primary-400">Click Here</a></p>
                </div>
                <template v-if="typeof form.copyright_form === 'string'">
                    <div class="flex">
                        <i class="bi text-3xl mr-2" :class="`bi-filetype-${extension(form.copyright_form)}`"></i>
                        <div>
                            <p><a :href="form.copyright_form" target="_blank" class="text-primary-400">{{ basename(form.copyright_form) }}</a></p>
                            <p><a href="#" @click.prevent="form.copyright_form = undefined" class="text-red-400 text-sm">Remove</a></p>
                        </div>
                    </div>
                </template>
                <template v-else>
                    <input class="block w-full" id="copyright_form" type="file" accept=".jpg,.jpeg,.png,.gif,.pdf,image/jpg,image/jpeg,image/png,image/gif,application/pdf" @input="setFile($event, 'copyright_form')" />
                </template>
                <div v-if="form.errors?.copyright_form" class="text-sm text-red-700">{{ form.errors.copyright_form }}</div>
            </div>
        </form>
        <div class="flex mt-3" v-if="can_submit">
            <a class="py-2 px-4 rounded" role="button" :href="url.previous"><i class="bi bi-chevron-compact-left"></i> Previous</a>
            <button type="submit" class="ml-auto bg-primary-500 text-white py-2 px-4" form="edit-form" :disabled="form.processing" @click="form.action = 'submit&next'">
                <Processing v-if="form.processing" class="h-3 my-1.5 w-auto mx-auto text-white" />
                <template v-else>Save & Next <i class="bi bi-chevron-compact-right"></i></template>
            </button>
        </div>
    </Layout>
</template>

<style scoped lang="scss"></style>
