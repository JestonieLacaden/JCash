<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, router } from '@inertiajs/vue3'

const props = defineProps({
  transactions: Object,
  filters: Object,
})

const filter = (key, value) => {
  router.get('/transactions', {
    ...props.filters,
    [key]: value,
  }, { preserveState: true })
}
</script>

<template>
  <Head title="Transactions" />

  <AuthenticatedLayout>
    <div class="max-w-5xl mx-auto p-4 space-y-4">

      <h1 class="text-xl font-bold">Transaction History</h1>

      <!-- Filters -->
      <div class="grid grid-cols-3 gap-2">
        <select @change="filter('type', $event.target.value)">
          <option value="">All</option>
          <option value="cash_in">Cash In</option>
          <option value="cash_out">Cash Out</option>
          <option value="capital_move">Capital Move</option>
        </select>

        <input type="date" @change="filter('date', $event.target.value)" />

        <input
          placeholder="Search ref / name / remarks"
          @input="filter('search', $event.target.value)"
        />
      </div>

      <!-- Table -->
      <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b text-left">
              <th>Date</th>
              <th>Type</th>
              <th>Amount</th>
              <th>Fee</th>
              <th>From / To</th>
              <th>Remarks</th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="t in transactions.data" :key="t.id" class="border-b">
              <td>{{ t.claimed_at }}</td>
              <td class="capitalize">{{ t.type.replace('_',' ') }}</td>
              <td>₱ {{ t.amount }}</td>
              <td class="text-green-600">₱ {{ t.fee }}</td>
              <td>
                <div v-if="t.type === 'capital_move'">
                  {{ t.from_account?.name || 'Cash' }} →
                  {{ t.to_account?.name || 'Cash' }}
                </div>
                <div v-else>
                  {{ t.gcash_account?.name }}
                </div>
              </td>
              <td>{{ t.remarks || '-' }}</td>
            </tr>
          </tbody>
        </table>
      </div>

    </div>
  </AuthenticatedLayout>
</template>
