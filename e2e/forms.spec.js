import {chooseOption, expect, test} from './fixtures.js'

for (const reset of ['Default reset', 'Event reset']) {
    test(`select ${reset.toLowerCase()} preserves the option label`, async ({page}) => {
        await page.goto('/admin/page/selects')
        const form = page.locator('main form[data-component="reset-form"]')
        const select = form.locator('select')
        const control = form.locator('.ts-control')
        await expect(select).toHaveValue('3')
        await expect(control).toContainText('Italy')

        await chooseOption(page, select, '1')
        await expect(select).toHaveValue('1')
        await expect(control).toContainText('France')
        await form.getByRole(reset === 'Default reset' ? 'button' : 'link', {name: reset, exact: true}).click()

        await expect(select).toHaveValue('3')
        await expect(control).toContainText('Italy')
        await expect(control).not.toContainText('undefined')
    })
}

test('showWhen updates date and nested text conditions on change', async ({page}) => {
    await page.goto('/admin/page/show-when')
    const title = page.locator('main input[name="title"]')
    const subtitle = page.locator('main input[name="somewrap[sub_title]"]')
    const nested = page.locator('main input[name="somewrap[sub_title_2]"]')
    await expect(title).toBeHidden()

    await page.locator('main input[name="date"]').fill('2025-06-15')
    await page.locator('main input[name="date"]').press('Tab')
    await expect(title).toBeVisible()
    await title.fill('test')
    await title.press('Tab')
    await expect(subtitle).toBeVisible()
    await subtitle.fill('test')
    await subtitle.press('Tab')
    await expect(nested).toBeVisible()

    await subtitle.fill('other')
    await subtitle.press('Tab')
    await expect(nested).toBeHidden()
    await title.fill('other')
    await title.press('Tab')
    await expect(subtitle).toBeHidden()
    await page.locator('main input[name="date"]').fill('2026-06-15')
    await page.locator('main input[name="date"]').press('Tab')
    await expect(title).toBeHidden()
})

test('JSON rows can be added, applied asynchronously and removed', async ({page}) => {
    await page.goto('/admin/page/json-page')
    const form = page.locator('main form').first()
    const titles = form.locator('input[name$="[title]"]')
    await expect(titles).toHaveCount(0)
    await form.getByRole('link', {name: 'Add', exact: true}).click()
    await expect(titles).toHaveCount(1)
    await titles.first().fill('E2E JSON row')

    const apply = async () => {
        const [response] = await Promise.all([
            page.waitForResponse(response => response.url().includes('/method/json-page')
                && response.request().method() === 'POST'),
            form.getByRole('button', {name: 'Save', exact: true}).click(),
        ])
        expect(response.status()).toBe(200)
    }

    await apply()
    await expect(page.locator('.after-apply-json')).toContainText('E2E JSON row')
    expect(JSON.parse(await page.locator('.after-apply-json').innerText()).data)
        .toEqual([{title: 'E2E JSON row', value: ''}])

    await form.locator('a.btn-error').click()
    await expect(titles).toHaveCount(0)
    await apply()
    await expect(page.locator('.after-apply-json')).not.toContainText('E2E JSON row')
})

test('async validation displays server errors beside the fields', async ({page}) => {
    await page.goto('/admin/page/forms')
    const form = page.locator('main form[data-component="default"]').first()
    await chooseOption(page, form.locator('select'), 'value 1')

    const [response] = await Promise.all([
        page.waitForResponse(response => response.url().includes('/method/forms')
            && response.request().method() === 'POST'),
        form.getByRole('button', {name: 'Click me', exact: true}).click(),
    ])

    expect(response.status()).toBe(422)
    const body = await response.json()
    expect(body.errors).toHaveProperty('text_1')
    await expect(form.getByText(body.errors.text_1[0], {exact: true})).toBeVisible()
})

test('stepper switches the visible content', async ({page}) => {
    await page.goto('/admin/page/forms')
    const form = page.locator('main form[data-component="default"]').first()
    await expect(form.getByText('Step 1 content', {exact: true})).toBeVisible()
    await form.getByRole('link', {name: 'Next', exact: true}).click()
    await expect(form.getByText('Step 2 content', {exact: true})).toBeVisible()
    await expect(form.getByText('Step 1 content', {exact: true})).toBeHidden()
})

test('async tabs load and render their HTML content', async ({page}) => {
    await page.goto('/admin/page/forms')
    const form = page.locator('main form[data-component="default"]').first()
    const [response] = await Promise.all([
        page.waitForResponse(response => new URL(response.url()).pathname === '/html'),
        form.getByText('Tab 2', {exact: true}).first().click(),
    ])

    expect(response.status()).toBe(200)
    await expect(form.locator('b:visible', {hasText: 'Hello'})).toHaveCount(1)
})
