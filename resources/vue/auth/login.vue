<script setup lang="ts">
import { useReCaptcha } from '@/ts/app';
import Layout from '@/vue/auth/layout.vue';
import Alert from '@/vue/components/alert.vue';
import Processing from '@/vue/components/processing.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

const { url } = usePage()

const form = useForm({
    username: null,
    password: null,
    remember: false,
    'g-recaptcha-response': null as unknown as string,
});

const recaptcha_ref = useReCaptcha((token) => {
    form['g-recaptcha-response'] = token;
    form.post(url)
})
</script>

<template>
    <Layout title="Login">
        <form @submit.prevent="form.post(url)" class="w-full md:w-96 p-6" novalidate>
            <div class="mb-8">
                <h2 class="font-serif text-3xl mb-2">Welcome back!</h2>
                <p>Login to your account to continue.</p>
            </div>
            <Alert />
            <div class="mb-4">
                <label for="username" class="block mb-1 font-semibold">Username</label>
                <div class="relative">
                    <div class="flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none">
                        <i class="bi bi-person"></i>
                    </div>
                    <input type="text" v-model="form.username" id="username" placeholder="Enter your username or email" class="pl-10" />
                </div>
                <div v-if="form.errors?.username" class="text-sm text-red-700">{{ form.errors.username }}</div>
            </div>
            <div class="mb-4">
                <label for="password" class="block mb-1 font-semibold">Password</label>
                <div class="relative">
                    <div class="flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none">
                        <i class="bi bi-key"></i>
                    </div>
                    <input type="password" v-model="form.password" id="password" placeholder="Enter your password" class="pl-10" />
                </div>
                <div v-if="form.errors?.password" class="text-sm text-red-700">{{ form.errors.password }}</div>
                <Link href="/auth/password" class="text-primary-400 text-sm">Forgot password?</Link>
            </div>
            <div class="mb-4">
                <div class="flex items-center">
                    <input type="checkbox" v-model="form.remember" id="remember" checked />
                    <label for="remember" class="ml-2">Remember me</label>
                </div>
                <div v-if="form.errors?.remember" class="text-sm text-red-700">{{ form.errors.remember }}</div>
            </div>
            <button ref="recaptcha_ref" type="submit" class="w-full bg-primary-500 text-white" :disabled="form.processing">
                <Processing v-if="form.processing" class="h-3 my-1.5 w-auto mx-auto text-white" />
                <template v-else>Login</template>
            </button>
        </form>
        <p class="mb-3">Not registered?
            <Link href="/auth/register" class="text-primary-400">Create an account</Link>
        </p>
        <p class="text-xs text-gray-400">
            Copyright &copy; Ram Arti Publishers,
            All rights reserved.
        </p>
    </Layout>
</template>

<style scoped lang="scss"></style>