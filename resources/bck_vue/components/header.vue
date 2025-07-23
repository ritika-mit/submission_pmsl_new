<script setup lang="ts">
import type { HeaderItem } from '@/ts/types';
import Processing from '@/vue/components/processing.vue';
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import { parse } from 'qs';
import { ref } from 'vue';

const page = usePage();

type Emits = {
    (e: 'asideButtonClick'): void;
}

defineEmits<Emits>()

const navbarOpened = ref(false);

const filter = useForm<{ q?: string }>(
    Object.assign({ q: '' }, parse(window.location.search, { ignoreQueryPrefix: true }))
)

const search = () => {
    filter.get('', { only: ['search'], preserveState: true, preserveScroll: true, })
}

const switchRole = (item: HeaderItem) => router.post(`/auth/switch/${item.value}`);
</script>

<template>
    <header class="fixed top-0 z-20 w-screen shadow-lg bg-primary-600">
        <nav class="flex flex-col md:flex-row items-center md:py-3">
            <div class="flex items-center justify-around max-md:py-1 w-full md:w-64">
                <button type="button" class="border-0 text-white md:hidden" @click="$emit('asideButtonClick')">
                    <i class="bi bi-list text-2xl"></i>
                </button>
                <a href="/">
                    <h1 class="text-2xl uppercase px-2 tracking-widest text-center font-bold text-white">submission</h1>
                </a>
                <button type="button" class="border-0 text-white md:hidden" @click="navbarOpened = !navbarOpened">
                    <i class="bi bi-search text-xl"></i>
                </button>
            </div>
            <div class="w-96 relative max-md:mb-3" :class="`${navbarOpened ? '' : 'max-md:hidden'}`">
                <form class="relative" @submit.prevent="search" id="search-form" novalidate>
                    <div class="flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none">
                        <i class="bi bi-search text-xl text-black"></i>
                    </div>
                    <input type="search" v-model="filter.q" class="p-2 border-0 pl-10" :class="{ 'rounded-b-none': filter.q && page.props.search?.items && page.props.search?.items.length > 0 }" placeholder="Search the anything here..." autocomplete="off" @input="search" />
                    <div v-if="filter.processing" class="flex absolute inset-y-0 right-0 items-center pr-3 pointer-events-none">
                        <Processing class="h-3 w-auto text-primary-500" />
                    </div>
                </form>
                <ul v-if="filter.q && page.props.search?.items && page.props.search?.items.length > 0" class="absolute z-10 max-h-60 w-full overflow-x-hidden overflow-y-auto overflow-ellipsis rounded-b border bg-white shadow-lg group-focus-within:block">
                    <li v-for="(row, i) in page.props.search?.items" :key="`row-${i}`">
                        <Link :href="`/manuscripts/${row.id}/view`" class="block border-t p-2.5 hover:bg-gray-100">
                        <p>{{ row.code }} - {{ row.type.label }}</p>
                        <p class="text-gray-500 text-sm">{{ row.revision?.title }}</p>
                        </Link>
                    </li>
                </ul>
            </div>
            <div class="mr-6 ml-auto max-md:pb-5 overflow-x-auto max-md:w-full">
                <ul v-if="page.props.header.items" class="flex flex-row font-medium text-sm w-max mx-auto">
                    <li v-for="item, index in page.props.header.items" :key="item.value" class="group">
                        <a href="#" @click.prevent="switchRole(item)" class="block hover:text-white" :class="[page.props.auth.user.section.value === item.value ? 'text-white' : 'text-primary-100', index !== page.props.header.items.length - 1 ? 'after:content-[\'/\'] after:text-gray-600' : '']" aria-current="page">
                            <span class="px-2.5" :class="{ 'underline underline-offset-4': page.props.auth.user.section.value === item.value }">{{ item.label }}</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </header>
</template>

<style scoped lang="scss"></style>
