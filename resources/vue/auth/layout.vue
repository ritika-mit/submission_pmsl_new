<script setup lang="ts">
import { Head } from "@inertiajs/vue3";

type Props = {
    title: string;
};

defineProps<Props>();
</script>

<template>
    <div>
        <Head :title="title" />
        <div class="md:flex min-h-screen">
            <div
                class="px-4 py-8 md:w-5/12 flex items-center justify-center flex-col text-white bg-[linear-gradient(to_bottom,rgba(0,0,0,0.75),rgba(3,31,58,0.75)),url('../image/book-library.webp'),url('../image/book-library.png')] bg-no-repeat bg-center bg-cover"
            >
                <h1
                    class="text-6xl md:text-8xl md:tracking-widest uppercase font-bold mb-3"
                >
                    pmsl
                </h1>
                <h2
                    class="text-2xl tracking-[9px] md:text-4xl md:tracking-[18px] uppercase font-thin"
                >
                    submission
                </h2>
            </div>
            <div
                class="md:w-7/12 flex items-center justify-center flex-col"
                v-bind="$attrs"
            >
                <slot />
            </div>
        </div>
    </div>
</template>

<style scoped lang="scss"></style>
