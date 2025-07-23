<script setup lang="ts">
import { basename, extension } from '@/ts/app';
import type { Manuscript, Review, ReviewDecision } from '@/ts/types';
import Processing from '@/vue/components/processing.vue';
import Layout from '@/vue/layout.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { reactive } from 'vue';

type Props = {
    manuscript: Manuscript;
    review?: Review;
    review_decisions: ReviewDecision[];
}

const { props: { url } } = usePage();
const { manuscript, review } = defineProps<Props>();

const files_keys = ['review_report'] as const;
const files = reactive<Record<string, File | undefined>>({});

const form = useForm({
    comments_to_author: review?.comments_to_author,
    decision: review?.decision.value,
    comments_to_associate_editor: review?.comments_to_associate_editor,
    review_report: review?.review_report
})

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
    <Layout title="View Manuscript">
        <div class="flex items-center mb-2">
            <Link :href="url.previous">
            <i class="bi bi-chevron-compact-left text-2xl"></i>
            </Link>
            <h4 class="text-2xl">Review Manuscript</h4>
            <button class="ml-auto bg-primary-500 text-white py-2 px-4" form="edit-form" type="submit" :disabled="form.processing">
                <Processing v-if="form.processing" class="h-3 my-1.5 w-auto mx-auto text-white" />
                <template v-else><i class="bi bi-arrow-up mr-1"></i> Submit Review</template>
            </button>
        </div>
        <form @submit.prevent="form.transform(transformFormData).post(`/manuscripts/${manuscript.id}/review`)" id="edit-form" class="border p-4 md:p-8 rounded shadow bg-white" novalidate>
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
                <label for="comments_to_author" class="block font-semibold">Comments to Author</label>
                <p class="text-sm text-red-400 mb-1">Here, kindly give the comments to the authors. It is also a humble request to the reviewer, kindly do not suggest his/her own articles for citations in the comments.</p>
                <textarea v-model="form.comments_to_author" id="comments_to_author" placeholder="Enter comments to author" rows="6"></textarea>
                <div v-if="form.errors?.comments_to_author" class="text-sm text-red-700">{{ form.errors.comments_to_author }}</div>
            </div>
            <div class="mb-4">
                <label class="block mb-1 font-semibold">Decision</label>
                <div class="max-h-72 overflow-y-auto">
                    <div class="flex items-center p-2" v-for="review_decision in review_decisions" :key="`review-decision-${review_decision.value}`">
                        <input v-model="form.decision" type="radio" :id="`review-decision-${review_decision.value}`" class="w-4 h-4" :value="review_decision.value" />
                        <label :for="`review-decision-${review_decision.value}`" class="ml-2 text-sm">{{ review_decision.label }}</label>
                    </div>
                </div>
                <div v-if="form.errors?.decision" class="text-sm text-red-700">{{ form.errors.decision }}</div>
            </div>
            <div class="mb-4">
                <label for="comments_to_associate_editor" class="block mb-1 font-semibold">Comments to Associate Editor</label>
                <textarea v-model="form.comments_to_associate_editor" id="comments_to_associate_editor" placeholder="Enter comments to author" rows="6"></textarea>
                <div v-if="form.errors?.comments_to_associate_editor" class="text-sm text-red-700">{{ form.errors.comments_to_associate_editor }}</div>
            </div>
            <div class="mb-4">
                <label class="block mb-1 font-semibold" for="review_report">Review Report</label>
                <template v-if="typeof form.review_report === 'string'">
                    <div class="flex">
                        <i class="bi text-3xl mr-2" :class="`bi-filetype-${extension(form.review_report!)}`"></i>
                        <div>
                            <p><a :href="form.review_report" target="_blank" class="text-primary-400">{{ basename(form.review_report) }}</a></p>
                            <p><a href="#" @click.prevent="form.review_report = undefined" class="text-red-400 text-sm">Remove</a></p>
                        </div>
                    </div>
                </template>
                <template v-else>
                    <input class="block w-full" id="review_report" type="file" accept=".doc,.docx,.jpg,.jpeg,.png,.gif,.pdf,image/jpg,image/jpeg,image/png,image/gif,application/pdf" @input="setFile($event, 'review_report')" />
                    <p class="text-sm text-gray-500">.docx, .doc, .jpg, .jpeg, .png, .gif, .pdf format only</p>
                </template>
                <div v-if="form.errors?.review_report" class="text-sm text-red-700">{{ form.errors.review_report }}</div>
            </div>
        </form>
        <div class="flex mt-3">
            <button type="submit" class="ml-auto bg-primary-500 text-white py-2 px-4" form="edit-form" :disabled="form.processing">
                <Processing v-if="form.processing" class="h-3 my-1.5 w-auto mx-auto text-white" />
                <template v-else><i class="bi bi-arrow-up mr-1"></i> Submit Review</template>
            </button>
        </div>
    </Layout>
</template>

<style scoped lang="scss"></style>
