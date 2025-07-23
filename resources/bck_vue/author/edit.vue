<script setup lang="ts">
import type { Author, Country, ResearchArea } from '@/ts/types';
import Processing from '@/vue/components/processing.vue';
import Layout from '@/vue/layout.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { vMaska } from 'maska';

type Props = {
    author: Author | null;
    research_areas: ResearchArea[];
    countries: Country[];
}

const { props: { url } } = usePage();
const { author, research_areas, countries } = defineProps<Props>();

const form = useForm({
    title: author?.title,
    first_name: author?.first_name,
    middle_name: author?.middle_name,
    last_name: author?.last_name,
    email: author?.email,
    password: null,
    password_confirmation: null,
    highest_qualification: author?.highest_qualification,
    scopus_id: author?.scopus_id,
    orcid_id: author?.orcid_id,
    position: author?.position,
    department: author?.department,
    organization_institution: author?.organization_institution,
    address_line_1: author?.address_line_1,
    address_line_2: author?.address_line_2,
    city: author?.city,
    state: author?.state,
    postal_code: author?.postal_code,
    country: author?.country?.id,
    research_areas: author?.research_areas?.map(item => item.id) || [],
    privacy_policy: author?.privacy_policy_accepted,
    subscribe_to_notifications: author?.subscribed_for_notifications,
    accept_review_request: author?.accept_review_request,
});
</script>

