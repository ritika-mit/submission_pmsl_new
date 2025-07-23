<script setup lang="ts">
import { useReCaptcha } from '@/ts/app';
import Layout from '@/vue/auth/layout.vue';
import Alert from '@/vue/components/alert.vue';
import Processing from '@/vue/components/processing.vue';
import { useForm, usePage } from '@inertiajs/vue3';

const { url } = usePage()

const form = useForm({
    'g-recaptcha-response': null as unknown as string,
});

const recaptcha_ref = useReCaptcha((token) => {
    form['g-recaptcha-response'] = token;
    form.post(url)
})
</script>

<template>
    <Layout title="Verify Your Email Address">
        <form @submit.prevent="form.post(url)" class="w-full md:w-96 p-6" novalidate>
            <div class="mb-8">
                <h2 class="font-serif text-3xl mb-2">Verify Your Email Address</h2>
                <p>Before proceeding, please check your email for a verification link.</p>
                <p>If you did not receive the email.</p>
            </div>
            <Alert />
            <button ref="recaptcha_ref" type="submit" class="w-full bg-primary-500 text-white" :disabled="form.processing">
                <Processing v-if="form.processing" class="h-3 my-1.5 w-auto mx-auto text-white" />
                <template v-else>Click here to request another</template>
            </button>
        </form>
        <p class="text-xs text-gray-400">Copyright &copy; {{ new Date().getFullYear() }} www.ijmems.in, All rights reserved.</p>
    </Layout>
</template>

<style scoped lang="scss"></style>
