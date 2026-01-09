<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DashboardSkeleton from '@/components/DashboardSkeleton.vue';
import GcashAccountsModal from '@/components/GcashAccountsModal.vue';
import PullToRefresh from '@/components/PullToRefresh.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';

const props = defineProps({
    gcashAccounts: Array,
});

const page = usePage();
const stats = computed(() => page.props.stats);
const showSuccess = ref(false);
const isLoading = ref(true);
const showGcashModal = ref(false);

// Watch for flash messages
watch(
    () => page.props.flash?.success,
    (message) => {
        if (message) {
            showSuccess.value = true;
            setTimeout(() => {
                showSuccess.value = false;
            }, 3000);
        }
    },
    { immediate: true },
);

const handleRefresh = () => {
    isLoading.value = true;
    router.reload({
        only: ['stats', 'gcashAccounts'],
        onFinish: () => {
            setTimeout(() => {
                isLoading.value = false;
            }, 600);
        },
    });
};

onMounted(() => {
    // Show skeleton briefly on initial load
    setTimeout(() => {
        isLoading.value = false;
    }, 600);

    // Track loading state for navigation
    router.on('start', (event) => {
        if (event.detail.visit.url.pathname === '/dashboard') {
            isLoading.value = true;
        }
    });
    router.on('finish', () => {
        setTimeout(() => {
            isLoading.value = false;
        }, 600);
    });
});
</script>

<template>
    <!-- Success Message -->
    <div
        v-if="showSuccess"
        class="fixed right-4 top-4 z-50 rounded-lg bg-green-500 px-6 py-3 text-white shadow-lg"
    >
        {{ page.props.flash?.success }}
    </div>

    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <PullToRefresh @refresh="handleRefresh">
            <div class="bg-gray-100">
                <!-- Mobile/Tablet container -->
                <div class="mx-auto max-w-7xl space-y-4 p-4 pb-24">
                    <!-- Header -->
                    <div class="flex items-center justify-between">
                        <h1 class="text-2xl font-bold">Dashboard</h1>
                    </div>

                    <!-- Loading Skeleton -->
                    <DashboardSkeleton v-if="isLoading" />

                    <!-- Cards -->
                    <div v-else class="space-y-4">
                        <!-- Top Row: Total GCash & Cash on Hand (Same width as bottom cards) -->
                        <div class="mx-auto max-w-2xl">
                            <div class="grid grid-cols-2 gap-4">
                                <button
                                    @click="showGcashModal = true"
                                    class="rounded-xl bg-white p-4 text-left shadow transition-all hover:shadow-lg hover:ring-2 hover:ring-indigo-500"
                                >
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <p class="text-xs text-gray-500">
                                            Total GCash
                                        </p>
                                        <svg
                                            class="h-4 w-4 text-indigo-500"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />
                                        </svg>
                                    </div>
                                    <p class="text-lg font-semibold">
                                        ₱
                                        {{ stats.totalGcash.toLocaleString() }}
                                    </p>
                                </button>

                                <div class="rounded-xl bg-white p-4 shadow">
                                    <p class="text-xs text-gray-500">
                                        Cash on Hand
                                    </p>
                                    <p class="text-lg font-semibold">
                                        ₱
                                        {{ stats.cashOnHand.toLocaleString() }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Total Capital & Tubo Today (Stacked, Max width for tablet) -->
                        <div class="mx-auto max-w-2xl space-y-4">
                            <div class="rounded-xl bg-white p-4 shadow">
                                <p class="text-xs text-gray-500">
                                    Total Capital
                                </p>
                                <p class="text-xl font-bold">
                                    ₱ {{ stats.totalCapital.toLocaleString() }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-white p-4 shadow">
                                <p class="text-xs text-gray-500">Tubo Today</p>
                                <p class="text-xl font-semibold text-green-600">
                                    ₱ {{ stats.tuboToday.toLocaleString() }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions - Desktop Only -->
                    <div class="mx-auto max-w-2xl">
                        <div
                            class="hidden rounded-xl bg-white p-6 shadow lg:block"
                        >
                            <p
                                class="mb-4 text-base font-semibold text-gray-900"
                            >
                                Quick Actions
                            </p>
                            <div class="flex gap-4">
                                <Link
                                    :href="
                                        route('transactions.create', {
                                            type: 'cash_in',
                                        })
                                    "
                                    class="flex-1 rounded-lg border-2 border-green-200 bg-green-50 px-6 py-4 text-center text-base font-medium text-green-700 transition-colors hover:border-green-300 hover:bg-green-100"
                                >
                                    Cash In
                                </Link>
                                <Link
                                    :href="
                                        route('transactions.create', {
                                            type: 'cash_out',
                                        })
                                    "
                                    class="flex-1 rounded-lg border-2 border-red-200 bg-red-50 px-6 py-4 text-center text-base font-medium text-red-700 transition-colors hover:border-red-300 hover:bg-red-100"
                                >
                                    Cash Out
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </PullToRefresh>

        <!-- GCash Accounts Modal -->
        <GcashAccountsModal
            :show="showGcashModal"
            :accounts="gcashAccounts"
            @close="showGcashModal = false"
        />
    </AuthenticatedLayout>
</template>
