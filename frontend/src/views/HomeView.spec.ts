import { describe, expect, it } from 'vitest'
import { mount } from '@vue/test-utils'
import HomeView from './HomeView.vue'

describe('HomeView', () => {
    it('renders the app name', () => {
        expect(mount(HomeView).text()).toContain('JuriHR')
    })
})
