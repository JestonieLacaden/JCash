<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';
import CustomSelect from '@/components/CustomSelect.vue';
import Toast from '@/components/Toast.vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

const { props } = usePage();
const feeSettings = props.feeSettings;

// Dropdown options
const typeOptions = [
    { value: 'cash_out', label: 'Cash Out' },
    { value: 'cash_in', label: 'Cash In' },
];

const accountOptions = computed(() => {
    const options = [{ value: 'cash', label: 'Cash on Hand' }];
    props.gcashAccounts.forEach((acc) => {
        options.push({
            value: acc.id,
            label: `GCash - ${acc.name}`,
        });
    });
    return options;
});

const gcashAccountOptions = computed(() => {
    return props.gcashAccounts.map((acc) => ({
        value: acc.id,
        label: `${acc.name} (₱${acc.balance})`,
    }));
});

const form = useForm({
    type: 'cash_out',
    gcash_account_id: null,
    from: '',
    to: '',
    amount: '',
    fee: 0,
    discounted: false,
    receiver_name: '',
    reference: '',
    remarks: '',
});

// Track if form has unsaved changes
const showCancelWarning = ref(false);
const pendingNavigation = ref(null);
const isConfirmingNavigation = ref(false);
const isSubmitting = ref(false);

// Toast notification
const showToast = ref(false);
const toastMessage = ref('');
const toastVariant = ref('error');
let toastTimeout = null;

const showErrorToast = (message) => {
    toastMessage.value = message;
    toastVariant.value = 'error';
    showToast.value = true;

    // Auto-hide after 4 seconds
    if (toastTimeout) clearTimeout(toastTimeout);
    toastTimeout = setTimeout(() => {
        showToast.value = false;
    }, 4000);
};

const closeToast = () => {
    showToast.value = false;
    if (toastTimeout) clearTimeout(toastTimeout);
};

const hasUnsavedChanges = computed(() => {
    return (
        form.amount !== '' ||
        form.receiver_name !== '' ||
        form.reference !== '' ||
        form.remarks !== '' ||
        form.gcash_account_id !== null ||
        (form.type === 'capital_move' && (form.from !== '' || form.to !== ''))
    );
});

// Cancel and reset form
const cancelForm = () => {
    if (hasUnsavedChanges.value) {
        showCancelWarning.value = true;
        pendingNavigation.value = route('dashboard');
    } else {
        router.visit(route('dashboard'));
    }
};

const confirmCancel = () => {
    showCancelWarning.value = false;
    isConfirmingNavigation.value = true;

    if (pendingNavigation.value) {
        router.visit(pendingNavigation.value);
        pendingNavigation.value = null;
    }
};

const cancelWarningDialog = () => {
    showCancelWarning.value = false;
    pendingNavigation.value = null;
};

// Track if navigation is happening via Inertia
const isInertiaNavigating = ref(false);

// Intercept navigation
const handleBeforeUnload = (e) => {
    // Only show browser warning if NOT navigating via Inertia and NOT confirming navigation
    if (
        hasUnsavedChanges.value &&
        !isInertiaNavigating.value &&
        !isConfirmingNavigation.value
    ) {
        e.preventDefault();
        e.returnValue = '';
    }
};

let removeInertiaListener = null;

onMounted(() => {
    // Set initial type from URL
    const typeFromUrl = new URLSearchParams(window.location.search).get('type');
    if (typeFromUrl) {
        form.type = typeFromUrl;
    }

    window.addEventListener('beforeunload', handleBeforeUnload);

    // Intercept Inertia navigation (in-app links)
    removeInertiaListener = router.on('before', (event) => {
        if (
            hasUnsavedChanges.value &&
            !isConfirmingNavigation.value &&
            !isSubmitting.value &&
            !event.detail.visit.url.href.includes('/transactions/create')
        ) {
            isInertiaNavigating.value = true;
            event.preventDefault();
            showCancelWarning.value = true;
            pendingNavigation.value = event.detail.visit.url.href;

            // Reset flag after a short delay
            setTimeout(() => {
                isInertiaNavigating.value = false;
            }, 100);
        }
    });
});

