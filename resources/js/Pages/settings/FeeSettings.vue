<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';
import Toast from '@/components/Toast.vue';
import { router, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    fee: Object,
    users: Array,
    gcashAccounts: Array,
});

const activeTab = ref('fees');
const showArchived = ref(false);

// Delete confirmation modal
const showDeleteModal = ref(false);
const accountToDelete = ref(null);

// Toast
const showToast = ref(false);
const toastMessage = ref('');
const toastVariant = ref('error');
let toastTimeout = null;

const showErrorToast = (message) => {
    toastMessage.value = message;
    toastVariant.value = 'error';
    showToast.value = true;
    if (toastTimeout) clearTimeout(toastTimeout);
    toastTimeout = setTimeout(() => {
        showToast.value = false;
    }, 4000);
};

const closeToast = () => {
    showToast.value = false;
    if (toastTimeout) clearTimeout(toastTimeout);
};

// Filter active and archived accounts
const activeAccounts = computed(() =>
    props.gcashAccounts.filter((acc) => acc.is_active),
);

const archivedAccounts = computed(() =>
    props.gcashAccounts.filter((acc) => !acc.is_active),
);

const feeForm = useForm({
    below_500_fee: props.fee?.below_500_fee || 0,
    five_hundred_to_999_fee: props.fee?.five_hundred_to_999_fee || 0,
    per_1000_fee: props.fee?.per_1000_fee || 0,
    discounted_per_1000_fee: props.fee?.discounted_per_1000_fee || 0,
});

// Watch for prop changes
watch(
    () => props.fee,
    (newFee) => {
        if (newFee) {
            feeForm.below_500_fee = newFee.below_500_fee || 0;
            feeForm.five_hundred_to_999_fee =
                newFee.five_hundred_to_999_fee || 0;
            feeForm.per_1000_fee = newFee.per_1000_fee || 0;
            feeForm.discounted_per_1000_fee =
                newFee.discounted_per_1000_fee || 0;
        }
    },
    { immediate: true },
);

const submitFees = () => feeForm.put(route('settings.fees.update'));

// GCash Account Form
const gcashForm = useForm({
    name: '',
});

const submitGcash = () => {
    gcashForm.post(route('settings.gcash.store'), {
        onSuccess: () => gcashForm.reset(),
    });
};

const deleteGcash = (accountId) => {
    accountToDelete.value = accountId;
    showDeleteModal.value = true;
};

const confirmDeleteGcash = () => {
    if (accountToDelete.value) {
        router.delete(route('settings.gcash.delete', accountToDelete.value), {
            onError: (errors) => {
                if (errors.delete) {
                    showErrorToast(errors.delete);
                }
            },
            onFinish: () => {
                showDeleteModal.value = false;
                accountToDelete.value = null;
            },
        });
    }
};

const cancelDelete = () => {
    showDeleteModal.value = false;
    accountToDelete.value = null;
};

const restoreGcash = (account) => {
    router.put(route('settings.gcash.update', account.id), {
        name: account.name,
        is_active: true,
    });
};

