<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

// OPTION 1: Direct access from $page.props
const { props } = usePage();
const isReadOnly = computed(() => props.isReadOnly);

// OPTION 2: Using the composable (recommended)
// import { useAuth } from '@/composables/useAuth'
// const { isReadOnly, isAdmin, isStaff, isAuditor } = useAuth()
</script>

<template>
    <div>
        <!-- 🔒 Hide elements for auditors -->
        <div v-if="!isReadOnly" class="flex gap-2">
            <a :href="route('exports.transactions')" class="btn-green">
                Export CSV
            </a>
        </div>

        <!-- 🔒 Disable inputs for auditors -->
        <input :disabled="isReadOnly" placeholder="Amount" class="input" />

        <!-- 🔒 Disable buttons for auditors -->
        <button
            :disabled="isReadOnly"
            class="btn-primary disabled:cursor-not-allowed disabled:opacity-50"
        >
            Save Changes
        </button>

        <!-- 📝 Show read-only badge for auditors -->
        <span
            v-if="isReadOnly"
            class="rounded bg-yellow-100 px-2 py-1 text-xs text-yellow-800"
        >
            📖 READ-ONLY MODE
        </span>
    </div>
</template>
