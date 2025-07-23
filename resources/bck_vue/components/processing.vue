<script setup lang="ts"></script>

<template>
    <svg width="106" height="26" viewBox="0 0 106 26" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M13 26C20.1797 26 26 20.1797 26 13C26 5.8203 20.1797 0 13 0C5.8203 0 0 5.8203 0 13C0 20.1797 5.8203 26 13 26Z" />
        <path d="M53 26C60.1797 26 66 20.1797 66 13C66 5.8203 60.1797 0 53 0C45.8203 0 40 5.8203 40 13C40 20.1797 45.8203 26 53 26Z" />
        <path d="M93 26C100.18 26 106 20.1797 106 13C106 5.8203 100.18 0 93 0C85.8203 0 80 5.8203 80 13C80 20.1797 85.8203 26 93 26Z" />
    </svg>
</template>

<style scoped lang="scss">
svg path {
    fill: currentColor;

    animation: load 1s infinite;

    &:nth-child(2) {
        animation-delay: 0.2s;
    }

    &:nth-child(3) {
        animation-delay: 0.4s;
    }
}

@keyframes load {
    0% {
        opacity: 0;
    }

    50% {
        opacity: 1;
    }

    100% {
        opacity: 0;
    }
}
</style>