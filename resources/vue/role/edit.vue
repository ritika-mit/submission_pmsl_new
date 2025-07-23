<script setup lang="ts">
import type { Permission, Role, Section } from '@/ts/types';
import Processing from '@/vue/components/processing.vue';
import Layout from '@/vue/layout.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

type Props = {
    role?: Role,
    sections: Section[];
    permissions: Permission[]
}

const { props: { url } } = usePage();
const { role, permissions } = defineProps<Props>();

const form = useForm({
    name: role?.name,
    section: role?.section?.value ?? null,
    default: role?.default,
    permissions: role?.permissions?.map(item => item.id) || [],
})
</script>

<template>
    <Layout :title="role?.id ? 'Edit Role' : 'Create Role'">
        <div class="flex items-center mb-2">
            <Link :href="url.previous">
            <i class="bi bi-chevron-compact-left text-2xl"></i>
            </Link>
            <h4 class="text-2xl">{{ role?.id ? 'Edit Role' : 'Create Role' }}</h4>
            <button class="ml-auto bg-primary-500 text-white py-2 px-4" :disabled="form.processing" form="edit-form" type="submit">
                <Processing v-if="form.processing" class="h-3 my-1.5 w-auto mx-auto text-white" />
                <template v-else>Save Role</template>
            </button>
        </div>
        <form @submit.prevent="form.post(url.current)" id="edit-form" class="border p-4 md:p-8 rounded shadow bg-white" novalidate>
            <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <label for="name" class="block mb-1 font-semibold">Name</label>
                    <input type="text" v-model="form.name" id="name" placeholder="Enter role name" autocomplete="off" />
                    <div v-if="form.errors?.name" class="text-sm text-red-700">{{ form.errors.name }}</div>
                </div>
                <div>
                    <label for="section" class="block mb-1 font-semibold">Section</label>
                    <select v-model="form.section" id="country">
                        <option :value="null">Select</option>
                        <option v-for="section in sections" :key="`country-${section.value}`" :value="section.value">{{ section.label }}</option>
                    </select>
                    <div v-if="form.errors?.section" class="text-sm text-red-700">{{ form.errors.section }}</div>
                </div>
                <div>
                    <label for="default" class="block mb-1 font-semibold">Is Default</label>
                    <label for="default" class="flex items-center border rounded p-2 bg-white">
                        <input id="default" type="checkbox" v-model="form.default" name="type" class="w-4 h-4" :value="true" />
                        <span class="ml-2">Yes</span>
                    </label>
                    <div v-if="form.errors?.default" class="text-sm text-red-700">{{ form.errors.default }}</div>
                </div>
            </div>
            <div class="mb-4">
                <label class="block mb-1 font-semibold">Permissions</label>
                <div class="flex items-center p-2" v-for="item in permissions" :key="`permission-${item.id}`">
                    <input v-model="form.permissions" type="checkbox" :id="`permission-${item.id}`" class="w-4 h-4" :value="item.id" />
                    <label :for="`permission-${item.id}`" class="ml-2 text-sm">{{ item.label }}</label>
                </div>
                <div v-if="form.errors?.permissions" class="text-sm text-red-700">{{ form.errors.permissions }}</div>
            </div>
        </form>
    </Layout>
</template>

<style scoped lang="scss"></style>
