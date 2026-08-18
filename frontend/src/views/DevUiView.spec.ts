import { describe, expect, it } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { mount } from '@vue/test-utils'
import DevUiView from './DevUiView.vue'

describe('DevUiView', () => {
  it('renders every UI kit component without errors', () => {
    setActivePinia(createPinia())
    const wrapper = mount(DevUiView)

    expect(wrapper.text()).toContain('UI Kit')
    expect(wrapper.findAll('table')).toHaveLength(1)
    expect(wrapper.text()).toContain('Terlambat')
  })
})
