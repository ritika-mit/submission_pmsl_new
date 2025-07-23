<script setup lang="ts">
const PREVIOUS = 'Previous';
const NEXT = 'Next';
const CHANGED = 'changed';

type Item = number | '...' | typeof PREVIOUS | typeof NEXT;

type Emits = {
    (e: typeof CHANGED, value: number): void;
}

type Props = {
    from: number;
    to: number;
    current: number;
    eachSide: number;
}

const emits = defineEmits<Emits>();

const props = defineProps<Props>();

function items() {
    const items: Item[] = [];

    const { from, to, current, eachSide } = props;

    items.push(PREVIOUS);

    if (to - from > 0) {

        items.push(from);

        if (to - from > 1) {
            items.push(current > from + 3 ? '...' : from + 1);

            for (let i = from + 2; i <= to - 2; i++) {
                if (i > current + eachSide || i < current - eachSide) {
                    continue;
                }

                items.push(i);
            }

            items.push(current < to - 3 ? '...' : to - 1);
        }

        items.push(to);
    }

    items.push(NEXT);

    return items;
}

function changed(item: Item) {
    const { current } = props;

    if (typeof item === 'number') {
        emits('changed', item);
    } else if (item === PREVIOUS) {
        emits('changed', current - 1);
    } else if (item === NEXT) {
        emits('changed', current + 1);
    }
}

function disabled(item: Item) {
    const { from, to, current } = props;

    return item === '...' || (item === PREVIOUS && current <= from) || (item === NEXT && current >= to);
}
</script>

<template>
    <ul class="inline-flex items-center -space-x-px">
        <li v-for="item in items()" :key="`page-${item}`">
            <a href="#" :class="{ active: item === current, disabled: disabled(item) }" @click.prevent="changed(item)">
                <template v-if="item === PREVIOUS">
                    <span class="sr-only">Previous</span>
                    <i class="bi bi-arrow-left"></i>
                </template>
                <template v-else-if="item === NEXT">
                    <span class="sr-only">Next</span>
                    <i class="bi bi-arrow-right"></i>
                </template>
                <template v-else>
                    {{ item }}
                </template>
            </a>
        </li>
    </ul>
</template>

<style scoped lang="scss">
nav ul li {
    a {
        @apply inline-block px-3 py-2 leading-tight text-gray-500 bg-white border border-gray-300 text-sm;

        &:hover,
        &.active {
            @apply bg-primary-600 border-primary-600 text-white;
        }

        &.disabled {
            @apply pointer-events-none;
        }
    }

    &:first-child {
        a {
            @apply rounded-l-lg;
        }
    }

    &:last-child {
        a {
            @apply rounded-r-lg;
        }
    }
}
</style>
