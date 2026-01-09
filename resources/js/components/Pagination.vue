<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: Array,
});
</script>

<template>
    <div
        v-if="links.length > 3"
        class="mt-4 flex flex-wrap justify-center gap-1"
    >
        <template v-for="(link, i) in links" :key="i">
            <span
                v-if="!link.url"
                class="px-3 py-1 text-gray-400"
                v-html="link.label"
            />
            <Link
                v-else
                :href="link.url"
                class="rounded border px-3 py-1"
                :class="link.active ? 'bg-indigo-600 text-white' : 'bg-white'"
                v-html="link.label"
                preserve-state
            />
        </template>
    </div>
</template>
