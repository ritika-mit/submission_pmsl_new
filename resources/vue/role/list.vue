<script setup lang="ts">
import { date } from '@/ts/app';
import type { Column, Filter, Role, TData } from '@/ts/types';
import Table from '@/vue/components/table.vue';
import Layout from '@/vue/layout.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { parse } from 'qs';

type Props = {
    data: TData<Role>,
    columns: Record<string, Column>,
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
</script>

<template>
    <Layout title="Roles">
        <div class="flex items-center mb-4">
            <h4 class="text-2xl">Roles</h4>
            <Link href="/roles/create" class="ml-auto py-2 px-4 rounded" role="button">Add New Role</Link>
        </div>
        <Table :columns="columns" :data="data" :filter="filter.data()" :processing="filter.processing" @filterChanged="submit">
            <template #index="{ index, filter }">{{ ((filter.page - 1) * filter.perPage) + index + 1 }}</template>
            <template #section="{ row }">{{ row.section.label }}</template>
            <template #default="{ row }">
                <i v-if="row.default" class="bi bi-check-square-fill"></i>
                <template v-else>-</template>
            </template>
            <template #created_at="{ row }">{{ date(row.created_at) }}</template>
            <template #updated_at="{ row }">{{ date(row.updated_at) }}</template>
            <template #action="{ row }">
                <Link :href="`/roles/${row.id}`">
                <i class="bi bi-pencil"></i>
                </Link>
            </template>
        </Table>
    </Layout>
</template>

<style scoped lang="scss"></style>