const deleteUser = (userId) => {
    if (confirm('Are you sure you want to delete this user?')) {
        router.delete(route('users.destroy', userId));
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <div class="mx-auto max-w-2xl p-4">
            <!-- Tabs -->
            <div class="mb-6 flex gap-2 border-b border-gray-200">
                <button
                    @click="activeTab = 'fees'"
                    class="px-4 py-2 font-medium transition-colors"
                    :class="
                        activeTab === 'fees'
                            ? 'border-b-2 border-indigo-600 text-indigo-600'
                            : 'text-gray-600 hover:text-gray-800'
                    "
                >
                    Fee Settings
                </button>
                <button
                    @click="activeTab = 'users'"
                    class="px-4 py-2 font-medium transition-colors"
                    :class="
                        activeTab === 'users'
                            ? 'border-b-2 border-indigo-600 text-indigo-600'
                            : 'text-gray-600 hover:text-gray-800'
                    "
                >
                    Users
                </button>
                <button
                    @click="activeTab = 'gcash'"
                    class="px-4 py-2 font-medium transition-colors"
                    :class="
                        activeTab === 'gcash'
                            ? 'border-b-2 border-indigo-600 text-indigo-600'
                            : 'text-gray-600 hover:text-gray-800'
                    "
                >
                    GCash Accounts
                </button>
            </div>

            <!-- Tab Content -->
            <div class="relative">
                <!-- Fee Settings Tab -->
                <div v-show="activeTab === 'fees'" class="space-y-4">
                    <h1 class="text-2xl font-bold text-gray-900">
                        Fee Settings
                    </h1>

                    <div class="space-y-4">
                        <!-- Below 500 Fee -->
                        <div>
                            <label
                                class="mb-1 block text-sm font-medium text-gray-700"
                            >
                                Below 500 fee
                            </label>
                            <input
                                v-model.number="feeForm.below_500_fee"
                                type="number"
                                step="0.01"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                placeholder="Enter fee for amounts below 500"
                            />
                            <p
                                v-if="feeForm.errors.below_500_fee"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ feeForm.errors.below_500_fee }}
                            </p>
                        </div>

                        <!-- 500-999 Fee -->
                        <div>
                            <label
                                class="mb-1 block text-sm font-medium text-gray-700"
                            >
                                500–999 fee
                            </label>
                            <input
                                v-model.number="feeForm.five_hundred_to_999_fee"
                                type="number"
                                step="0.01"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                placeholder="Enter fee for amounts 500-999"
                            />
                            <p
                                v-if="feeForm.errors.five_hundred_to_999_fee"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ feeForm.errors.five_hundred_to_999_fee }}
                            </p>
                        </div>

                        <!-- Per 1000 Fee -->
                        <div>
                            <label
                                class="mb-1 block text-sm font-medium text-gray-700"
                            >
                                Per 1000 fee
                            </label>
                            <input
                                v-model.number="feeForm.per_1000_fee"
                                type="number"
                                step="0.01"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                placeholder="Enter fee per 1000"
                            />
                            <p
                                v-if="feeForm.errors.per_1000_fee"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ feeForm.errors.per_1000_fee }}
                            </p>
                        </div>

                        <!-- Discount Per 1000 -->
                        <div>
                            <label
                                class="mb-1 block text-sm font-medium text-gray-700"
                            >
                                Discount per 1000
                            </label>
                            <input
                                v-model.number="feeForm.discounted_per_1000_fee"
                                type="number"
                                step="0.01"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                placeholder="Enter discounted fee per 1000"
                            />
                            <p
                                v-if="feeForm.errors.discounted_per_1000_fee"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ feeForm.errors.discounted_per_1000_fee }}
                            </p>
                        </div>
                    </div>

                    <!-- Full Width Save Button -->
                    <button
                        @click="submitFees"
                        :disabled="feeForm.processing"
                        class="w-full rounded-lg bg-indigo-600 py-3 font-medium text-white transition-colors hover:bg-indigo-700 disabled:opacity-50"
                    >
                        {{ feeForm.processing ? 'Saving...' : 'Save' }}
                    </button>
                </div>

                <!-- Users Tab -->
                <div v-show="activeTab === 'users'" class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h1 class="text-2xl font-bold text-gray-900">Users</h1>
                        <a
                            :href="route('users.create')"
                            class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                        >
                            Add User
                        </a>
                    </div>

                    <div class="space-y-2">
                        <div
                            v-for="user in users"
                            :key="user.id"
                            class="flex items-center justify-between rounded-lg border border-gray-200 bg-white p-4"
                        >
                            <div>
                                <p class="font-medium text-gray-900">
                                    {{ user.name }}
                                </p>
                                <p class="text-sm text-gray-600">
                                    {{ user.email }}
                                </p>
                                <span
                                    class="mt-1 inline-block rounded bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-800"
                                >
                                    {{ user.role }}
                                </span>
                            </div>
                            <div class="flex gap-2">
                                <a
                                    :href="route('users.edit', user.id)"
                                    class="rounded-lg bg-gray-100 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-200"
                                >
                                    Edit
                                </a>
                                <button
                                    @click="deleteUser(user.id)"
                                    class="rounded-lg bg-red-100 px-3 py-1.5 text-sm font-medium text-red-700 hover:bg-red-200"
                                >
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- GCash Accounts Tab -->
                <div v-show="activeTab === 'gcash'" class="space-y-4">
                    <h1 class="text-2xl font-bold text-gray-900">
                        GCash Accounts
                    </h1>

                    <!-- Add New GCash Account Form -->
                    <form @submit.prevent="submitGcash" class="flex gap-2">
                        <input
                            v-model="gcashForm.name"
                            type="text"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="New GCash account name"
                            required
                        />
                        <button
                            type="submit"
                            :disabled="gcashForm.processing"
                            class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
                        >
                            Add
                        </button>
                    </form>

                    <!-- Active Accounts -->
                    <div class="space-y-2">
                        <div
                            v-for="account in activeAccounts"
                            :key="account.id"
                            class="flex items-center justify-between rounded-lg border border-gray-200 bg-white p-4"
                        >
                            <div>
                                <p class="font-medium text-gray-900">
                                    {{ account.name }}
                                </p>
                                <p class="text-sm text-gray-600">
                                    Balance: ₱{{
                                        account.balance?.toLocaleString() || '0'
                                    }}
                                </p>
                            </div>
                            <div class="flex gap-2">
                                <form
                                    @submit.prevent="
                                        router.put(
                                            route(
                                                'settings.gcash.update',
                                                account.id,
                                            ),
                                            {
                                                name: account.name,
                                                is_active: false,
                                            },
                                        )
                                    "
                                >
                                    <button
                                        type="submit"
                                        class="rounded-lg bg-green-100 px-4 py-2 text-sm font-medium text-green-700 transition-colors hover:bg-green-200"
                                    >
                                        Active
                                    </button>
                                </form>
                                <button
                                    v-if="account.transactions_count === 0"
                                    @click="deleteGcash(account.id)"
                                    class="rounded-lg bg-red-100 px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-200"
                                >
                                    <svg
                                        class="h-4 w-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                        />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Show Archived Toggle -->
                    <div
                        v-if="archivedAccounts.length > 0"
                        class="flex items-center gap-2"
                    >
                        <input
                            v-model="showArchived"
                            type="checkbox"
                            id="showArchived"
                            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-2 focus:ring-indigo-500"
                        />
                        <label
                            for="showArchived"
                            class="text-sm font-medium text-gray-700"
                        >
                            Show Archived Accounts ({{
                                archivedAccounts.length
                            }})
                        </label>
                    </div>

                    <!-- Archived Accounts -->
                    <div
                        v-if="showArchived && archivedAccounts.length > 0"
                        class="space-y-2"
                    >
                        <h3
                            class="text-sm font-semibold uppercase tracking-wide text-gray-500"
                        >
                            Archived Accounts
                        </h3>
                        <div
                            v-for="account in archivedAccounts"
                            :key="account.id"
                            class="flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50 p-4"
                        >
                            <div>
                                <p class="font-medium text-gray-700">
                                    {{ account.name }}
                                </p>
                                <p class="text-sm text-gray-500">
                                    Balance: ₱{{
                                        account.balance?.toLocaleString() || '0'
                                    }}
                                </p>
                            </div>
                            <button
                                @click="restoreGcash(account)"
                                class="rounded-lg bg-indigo-100 px-4 py-2 text-sm font-medium text-indigo-700 transition-colors hover:bg-indigo-200"
                            >
                                Restore
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete GCash Account Modal -->
        <ConfirmModal
            :show="showDeleteModal"
            title="Delete GCash Account"
            message="Are you sure you want to delete this GCash account? This action cannot be undone."
            confirmText="Delete"
            cancelText="Cancel"
            variant="danger"
            @confirm="confirmDeleteGcash"
            @cancel="cancelDelete"
        />

        <!-- Toast Notification -->
        <Toast
            :show="showToast"
            :message="toastMessage"
            :variant="toastVariant"
            @close="closeToast"
        />
    </AuthenticatedLayout>
</template>
