import {expect, test} from './fixtures.js'

test('closing a toast by hand keeps the other toasts when its timer fires', async ({page}) => {
    await page.goto('/admin')
    const toasts = page.locator('.toast-container .toast-item')

    await page.evaluate(() => MoonShine.ui.toast('E2E short toast', 'success', 1500))
    await expect(toasts.filter({hasText: 'E2E short toast'})).toBeVisible()
    await page.evaluate(() => MoonShine.ui.toast('E2E long toast', 'info', 8000))
    await expect(toasts.filter({hasText: 'E2E long toast'})).toBeVisible()

    await toasts.filter({hasText: 'E2E short toast'}).click()
    await expect(toasts.filter({hasText: 'E2E short toast'})).toBeHidden()

    // The closed toast's own timer fires at 1.5 s; the long toast must outlive it.
    await page.waitForTimeout(2000)
    await expect(toasts.filter({hasText: 'E2E long toast'})).toBeVisible()
})

test('toasts shown in the same millisecond are all displayed', async ({page}) => {
    await page.goto('/admin')
    const toasts = page.locator('.toast-container .toast-item')

    await page.evaluate(() => {
        const now = Date.now()
        const original = Date.now
        Date.now = () => now
        try {
            MoonShine.ui.toast('E2E first same-time toast', 'success', 0)
            MoonShine.ui.toast('E2E second same-time toast', 'error', 0)
        } finally {
            Date.now = original
        }
    })

    await expect(toasts.filter({hasText: 'E2E first same-time toast'})).toBeVisible()
    await expect(toasts.filter({hasText: 'E2E second same-time toast'})).toBeVisible()
})
