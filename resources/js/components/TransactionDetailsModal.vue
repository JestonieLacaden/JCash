<template>
    <teleport to="body">
        <transition
            enter-active-class="transition-opacity duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-300"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show"
                @click="$emit('close')"
                class="fixed inset-0 z-50 flex items-end justify-center bg-black bg-opacity-50 sm:items-center"
            >
                <transition
                    enter-active-class="transition-transform duration-300"
                    enter-from-class="translate-y-full sm:translate-y-0 sm:scale-95"
                    enter-to-class="translate-y-0 sm:scale-100"
                    leave-active-class="transition-transform duration-300"
                    leave-from-class="translate-y-0 sm:scale-100"
                    leave-to-class="translate-y-full sm:translate-y-0 sm:scale-95"
                >
                    <div
                        v-if="show"
                        @click.stop
                        class="w-full max-w-lg rounded-t-2xl bg-white shadow-2xl sm:rounded-2xl"
                    >
                        <!-- Header -->
                        <div
                            class="border-b border-gray-200 px-6 py-4 sm:px-8 sm:py-5"
                        >
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="rounded-full px-3 py-1 text-sm font-medium"
                                            :class="
                                                getTypeBadgeClass(
                                                    transaction.type,
                                                )
                                            "
                                        >
                                            {{ getTypeLabel(transaction.type) }}
                                        </span>
                                        <span
                                            v-if="
                                                transaction.discounted &&
                                                isCashTransaction
                                            "
                                            class="rounded-full bg-purple-100 px-2 py-0.5 text-xs font-medium text-purple-700"
                                        >
                                            Discounted
                                        </span>
                                    </div>
                                    <p class="mt-2 text-sm text-gray-500">
                                        {{
                                            formatDateTime(
                                                transaction.created_at,
                                            )
                                        }}
                                    </p>
                                </div>
                                <button
                                    @click="$emit('close')"
                                    class="rounded-full p-2 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600"
                                >
                                    <svg
                                        class="h-5 w-5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"
                                        />
                                    </svg>
                                </button>
                            </div>

                            <!-- Amount Display -->
                            <div
                                class="mt-4 rounded-lg p-4"
                                :class="getAmountBgClass(transaction.type)"
                            >
                                <div
                                    class="flex items-baseline justify-between"
                                >
                                    <span
                                        class="text-sm font-medium"
                                        :class="
                                            getAmountLabelClass(
                                                transaction.type,
                                            )
                                        "
                                    >
                                        Amount
                                    </span>
                                    <span
                                        class="text-3xl font-bold"
                                        :class="
                                            getAmountTextClass(transaction.type)
                                        "
                                    >
                                        ₱{{
                                            transaction.amount.toLocaleString()
                                        }}
                                    </span>
                                </div>
                                <div
                                    v-if="transaction.fee && isCashTransaction"
                                    class="mt-2 flex items-center justify-between border-t pt-2"
                                    :class="getFeeBorderClass(transaction.type)"
                                >
                                    <span
                                        class="text-xs font-medium"
                                        :class="
                                            getAmountLabelClass(
                                                transaction.type,
                                            )
                                        "
                                    >
                                        Fee
                                    </span>
                                    <span
                                        class="text-lg font-semibold text-green-600"
                                    >
                                        ₱{{ transaction.fee.toLocaleString() }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Details Section -->
                        <div
                            class="max-h-[50vh] overflow-y-auto px-6 py-4 sm:px-8"
                        >
                            <!-- Cash In/Out Details -->
                            <div v-if="isCashTransaction" class="space-y-4">
                                <!-- GCash Account with Previous Balance -->
                                <div class="flex gap-3">
                                    <div class="flex-shrink-0 mt-1">
                                        <div class="h-8 w-8 rounded-full bg-gray-100 flex items-center justify-center">
                                            <svg class="h-4 w-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-medium text-gray-500">GCash Account</p>
                                        <p class="mt-1 font-medium text-gray-900">
                                            {{ transaction.gcash_account?.name || 'N/A' }}
                                            <span v-if="transaction.previous_balance !== null" class="text-sm text-gray-400">
                                                (₱{{ transaction.previous_balance.toLocaleString() }})
                                                <span :class="transaction.type === 'cash_in' ? 'text-red-500' : 'text-green-500'">
                                                    {{ transaction.type === 'cash_in' ? '-' : '+' }}
                                                    ₱{{ transaction.amount.toLocaleString() }}
                                                </span>
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                
                                <DetailRow
                                    v-if="transaction.receiver_name"
                                    label="Customer Name"
                                    :value="transaction.receiver_name"
                                    icon="user"
                                />
                                <DetailRow
                                    v-if="transaction.reference"
                                    label="Reference"
                                    :value="transaction.reference"
                                    icon="hashtag"
                                />
                                <DetailRow
                                    v-if="transaction.remarks"
                                    label="Remarks"
                                    :value="transaction.remarks"
                                    icon="document-text"
                                    multiline
                                />
                            </div>

                            <!-- Adjustment Details -->
                            <div
                                v-else-if="transaction.type === 'adjustment'"
                                class="space-y-4"
                            >
                                <DetailRow
                                    label="Target"
                                    :value="getAdjustmentTarget()"
                                    icon="target"
                                />
                                <DetailRow
                                    label="Direction"
                                    :value="getAdjustmentDirection()"
                                    icon="arrows-expand"
                                />
                                <DetailRow
                                    v-if="transaction.remarks"
                                    label="Remarks"
                                    :value="transaction.remarks"
                                    icon="document-text"
                                    multiline
                                />
                            </div>

                            <!-- Capital Movement Details -->
                            <div
                                v-else-if="transaction.type === 'capital_move'"
                                class="space-y-4"
                            >
                                <!-- From -->
                                <div
                                    class="rounded-lg border border-red-200 bg-red-50 p-3"
                                >
                                    <div class="flex items-center gap-2">
                                        <svg
                                            class="h-5 w-5 text-red-600"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M17 8l4 4m0 0l-4 4m4-4H3"
                                            />
                                        </svg>
                                        <span
                                            class="text-xs font-medium text-red-700"
                                            >From</span
                                        >
                                    </div>
                                    <p class="mt-1 font-semibold text-red-900">
                                        {{ getCapitalMoveAccount('from') }}
                                    </p>
                                </div>

                                <!-- To -->
                                <div
                                    class="rounded-lg border border-green-200 bg-green-50 p-3"
                                >
                                    <div class="flex items-center gap-2">
                                        <svg
                                            class="h-5 w-5 text-green-600"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M7 16l-4-4m0 0l4-4m-4 4h18"
                                            />
                                        </svg>
                                        <span
                                            class="text-xs font-medium text-green-700"
                                            >To</span
                                        >
                                    </div>
                                    <p
                                        class="mt-1 font-semibold text-green-900"
                                    >
                                        {{ getCapitalMoveAccount('to') }}
                                    </p>
                                </div>

                                <DetailRow
                                    v-if="transaction.reference"
                                    label="Reference"
                                    :value="transaction.reference"
                                    icon="hashtag"
                                />
                                <DetailRow
                                    v-if="transaction.remarks"
                                    label="Remarks"
                                    :value="transaction.remarks"
                                    icon="document-text"
                                    multiline
                                />
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="border-t border-gray-200 px-6 py-4 sm:px-8">
                            <button
                                @click="$emit('close')"
                                class="w-full rounded-lg bg-gray-100 px-4 py-3 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-200"
                            >
                                Close
                            </button>
                        </div>
                    </div>
                </transition>
            </div>
        </transition>
    </teleport>
</template>

<script setup>
import { computed, h, watch } from 'vue';

// DetailRow component using h() render function
const DetailRow = (props) => {
    const getIcon = () => {
        const iconProps = {
            class: 'h-4 w-4 text-gray-600',
            fill: 'none',
            stroke: 'currentColor',
            viewBox: '0 0 24 24',
        };

        const paths = {
            user: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
            'credit-card':
                'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
            hashtag: 'M7 20l4-16m2 16l4-16M6 9h14M4 15h14',
            'document-text':
                'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
            target: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
            'arrows-expand':
                'M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4',
        };

        const path = paths[props.icon];
        if (!path) return null;

        return h(
            'svg',
            iconProps,
            h('path', {
                'stroke-linecap': 'round',
                'stroke-linejoin': 'round',
                'stroke-width': '2',
                d: path,
            }),
        );
    };

    return h('div', { class: 'flex gap-3' }, [
        h('div', { class: 'flex-shrink-0 mt-1' }, [
            h(
                'div',
                {
                    class: 'h-8 w-8 rounded-full bg-gray-100 flex items-center justify-center',
                },
                [getIcon()],
            ),
        ]),
        h('div', { class: 'flex-1 min-w-0' }, [
            h('p', { class: 'text-xs font-medium text-gray-500' }, props.label),
            h(
                'p',
                {
                    class: [
                        'mt-1 font-medium text-gray-900',
                        props.multiline ? 'whitespace-pre-wrap' : '',
                    ],
                },
                props.value,
            ),
        ]),
    ]);
};

DetailRow.props = {
    label: String,
    value: String,
    icon: String,
    multiline: Boolean,
};

const props = defineProps({
    show: Boolean,
    transaction: {
        type: Object,
        default: () => ({}),
    },
    gcashAccounts: {
        type: Array,
        default: () => [],
    },
});

defineEmits(['close']);

// Prevent body scroll when modal is open
watch(
    () => props.show,
    (newVal) => {
        if (newVal) {
            // Lock body scroll
            document.body.style.overflow = 'hidden';
        } else {
            // Unlock body scroll
            document.body.style.overflow = '';
        }
    },
);

const isCashTransaction = computed(() => {
    return ['cash_in', 'cash_out'].includes(props.transaction.type);
});

const formatDateTime = (date) => {
    if (!date) return 'N/A';
    return new Intl.DateTimeFormat('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        hour12: true,
    }).format(new Date(date));
};

