<script setup>
import { ref } from 'vue'
import axios from 'axios'

const emit = defineEmits(['verified'])

const pin = ref('')
const error = ref(null)
const loading = ref(false)

const verify = async () => {
  error.value = null
  loading.value = true

  try {
    await axios.post(route('pin.verify'), {
      pin: pin.value,
    })

    emit('verified')
  } catch (e) {
    error.value = 'Invalid PIN'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white rounded-lg p-6 w-80 space-y-4">
      <h2 class="text-lg font-semibold text-center">Enter PIN</h2>

      <input
        v-model="pin"
        type="password"
        maxlength="6"
        class="w-full border rounded px-3 py-2 text-center"
        placeholder="••••"
      />

      <p v-if="error" class="text-red-600 text-sm text-center">
        {{ error }}
      </p>

      <button
        @click="verify"
        :disabled="loading"
        class="w-full bg-indigo-600 text-white py-2 rounded"
      >
        Verify
      </button>
    </div>
  </div>
</template>
