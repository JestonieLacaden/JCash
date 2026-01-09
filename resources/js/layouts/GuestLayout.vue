<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center justify-center bg-gray-100"
    >
        <!-- Logo centered at top -->
        <div class="mb-8">
            <Link href="/">
                <div class="text-5xl font-bold">
                    <span class="text-indigo-600">J</span
                    ><span class="text-gray-800">Cash</span>
                </div>
            </Link>
        </div>

        <!-- Form content without background -->
        <div class="w-full max-w-md px-6">
            <slot />
        </div>
    </div>
</template>
