import {expect, test as base} from '@playwright/test'

export const XSS_MARKER = 'E2E-XSS'
export const EDITABLE_USER_EMAIL = 'e2e-user@example.test'

export const listTable = (page, resource) => page.locator(
    `main table[data-name="index-table-${resource}-resource"]:not([data-skeleton]):visible`,
)

export const chooseOption = async (page, select, value) => {
    await select.locator('..').locator('.ts-control').click()
    await page.locator(`.ts-dropdown .option[data-value=${JSON.stringify(value)}]:visible`).click()
}

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