const getTypeLabel = (type) => {
    const labels = {
        cash_in: 'Cash In',
        cash_out: 'Cash Out',
        adjustment: 'Adjustment',
        capital_move: 'Capital Move',
    };
    return labels[type] || type;
};

const getTypeBadgeClass = (type) => {
    const classes = {
        cash_in: 'bg-green-100 text-green-700',
        cash_out: 'bg-red-100 text-red-700',
        adjustment: 'bg-blue-100 text-blue-700',
        capital_move: 'bg-purple-100 text-purple-700',
    };
    return classes[type] || 'bg-gray-100 text-gray-700';
};

const getAmountBgClass = (type) => {
    const classes = {
        cash_in: 'bg-green-50 border border-green-200',
        cash_out: 'bg-red-50 border border-red-200',
        adjustment: 'bg-blue-50 border border-blue-200',
        capital_move: 'bg-purple-50 border border-purple-200',
    };
    return classes[type] || 'bg-gray-50 border border-gray-200';
};

const getAmountLabelClass = (type) => {
    const classes = {
        cash_in: 'text-green-700',
        cash_out: 'text-red-700',
        adjustment: 'text-blue-700',
        capital_move: 'text-purple-700',
    };
    return classes[type] || 'text-gray-700';
};

const getAmountTextClass = (type) => {
    const classes = {
        cash_in: 'text-green-900',
        cash_out: 'text-red-900',
        adjustment: 'text-blue-900',
        capital_move: 'text-purple-900',
    };
    return classes[type] || 'text-gray-900';
};

