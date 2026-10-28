import {expect, test as base} from '@playwright/test'

export const XSS_MARKER = 'E2E-XSS'
export const EDITABLE_USER_EMAIL = 'e2e-user@example.test'

export const listTable = (page, resource) => page.locator(
    `main table[data-name="index-table-${resource}-resource"]:not([data-skeleton]):visible`,
)

export const selectControl = select => select.locator('xpath=following-sibling::*[contains(@class, "ts-wrapper")][1]').locator('.ts-control')
export const selectCombobox = select => selectControl(select).locator('xpath=self::*[@aria-controls] | .//*[@aria-controls]').first()

export const chooseOption = async (page, select, value) => {
    if (await select.evaluate((el, selected) => [...el.selectedOptions].some(option => option.value === selected), value)) {
        return
    }
    const control = selectControl(select)
    const combobox = selectCombobox(select)
    await expect(combobox).toBeAttached()
    if (await combobox.getAttribute('aria-expanded') !== 'true') {
        await control.click()
    }
    const dropdown = await combobox.getAttribute('aria-controls')
    await page.locator(`[id=${JSON.stringify(dropdown)}] .option[data-value=${JSON.stringify(value)}]:visible`).click()
}

/**
 * Fails the test on uncaught page errors, server errors, and executed XSS payloads from E2eSeeder.
 */
export const test = base.extend({
    page: async ({page}, use, testInfo) => {
        const problems = []
        const consoleMessages = []
        const asyncRequests = []

        page.on('pageerror', error => problems.push(`page error: ${error.message}`))
        page.on('response', response => {
            if (response.status() >= 500) {
                problems.push(`${response.status()} ${response.url()}`)
            }
            if (['xhr', 'fetch'].includes(response.request().resourceType())) {
                asyncRequests.push({method: response.request().method(), status: response.status(), url: response.url()})
            }
        })
        page.on('console', message => {
            if (['warning', 'error'].includes(message.type())) {
                consoleMessages.push({type: message.type(), text: message.text()})
            }
            if (message.text().includes(XSS_MARKER)) {
                problems.push(`executed payload: ${message.text()}`)
            }
        })

        await use(page)

        if (consoleMessages.length) {
            await testInfo.attach('browser-console', {body: JSON.stringify(consoleMessages, null, 2), contentType: 'application/json'})
        }
        if (asyncRequests.length) {
            await testInfo.attach('async-requests', {body: JSON.stringify(asyncRequests, null, 2), contentType: 'application/json'})
        }

        expect(problems).toEqual([])
    },
})

export {expect}
