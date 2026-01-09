<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'

const props = defineProps({
  date: String,
  summary: Object,
  session: Object,
})

const form = useForm({
  date: props.date,
})

const submit = () => {
  form.get(route('reports.daily'), {
    preserveScroll: true,
    replace: true,
  })
}

const money = (v) =>
  new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP',
  }).format(v ?? 0)
</script>

<template>
  <Head title="Daily Report" />

  <AuthenticatedLayout>
    <div class="max-w-4xl mx-auto p-4 space-y-4">

      <!-- Date Picker -->
      <div class="flex gap-2 items-end">
        <input type="date" v-model="form.date" class="border rounded p-2" />
        <button @click="submit" class="bg-indigo-600 text-white px-4 py-2 rounded">
          Load
        </button>
      </div>

      <!-- Summary Cards -->
      <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
        <div class="card">Cash In<br><b>{{ money(summary.cash_in) }}</b></div>
        <div class="card">Cash Out<br><b>{{ money(summary.cash_out) }}</b></div>
        <div class="card">Fees<br><b class="text-green-600">{{ money(summary.fees) }}</b></div>
        <div class="card">Adj Add<br><b>{{ money(summary.adjustment_add) }}</b></div>
        <div class="card">Adj Deduct<br><b>{{ money(summary.adjustment_deduct) }}</b></div>
      </div>

      <!-- Session Info -->
      <div v-if="session" class="bg-white rounded shadow p-4">
        <h3 class="font-bold mb-2">Daily Session</h3>
        <div>Starting Cash: {{ money(session.starting_cash) }}</div>
        <div>Starting GCash: {{ money(session.starting_gcash) }}</div>
      </div>

    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
.card {
  @apply bg-white rounded shadow p-4 text-center;
}
</style>
