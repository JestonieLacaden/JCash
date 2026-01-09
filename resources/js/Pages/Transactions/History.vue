<script setup>
import CustomSelect from '@/components/CustomSelect.vue';
import Pagination from '@/components/Pagination.vue';
import PullToRefresh from '@/components/PullToRefresh.vue';
import TransactionDetailsModal from '@/components/TransactionDetailsModal.vue';
import TransactionSkeleton from '@/components/TransactionSkeleton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';

const props = defineProps({
    filters: { type: Object, default: () => ({}) },
    gcashAccounts: Array,
    transactions: Object,
});

// Dropdown options
const typeOptions = [
    { value: '', label: 'All Types' },
    { value: 'cash_in', label: 'Cash In' },
    { value: 'cash_out', label: 'Cash Out' },
    { value: 'adjustment', label: 'Adjustment' },
    { value: 'capital_move', label: 'Capital Move' },
];

const accountOptions = computed(() => {
    const options = [
        { value: '', label: 'All Accounts' },
        { value: 'cash', label: 'Cash Wallet' },
    ];
    props.gcashAccounts.forEach((acc) => {
        options.push({ value: acc.id, label: acc.name });
    });
    return options;
});

const form = useForm({
    type: props.filters.type ?? '',
    account: props.filters.account ?? '',
    from: props.filters.from ?? '',
    to: props.filters.to ?? '',
    search: props.filters.search ?? '',
});

// Default = today
const today = new Date().toISOString().slice(0, 10);
const needsInitialApply = !form.from && !form.to;
if (needsInitialApply) {
    form.from = today;
    form.to = today;
}

// UI State
const showDate = ref(false);
const showTypeFilter = ref(false);
const showAccountFilter = ref(false);
const showExportMenu = ref(false);
const showFilterSheet = ref(false);
const filterBar = ref(null);
const isLoading = ref(true);
const showDetailsModal = ref(false);
const selectedTransaction = ref(null);
const showSummary = ref(false);

// Summary stats computed
const summaryStats = computed(() => {
    const transactions = props.transactions.data || [];

    const totalFees = transactions
        .filter((t) => t.type === 'cash_in' || t.type === 'cash_out')
        .reduce((sum, t) => sum + (t.fee || 0), 0);

    const totalCashIn = transactions
        .filter((t) => t.type === 'cash_in')
        .reduce((sum, t) => sum + t.amount, 0);

    const totalCashOut = transactions
        .filter((t) => t.type === 'cash_out')
        .reduce((sum, t) => sum + t.amount, 0);

    const totalAdjustments = transactions.filter(
        (t) => t.type === 'adjustment',
    ).length;

    const totalCapitalMoves = transactions.filter(
        (t) => t.type === 'capital_move',
    ).length;

    return {
        totalFees,
        totalCashIn,
        totalCashOut,
        totalAdjustments,
        totalCapitalMoves,
        totalTransactions: transactions.length,
    };
});

// Temp filter values for bottom sheet
const tempFilters = ref({
    type: form.type,
    account: form.account,
    from: form.from,
    to: form.to,
});

// Open filter sheet
const openFilterSheet = () => {
    tempFilters.value = {
        type: form.type,
        account: form.account,
        from: form.from,
        to: form.to,
    };
    showFilterSheet.value = true;
};

// Apply filters from bottom sheet
const applyFilters = () => {
    form.type = tempFilters.value.type;
    form.account = tempFilters.value.account;
    form.from = tempFilters.value.from;
    form.to = tempFilters.value.to;
    showFilterSheet.value = false;
    apply();
};

// Clear filters
const clearFilters = () => {
    tempFilters.value = {
        type: '',
        account: '',
        from: today,
        to: today,
    };
};

// Date preset functions for bottom sheet
const setTempToday = () => {
    tempFilters.value.from = today;
    tempFilters.value.to = today;
};

