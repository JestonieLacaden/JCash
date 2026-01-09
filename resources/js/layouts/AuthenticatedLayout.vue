<script setup>
import ApplicationLogo from '@/components/ApplicationLogo.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

// Get role status
const { props } = usePage();
const userRole = computed(() => props.userRole);
const isReadOnly = computed(() => props.isReadOnly);
const isAdmin = computed(() => userRole.value === 'admin');
const isStaff = computed(() => userRole.value === 'staff');
const isAuditor = computed(() => userRole.value === 'auditor');

// Floating menu state
const showFloatingMenu = ref(false);
</script>

<template>
    <div class="min-h-screen bg-gray-100 pb-20">
        <!-- Top Header (Logo + User) - Simple & Clean -->
        <div
            class="sticky top-0 z-10 border-b border-gray-200 bg-white px-4 py-3"
        >
            <div class="mx-auto flex max-w-7xl items-center justify-between">
                <!-- Logo -->
                <div class="flex items-center gap-2">
                    <Link :href="route('dashboard')">
                        <ApplicationLogo
                            class="h-8 w-auto fill-current text-gray-800"
                        />
                    </Link>
                    <span class="text-lg font-bold">JCash</span>
                </div>

                <!-- User Info with Profile Icon -->
                <div class="flex items-center gap-2">
                    <div class="text-sm font-medium text-gray-700">
                        {{ $page.props.auth.user.name }}
                    </div>
                    <Link :href="route('profile.edit')">
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-600"
                        >
                            <svg
                                class="h-5 w-5 text-white"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                />
                            </svg>
                        </div>
                    </Link>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <main>
            <slot />
        </main>

        <!-- Floating Menu - Cash In/Out (Home only) -->
        <div
            v-if="(isAdmin || isStaff) && route().current('dashboard')"
            class="fixed bottom-20 right-4 z-20 md:bottom-6"
        >
            <!-- Action Buttons (appear above + button) -->
            <div v-if="showFloatingMenu" class="mb-2 flex flex-col gap-2">
                <Link
                    :href="route('transactions.create', { type: 'cash_in' })"
                    class="flex items-center gap-2 rounded-full bg-green-600 px-4 py-2 text-sm font-medium text-white shadow-lg hover:bg-green-700"
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
                            d="M7 11l5-5m0 0l5 5m-5-5v12"
                        />
                    </svg>
                    Cash In
                </Link>
                <Link
                    :href="route('transactions.create', { type: 'cash_out' })"
                    class="flex items-center gap-2 rounded-full bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-lg hover:bg-red-700"
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
                            d="M17 13l-5 5m0 0l-5-5m5 5V6"
                        />
                    </svg>
                    Cash Out
                </Link>
            </div>

            <!-- + Button -->
            <button
                @click="showFloatingMenu = !showFloatingMenu"
                class="flex h-14 w-14 items-center justify-center rounded-full bg-indigo-600 text-white shadow-lg transition-transform hover:bg-indigo-700"
                :class="{ 'rotate-45': showFloatingMenu }"
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
                        d="M12 4v16m8-8H4"
                    />
                </svg>
            </button>
        </div>

        <!-- Bottom Navigation - Mobile First -->
        <nav
            class="fixed bottom-0 left-0 right-0 z-10 border-t border-gray-200 bg-white"
        >
            <div class="mx-auto max-w-7xl">
                <div class="flex justify-around">
                    <!-- Dashboard -->
                    <Link
                        :href="route('dashboard')"
                        class="flex flex-1 flex-col items-center py-2 text-xs"
                        :class="
                            route().current('dashboard')
                                ? 'text-indigo-600'
                                : 'text-gray-600'
                        "
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
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
                            />
                        </svg>
                        <span class="mt-1">Home</span>
                    </Link>

                    <!-- History -->
                    <Link
                        :href="route('transactions.history')"
                        class="flex flex-1 flex-col items-center py-2 text-xs"
                        :class="
                            route().current('transactions.history')
                                ? 'text-indigo-600'
                                : 'text-gray-600'
                        "
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
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                        <span class="mt-1">History</span>
                    </Link>

                    <!-- Funds (Admin only) -->
                    <Link
                        v-if="isAdmin"
                        :href="route('transactions.adjustment')"
                        class="flex flex-1 flex-col items-center py-2 text-xs"
                        :class="
                            route().current('transactions.adjustment')
                                ? 'text-indigo-600'
                                : 'text-gray-600'
                        "
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
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                        <span class="mt-1">Funds</span>
                    </Link>

                    <!-- Settings (Admin only) -->
                    <Link
                        v-if="isAdmin"
                        :href="route('settings.fees')"
                        class="flex flex-1 flex-col items-center py-2 text-xs"
                        :class="
                            route().current('settings.*')
                                ? 'text-indigo-600'
                                : 'text-gray-600'
                        "
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
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                            />
                        </svg>
                        <span class="mt-1">Settings</span>
                    </Link>
                </div>
            </div>
        </nav>
    </div>
</template>
