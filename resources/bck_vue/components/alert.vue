<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';

const page = usePage();
</script>

<template>
    <div v-if="page.props.flash?.type && page.props.flash?.message" class="flex p-4 mb-4 text-sm rounded-lg" :class="{ 'text-red-800 bg-red-100 border border-red-200': page.props.flash.type === 'error', 'text-green-800 bg-green-100 border border-green-200': page.props.flash.type === 'success' }" role="alert">
        <i class="bi bi-info-circle-fill text-md mr-3"></i>
        <div>
            <span class="font-medium">{{ page.props.flash.type.toLocaleUpperCase() }}!</span> {{ page.props.flash.message }}
        </div>
    </div>
</template>

<style scoped lang="scss"></style>