const setTempYesterday = () => {
    const yesterday = new Date();
    yesterday.setDate(yesterday.getDate() - 1);
    const yStr = yesterday.toISOString().slice(0, 10);
    tempFilters.value.from = yStr;
    tempFilters.value.to = yStr;
};

const setTempLast7Days = () => {
    const d = new Date();
    const s = new Date(d);
    s.setDate(s.getDate() - 7);
    tempFilters.value.from = s.toISOString().slice(0, 10);
    tempFilters.value.to = d.toISOString().slice(0, 10);
};

const setTempLast30Days = () => {
    const d = new Date();
    const s = new Date(d);
    s.setDate(s.getDate() - 30);
    tempFilters.value.from = s.toISOString().slice(0, 10);
    tempFilters.value.to = d.toISOString().slice(0, 10);
};

// Check active date preset
const isYesterdayActive = computed(() => {
    const yesterday = new Date();
    yesterday.setDate(yesterday.getDate() - 1);
    const yStr = yesterday.toISOString().slice(0, 10);
    return tempFilters.value.from === yStr && tempFilters.value.to === yStr;
});

const isLast7DaysActive = computed(() => {
    const d = new Date();
    const s = new Date(d);
    s.setDate(s.getDate() - 7);
    const fromStr = s.toISOString().slice(0, 10);
    const toStr = d.toISOString().slice(0, 10);
    return tempFilters.value.from === fromStr && tempFilters.value.to === toStr;
});

const isLast30DaysActive = computed(() => {
    const d = new Date();
    const s = new Date(d);
    s.setDate(s.getDate() - 30);
    const fromStr = s.toISOString().slice(0, 10);
    const toStr = d.toISOString().slice(0, 10);
    return tempFilters.value.from === fromStr && tempFilters.value.to === toStr;
});

// Close all dropdowns helper
const closeAllDropdowns = () => {
    showDate.value = false;
    showTypeFilter.value = false;
    showAccountFilter.value = false;
    showExportMenu.value = false;
};

// Date Label
const dateLabel = computed(() => {
    if (form.from === today && form.to === today) return 'Today';

    // Check for Yesterday
    const yesterday = new Date();
    yesterday.setDate(yesterday.getDate() - 1);
    const yesterdayStr = yesterday.toISOString().slice(0, 10);
    if (form.from === yesterdayStr && form.to === yesterdayStr)
        return 'Yesterday';

    // Check for same date (but not today or yesterday)
    if (form.from === form.to) return form.from;

    const diff =
        (new Date(form.to) - new Date(form.from)) / (1000 * 60 * 60 * 24);

    if (diff === 7) return 'Last 7 days';
    if (diff === 30) return 'Last 30 days';

    return `${form.from} → ${form.to}`;
});

// Apply filter
const apply = () => {
    isLoading.value = true;
    form.get(route('transactions.history'), {
        preserveState: true,
        replace: true,
        onFinish: () => {
            setTimeout(() => {
                isLoading.value = false;
            }, 600);
        },
    });
};

// Date presets
const setRange = (range) => {
    const d = new Date();
    const f = (x) => x.toISOString().slice(0, 10);

    if (range === 'today') form.from = form.to = f(d);
    if (range === 'yesterday') {
        d.setDate(d.getDate() - 1);
        form.from = form.to = f(d);
    }
    if (range === '7') {
        const s = new Date(d);
        s.setDate(s.getDate() - 7);
        form.from = f(s);
        form.to = f(d);
    }
    if (range === '30') {
        const s = new Date(d);
        s.setDate(s.getDate() - 30);
        form.from = f(s);
        form.to = f(d);
    }

    closeAllDropdowns();
    apply();
};

// Click outside
const onClickOutside = (e) => {
    if (filterBar.value && !filterBar.value.contains(e.target)) {
        closeAllDropdowns();
    }
};

const handleRefresh = () => {
    isLoading.value = true;
    router.reload({
        only: ['transactions'],
        onFinish: () => {
            setTimeout(() => {
                isLoading.value = false;
            }, 600);
        },
    });
};

