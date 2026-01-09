<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    users: Object,
});

const deleteUser = (userId) => {
    if (confirm('Are you sure you want to delete this user?')) {
        router.delete(route('users.destroy', userId), {
            preserveScroll: true,
        });
    }
};

const getRoleBadgeClass = (role) => {
    return role === 'admin'
        ? 'bg-purple-100 text-purple-800'
        : 'bg-yellow-100 text-yellow-800';
};
</script>

<template>
    <Head title="User Management" />

    <AuthenticatedLayout>
        <div class="mx-auto max-w-7xl p-4">
            <!-- Header -->
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-2xl font-bold">User Management</h1>
                <Link
                    :href="route('users.create')"
                    class="rounded bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700"
                >
                    ➕ Add User
                </Link>
            </div>

            <!-- Users Table -->
            <div class="overflow-hidden rounded-lg bg-white shadow">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                            >
                                Name
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                            >
                                Username
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                            >
                                Email
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                            >
                                Role
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                            >
                                Created
                            </th>
                            <th
                                class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500"
                            >
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <tr
                            v-for="user in users.data"
                            :key="user.id"
                            class="hover:bg-gray-50"
                        >
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ user.name }}
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="text-sm text-gray-500">
                                    {{ user.username }}
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="text-sm text-gray-500">
                                    {{ user.email }}
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span
                                    class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                                    :class="getRoleBadgeClass(user.role)"
                                >
                                    {{ user.role }}
                                </span>
                            </td>
                            <td
                                class="whitespace-nowrap px-6 py-4 text-sm text-gray-500"
                            >
                                {{
                                    new Date(
                                        user.created_at,
                                    ).toLocaleDateString()
                                }}
                            </td>
                            <td
                                class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium"
                            >
                                <Link
                                    :href="route('users.edit', user.id)"
                                    class="mr-3 text-indigo-600 hover:text-indigo-900"
                                >
                                    Edit
                                </Link>
                                <button
                                    @click="deleteUser(user.id)"
                                    class="text-red-600 hover:text-red-900"
                                >
                                    Delete
                                </button>
                            </td>
                        </tr>

                        <tr v-if="!users.data.length">
                            <td
                                colspan="6"
                                class="px-6 py-4 text-center text-gray-500"
                            >
                                No users found
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div
                    v-if="users.links.length > 3"
                    class="flex justify-center border-t border-gray-200 bg-gray-50 px-4 py-3"
                >
                    <div class="flex gap-1">
                        <Link
                            v-for="(link, index) in users.links"
                            :key="index"
                            :href="link.url"
                            v-html="link.label"
                            :class="[
                                'rounded border px-3 py-1',
                                link.active
                                    ? 'border-indigo-600 bg-indigo-600 text-white'
                                    : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50',
                                !link.url && 'cursor-not-allowed opacity-50',
                            ]"
                            :disabled="!link.url"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
