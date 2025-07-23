<script setup lang="ts">
import { date } from '@/ts/app';
import type { Action, Author, Column, Filter, TData } from '@/ts/types';
import Table from '@/vue/components/table.vue';
import Layout from '@/vue/layout.vue';
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import { parse } from 'qs';

type Props = {
    data: TData<Author>,
    columns: Record<string, Column>,
    actions: Action[];
}

const { props: { url } } = usePage();
const { columns, data } = defineProps<Props>();

const filter = useForm(
    parse(window.location.search, { ignoreQueryPrefix: true }) as unknown as Filter
)

function submit(_filter: Filter) {
    filter.transform(() => _filter).get(url.current, {
        preserveState: true,
        preserveScroll: true
    })
}

function action($event: Event, row: Author) {
    const target = $event.target as HTMLSelectElement;
    const action = target.value;
    target.value = '';

    switch (action) {
        case 'edit':
            return router.visit(`/authors/${row.id}`);

        case 'permission':
            return router.visit(`/authors/${row.id}/permission`);
    }
}

</script>

<template>
    <Layout title="Authors">
        <div class="flex items-center mb-4">
            <h4 class="text-2xl">Authors</h4>
            <Link href="/authors/create" class="ml-auto py-2 px-4 rounded" role="button">Add New Author</Link>
        </div>
        <Table :columns="columns" :data="data" :filter="filter.data()" :processing="filter.processing" @filterChanged="submit">
            <template #index="{ index, filter }">{{ ((filter.page - 1) * filter.perPage) + index + 1 }}</template>
            <template #roles="{ row }">{{ row.roles?.map(item => item.name).join(', ') || '-' }}</template>
            <template #created_at="{ row }">{{ date(row.created_at) }}</template>
            <template #updated_at="{ row }">{{ date(row.updated_at) }}</template>
            <template #action="{ row }">
                <select class="py-1.5" @change="$event => action($event, row)">
                    <option value="">Select</option>
                    <option v-for="action in actions" :value="action.value">{{ action.label }}</option>
                </select>
            </template>
        </Table>
    </Layout>
</template>

<style scoped lang="scss"></style>
