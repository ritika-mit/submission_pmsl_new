<script setup lang="ts">
import Alert from "@/vue/components/alert.vue";
import Aside from "@/vue/components/aside.vue";
import Header from "@/vue/components/header.vue";
import { Head } from "@inertiajs/vue3";
import { ref } from "vue";

type Props = {
    title?: string;
};

defineProps<Props>();

const asideOpened = ref(false);
</script>

<template>
    <div>
        <Head :title="title" />
        <Header @aside-button-click="asideOpened = !asideOpened" />
        <Aside :aside-opened="asideOpened" />
        <main
            class="min-h-screen pt-28 md:pt-20 p-2 md:p-4 transition-[margin] md:ml-64"
            v-bind="$attrs"
        >
            <Alert />
            <slot />
            <p class="text-xs text-gray-400 px-2 text-center pt-3">
                Copyright &copy; Ram Arti Publishers,
                All rights reserved.
            </p>

        </main>
    </div>
</template>

<style scoped lang="scss">
aside {
    min-height: calc(100vh - 4rem);
    min-height: calc(100svh - 4rem);
}
</style>