const getFeeBorderClass = (type) => {
    const classes = {
        cash_in: 'border-green-200',
        cash_out: 'border-red-200',
    };
    return classes[type] || 'border-gray-200';
};

const getAdjustmentTarget = () => {
    if (props.transaction.gcash_account) {
        return `GCash: ${props.transaction.gcash_account.name}`;
    }
    return 'Cash on Hand';
};

const getAdjustmentDirection = () => {
    if (
        props.transaction.remarks?.toLowerCase().includes('add') ||
        props.transaction.remarks?.toLowerCase().includes('increase')
    ) {
        return 'Added/Increased';
    }
    if (
        props.transaction.remarks?.toLowerCase().includes('deduct') ||
        props.transaction.remarks?.toLowerCase().includes('decrease')
    ) {
        return 'Deducted/Decreased';
    }
    return 'Adjusted';
};

const getCapitalMoveAccount = (direction) => {
    const t = props.transaction;

    if (direction === 'from') {
        if (t.from_account_id) {
            // Find account in gcashAccounts array
            const account = props.gcashAccounts.find(
                (acc) => acc.id === t.from_account_id,
            );
            if (account) {
                return account.name;
            }
            // Fallback
            return (
                t.from_account?.name ||
                t.fromAccount?.name ||
                `GCash Account (ID: ${t.from_account_id})`
            );
        }
        return 'Cash on Hand';
    } else {
        if (t.to_account_id) {
            // Find account in gcashAccounts array
            const account = props.gcashAccounts.find(
                (acc) => acc.id === t.to_account_id,
            );
            if (account) {
                return account.name;
            }
            // Fallback
            return (
                t.to_account?.name ||
                t.toAccount?.name ||
                `GCash Account (ID: ${t.to_account_id})`
            );
        }
        return 'Cash on Hand';
    }
};
</script>
