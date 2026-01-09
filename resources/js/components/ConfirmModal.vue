<template>
    <teleport to="body">
        <transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
                @click="$emit('cancel')"
            >
                <div
                    @click.stop
                    class="mx-4 w-full max-w-sm rounded-lg bg-white p-6 shadow-xl"
                >
                    <h3 class="mb-2 text-lg font-semibold text-gray-900">
                        {{ title }}
                    </h3>
                    <p class="mb-6 text-sm text-gray-600">
                        {{ message }}
                    </p>
                    <div class="flex gap-3">
                        <button
                            @click="$emit('cancel')"
                            :class="cancelButtonClass"
                        >
                            {{ cancelText }}
                        </button>
                        <button
                            @click="$emit('confirm')"
                            :class="confirmButtonClass"
                        >
                            {{ confirmText }}
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </teleport>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        required: true,
    },
    message: {
        type: String,
        required: true,
    },
    confirmText: {
        type: String,
        default: 'Confirm',
    },
    cancelText: {
        type: String,
        default: 'Cancel',
    },
    variant: {
        type: String,
        default: 'danger', // 'danger' or 'primary'
    },
});

defineEmits(['confirm', 'cancel']);

const cancelButtonClass =
    'flex-1 rounded-lg border-2 border-gray-300 bg-white py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50';

const confirmButtonClass = computed(() => {
    if (props.variant === 'danger') {
        return 'flex-1 rounded-lg bg-red-600 py-2.5 text-sm font-medium text-white hover:bg-red-700';
    }
    return 'flex-1 rounded-lg bg-indigo-600 py-2.5 text-sm font-medium text-white hover:bg-indigo-700';
});
</script>