<template>
    <Layout :title="author?.id ? 'Edit Author' : 'Create Author'">
        <div class="flex items-center mb-2">
            <Link :href="url.previous">
            <i class="bi bi-chevron-compact-left text-2xl"></i>
            </Link>
            <h4 class="text-2xl">{{ author?.id ? 'Edit Author' : 'Create Author' }}</h4>
            <button class="ml-auto bg-primary-500 text-white py-2 px-4" :disabled="form.processing" form="edit-form" type="submit">
                <Processing v-if="form.processing" class="h-3 my-1.5 w-auto mx-auto text-white" />
                <template v-else>Save Author</template>
            </button>
        </div>
        <form @submit.prevent="form.post(url.current)" id="edit-form" class="border p-4 md:p-8 rounded shadow bg-white w-full grid grid-cols-1 md:grid-cols-2 gap-4 mb-6" novalidate>
            <div>
                <label for="title" class="block mb-1 font-semibold">Title</label>
                <input type="text" v-model="form.title" id="title" placeholder="Enter author title" />
                <p class="text-sm text-gray-500">Prof., Dr., Mr., Ms., etc</p>
                <div v-if="form.errors?.title" class="text-sm text-red-700">{{ form.errors.title }}</div>
            </div>
            <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="first_name" class="block mb-1 font-semibold">First Name</label>
                    <input type="text" v-model="form.first_name" id="first_name" placeholder="Enter author first name" />
                    <div v-if="form.errors?.first_name" class="text-sm text-red-700">{{ form.errors.first_name }}</div>
                </div>
                <div>
                    <label for="middle_name" class="block mb-1 font-semibold">Middle Name <span class="text-sm font-light text-gray-400">(Optional)</span></label>
                    <input type="text" v-model="form.middle_name" id="middle_name" placeholder="Enter author middle name" />
                    <div v-if="form.errors?.middle_name" class="text-sm text-red-700">{{ form.errors.middle_name }}</div>
                </div>
                <div>
                    <label for="last_name" class="block mb-1 font-semibold">Last Name</label>
                    <input type="text" v-model="form.last_name" id="last_name" placeholder="Enter author last name" />
                    <div v-if="form.errors?.last_name" class="text-sm text-red-700">{{ form.errors.last_name }}</div>
                </div>
            </div>
            <div class="md:col-span-2">
                <label for="email" class="block mb-1 font-semibold">E-Mail Address</label>
                <input type="email" v-model="form.email" id="username" placeholder="Enter author email" />
                <div v-if="form.errors?.email" class="text-sm text-red-700">{{ form.errors.email }}</div>
            </div>
            <div class="md:col-span-2">
                <label for="highest_qualification" class="block mb-1 font-semibold">Highest Qualification</label>
                <input type="text" v-model="form.highest_qualification" id="highest_qualification" placeholder="Enter author highest qualification" />
                <p class="text-sm text-gray-500">Doctorate, Masters, etc</p>
                <div v-if="form.errors?.highest_qualification" class="text-sm text-red-700">{{ form.errors.highest_qualification }}</div>
            </div>
            <div>
                <label for="scopus_id" class="block mb-1 font-semibold">Scopus ID <span class="text-sm font-light text-gray-400">(Optional)</span></label>
                <input type="text" v-model="form.scopus_id" id="scopus_id" placeholder="Enter author scopus id" />
                <div v-if="form.errors?.scopus_id" class="text-sm text-red-700">{{ form.errors.scopus_id }}</div>
            </div>
            <div>
                <label for="orcid_id" class="block mb-1 font-semibold">ORCID ID <span class="text-sm font-light text-gray-400">(Optional)</span></label>
                <input type="text" v-model="form.orcid_id" id="orcid_id" placeholder="Enter author orcid id" v-maska data-maska="****-****-****-****" />
                <div v-if="form.errors?.orcid_id" class="text-sm text-red-700">{{ form.errors.orcid_id }}</div>
            </div>
            <div>
                <label for="position" class="block mb-1 font-semibold">Position</label>
                <input type="text" v-model="form.position" id="position" placeholder="Enter author position" />
                <div v-if="form.errors?.position" class="text-sm text-red-700">{{ form.errors.position }}</div>
            </div>
            <div>
                <label for="department" class="block mb-1 font-semibold">Department</label>
                <input type="text" v-model="form.department" id="department" placeholder="Enter author department" />
                <div v-if="form.errors?.department" class="text-sm text-red-700">{{ form.errors.department }}</div>
            </div>
            <div class="md:col-span-2">
                <label for="organization_institution" class="block mb-1 font-semibold">Organization or Institution</label>
                <input type="text" v-model="form.organization_institution" id="organization_institution" placeholder="Enter author organization or institution" />
                <div v-if="form.errors?.organization_institution" class="text-sm text-red-700">{{ form.errors.organization_institution }}</div>
            </div>
            <div class="md:col-span-2">
                <label for="address_line_1" class="block mb-1 font-semibold">Address Line 1</label>
                <input type="text" v-model="form.address_line_1" id="address_line_1" placeholder="Enter author address line 1" />
                <div v-if="form.errors?.address_line_1" class="text-sm text-red-700">{{ form.errors.address_line_1 }}</div>
            </div>
            <div class="md:col-span-2">
                <label for="address_line_2" class="block mb-1 font-semibold">Address Line 2</label>
                <input type="text" v-model="form.address_line_2" id="address_line_2" placeholder="Enter author address line 2" />
                <div v-if="form.errors?.address_line_2" class="text-sm text-red-700">{{ form.errors.address_line_2 }}</div>
            </div>
            <div>
                <label for="city" class="block mb-1 font-semibold">City</label>
                <input type="text" v-model="form.city" id="city" placeholder="Enter author city" />
                <div v-if="form.errors?.city" class="text-sm text-red-700">{{ form.errors.city }}</div>
            </div>
            <div>
                <label for="state" class="block mb-1 font-semibold">State</label>
                <input type="text" v-model="form.state" id="state" placeholder="Enter author state" />
                <div v-if="form.errors?.state" class="text-sm text-red-700">{{ form.errors.state }}</div>
            </div>
            <div>
                <label for="postal_code" class="block mb-1 font-semibold">Zip or Postal Code</label>
                <input type="text" v-model="form.postal_code" id="postal_code" placeholder="Enter author postal code" />
                <div v-if="form.errors?.postal_code" class="text-sm text-red-700">{{ form.errors.postal_code }}</div>
            </div>
            <div>
                <label for="country" class="block mb-1 font-semibold">Country</label>
                <select v-model="form.country" id="country">
                    <option :value="null">Select</option>
                    <option v-for="country in countries" :key="`country-${country.id}`" :value="country.id">{{ country?.name }}</option>
                </select>
                <div v-if="form.errors?.country" class="text-sm text-red-700">{{ form.errors.country }}</div>
            </div>
            <div class="md:col-span-2">
                <label class="block mb-1 font-semibold">Areas of Interest or Expertise</label>
                <div class="max-h-72 overflow-y-auto">
                    <div class="flex items-center p-2" v-for="item in  research_areas " :key="`research-area-${item.id}`">
                        <input v-model="form.research_areas" type="checkbox" :id="`research-area-${item.id}`" :value="item.id" />
                        <label :for="`research-area-${item.id}`" class="ml-2 text-sm">{{ item.research_area }}</label>
                    </div>
                </div>
                <p class="text-sm text-gray-500">Kindly select at least 3 to 5 broad research areas of article</p>
                <div v-if="form.errors?.research_areas" class="text-sm text-red-700">{{ form.errors.research_areas }}</div>
            </div>
            <div class="md:col-span-2">
                <div class="mb-4">
                    <div class="flex items-center">
                        <input v-model="form.privacy_policy" type="checkbox" id="privacy-policy" disabled />
                        <label for="privacy-policy" class="ml-2 text-sm">Yes, I agree to have my data collected and stored according to the <a href="https://www.ijmems.in/privacypolicy.php" target="_blank" class="text-primary-400">privacy statement</a>.</label>
                    </div>
                    <div v-if="form.errors?.privacy_policy" class="text-sm text-red-700">{{ form.errors.privacy_policy }}</div>
                </div>
                <div class="mb-4">
                    <div class="flex items-center">
                        <input v-model="form.subscribe_to_notifications" type="checkbox" id="subscribe-to-notifications" />
                        <label for="subscribe-to-notifications" class="ml-2 text-sm">Yes, I would like to be notified of new publications and announcements.</label>
                    </div>
                    <div v-if="form.errors?.subscribe_to_notifications" class="text-sm text-red-700">{{ form.errors.subscribe_to_notifications }}</div>
                </div>
                <div class="mb-4">
                    <div class="flex items-center">
                        <input v-model="form.accept_review_request" type="checkbox" id="accept-review-request" />
                        <label for="accept-review-request" class="ml-2 text-sm">Yes, I would like to be contacted with requests to review submissions to this journal.</label>
                    </div>
                    <div v-if="form.errors?.accept_review_request" class="text-sm text-red-700">{{ form.errors.accept_review_request }}</div>
                </div>
            </div>
        </form>
    </Layout>
</template>

<style scoped lang="scss"></style>
