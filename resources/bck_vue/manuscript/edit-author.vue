<script setup lang="ts">
import type { Country, RevisionAuthor, RevisionReviewer } from '@/ts/types';
import { useForm } from '@inertiajs/vue3';
import { onUnmounted } from 'vue';

type Props = {
    action: string;
    author?: Partial<RevisionReviewer['reviewer'] | RevisionAuthor['author']>;
    countries: Country[];
}

type Emits = {
    (e: 'onSuccess'): void;
}

const { author } = defineProps<Props>()

defineEmits<Emits>()

const form = useForm({
    id: author?.id,
    title: author?.title,
    first_name: author?.first_name,
    middle_name: author?.middle_name,
    last_name: author?.last_name,
    email: author?.email,
    highest_qualification: author?.highest_qualification,
    organization_institution: author?.organization_institution,
    country: author?.country ?? null,
})

onUnmounted(() => form.reset());

function transformFormData(data: any) {
    data.country = data.country?.id;
    return data;
}
</script>

<template>
    <form class="grid grid-cols-2 gap-4 p-6" @submit.prevent="form.transform(transformFormData).post(action, { onSuccess: () => $emit('onSuccess') })" novalidate>
        <div>
            <label for="title" class="block mb-1 font-semibold">Title</label>
            <input type="text" v-model="form.title" id="title" placeholder="Enter author title" />
            <p class="text-sm text-gray-500">Prof., Dr., Mr., Ms., etc</p>
            <div v-if="form.errors?.title" class="text-sm text-red-700">{{ form.errors.title }}</div>
        </div>
        <div class="col-span-2 grid grid-cols-3 gap-2">
            <div>
                <label for="first_name" class="block mb-1 font-semibold">First Name</label>
                <input type="text" v-model="form.first_name" id="first_name" placeholder="Enter author first name" />
                <div v-if="form.errors?.first_name" class="text-sm text-red-700">{{ form.errors.first_name }}</div>
            </div>
            <div>
                <label for="middle_name" class="block mb-1 font-semibold">Middle Name</label>
                <input type="text" v-model="form.middle_name" id="middle_name" placeholder="Enter author middle name" />
                <div v-if="form.errors?.middle_name" class="text-sm text-red-700">{{ form.errors.middle_name }}</div>
            </div>
            <div>
                <label for="last_name" class="block mb-1 font-semibold">Last Name</label>
                <input type="text" v-model="form.last_name" id="last_name" placeholder="Enter author last name" />
                <div v-if="form.errors?.last_name" class="text-sm text-red-700">{{ form.errors.last_name }}</div>
            </div>
        </div>
        <div class="col-span-2">
            <label for="email" class="block mb-1 font-semibold">E-Mail Address</label>
            <input type="email" v-model="form.email" id="username" placeholder="Enter author email" />
            <div v-if="form.errors?.email" class="text-sm text-red-700">{{ form.errors.email }}</div>
        </div>
        <div class="col-span-2">
            <label for="highest_qualification" class="block mb-1 font-semibold">Highest Qualification</label>
            <input type="text" v-model="form.highest_qualification" id="highest_qualification" placeholder="Enter author highest qualification" />
            <p class="text-sm text-gray-500">Doctorate, Masters, etc</p>
            <div v-if="form.errors?.highest_qualification" class="text-sm text-red-700">{{ form.errors.highest_qualification }}</div>
        </div>
        <div class="col-span-2">
            <label for="organization_institution" class="block mb-1 font-semibold">Organization or Institution</label>
            <input type="text" v-model="form.organization_institution" id="organization_institution" placeholder="Enter author organization or institution" />
            <div v-if="form.errors?.organization_institution" class="text-sm text-red-700">{{ form.errors.organization_institution }}</div>
        </div>
        <div>
            <label for="country" class="block mb-1 font-semibold">Country</label>
            <select v-model="form.country" id="country">
                <option :value="null">Select</option>
                <option v-for="country in countries" :key="`country-${country.id}`" :value="country">{{ country?.name }}</option>
            </select>
            <div v-if="form.errors?.country" class="text-sm text-red-700">{{ form.errors.country }}</div>
        </div>
        <div>
            <label for="country" class="block mb-1 font-semibold">Action</label>
            <button type="submit" class="w-full bg-primary-500 text-white">Save</button>
        </div>
    </form>
</template>

<style scoped lang="scss"></style>