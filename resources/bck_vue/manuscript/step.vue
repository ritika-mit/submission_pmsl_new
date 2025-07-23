<script setup lang="ts">
import type { ManuscriptStatus } from '@/ts/types';

type Props = {
    basic?: boolean;
    reviewer?: boolean;
    author?: boolean;
    status?: ManuscriptStatus;
    active?: 'basic' | 'reviewer' | 'author' | 'submit'
}

defineProps<Props>();
</script>

<template>
    <div class="w-full overflow-x-auto">
        <ol class="flex items-center w-full font-medium text-center text-gray-500 mb-4 whitespace-nowrap">
            <li class="flex md:w-full items-center after:content-[''] after:w-full after:h-1 after:border-b after:border-gray-200 after:border-1 after:hidden sm:after:inline-block">
                <span class="flex items-center after:content-['/'] sm:after:hidden after:text-gray-200 pr-3" :class="{ 'text-primary-600': basic, 'text-primary-600 underline underline-offset-8': active === 'basic' }">
                    <svg v-if="basic" aria-hidden="true" class="w-4 h-4 mr-2 sm:w-5 sm:h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    Manuscript Details
                </span>
            </li>
            <li class="flex md:w-full items-center after:content-[''] after:w-full after:h-1 after:border-b after:border-gray-200 after:border-1 after:hidden sm:after:inline-block">
                <span class="flex items-center after:content-['/'] sm:after:hidden after:text-gray-200 px-3" :class="{ 'text-primary-600': reviewer, 'text-primary-600 underline underline-offset-8': active === 'reviewer' }">
                    <svg v-if="reviewer" aria-hidden="true" class="w-4 h-4 mr-2 sm:w-5 sm:h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    Reviewers Details
                </span>
            </li>
            <li class="flex md:w-full items-center after:content-[''] after:w-full after:h-1 after:border-b after:border-gray-200 after:border-1 after:hidden sm:after:inline-block">
                <span class="flex items-center after:content-['/'] sm:after:hidden after:text-gray-200 px-3" :class="{ 'text-primary-600': author, 'text-primary-600 underline underline-offset-8': active === 'author' }">
                    <svg v-if="author" aria-hidden="true" class="w-4 h-4 mr-2 sm:w-5 sm:h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    Co-authors Details
                </span>
            </li>
            <li>
                <span class="flex items-center after:content-['/'] sm:after:hidden after:text-gray-200 pl-3" :class="{ 'text-primary-600': status?.value !== 'pending', 'text-primary-600 underline underline-offset-8': active === 'submit' }">
                    <svg v-if="status?.value !== 'pending'" aria-hidden="true" class="w-4 h-4 mr-2 sm:w-5 sm:h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    {{ status?.value === 'pending' ? 'Review & Submit' : status?.label }}
                </span>
            </li>
        </ol>
    </div>
</template>

<style scoped lang="scss"></style>
