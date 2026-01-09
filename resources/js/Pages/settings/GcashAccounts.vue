<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'

defineProps({
  accounts: Array,
})

const form = useForm({
    name: '',
});

function submit() {
    form.post(route('settings.gcash.store'), {
        onSuccess: () => form.reset(),
    });
}
</script>

<template>
  <Head title="GCash Accounts" />

  <AuthenticatedLayout>
    <div class="max-w-md mx-auto p-4 space-y-6">

      <h1 class="text-xl font-bold">GCash Accounts</h1>

      <!-- Add new -->
      <form @submit.prevent="submit"
            class="flex gap-2">
        <input v-model="form.name"
               class="border rounded px-2 py-1 w-full"
               placeholder="New GCash account name" />
        <button class="bg-indigo-600 text-white px-3 rounded">
          Add
        </button>
      </form>

      <!-- List -->
      <div v-for="acc in accounts"
           :key="acc.id"
           class="flex items-center justify-between border p-2 rounded">

        <div>
          <div class="font-medium">{{ acc.name }}</div>
          <div class="text-xs text-gray-500">
            Balance: ₱{{ acc.balance }}
          </div>
        </div>

        <form
          @submit.prevent="$inertia.put(
            route('settings.gcash.update', acc.id),
            { name: acc.name, is_active: !acc.is_active }
          )"
        >
          <button
            :class="acc.is_active ? 'text-green-600' : 'text-gray-400'">
            {{ acc.is_active ? 'Active' : 'Inactive' }}
          </button>
        </form>
      </div>

    </div>
  </AuthenticatedLayout>
</template>
