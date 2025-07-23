<script setup lang="ts">
import type { Author, Role } from '@/ts/types';
import Processing from '@/vue/components/processing.vue';
import Layout from '@/vue/layout.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

function getPageParam(): number {
  const params = new URLSearchParams(window.location.search);
  return Number(params.get('page')) || 1;
} 

type Props = {
    author: Author;
    roles: Role[];
}

const { props: { url } } = usePage();
const { author, roles } = defineProps<Props>();

const form = useForm({
    roles: author.roles?.map(item => item.id) || [],
    page: getPageParam()
})
</script>

<template>
    <Layout title="Edit Author Permission">
        <div class="flex items-center mb-2">
            <Link :href="`/authors?page=${form.page}`">
                <i class="bi bi-chevron-compact-left text-2xl"></i>
            </Link>
            <h4 class="text-2xl">{{ author?.id ? 'Edit Author' : 'Create Author' }}</h4>
            <button class="ml-auto bg-primary-500 text-white py-2 px-4" :disabled="form.processing" form="edit-form" type="submit">
                <Processing v-if="form.processing" class="h-3 my-1.5 w-auto mx-auto text-white" />
                <template v-else>Save Author</template>
            </button>
        </div>
        <form @submit.prevent="form.post(url.current)" id="edit-form" class="border p-4 md:p-8 rounded shadow bg-white" novalidate>
            <input type="hidden" name="page" :value="form.page" />
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
                <div>
                    <label class="block mb-1 font-semibold">Name</label>
                    <span class="border block p-2.5 rounded">{{ author.name || '-' }}</span>
                </div>
                <div>
                    <label class="block mb-1 font-semibold">Email</label>
                    <span class="border block p-2.5 rounded">{{ author.email || '-' }}</span>
                </div>
            </div>
            <div class="mb-4">
                <label class="block mb-1 font-semibold">Roles</label>
                <div class="max-h-72 overflow-y-auto">
                    <div class="flex items-center p-2" v-for="item in roles" :key="`role-${item.id}`">
                        <input v-model="form.roles" type="checkbox" :id="`role-${item.id}`" class="w-4 h-4" :value="item.id" />
                        <label :for="`role-${item.id}`" class="ml-2 text-sm">{{ item.name }}</label>
                    </div>
                </div>
                <div v-if="form.errors?.roles" class="text-sm text-red-700">{{ form.errors.roles }}</div>
            </div>
        </form>
    </Layout>
</template>

<style scoped lang="scss"></style>