onUnmounted(() => {
    window.removeEventListener('beforeunload', handleBeforeUnload);
    if (removeInertiaListener) {
        removeInertiaListener();
    }
});

// AUTO FEE WATCHER
watch(
    () => [form.amount, form.discounted],
    () => {
        const amount = Number(form.amount);
        if (!amount || amount <= 0) {
            form.fee = 0;
            return;
        }

        if (amount < 500) {
            form.fee = feeSettings.below_500_fee;
        } else if (amount < 1000) {
            form.fee = feeSettings.five_hundred_to_999_fee;
        } else {
            const perThousand = form.discounted
                ? feeSettings.discounted_per_1000_fee
                : feeSettings.per_1000_fee;

            const thousands = Math.ceil(amount / 1000);
            form.fee = thousands * perThousand;
        }
    },
);

const submit = () => {
    // Validate required fields in UI order
    if (!form.gcash_account_id) {
        showErrorToast('Please select a GCash Account');
        return;
    }

    if (!form.amount || form.amount <= 0) {
        showErrorToast('Please enter a valid amount');
        return;
    }

    isSubmitting.value = true;
    form.post('/transactions', {
        onError: () => {
            isSubmitting.value = false;
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};
</script>

<template>
    <Head title="New Transaction" />

    <AuthenticatedLayout>
        <div class="bg-gray-100 pb-24">
            <div class="mx-auto max-w-md space-y-4 p-4">
                <h1 class="text-xl font-bold">New Transaction</h1>

                <!-- Type -->
                <CustomSelect
                    v-model="form.type"
                    :options="typeOptions"
                    label="Type"
                    placeholder="Select transaction type"
                />

                <!-- GCash Account -->
                <CustomSelect
                    v-model="form.gcash_account_id"
                    :options="gcashAccountOptions"
                    label="GCash Account"
                    placeholder="Select account"
                />

                <!--Reciever / Customer Name-->
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">
                        Customer Name (optional)
                    </label>
                    <input
                        type="text"
                        v-model="form.receiver_name"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Juan Dela Cruz"
                    />
                </div>
                <!-- Amount -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700"
                        >Amount</label
                    >
                    <input
                        type="number"
                        v-model="form.amount"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Enter amount"
                    />
                </div>
                <!-- Fee -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700"
                        >Fee</label
                    >
                    <input
                        type="number"
                        v-model="form.fee"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                </div>
                <!-- Discounted -->
                <div
                    v-if="form.type !== 'capital_move'"
                    class="flex items-center gap-2"
                >
                    <input
                        type="checkbox"
                        v-model="form.discounted"
                        id="discounted"
                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-2 focus:ring-indigo-500"
                    />
                    <label
                        for="discounted"
                        class="text-sm font-medium text-gray-700"
                        >Apply Discounted Fee</label
                    >
                </div>
                <!-- Reference -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700"
                        >Reference (optional)</label
                    >
                    <input
                        type="text"
                        v-model="form.reference"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                </div>

                <!-- Remarks -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700"
                        >Remarks</label
                    >
                    <textarea
                        v-model="form.remarks"
                        rows="3"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    ></textarea>
                </div>

                <!-- Submit -->
                <div class="flex gap-3">
                    <button
                        @click="cancelForm"
                        class="flex-1 rounded-lg border-2 border-gray-300 bg-white py-3 font-medium text-gray-700 hover:bg-gray-50"
                    >
                        Cancel
                    </button>
                    <button
                        @click="submit"
                        class="flex-1 rounded-lg bg-indigo-600 py-3 font-medium text-white hover:bg-indigo-700"
                        :disabled="form.processing"
                    >
                        Save Transaction
                    </button>
                </div>
            </div>
        </div>

        <!-- Cancel Warning Modal -->
        <ConfirmModal
            :show="showCancelWarning"
            title="Discard Changes?"
            message="You have unsaved changes. All input data will be lost if you proceed."
            confirm-text="Yes, Discard"
            cancel-text="No, Continue Editing"
            variant="danger"
            @confirm="confirmCancel"
            @cancel="cancelWarningDialog"
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
