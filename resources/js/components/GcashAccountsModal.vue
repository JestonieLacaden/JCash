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
                        class="w-full max-w-2xl rounded-t-2xl bg-white shadow-2xl sm:rounded-2xl"
                    >
                        <!-- Header -->
                        <div
                            class="border-b border-gray-200 px-6 py-4 sm:px-8 sm:py-5"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3
                                        class="text-xl font-bold text-gray-900 sm:text-2xl"
                                    >
                                        GCash Accounts
                                    </h3>
                                    <p class="mt-1 text-sm text-gray-500">
                                        Last updated:
                                        {{ formatDateTime(lastUpdated) }}
                                    </p>
                                </div>
                                <button
                                    @click="$emit('close')"
                                    class="rounded-full p-2 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600"
                                >
                                    <svg
                                        class="h-6 w-6"
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

                            <!-- Total Summary -->
                            <div class="mt-4 rounded-lg bg-indigo-50 p-4">
                                <div class="flex items-center justify-between">
                                    <span
                                        class="text-sm font-medium text-indigo-700"
                                    >
                                        Total GCash Balance
                                    </span>
                                    <span
                                        class="text-2xl font-bold text-indigo-900"
                                    >
                                        ₱{{ totalBalance.toLocaleString() }}
                                    </span>
                                </div>
                                <div
                                    class="mt-1 text-xs text-indigo-600"
                                    v-if="activeAccounts.length > 0"
                                >
                                    {{ activeAccounts.length }} active account{{
                                        activeAccounts.length !== 1 ? 's' : ''
                                    }}
                                </div>
                            </div>
                        </div>

                        <!-- Accounts List -->
                        <div
                            class="max-h-[60vh] overflow-y-auto px-6 py-4 sm:px-8"
                        >
                            <div
                                v-if="activeAccounts.length === 0"
                                class="py-12 text-center text-gray-400"
                            >
                                No active GCash accounts
                            </div>

                            <div v-else class="space-y-3">
                                <div
                                    v-for="account in activeAccounts"
                                    :key="account.id"
                                    class="flex items-center justify-between rounded-xl border p-4 transition-all hover:border-indigo-200 hover:bg-indigo-50"
                                    :class="
                                        getBalanceColorClass(account.balance)
                                    "
                                >
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <h4
                                                class="font-semibold text-gray-900"
                                            >
                                                {{ account.name }}
                                            </h4>
                                            <span
                                                v-if="
                                                    isLowBalance(
                                                        account.balance,
                                                    )
                                                "
                                                class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700"
                                            >
                                                Low
                                            </span>
                                        </div>
                                        <div
                                            class="mt-1 flex items-baseline gap-2"
                                        >
                                            <span
                                                class="text-2xl font-bold"
                                                :class="
                                                    getBalanceTextColor(
                                                        account.balance,
                                                    )
                                                "
                                            >
                                                ₱{{
                                                    account.balance.toLocaleString()
                                                }}
                                            </span>
                                            <span class="text-sm text-gray-500">
                                                {{
                                                    getBalancePercentage(
                                                        account.balance,
                                                    )
                                                }}%
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Balance indicator icon -->
                                    <div
                                        class="ml-4 flex h-12 w-12 items-center justify-center rounded-full"
                                        :class="
                                            getBalanceBgColor(account.balance)
                                        "
                                    >
                                        <svg
                                            v-if="isLowBalance(account.balance)"
                                            class="h-6 w-6"
                                            :class="
                                                getBalanceIconColor(
                                                    account.balance,
                                                )
                                            "
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                                            />
                                        </svg>
                                        <svg
                                            v-else
                                            class="h-6 w-6"
                                            :class="
                                                getBalanceIconColor(
                                                    account.balance,
                                                )
                                            "
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />
                                        </svg>
                                    </div>
                                </div>
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
import { computed } from 'vue';

const props = defineProps({
    show: Boolean,
    accounts: {
        type: Array,
        default: () => [],
    },
});

defineEmits(['close']);

const activeAccounts = computed(() => {
    return props.accounts.filter((acc) => acc.is_active);
});

const totalBalance = computed(() => {
    return activeAccounts.value.reduce(
        (sum, acc) => sum + (acc.balance || 0),
        0,
    );
});

const lastUpdated = computed(() => {
    if (activeAccounts.value.length === 0) return new Date();
    // Get the most recent updated_at from all accounts
    const dates = activeAccounts.value
        .map((acc) => new Date(acc.updated_at))
        .filter((date) => !isNaN(date));
    return dates.length > 0 ? new Date(Math.max(...dates)) : new Date();
});

const formatDateTime = (date) => {
    return new Intl.DateTimeFormat('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        hour12: true,
    }).format(date);
};

// Balance threshold: 5000 pesos
const LOW_BALANCE_THRESHOLD = 5000;

const isLowBalance = (balance) => {
    return balance < LOW_BALANCE_THRESHOLD;
};

const getBalancePercentage = (balance) => {
    if (totalBalance.value === 0) return 0;
    return ((balance / totalBalance.value) * 100).toFixed(1);
};

const getBalanceColorClass = (balance) => {
    if (balance < LOW_BALANCE_THRESHOLD) {
        return 'border-amber-200 bg-amber-50';
    }
    return 'border-gray-200 bg-white';
};

const getBalanceTextColor = (balance) => {
    if (balance < LOW_BALANCE_THRESHOLD) {
        return 'text-amber-700';
    }
    return 'text-gray-900';
};

const getBalanceBgColor = (balance) => {
    if (balance < LOW_BALANCE_THRESHOLD) {
        return 'bg-amber-100';
    }
    return 'bg-green-100';
};

const getBalanceIconColor = (balance) => {
    if (balance < LOW_BALANCE_THRESHOLD) {
        return 'text-amber-600';
    }
    return 'text-green-600';
};
</script>
