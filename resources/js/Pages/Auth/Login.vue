<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    login: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-3">
            <!-- Username/Email Input -->
            <div>
                <TextInput
                    id="login"
                    type="text"
                    class="block w-full rounded-lg border border-gray-300 px-4 py-3 text-base"
                    v-model="form.login"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="Mobile number or email address"
                />
                <InputError class="mt-2" :message="form.errors.login" />
            </div>

            <!-- Password Input -->
            <div>
                <TextInput
                    id="password"
                    type="password"
                    class="block w-full rounded-lg border border-gray-300 px-4 py-3 text-base"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    placeholder="Password"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <!-- Remember Me Checkbox -->
            <div class="flex items-center">
                <label class="flex items-center">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="ms-2 text-sm text-gray-600">Remember me</span>
                </label>
            </div>

            <!-- Log In Button (Facebook style) -->
            <div>
                <button
                    type="submit"
                    class="w-full rounded-lg bg-indigo-600 px-4 py-3 text-lg font-bold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Log In
                </button>
            </div>

            <!-- Forgotten Password Link -->
            <div class="text-center">
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-sm text-indigo-600 hover:underline"
                >
                    Forgotten password?
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