onMounted(() => {
    document.addEventListener('click', onClickOutside);

    // Show skeleton briefly on initial load, then show content
    setTimeout(() => {
        isLoading.value = false;
    }, 600);

    // Apply today filter on initial load if no filters were set
    if (needsInitialApply) {
        isLoading.value = true;
        apply();
    }

    // Track loading state for navigation
    router.on('start', (event) => {
        // Only show loading for history page visits
        if (event.detail.visit.url.pathname.includes('history')) {
            isLoading.value = true;
        }
    });
    router.on('finish', () => {
        setTimeout(() => {
            isLoading.value = false;
        }, 600);
    });
});
onUnmounted(() => {
    document.removeEventListener('click', onClickOutside);
});

// Format
const formatMoney = (v) =>
    new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
    }).format(v);

// Type badge colors
const getTypeBadge = (type) => {
    const badges = {
        cash_in: 'bg-green-100 text-green-700',
        cash_out: 'bg-red-100 text-red-700',
        adjustment: 'bg-blue-100 text-blue-700',
        capital_move: 'bg-purple-100 text-purple-700',
    };
    return badges[type] || 'bg-gray-100 text-gray-700';
};

const getTypeLabel = (type) => {
    return type.replace('_', ' ').replace(/\b\w/g, (l) => l.toUpperCase());
};

const openTransactionDetails = (transaction) => {
    selectedTransaction.value = transaction;
    showDetailsModal.value = true;
};
</script>

