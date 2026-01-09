<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CustomSelect from '@/components/CustomSelect.vue';
import Toast from '@/components/Toast.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    gcashAccounts: Array,
});

const activeTab = ref('adjustment');

// Dropdown options
const targetOptions = [
    { value: 'cash', label: 'Cash on Hand' },
    { value: 'gcash', label: 'GCash Account' },
];

const directionOptions = [
    { value: 'add', label: 'Add Balance' },
    { value: 'deduct', label: 'Deduct Balance' },
];

const gcashAccountOptionsAdjustment = computed(() => {
    return props.gcashAccounts.map((acc) => ({
        value: acc.id,
        label: `${acc.name} (₱${acc.balance.toLocaleString()})`,
    }));
});

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

// Balance Adjustment Form
const adjustmentForm = useForm({
    target: 'cash',
    direction: 'add',
    amount: '',
    gcash_account_id: null,
    remarks: '',
});

const submitAdjustment = () => {
    if (!adjustmentForm.amount || adjustmentForm.amount <= 0) {
        showErrorToast('Please enter a valid amount');
        return;
    }
    if (adjustmentForm.target === 'gcash' && !adjustmentForm.gcash_account_id) {
        showErrorToast('Please select a GCash Account');
        return;
    }
    if (!adjustmentForm.remarks) {
        showErrorToast('Please enter remarks');
        return;
    }
    adjustmentForm.post(route('transactions.adjustment.store'));
};

// Capital Movement Form
const capitalForm = useForm({
    type: 'capital_move',
    from: '',
    to: '',
    amount: '',
    reference: '',
    remarks: '',
});

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

const submitCapitalMove = () => {
    if (!capitalForm.from) {
        showErrorToast('Please select From account');
        return;
    }
    if (!capitalForm.to) {
        showErrorToast('Please select To account');
        return;
    }
    if (capitalForm.from === capitalForm.to) {
        showErrorToast('From and To cannot be the same');
        return;
    }
    if (!capitalForm.amount || capitalForm.amount <= 0) {
        showErrorToast('Please enter a valid amount');
        return;
    }
    capitalForm.post('/transactions');
};
</script>

<template>
    <Head title="Funds" />

    <AuthenticatedLayout>
        <div class="bg-gray-100 pb-24">
            <div class="mx-auto max-w-md p-4">
                <h1 class="mb-4 text-xl font-bold">Funds Management</h1>

                <!-- Tabs -->
                <div class="mb-6 flex gap-2 border-b border-gray-200">
                    <button
                        @click="activeTab = 'adjustment'"
                        class="px-4 py-2 font-medium transition-colors"
                        :class="
                            activeTab === 'adjustment'
                                ? 'border-b-2 border-indigo-600 text-indigo-600'
                                : 'text-gray-500'
                        "
                    >
                        Balance Adjustment
                    </button>
                    <button
                        @click="activeTab = 'capital'"
                        class="px-4 py-2 font-medium transition-colors"
                        :class="
                            activeTab === 'capital'
                                ? 'border-b-2 border-indigo-600 text-indigo-600'
                                : 'text-gray-500'
                        "
                    >
                        Capital Movement
                    </button>
                </div>

                <!-- Balance Adjustment Tab -->
                <div v-if="activeTab === 'adjustment'" class="space-y-4">
                    <!-- Target -->
                    <CustomSelect
                        v-model="adjustmentForm.target"
                        :options="targetOptions"
                        label="Target"
                        placeholder="Select target"
                    />

                    <!-- GCash Account -->
                    <CustomSelect
                        v-if="adjustmentForm.target === 'gcash'"
                        v-model="adjustmentForm.gcash_account_id"
                        :options="gcashAccountOptionsAdjustment"
                        label="GCash Account"
                        placeholder="Select account"
                    />

                    <!-- Direction -->
                    <CustomSelect
                        v-model="adjustmentForm.direction"
                        :options="directionOptions"
                        label="Adjustment Type"
                        placeholder="Select type"
                    />

                    <!-- Amount -->
                    <div>
                        <label
                            class="mb-2 block text-sm font-medium text-gray-700"
                            >Amount</label
                        >
                        <input
                            v-model="adjustmentForm.amount"
                            type="number"
                            min="1"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="Enter amount"
                        />
                    </div>

                    <!-- Remarks -->
                    <div>
                        <label
                            class="mb-2 block text-sm font-medium text-gray-700"
                            >Remarks</label
                        >
                        <textarea
                            v-model="adjustmentForm.remarks"
                            rows="3"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="Reason / notes (required)"
                        ></textarea>
                    </div>

                    <!-- Submit -->
                    <button
                        @click="submitAdjustment"
                        :disabled="adjustmentForm.processing"
                        class="w-full rounded-lg bg-indigo-600 py-3 font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
                    >
                        Save Adjustment
                    </button>
                </div>

                <!-- Capital Movement Tab -->
                <div v-if="activeTab === 'capital'" class="space-y-4">
                    <CustomSelect
                        v-model="capitalForm.from"
                        :options="accountOptions"
                        label="From"
                        placeholder="Select source"
                    />

                    <CustomSelect
                        v-model="capitalForm.to"
                        :options="accountOptions"
                        label="To"
                        placeholder="Select destination"
                    />

                    <!-- Amount -->
                    <div>
                        <label
                            class="mb-2 block text-sm font-medium text-gray-700"
                            >Amount</label
                        >
                        <input
                            type="number"
                            v-model="capitalForm.amount"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="Enter amount"
                        />
                    </div>

                    <!-- Reference -->
                    <div>
                        <label
                            class="mb-2 block text-sm font-medium text-gray-700"
                            >Reference (optional)</label
                        >
                        <input
                            type="text"
                            v-model="capitalForm.reference"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="Transaction reference"
                        />
                    </div>

                    <!-- Remarks -->
                    <div>
                        <label
                            class="mb-2 block text-sm font-medium text-gray-700"
                            >Remarks (optional)</label
                        >
                        <textarea
                            v-model="capitalForm.remarks"
                            rows="3"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="Additional notes"
                        ></textarea>
                    </div>

                    <!-- Submit -->
                    <button
                        @click="submitCapitalMove"
                        :disabled="capitalForm.processing"
                        class="w-full rounded-lg bg-indigo-600 py-3 font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
                    >
                        Move Capital
                    </button>
                </div>
            </div>
        </div>

        <!-- Toast Notification -->
        <Toast
            :show="showToast"
            :message="toastMessage"
            :variant="toastVariant"
            @close="closeToast"
        />
    </AuthenticatedLayout>
</template>
