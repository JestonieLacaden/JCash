<template>
    <!-- Mobile Card Skeleton -->
    <div class="space-y-3 lg:hidden">
        <div
            v-for="i in count"
            :key="i"
            class="animate-pulse rounded-lg bg-white p-4 shadow"
        >
            <div class="flex items-start justify-between">
                <div class="flex-1 space-y-3">
                    <!-- Type badge and account name -->
                    <div class="flex items-center gap-2">
                        <div class="h-5 w-20 rounded-full bg-gray-200"></div>
                        <div class="h-4 w-24 rounded bg-gray-200"></div>
                    </div>

                    <!-- Amount and fee -->
                    <div class="flex items-baseline justify-between">
                        <div class="h-6 w-32 rounded bg-gray-200"></div>
                        <div class="h-5 w-20 rounded bg-gray-200"></div>
                    </div>

                    <!-- Remarks -->
                    <div class="h-4 w-3/4 rounded bg-gray-200"></div>

                    <!-- Timestamp -->
                    <div class="h-3 w-40 rounded bg-gray-200"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Desktop Table Skeleton -->
    <div class="hidden overflow-hidden rounded-lg bg-white shadow lg:block">
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
                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                    >
                        Amount
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                    >
                        Fee
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                    >
                        Remarks
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                <tr v-for="i in count" :key="i" class="animate-pulse">
                    <td class="whitespace-nowrap px-6 py-4">
                        <div class="h-4 w-32 rounded bg-gray-200"></div>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4">
                        <div class="h-5 w-20 rounded-full bg-gray-200"></div>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4">
                        <div class="h-4 w-24 rounded bg-gray-200"></div>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4">
                        <div class="h-5 w-28 rounded bg-gray-200"></div>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4">
                        <div class="h-4 w-20 rounded bg-gray-200"></div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="h-4 w-40 rounded bg-gray-200"></div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup>
defineProps({
    count: {
        type: Number,
        default: 5,
    },
});
</script>
