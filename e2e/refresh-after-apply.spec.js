import {expect, test} from './fixtures.js'

test('refreshAfterApply displays the saved value and keeps the form usable', async ({page}) => {
    await page.goto('/admin/resource/refresh-after-apply-resource/form-page/1')

    const form = page.locator('main form').filter({has: page.locator('input[name="name"]')})
    const name = form.locator('input[name="name"]')
    const original = await name.inputValue()

    try {
        await name.fill('  refreshed name  ')

        const saved = page.waitForResponse(response => response.request().method() === 'POST'
            && response.url().includes('refresh-after-apply-resource'))
        await form.locator('button[type="submit"]').click()
        expect((await saved).status()).toBe(200)
        await expect(name).toHaveValue('REFRESHED NAME')

        await name.fill('  refreshed again  ')
        const savedAgain = page.waitForResponse(response => response.request().method() === 'POST'
            && response.url().includes('refresh-after-apply-resource'))
        await form.locator('button[type="submit"]').click()
        expect((await savedAgain).status()).toBe(200)
        await expect(name).toHaveValue('REFRESHED AGAIN')

        await page.reload()
        await expect(name).toHaveValue('REFRESHED AGAIN')
    } finally {
        await page.goto('/admin/resource/tag-resource/tag-form-page/1')
        const tagForm = page.locator('main form[data-component="tag-resource"]')
        await tagForm.locator('input[name="name"]').fill(original)
        const restored = page.waitForResponse(response => response.request().method() === 'POST'
            && response.url().includes('/tag-resource/crud/1'))
        await tagForm.getByRole('button', {name: 'Save', exact: true}).click()
        expect((await restored).status()).toBe(200)
        await page.reload()
        await expect(tagForm.locator('input[name="name"]')).toHaveValue(original)
    }
})
