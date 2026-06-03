import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import StatusBadge from './StatusBadge.vue'

describe('StatusBadge', () => {
  it('affiche le libellé pour pending', () => {
    const wrapper = mount(StatusBadge, { props: { status: 'pending' } })
    expect(wrapper.text()).toContain('En attente')
  })

  it('affiche le libellé pour approved', () => {
    const wrapper = mount(StatusBadge, { props: { status: 'approved' } })
    expect(wrapper.text()).toContain('Approuvée')
  })

  it('affiche le libellé pour refused', () => {
    const wrapper = mount(StatusBadge, { props: { status: 'refused' } })
    expect(wrapper.text()).toContain('Refusée')
  })
})
