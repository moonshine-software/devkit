import {EDITABLE_USER_EMAIL, expect, listTable, test} from './fixtures.js'

test('saving an unchanged text field preserves its HTML entities', async ({page}) => {
    await page.goto('/admin/resource/user-resource/user-index-page?u_reset=1&u_sort=id')
    const row = listTable(page, 'user').locator(':scope > tbody > tr', {hasText: EDITABLE_USER_EMAIL})
    const editUrl = await row.locator('a[href*="/user-form-page/"]').getAttribute('href')
    await page.goto(editUrl)
    const form = page.locator('main form:has(input[name="name"])').first()
    const name = form.locator('input[name="name"]')
    const original = await name.inputValue()

    const save = async () => {
        const [response] = await Promise.all([
            page.waitForResponse(response => response.url().includes('/user-resource/crud/')
                && response.request().method() === 'POST'),
            form.getByRole('button', {name: 'Save', exact: true}).click(),
        ])
        expect(response.status()).toBe(200)
        await page.goto(editUrl)
    }

    try {
        await name.fill('Tom & Jerry')
        await save()
        await expect(name).toHaveValue('Tom &amp; Jerry')

        await save()
        await expect(name).toHaveValue('Tom &amp; Jerry')
    } finally {
        await name.fill(original)
        await save()
    }
})
