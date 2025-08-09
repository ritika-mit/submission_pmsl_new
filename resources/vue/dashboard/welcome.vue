<script setup lang="ts">
import Layout from '@/vue/layout.vue';

type Props = {
    username: string
}

defineProps<Props>()

</script>

<template>
    <Layout title="Home">
<div
    class="w-full h-full flex items-center justify-center bg-cover bg-center"
    style="background-image: url('resources\image\book-library.png');"
  >
    <div class="flex flex-col items-center justify-center w-full h-full bg-white/80 p-10">
      <h1 class="text-5xl font-bold mb-8 text-center">
        Welcome, {{ username }}, to the Submission Portal
      </h1>
      <p class="text-2xl text-gray-600 text-center max-w-2xl">
        We’re glad to have you here. As an author or reviewer, you can submit manuscripts, track statuses, and manage reviews—all from a single, unified platform.
      </p>
    </div>
  </div>
    </Layout>
</template>

<style scoped lang="scss"></style>
