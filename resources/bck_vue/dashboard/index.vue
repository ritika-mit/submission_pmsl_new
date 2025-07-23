<script setup lang="ts">
import type { Author, Manuscript } from '@/ts/types';
import Charts from '@/vue/dashboard/charts.vue';
import Layout from '@/vue/layout.vue';

type Props = {
    publishedManuscripts: number,
    totalManuscripts: number,
    totalAuthor: number,
    manuscriptCountByStatus: { value: number, name: string }[];
    authorsVsManuscripts: { months: string[], authors?: number[], manuscripts: number[] };
    authors?: Author[],
    manuscripts?: Manuscript[]
}

defineProps<Props>()
</script>

<template>
    <Layout title="Home">
        <div class="mb-4 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white shadow p-4 rounded md:rounded-lg md:p-8">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <span class="text-2xl sm:text-3xl leading-none font-bold text-gray-900 mb-4">{{ publishedManuscripts }}</span>
                        <h3 class="text-base font-normal text-gray-500">Published Manuscripts</h3>
                    </div>
                    <div class="ml-5 w-0 flex items-center justify-end flex-1">
                        <i class="bi bi-file-pdf text-6xl text-gray-300"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white shadow p-4 rounded md:rounded-lg md:p-8">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <span class="text-2xl sm:text-3xl leading-none font-bold text-gray-900 mb-4">{{ totalManuscripts }}</span>
                        <h3 class="text-base font-normal text-gray-500">Total Manuscripts</h3>
                    </div>
                    <div class="ml-5 w-0 flex items-center justify-end flex-1">
                        <i class="bi bi-file-richtext text-6xl text-gray-300"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white shadow p-4 rounded md:rounded-lg md:p-8">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <span class="text-2xl sm:text-3xl leading-none font-bold text-gray-900 mb-4">{{ totalAuthor }}</span>
                        <h3 class="text-base font-normal text-gray-500">Total Authors</h3>
                    </div>
                    <div class="ml-5 w-0 flex items-center justify-end flex-1">
                        <i class="bi bi-people text-6xl text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
        <Charts :manuscript-count-by-status="manuscriptCountByStatus" :authors-vs-manuscripts="authorsVsManuscripts" />
        <div v-if="authors || manuscripts" class="mb-4 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div v-if="authors">
                <label class="block mb-1 font-semibold">Authors</label>
                <div class="overflow-y-auto rounded">
                    <table class="table text-left text-sm">
                        <thead class="uppercase">
                            <tr>
                                <th>S. No.</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Country</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(row, i) in authors" :key="`row-${i}`">
                                <td>{{ i + 1 }}</td>
                                <td>{{ row.title }} {{ row.name }}</td>
                                <td>{{ row.email }}</td>
                                <td>{{ row.country?.name }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div v-if="manuscripts">
                <label class="block mb-1 font-semibold">Manuscripts</label>
                <div class="overflow-y-auto rounded">
                    <table class="table text-left text-sm">
                        <thead class="uppercase">
                            <tr>
                                <th>S. No.</th>
                                <th>Paper ID</th>
                                <th>Manuscript Type</th>
                                <th>Title</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(row, i) in manuscripts" :key="`row-${i}`">
                                <td>{{ i + 1 }}</td>
                                <td class="truncate">{{ row.code }}</td>
                                <td class="truncate">{{ row.type.label }}</td>
                                <td class="truncate">{{ row.revision?.title }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </Layout>
</template>

<style scoped lang="scss"></style>
