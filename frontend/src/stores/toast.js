import { defineStore } from 'pinia'
import { ref } from 'vue'

let nextId = 1

export const useToastStore = defineStore('toast', () => {
  const items = ref([])

  function push(message, type = 'success', durationMs = 4500) {
    const id = nextId++
    items.value.push({ id, message, type })
    if (durationMs > 0) {
      setTimeout(() => remove(id), durationMs)
    }
    return id
  }

  function remove(id) {
    items.value = items.value.filter((t) => t.id !== id)
  }

  function success(message, durationMs) {
    return push(message, 'success', durationMs)
  }

  function error(message, durationMs) {
    return push(message, 'error', durationMs ?? 6000)
  }

  function info(message, durationMs) {
    return push(message, 'info', durationMs)
  }

  return { items, push, remove, success, error, info }
})
