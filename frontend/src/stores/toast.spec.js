import { describe, it, expect, beforeEach } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useToastStore } from './toast'

describe('useToastStore', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  it('ajoute un toast success', () => {
    const store = useToastStore()
    store.success('Opération réussie')
    expect(store.items).toHaveLength(1)
    expect(store.items[0].type).toBe('success')
    expect(store.items[0].message).toBe('Opération réussie')
  })

  it('ajoute un toast error', () => {
    const store = useToastStore()
    store.error('Erreur')
    expect(store.items[0].type).toBe('error')
  })

  it('supprime un toast par id', () => {
    const store = useToastStore()
    const id = store.info('Info')
    store.remove(id)
    expect(store.items).toHaveLength(0)
  })
})
