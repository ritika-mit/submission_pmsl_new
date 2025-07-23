<script setup lang="ts">
import { date, extension } from '@/ts/app';
import type { Action, Column, Filter, Manuscript, Revision, TData } from '@/ts/types';
import Modal from '@/vue/components/modal.vue';
import Processing from '@/vue/components/processing.vue';
import Table from '@/vue/components/table.vue';
import Layout from '@/vue/layout.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { parse } from 'qs';
import { ref } from 'vue';

type Props = {
    data: TData<Manuscript & Revision & { actions: Action[] }>,
    columns: Record<string, Column>,
    actions: Action[];
}

const { props: { url } } = usePage();
const { columns, data } = defineProps<Props>();

const updateManuscript = ref<Manuscript>();
const formatterDialogModalAction = ref(false);

const filter = useForm(
    parse(window.location.search, { ignoreQueryPrefix: true }) as unknown as Filter
)
const updateFormatterForm = useForm({
    formatted_paper:undefined as unknown as string | File | undefined,
    correction_file:undefined as unknown as string | File | undefined,
    other_file:undefined as unknown as string | File | undefined,

});

function submit(_filter: Filter) {
    filter.transform(() => _filter).get(url.current, {
        preserveState: true,
        preserveScroll: true
    })
}

function actionEvent($event: Event, row: Manuscript) {
    const target = $event.target as HTMLSelectElement;
    const action = target.value;
    target.value = '';
    switch (action) {
        case 'format_paper':
            updateManuscript.value = row;
            formatterDialogModalAction.value = true;
            return;
    }
}

</script>

<template>
    <Layout title="Formatter">
        <div class="flex items-center mb-4">
            <h4 class="text-2xl">Formatter</h4>
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
            <template #action="{ row }">
                <select class="py-1.5" @change="$event => actionEvent($event, row)">
                    <option value="">Select</option>
                    <option v-for="action in actions" :value="action.value">{{ action.label }}</option>
                </select>
            </template>
        </Table>
        <Modal v-if="formatterDialogModalAction" title="Formatter" size="sm" @close="formatterDialogModalAction = false">
            <div class="p-3">
                <h4 class="text-center text-lg py-2 underline font-bold">Upload Formatter Paper</h4>
                <div class="py-2">
                    <div class="col-md-12">
                        <div class="px-3 mb-3">
                            <label class="block mb-1 font-semibold" for="formatted_paper">Formatted paper</label>
                            <input class="block w-full" id="formatted_paper" type="file" accept=".doc,.docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" @input="$event => updateFormatterForm.formatted_paper = ($event.target! as HTMLInputElement).files![0]" />
                            <p class="text-sm text-gray-500">.doc, .docx format only</p>
                            <div v-if="updateFormatterForm.errors?.formatted_paper" class="text-sm text-red-700">{{ updateFormatterForm.errors.formatted_paper }}</div>
                        </div>
                        <div class="px-3 mb-3">
                            <label class="block mb-1 font-semibold" for="correction_file">Correction File</label>
                            <input class="block w-full" id="correction_file" type="file" accept=".doc,.docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" @input="$event => updateFormatterForm.correction_file = ($event.target! as HTMLInputElement).files![0]" />
                            <p class="text-sm text-gray-500">.doc, .docx format only</p>
                            <div v-if="updateFormatterForm.errors?.correction_file" class="text-sm text-red-700">{{ updateFormatterForm.errors.correction_file }}</div>
                        </div>
                        <div class="px-3 mb-3">
                            <label class="block mb-1 font-semibold" for="other_file">Other file</label>
                            <input class="block w-full" id="other_file" type="file" @input="$event => updateFormatterForm.other_file = ($event.target! as HTMLInputElement).files![0]" />
                            <div v-if="updateFormatterForm.errors?.other_file" class="text-sm text-red-700">{{ updateFormatterForm.errors.other_file }}</div>
                        </div>
                    </div>
                </div>
                <div class="py-2 flex justify-center space-x-1">
                    <Processing v-if="updateFormatterForm.processing" class="h-3 my-1.5 w-auto mx-auto" />
                    <template v-else>
                        <button type="button" class="text-sm py-2 px-4 bg-primary-500 text-white" @click.prevent="updateFormatterForm.post(`/formatter/${updateManuscript?.id}`, { onSuccess: () => formatterDialogModalAction = false })"> Save
                        </button>
                        <button type="button" class="text-sm py-2 px-4" @click.prevent="formatterDialogModalAction = false">Cancel</button>
                    </template>
                </div>
            </div>
        </Modal>
    </Layout>
</template>

<style scoped lang="scss"></style>
