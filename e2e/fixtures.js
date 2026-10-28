import {expect, test as base} from '@playwright/test'

export const XSS_MARKER = 'E2E-XSS'

/**
 * Fails the test on uncaught page errors, server errors, and executed XSS payloads from E2eSeeder.
 */
export const test = base.extend({
    page: async ({page}, use) => {
        const problems = []

        page.on('pageerror', error => problems.push(`page error: ${error.message}`))
        page.on('response', response => {
            if (response.status() >= 500) {
                problems.push(`${response.status()} ${response.url()}`)
            }
        })
        page.on('console', message => {
            if (message.text().includes(XSS_MARKER)) {
                problems.push(`executed payload: ${message.text()}`)
            }
        })

        await use(page)

        expect(problems).toEqual([])
    },
})

export {expect}
