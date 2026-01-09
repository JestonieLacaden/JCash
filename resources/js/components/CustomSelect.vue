<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';

const props = defineProps({
    modelValue: [String, Number],
    options: {
        type: Array,
        required: true,
        // Expected format: [{ value: 'key', label: 'Display Text' }]
    },
    placeholder: {
        type: String,
        default: 'Select an option',
    },
    label: String,
    disabled: Boolean,
});

const emit = defineEmits(['update:modelValue']);

const isOpen = ref(false);
const selectId = ref(`select-${Math.random().toString(36).substr(2, 9)}`);

const selectedOption = computed(() => {
    return props.options.find((opt) => opt.value === props.modelValue);
});

const displayText = computed(() => {
    return selectedOption.value?.label || props.placeholder;
});

const selectOption = (value) => {
    emit('update:modelValue', value);
    isOpen.value = false;
};

const toggleDropdown = (e) => {
    if (!props.disabled) {
        // Close other dropdowns when opening this one
        if (!isOpen.value) {
            window.dispatchEvent(
                new CustomEvent('close-all-selects', {
                    detail: { exceptId: selectId.value },
                }),
            );
        }
        isOpen.value = !isOpen.value;
        e.stopPropagation();
    }
};

// Close this dropdown when another opens
const handleCloseOthers = (e) => {
    if (e.detail?.exceptId !== selectId.value) {
        isOpen.value = false;
    }
};

// Close on click outside
const handleClickOutside = (e) => {
    if (!e.target.closest(`#${selectId.value}`)) {
        isOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    window.addEventListener('close-all-selects', handleCloseOthers);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
    window.removeEventListener('close-all-selects', handleCloseOthers);
});
</script>

<template>
    <div class="custom-select-container" :id="selectId">
        <label
            v-if="label"
            class="mb-2 block text-sm font-medium text-gray-700"
        >
            {{ label }}
        </label>
        <div class="relative">
            <button
                type="button"
                @click="toggleDropdown"
                :disabled="disabled"
                class="flex w-full items-center justify-between rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-left text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:cursor-not-allowed disabled:bg-gray-100"
                :class="{ 'text-gray-400': !selectedOption }"
            >
                <span>{{ displayText }}</span>
                <svg
                    class="h-4 w-4 transition-transform"
                    :class="{ 'rotate-180': isOpen }"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                >
                    <path
                        fill-rule="evenodd"
                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                        clip-rule="evenodd"
                    />
                </svg>
            </button>

            <transition
                enter-active-class="transition ease-out duration-100"
                enter-from-class="opacity-0 scale-95"
                enter-to-class="opacity-100 scale-100"
                leave-active-class="transition ease-in duration-75"
                leave-from-class="opacity-100 scale-100"
                leave-to-class="opacity-0 scale-95"
            >
                <div
                    v-if="isOpen"
                    class="absolute z-50 mt-2 max-h-60 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg"
                >
                    <button
                        v-for="option in options"
                        :key="option.value"
                        type="button"
                        @click="selectOption(option.value)"
                        class="block w-full px-4 py-2.5 text-left text-sm transition-colors hover:bg-gray-50"
                        :class="{
                            'bg-indigo-50 font-medium text-indigo-600':
                                option.value === modelValue,
                        }"
                    >
                        {{ option.label }}
                    </button>
                </div>
            </transition>
        </div>
    </div>
</template>

<style scoped>
.custom-select-container {
    position: relative;
}
</style>
