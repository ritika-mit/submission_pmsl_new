<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';

type Props = {
    asideOpened: boolean
}

defineProps<Props>()

const page = usePage();

const logout = () => router.post('/auth/logout', {});
</script>

<template>
    <aside class="flex flex-col fixed top-0 bottom-0 transition-[width] md:w-64 pt-24 md:pt-16 overflow-y-auto bg-primary-50 bg-opacity-10" :class="`${asideOpened ? 'max-md:bg-white max-md:z-10' : 'w-0'}`" aria-label="Sidebar">
        <div class="flex flex-col flex-1 py-3 overflow-y-auto">
            <div class="flex-1 px-2">
                <ul class="pb-2 space-y-1 text-sm">
                    <li v-for="item in page.props.aside.items" class="group">
                        <Link :href="item.path" class="flex items-center p-1.5 rounded group" :class="`${item.active ? 'bg-primary-500 text-white' : 'group-hover:bg-primary-500 hover:text-white'}`">
                        <span class="transition duration-75">
                            <i class="bi bi-chevron-compact-right text-lg"></i>
                        </span>
                        <span class="ml-1 group-hover:underline">{{ item.label }}</span>
                        <span v-if="item.badge" class="text-xs ml-auto" :class="`${item.active ? 'text-gray-200' : 'text-gray-500 group-hover:text-gray-200'}`">{{ item.badge }}</span>
                        </Link>
                    </li>
                </ul>
            </div>
        </div>
        <div class="flex items-center justify-center bg-primary-100 bg-opacity-20 p-2">
            <div class="mr-2 h-8 w-8 rounded-full border-0 bg-primary-500 p-1">
                <svg class="rounded-circle h-6 w-6 text-gray-200" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div>
                <span class="block text-sm text-primary-500">{{ page.props.auth.user.name }}</span>
                <span class="block truncate text-xs text-gray-500">{{ page.props.auth.user.email }}</span>
            </div>
            <a href="#" class="my-auto ml-auto text-gray-500" @click.prevent="logout">
                <i class="bi bi-box-arrow-right align-text-top text-xl"></i>
            </a>
        </div>
    </aside>
</template>

<style scoped lang="scss"></style>
