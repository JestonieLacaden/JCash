<template>
    <div>
        <!-- Pull to Refresh Indicator -->
        <div
            v-if="isPulling"
            class="fixed left-1/2 top-0 z-50 -translate-x-1/2 transform transition-all"
            :style="{
                top: pullDistance / 2 + 'px',
                opacity: pullDistance / pullThreshold,
            }"
        >
            <div class="rounded-full bg-white p-3 shadow-lg">
                <svg
                    class="h-6 w-6 text-indigo-600"
                    :class="{
                        'animate-spin': pullDistance >= pullThreshold,
                    }"
                    fill="none"
                    viewBox="0 0 24 24"
                >
                    <circle
                        class="opacity-25"
                        cx="12"
                        cy="12"
                        r="10"
                        stroke="currentColor"
                        stroke-width="4"
                    ></circle>
                    <path
                        class="opacity-75"
                        fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                    ></path>
                </svg>
            </div>
        </div>

        <!-- Slot for page content -->
        <slot></slot>
    </div>
</template>

<script setup>
import { onMounted, onUnmounted, ref } from 'vue';

const emit = defineEmits(['refresh']);

const isPulling = ref(false);
const pullDistance = ref(0);
const touchStartY = ref(0);
const pullThreshold = 80;

const handleTouchStart = (e) => {
    if (window.scrollY === 0) {
        touchStartY.value = e.touches[0].clientY;
    }
};

const handleTouchMove = (e) => {
    if (window.scrollY === 0) {
        const touchY = e.touches[0].clientY;
        const distance = touchY - touchStartY.value;

        if (distance > 0) {
            pullDistance.value = Math.min(distance, pullThreshold * 1.5);
            isPulling.value = true;

            // Prevent default scroll if pulling
            if (distance > 10) {
                e.preventDefault();
            }
        }
    }
};

const handleTouchEnd = () => {
    if (pullDistance.value >= pullThreshold) {
        emit('refresh');
    }
    isPulling.value = false;
    pullDistance.value = 0;
};

onMounted(() => {
    document.addEventListener('touchstart', handleTouchStart, {
        passive: true,
    });
    document.addEventListener('touchmove', handleTouchMove, { passive: false });
    document.addEventListener('touchend', handleTouchEnd, { passive: true });
});

onUnmounted(() => {
    document.removeEventListener('touchstart', handleTouchStart);
    document.removeEventListener('touchmove', handleTouchMove);
    document.removeEventListener('touchend', handleTouchEnd);
});
</script>
