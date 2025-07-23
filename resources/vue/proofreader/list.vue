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
const proofreaderDialogModalAction = ref(false);

const filter = useForm(
    parse(window.location.search, { ignoreQueryPrefix: true }) as unknown as Filter
)
const updateProofreaderForm = useForm({
    proofreader_paper:undefined as unknown as string | File | undefined
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
        case 'proofreader_paper':
            updateManuscript.value = row;
            proofreaderDialogModalAction.value = true;
            return;
    }
}

</script>

<template>
    <Layout title="Proofreader">
        <div class="flex items-center mb-4">
            <h4 class="text-2xl">Proof Read</h4>
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
                    <div class="whitespace-nowrap mb-1" v-if="row.revision?.formatted_paper">
                        <a :href="row.revision.formatted_paper" target="_blank" class="text-primary-400 hover:underline"><i class="bi" :class="`bi-filetype-${extension(row.revision.formatted_paper)}`"></i> Formatted Paper</a>
                    </div>
                    <div class="whitespace-nowrap mb-1" v-if="row.revision?.correction_file">
                        <a :href="row.revision.correction_file" target="_blank" class="text-primary-400 hover:underline"><i class="bi" :class="`bi-filetype-${extension(row.revision.correction_file)}`"></i> Correction File</a>
                    </div>
                    <div class="whitespace-nowrap mb-1" v-if="row.revision?.other_file">
                        <a :href="row.revision.other_file" target="_blank" class="text-primary-400 hover:underline"><i class="bi" :class="`bi-filetype-${extension(row.revision.other_file)}`"></i> Other File</a>
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
        <Modal v-if="proofreaderDialogModalAction" title="Proofreader" size="sm" @close="proofreaderDialogModalAction = false">
            <div class="p-3">
                <h4 class="text-center text-lg py-2 underline font-bold">Upload Proofreader Paper</h4>
                <div class="py-2">
                    <div class="col-md-12">
                        <div class="px-3 mb-3">
                            <label class="block mb-1 font-semibold" for="proofreader_paper">Upload Proofreader Paper</label>
                            <input class="block w-full" id="proofreader_paper" type="file" @input="$event => updateProofreaderForm.proofreader_paper = ($event.target! as HTMLInputElement).files![0]" />
                            <div v-if="updateProofreaderForm.errors?.proofreader_paper" class="text-sm text-red-700">{{ updateProofreaderForm.errors.proofreader_paper }}</div>
                        </div>
                    </div>
                </div>
                <div class="py-2 flex justify-center space-x-1">
                    <Processing v-if="updateProofreaderForm.processing" class="h-3 my-1.5 w-auto mx-auto" />
                    <template v-else>
                        <button type="button" class="text-sm py-2 px-4 bg-primary-500 text-white" @click.prevent="updateProofreaderForm.post(`/proofreader/${updateManuscript?.id}`, { onSuccess: () => proofreaderDialogModalAction = false })"> Save
                        </button>
                        <button type="button" class="text-sm py-2 px-4" @click.prevent="proofreaderDialogModalAction = false">Cancel</button>
                    </template>
                </div>
            </div>
        </Modal>
    </Layout>
</template>

<style scoped lang="scss"></style>