<template>
    <Head title="Transaction History" />

    <AuthenticatedLayout>
        <PullToRefresh @refresh="handleRefresh">
            <!-- Header Bar -->
            <div
                ref="filterBar"
                class="sticky top-0 z-20 border-b bg-white shadow-sm"
            >
                <div class="mx-auto max-w-7xl px-4 py-3">
                    <!-- Mobile: Search + Filter + Export -->
                    <div class="flex items-center gap-2 lg:hidden">
                        <input
                            v-model="form.search"
                            @input="apply"
                            placeholder="Search reference..."
                            class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        />

                        <!-- Summary Button -->
                        <button
                            @click="showSummary = !showSummary"
                            class="flex items-center justify-center rounded-lg border border-gray-300 p-2 hover:bg-gray-50"
                            :class="{
                                'border-indigo-500 bg-indigo-50': showSummary,
                            }"
                        >
                            <svg
                                class="h-5 w-5"
                                :class="
                                    showSummary
                                        ? 'text-indigo-600'
                                        : 'text-gray-600'
                                "
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                                />
                            </svg>
                        </button>

                        <!-- Filter Button -->
                        <button
                            @click="openFilterSheet"
                            class="flex items-center justify-center rounded-lg border border-gray-300 p-2 hover:bg-gray-50"
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
                                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"
                                />
                            </svg>
                        </button>

                        <!-- Export Menu Button -->
                        <div class="relative">
                            <button
                                @click.stop="showExportMenu = !showExportMenu"
                                class="flex items-center justify-center rounded-lg border border-gray-300 p-2 hover:bg-gray-50"
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
                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
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
                                    v-if="showExportMenu"
                                    class="absolute right-0 top-full z-50 mt-2 w-48 rounded-lg border bg-white shadow-lg"
                                >
                                    <a
                                        :href="
                                            route('exports.transactions', form)
                                        "
                                        class="block px-4 py-2 text-sm hover:bg-gray-100"
                                    >
                                        Export Full CSV
                                    </a>
                                    <a
                                        :href="
                                            route('exports.daily', {
                                                date: form.from,
                                            })
                                        "
                                        class="block px-4 py-2 text-sm hover:bg-gray-100"
                                    >
                                        Export Daily CSV
                                    </a>
                                </div>
                            </transition>
                        </div>
                    </div>

                    <!-- Desktop: Filters + Export Buttons -->
                    <div class="hidden items-center justify-between lg:flex">
                        <div class="flex flex-wrap items-center gap-2">
                            <!-- Date Filter -->
                            <div class="relative">
                                <button
                                    @click.stop="
                                        showDate = !showDate;
                                        showTypeFilter = false;
                                        showAccountFilter = false;
                                    "
                                    class="flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm hover:bg-gray-50"
                                >
                                    {{ dateLabel }}
                                    <svg
                                        class="h-4 w-4"
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
                                        v-if="showDate"
                                        class="absolute left-0 mt-2 w-56 rounded-lg border bg-white shadow-lg"
                                    >
                                        <button
                                            @click="setRange('today')"
                                            class="block w-full px-4 py-2 text-left text-sm hover:bg-gray-100"
                                        >
                                            Today
                                        </button>
                                        <button
                                            @click="setRange('yesterday')"
                                            class="block w-full px-4 py-2 text-left text-sm hover:bg-gray-100"
                                        >
                                            Yesterday
                                        </button>
                                        <button
                                            @click="setRange('7')"
                                            class="block w-full px-4 py-2 text-left text-sm hover:bg-gray-100"
                                        >
                                            Last 7 days
                                        </button>
                                        <button
                                            @click="setRange('30')"
                                            class="block w-full px-4 py-2 text-left text-sm hover:bg-gray-100"
                                        >
                                            Last 30 days
                                        </button>

                                        <div class="space-y-2 border-t p-3">
                                            <input
                                                type="date"
                                                v-model="form.from"
                                                @change="apply"
                                                class="w-full rounded border px-2 py-1 text-sm"
                                            />
                                            <input
                                                type="date"
                                                v-model="form.to"
                                                @change="apply"
                                                class="w-full rounded border px-2 py-1 text-sm"
                                            />
                                        </div>
                                    </div>
                                </transition>
                            </div>

                            <!-- Type Filter -->
                            <div class="relative">
                                <button
                                    @click.stop="
                                        showTypeFilter = !showTypeFilter;
                                        showDate = false;
                                        showAccountFilter = false;
                                    "
                                    class="flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm hover:bg-gray-50"
                                >
                                    {{
                                        form.type
                                            ? getTypeLabel(form.type)
                                            : 'All Types'
                                    }}
                                    <svg
                                        class="h-4 w-4"
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
                                        v-if="showTypeFilter"
                                        class="absolute left-0 z-50 mt-2 w-48 rounded-lg border bg-white shadow-lg"
                                    >
                                        <button
                                            @click="
                                                form.type = '';
                                                apply();
                                                showTypeFilter = false;
                                            "
                                            class="block w-full px-4 py-2 text-left text-sm hover:bg-gray-100"
                                        >
                                            All Types
                                        </button>
                                        <button
                                            @click="
                                                form.type = 'cash_in';
                                                apply();
                                                showTypeFilter = false;
                                            "
                                            class="block w-full px-4 py-2 text-left text-sm hover:bg-gray-100"
                                        >
                                            Cash In
                                        </button>
                                        <button
                                            @click="
                                                form.type = 'cash_out';
                                                apply();
                                                showTypeFilter = false;
                                            "
                                            class="block w-full px-4 py-2 text-left text-sm hover:bg-gray-100"
                                        >
                                            Cash Out
                                        </button>
                                        <button
                                            @click="
                                                form.type = 'adjustment';
                                                apply();
                                                showTypeFilter = false;
                                            "
                                            class="block w-full px-4 py-2 text-left text-sm hover:bg-gray-100"
                                        >
                                            Adjustment
                                        </button>
                                        <button
                                            @click="
                                                form.type = 'capital_move';
                                                apply();
                                                showTypeFilter = false;
                                            "
                                            class="block w-full px-4 py-2 text-left text-sm hover:bg-gray-100"
                                        >
                                            Capital Move
                                        </button>
                                    </div>
                                </transition>
                            </div>

                            <!-- Account Filter -->
                            <div class="relative">
                                <button
                                    @click.stop="
                                        showAccountFilter = !showAccountFilter;
                                        showDate = false;
                                        showTypeFilter = false;
                                    "
                                    class="flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm hover:bg-gray-50"
                                >
                                    {{
                                        form.account
                                            ? form.account === 'cash'
                                                ? 'Cash Wallet'
                                                : gcashAccounts.find(
                                                      (a) =>
                                                          a.id == form.account,
                                                  )?.name
                                            : 'All Accounts'
                                    }}
                                    <svg
                                        class="h-4 w-4"
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
                                        v-if="showAccountFilter"
                                        class="absolute left-0 z-50 mt-2 w-48 rounded-lg border bg-white shadow-lg"
                                    >
                                        <button
                                            @click="
                                                form.account = '';
                                                apply();
                                                showAccountFilter = false;
                                            "
                                            class="block w-full px-4 py-2 text-left text-sm hover:bg-gray-100"
                                        >
                                            All Accounts
                                        </button>
                                        <button
                                            @click="
                                                form.account = 'cash';
                                                apply();
                                                showAccountFilter = false;
                                            "
                                            class="block w-full px-4 py-2 text-left text-sm hover:bg-gray-100"
                                        >
                                            Cash Wallet
                                        </button>
                                        <button
                                            v-for="a in gcashAccounts"
                                            :key="a.id"
                                            @click="
                                                form.account = a.id;
                                                apply();
                                                showAccountFilter = false;
                                            "
                                            class="block w-full px-4 py-2 text-left text-sm hover:bg-gray-100"
                                        >
                                            {{ a.name }}
                                        </button>
                                    </div>
                                </transition>
                            </div>

                            <!-- Search -->
                            <input
                                v-model="form.search"
                                @input="apply"
                                placeholder="Search reference..."
                                class="w-64 rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                        </div>

                        <!-- Summary + Export Buttons (Desktop) -->
                        <div class="flex gap-2">
                            <!-- Summary Button -->
                            <button
                                @click="showSummary = !showSummary"
                                class="flex items-center gap-2 rounded-lg border px-4 py-2 text-sm font-medium transition-colors"
                                :class="
                                    showSummary
                                        ? 'border-indigo-500 bg-indigo-50 text-indigo-600'
                                        : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50'
                                "
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
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                                    />
                                </svg>
                                Summary
                            </button>

                            <a
                                :href="route('exports.transactions', form)"
                                class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700"
                            >
                                Export CSV
                            </a>
                            <a
                                :href="
                                    route('exports.daily', { date: form.from })
                                "
                                class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                            >
                                Daily CSV
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="mx-auto max-w-7xl px-4 py-6">
                <h1 class="mb-4 text-2xl font-bold">Transaction History</h1>

                <!-- Loading Skeleton -->
                <TransactionSkeleton v-if="isLoading" :count="5" />

                <!-- Transactions Content -->
                <div v-else>
                    <!-- Mobile: Card Layout -->
                    <div class="space-y-3 lg:hidden">
                        <button
                            v-for="t in transactions.data"
                            :key="t.id"
                            @click="openTransactionDetails(t)"
                            class="w-full rounded-lg bg-white p-4 text-left shadow transition-all hover:shadow-lg hover:ring-2 hover:ring-indigo-500"
                        >
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span
                                            :class="getTypeBadge(t.type)"
                                            class="rounded-full px-2 py-0.5 text-xs font-medium"
                                        >
                                            {{ getTypeLabel(t.type) }}
                                        </span>
                                        <span class="text-xs text-gray-500">
                                            {{
                                                t.gcash_account?.name ??
                                                'Cash Wallet'
                                            }}
                                        </span>
                                    </div>

                                    <div
                                        class="mt-2 flex items-baseline justify-between"
                                    >
                                        <p class="text-lg font-bold">
                                            {{ formatMoney(t.amount) }}
                                        </p>
                                        <p
                                            v-if="
                                                t.type === 'cash_in' ||
                                                t.type === 'cash_out'
                                            "
                                            class="text-sm font-medium text-green-600"
                                        >
                                            Fee: {{ formatMoney(t.fee ?? 0) }}
                                        </p>
                                    </div>

                                    <p
                                        v-if="t.reference"
                                        class="mt-1 text-sm text-gray-600"
                                    >
                                        {{ t.reference }}
                                    </p>

                                    <p class="mt-2 text-xs text-gray-400">
                                        {{
                                            new Date(
                                                t.created_at,
                                            ).toLocaleString()
                                        }}
                                    </p>
                                </div>
                            </div>
                        </button>

                        <div
                            v-if="!transactions.data.length"
                            class="rounded-lg bg-white py-12 text-center text-gray-400"
                        >
                            No transactions found
                        </div>
                    </div>

                    <!-- Desktop: Table -->
                    <div
                        class="hidden overflow-hidden rounded-lg bg-white shadow lg:block"
                    >
                        <table class="min-w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Date
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Type
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Account
                                    </th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Amount
                                    </th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Fee
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Reference
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr
                                    v-for="t in transactions.data"
                                    :key="t.id"
                                    @click="openTransactionDetails(t)"
                                    class="cursor-pointer transition-colors hover:bg-indigo-50"
                                >
                                    <td
                                        class="whitespace-nowrap px-6 py-4 text-sm text-gray-900"
                                    >
                                        {{
                                            new Date(
                                                t.created_at,
                                            ).toLocaleString()
                                        }}
                                    </td>
                                    <td
                                        class="whitespace-nowrap px-6 py-4 text-sm"
                                    >
                                        <span
                                            :class="getTypeBadge(t.type)"
                                            class="rounded-full px-2.5 py-0.5 text-xs font-medium"
                                        >
                                            {{ getTypeLabel(t.type) }}
                                        </span>
                                    </td>
                                    <td
                                        class="whitespace-nowrap px-6 py-4 text-sm text-gray-900"
                                    >
                                        {{
                                            t.gcash_account?.name ??
                                            'Cash Wallet'
                                        }}
                                    </td>
                                    <td
                                        class="whitespace-nowrap px-6 py-4 text-right text-sm font-semibold text-gray-900"
                                    >
                                        {{ formatMoney(t.amount) }}
                                    </td>
                                    <td
                                        class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium text-green-600"
                                    >
                                        <span
                                            v-if="
                                                t.type === 'cash_in' ||
                                                t.type === 'cash_out'
                                            "
                                        >
                                            {{ formatMoney(t.fee ?? 0) }}
                                        </span>
                                        <span v-else class="text-gray-400"
                                            >Free</span
                                        >
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ t.reference ?? '-' }}
                                    </td>
                                </tr>

                                <tr v-if="!transactions.data.length">
                                    <td
                                        colspan="6"
                                        class="py-12 text-center text-gray-400"
                                    >
                                        No transactions found
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <Pagination :links="transactions.links" class="mt-4" />
                </div>
            </div>

            <!-- Mobile Filter Bottom Sheet -->
            <teleport to="body">
                <!-- Backdrop -->
                <transition
                    enter-active-class="transition-opacity duration-300"
                    enter-from-class="opacity-0"
                    enter-to-class="opacity-100"
                    leave-active-class="transition-opacity duration-300"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <div
                        v-if="showFilterSheet"
                        @click="showFilterSheet = false"
                        class="fixed inset-0 z-40 bg-black bg-opacity-50 lg:hidden"
                    ></div>
                </transition>

                <!-- Bottom Sheet -->
                <transition
                    enter-active-class="transition-transform duration-300"
                    enter-from-class="translate-y-full"
                    enter-to-class="translate-y-0"
                    leave-active-class="transition-transform duration-300"
                    leave-from-class="translate-y-0"
                    leave-to-class="translate-y-full"
                >
                    <div
                        v-if="showFilterSheet"
                        class="fixed bottom-0 left-0 right-0 z-50 max-h-[80vh] overflow-y-auto rounded-t-2xl bg-white shadow-2xl lg:hidden"
                    >
                        <!-- Handle Bar -->
                        <div class="flex justify-center py-3">
                            <div
                                class="h-1 w-12 rounded-full bg-gray-300"
                            ></div>
                        </div>

                        <!-- Content -->
                        <div class="px-6 pb-6">
                            <h3 class="mb-4 text-lg font-semibold">
                                Filter Transactions
                            </h3>

                            <!-- Date Range -->
                            <div class="mb-4">
                                <label
                                    class="mb-2 block text-sm font-medium text-gray-700"
                                    >Date Range</label
                                >
                                <div class="mb-2 grid grid-cols-2 gap-2">
                                    <button
                                        @click="setTempToday"
                                        :class="
                                            tempFilters.from === today &&
                                            tempFilters.to === today
                                                ? 'bg-indigo-600 text-white'
                                                : 'bg-gray-100 text-gray-700'
                                        "
                                        class="rounded-lg px-3 py-2 text-sm font-medium"
                                    >
                                        Today
                                    </button>
                                    <button
                                        @click="setTempYesterday"
                                        :class="
                                            isYesterdayActive
                                                ? 'bg-indigo-600 text-white'
                                                : 'bg-gray-100 text-gray-700'
                                        "
                                        class="rounded-lg px-3 py-2 text-sm font-medium"
                                    >
                                        Yesterday
                                    </button>
                                    <button
                                        @click="setTempLast7Days"
                                        :class="
                                            isLast7DaysActive
                                                ? 'bg-indigo-600 text-white'
                                                : 'bg-gray-100 text-gray-700'
                                        "
                                        class="rounded-lg px-3 py-2 text-sm font-medium"
                                    >
                                        Last 7 days
                                    </button>
                                    <button
                                        @click="setTempLast30Days"
                                        :class="
                                            isLast30DaysActive
                                                ? 'bg-indigo-600 text-white'
                                                : 'bg-gray-100 text-gray-700'
                                        "
                                        class="rounded-lg px-3 py-2 text-sm font-medium"
                                    >
                                        Last 30 days
                                    </button>
                                </div>
                                <div class="space-y-2">
                                    <input
                                        type="date"
                                        v-model="tempFilters.from"
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"
                                    />
                                    <input
                                        type="date"
                                        v-model="tempFilters.to"
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"
                                    />
                                </div>
                            </div>

                            <!-- Transaction Type -->
                            <div class="mb-4">
                                <CustomSelect
                                    v-model="tempFilters.type"
                                    :options="typeOptions"
                                    label="Transaction Type"
                                    placeholder="Select type"
                                />
                            </div>

                            <!-- Account -->
                            <div class="mb-6">
                                <CustomSelect
                                    v-model="tempFilters.account"
                                    :options="accountOptions"
                                    label="Account"
                                    placeholder="Select account"
                                />
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex gap-3">
                                <button
                                    @click="clearFilters"
                                    class="flex-1 rounded-lg border border-gray-300 px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50"
                                >
                                    Clear
                                </button>
                                <button
                                    @click="applyFilters"
                                    class="flex-1 rounded-lg bg-indigo-600 px-4 py-3 text-sm font-medium text-white hover:bg-indigo-700"
                                >
                                    Apply Filters
                                </button>
                            </div>
                        </div>
                    </div>
                </transition>
            </teleport>
        </PullToRefresh>

        <!-- Transaction Details Modal -->
        <TransactionDetailsModal
            :show="showDetailsModal"
            :transaction="selectedTransaction || {}"
            :gcashAccounts="gcashAccounts"
            @close="showDetailsModal = false"
        />

        <!-- Summary Stats Slide-down Panel -->
        <teleport to="body">
            <transition
                enter-active-class="transition ease-out duration-300"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition ease-in duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="showSummary"
                    @click="showSummary = false"
                    class="fixed inset-0 z-30 bg-black bg-opacity-50"
                ></div>
            </transition>

            <transition
                enter-active-class="transition ease-out duration-300"
                enter-from-class="-translate-y-full"
                enter-to-class="translate-y-0"
                leave-active-class="transition ease-in duration-200"
                leave-from-class="translate-y-0"
                leave-to-class="-translate-y-full"
            >
                <div
                    v-if="showSummary"
                    class="fixed left-0 right-0 top-0 z-40 mx-auto max-w-2xl rounded-b-2xl bg-white shadow-2xl"
                >
                    <!-- Header -->
                    <div class="border-b border-gray-200 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">
                                📊 Earnings Summary
                            </h3>
                            <button
                                @click="showSummary = false"
                                class="rounded-lg p-1 hover:bg-gray-100"
                            >
                                <svg
                                    class="h-5 w-5 text-gray-400"
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
                        <p class="mt-1 text-sm text-gray-500">
                            {{ dateLabel }}
                        </p>
                    </div>

                    <!-- Stats Content -->
                    <div class="p-6">
                        <!-- Main Stat: Total Fees -->
                        <div
                            class="mb-6 rounded-lg bg-gradient-to-r from-green-500 to-green-600 p-6 text-white"
                        >
                            <p class="text-sm font-medium opacity-90">
                                Total Fees Collected
                            </p>
                            <p class="mt-2 text-3xl font-bold">
                                ₱{{
                                    summaryStats.totalFees.toLocaleString(
                                        undefined,
                                        {
                                            minimumFractionDigits: 2,
                                            maximumFractionDigits: 2,
                                        },
                                    )
                                }}
                            </p>
                        </div>

                        <!-- Stats Grid -->
                        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                            <!-- Total Transactions -->
                            <div
                                class="rounded-lg border border-gray-200 bg-gray-50 p-4"
                            >
                                <p class="text-xs font-medium text-gray-500">
                                    Transactions
                                </p>
                                <p
                                    class="mt-1 text-2xl font-bold text-gray-900"
                                >
                                    {{ summaryStats.totalTransactions }}
                                </p>
                            </div>

                            <!-- Total Cash In -->
                            <div
                                class="rounded-lg border border-blue-200 bg-blue-50 p-4"
                            >
                                <p class="text-xs font-medium text-blue-600">
                                    Cash In
                                </p>
                                <p class="mt-1 text-lg font-bold text-blue-900">
                                    ₱{{
                                        summaryStats.totalCashIn.toLocaleString()
                                    }}
                                </p>
                            </div>

                            <!-- Total Cash Out -->
                            <div
                                class="rounded-lg border border-red-200 bg-red-50 p-4"
                            >
                                <p class="text-xs font-medium text-red-600">
                                    Cash Out
                                </p>
                                <p class="mt-1 text-lg font-bold text-red-900">
                                    ₱{{
                                        summaryStats.totalCashOut.toLocaleString()
                                    }}
                                </p>
                            </div>

                            <!-- Adjustments -->
                            <div
                                class="rounded-lg border border-yellow-200 bg-yellow-50 p-4"
                            >
                                <p class="text-xs font-medium text-yellow-600">
                                    Adjustments
                                </p>
                                <p
                                    class="mt-1 text-2xl font-bold text-yellow-900"
                                >
                                    {{ summaryStats.totalAdjustments }}
                                </p>
                            </div>

                            <!-- Capital Moves -->
                            <div
                                class="rounded-lg border border-purple-200 bg-purple-50 p-4"
                            >
                                <p class="text-xs font-medium text-purple-600">
                                    Capital Moves
                                </p>
                                <p
                                    class="mt-1 text-2xl font-bold text-purple-900"
                                >
                                    {{ summaryStats.totalCapitalMoves }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </transition>
        </teleport>
    </AuthenticatedLayout>
</template>
