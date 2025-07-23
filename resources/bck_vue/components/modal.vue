<script setup lang="ts">
import { onMounted, onUnmounted } from 'vue';

type Props = {
    title: string;
    size?: 'sm' | 'md' | 'lg' | 'xl' | 'xxl' | 'xxxl'
}

type Emits = {
    (e: 'close'): void;
}

withDefaults(defineProps<Props>(), { size: 'md' })

const emit = defineEmits<Emits>()

const close = () => emit('close');

const closeOnEscape = (e: KeyboardEvent) => {
    if (e.key === 'Escape') {
        close();
    }
}

onMounted(() => {
    document.body.style.overflow = 'hidden';
    document.addEventListener('keydown', closeOnEscape)
})

onUnmounted(() => {
    document.removeEventListener('keydown', closeOnEscape);
    document.body.style.overflow = '';
})
</script>

<template>
    <Teleport to="body">
        <Transition leave-active-class="duration-200">
            <div class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50" scroll-region>
                <Transition enter-active-class="ease-out duration-300" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="ease-in duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
                    <div class="fixed inset-0 transform transition-all" @click.prevent="close()">
                        <div class="absolute inset-0 bg-gray-800 opacity-75" />
                    </div>
                </Transition>
                <Transition enter-active-class="ease-out duration-300" enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" enter-to-class="opacity-100 translate-y-0 sm:scale-100" leave-active-class="ease-in duration-200" leave-from-class="opacity-100 translate-y-0 sm:scale-100" leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                    <div class="mb-6 bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full sm:mx-auto" :class="{ 'max-w-xl': size === 'sm', 'max-w-2xl': size === 'md', 'max-w-3xl': size === 'lg', 'max-w-4xl': size === 'xl', 'max-w-5xl': size === 'xxl', 'max-w-7xl': size === 'xxxl' }">
                        <div class="flex items-start px-4 py-3 border-b rounded-t">
                            <h3 class="text-xl font-semibold text-gray-900">{{ title }}</h3>
                            <button type="button" class="py-1 border-0 ml-auto" @click.prevent="close()">
                                <i class="bi bi-x-lg"></i>
                                <span class="sr-only">Close modal</span>
                            </button>
                        </div>
                        <slot />
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped lang="scss"></style>