<script setup lang="ts">
import { useReCaptcha } from '@/ts/app';
import Layout from '@/vue/auth/layout.vue';
import Alert from '@/vue/components/alert.vue';
import Processing from '@/vue/components/processing.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

const { url } = usePage()

type Props = {
    email: string;
}

const { email } = defineProps<Props>()

const form = useForm({
    email: email,
    password: null,
    password_confirmation: null,
    'g-recaptcha-response': null as unknown as string,
});

const recaptcha_ref = useReCaptcha((token) => {
    form['g-recaptcha-response'] = token;
    form.post(url)
})
</script>

<template>
    <Layout title="Change Your Password">
        <form @submit.prevent="form.post(url)" class="w-full md:w-96 p-6" novalidate>
            <div class="mb-8">
                <h2 class="font-serif text-3xl mb-2">Change Your Password</h2>
                <p>Enter a new password below to change your password.</p>
            </div>
            <Alert />
            <div class="mb-4">
                <label for="email" class="block mb-1 font-semibold">Password</label>
                <div class="relative">
                    <div class="flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none">
                        <i class="bi bi-key"></i>
                    </div>
                    <input type="password" v-model="form.password" id="password" placeholder="Enter your password" class="pl-10" />
                </div>
                <div v-if="form.errors?.password" class="text-sm text-red-700">{{ form.errors.password }}</div>
            </div>
            <div class="mb-4">
                <label for="password_confirmation" class="block mb-1 font-semibold">Confirm Password</label>
                <div class="relative">
                    <div class="flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none">
                        <i class="bi bi-key"></i>
                    </div>
                    <input type="password" v-model="form.password_confirmation" id="password_confirmation" placeholder="Confirm your password" class="pl-10" />
                </div>
                <div v-if="form.errors?.password_confirmation" class="text-sm text-red-700">{{ form.errors.password_confirmation }}</div>
            </div>
            <button ref="recaptcha_ref" type="submit" class="w-full bg-primary-500 text-white" :disabled="form.processing">
                <Processing v-if="form.processing" class="h-3 my-1.5 w-auto mx-auto text-white" />
                <template v-else>Reset Password</template>
            </button>
        </form>
        <p class="mb-3">Back to
            <Link href="/auth" class="text-primary-400">Login</Link>
        </p>
        <p class="text-xs text-gray-400 mb-3 text-center">
                Copyright &copy; Ram Arti Publishers,
                All rights reserved.
        </p>
    </Layout>
</template>

<style scoped lang="scss"></style>
