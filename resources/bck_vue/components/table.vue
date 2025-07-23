<script setup lang="ts" generic="T">
import { getValue } from '@/ts/app';
import type { Column, Filter, TData } from '@/ts/types';
import Pagination from '@/vue/components/pagination.vue';
import { reactive } from 'vue';

type Props = {
    data: TData<T>;
    filter: Filter;
    processing?: boolean,
    columns: Record<string, Column>;
}

type Emits = {
    (e: 'filterChanged', filter: Filter): void;
}

const { filter: _filter } = defineProps<Props>();

const emits = defineEmits<Emits>();

const filter = reactive<Filter>({
    page: +_filter.page || 1,
    perPage: +_filter.perPage || 15,
    search: _filter.search,
    order: _filter.order || {},
});

function filterChange(e: Event) {
    const target = e.target as HTMLInputElement;
    if (target.name === 'search') {
        filter.search = target.value;
    } else if (target.name === 'perPage') {
        filter.perPage = +target.value;
    }
    filter.page = 1;

    emits('filterChanged', filter);
}

function orderChange(column: string) {
    filter.order[column] = typeof filter.order[column] === 'undefined'
        ? 'asc'
        : filter.order[column] === 'asc'
            ? 'desc'
            : undefined;

    emits('filterChanged', filter);
}

function pageChange(value: number) {
    filter.page = value;

    emits('filterChanged', filter);
}

function orderIcon(column: string) {
    const order = filter.order[column];

    return typeof order === 'undefined'
        ? 'bi-arrow-down-up opacity-30'
        : order === 'asc'
            ? 'bi-arrow-up'
            : 'bi-arrow-down';
}
</script>

<template>
    <div class="flex items-center mb-3">
        <div class="w-20">
            <div class="relative">
                <select class="py-1 rounded" name="perPage" :value="filter.perPage" @change="filterChange">
                    <option v-for="(item, index) in [15, 50, 100, 250]" :key="index" :value="item">
                        {{ item }}
                    </option>
                </select>
                <div class="flex absolute inset-y-0 right-0 items-center pr-3 pointer-events-none">
                    <i class="bi bi-chevron-down text-gray-400"></i>
                </div>
            </div>
        </div>
        <div class="w-56 ml-auto">
            <div class="relative">
                <div class="flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none">
                    <i class="bi bi-search text-gray-400"></i>
                </div>
                <input type="search" class="p-1 pl-8 rounded" placeholder="Search" name="search" :value="filter.search" @input="filterChange" />
            </div>
        </div>
    </div>
    <div class="overflow-y-auto relative rounded shadow mb-3">
        <table class="table text-left text-sm">
            <thead class="uppercase">
                <tr>
                    <th v-for="({ label, orderable }, key) in columns" :key="key" class="relative cursor-pointer pr-3" :class="filter.order[key]" @click="orderable ? orderChange(key) : null">
                        {{ label }}
                        <i v-if="orderable" class="absolute right-3 bi" :class="orderIcon(key)"></i>
                    </th>
                </tr>
            </thead>
            <tbody>
                <template v-if="processing">
                    <tr v-for="(_, i) in filter.perPage" :key="`row-${i}`">
                        <td v-for="(_, key) in columns" :key="`column-${key}-${i}`">
                            <div class="h-2.5 animate-pulse bg-gray-300 rounded-full w-3/4"></div>
                        </td>
                    </tr>
                </template>
                <template v-else>
                    <template v-if="data.items.length === 0">
                        <tr>
                            <td :colspan="Object.keys(columns).length" class="py-7 text-center">
                                <i class="bi bi-emoji-frown mt-3 text-7xl text-gray-400"></i>
                                <h6 class="mt-3 text-2xl text-gray-800">No result found.</h6>
                                <p class="text-lg text-gray-700">Try changing the filters or search term.</p>
                            </td>
                        </tr>
                    </template>
                    <template v-else>
                        <tr v-for="(row, i) in data.items" :key="`row-${i}`">
                            <td v-for="(_, key) in columns" :key="`column-${key}-${i}`">
                                <slot :name="key" :key="`slot-${key}-${i}`" :row="row" :filter="filter" :index="i">{{ getValue(key as keyof T, row) }}</slot>
                            </td>
                        </tr>
                    </template>
                </template>
            </tbody>
        </table>
    </div>
    <div class="flex items-center mb-3">
        <div class="text-gray-600">Showing <span class="font-semibold text-gray-900">{{ filter.perPage * (filter.page - 1) + 1 }}</span> to <span class="font-semibold text-gray-900">{{ filter.perPage * (filter.page - 1) + data.items.length }}</span> of <span class="font-semibold text-gray-900">{{ data.total }}</span> entries</div>
        <nav class="ml-auto">
            <Pagination :from="1" :current="+filter.page" :to="Math.ceil(data.total / filter.perPage)" :each-side="2" @changed="pageChange" />
        </nav>
    </div>
</template>

<style scoped lang="scss"></style>
