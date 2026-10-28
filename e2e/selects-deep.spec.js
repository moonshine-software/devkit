import {chooseOption, expect, selectCombobox, selectControl, test} from './fixtures.js'

const box = (page, title) => page.getByRole('heading', {name: title, exact: true}).first()
    .locator('xpath=ancestor::div[contains(concat(" ", normalize-space(@class), " "), " box ")][1]')

for (const [group, label] of [['Default', 'Russia'], ['Group', 'Saint-Petersburg']]) {
    test(`select ${group}: native and enhanced selections keep the displayed label`, async ({page}) => {
        await page.goto('/admin/page/selects', {waitUntil: 'domcontentloaded'})
        const container = box(page, group)
        const enhanced = container.locator('select').nth(0)
        await chooseOption(page, enhanced, '2')
        await expect(enhanced).toHaveValue('2')
        await expect(selectControl(enhanced)).toContainText(label)
        const native = container.locator('select.form-select').last()
        await native.selectOption('4')
        await expect(native).toHaveValue('4')
    })
}

test('multiple select adds and removes options without losing another item', async ({page}) => {
    await page.goto('/admin/page/selects', {waitUntil: 'domcontentloaded'})
    const select = box(page, 'Default').locator('select').nth(1)
    await chooseOption(page, select, '1')
    await chooseOption(page, select, '4')
    await expect.poll(() => select.evaluate(el => [...el.selectedOptions].map(o => o.value).sort())).toEqual(['1', '3', '4'])
    await selectControl(select).locator('.item[data-value="1"] .remove').click()
    await expect.poll(() => select.evaluate(el => [...el.selectedOptions].map(o => o.value).sort())).toEqual(['3', '4'])
    await expect(selectControl(select)).not.toContainText('undefined')
})

for (const index of [0, 1, 2]) {
    test(`async select variant ${index + 1} loads options and preserves a chosen label`, async ({page}) => {
        await page.goto('/admin/page/selects', {waitUntil: 'domcontentloaded'})
        const select = box(page, 'Async').locator('select').nth(index)
        await selectControl(select).click()
        if (index === 0) await selectControl(select).locator('input').fill('a')
        const dropdown = await selectCombobox(select).getAttribute('aria-controls')
        const option = page.locator(`[id="${dropdown}"] .option[data-value="1"]`)
        await expect(option).toBeVisible()
        const label = (await option.innerText()).trim()
        expect(['France', 'Moscow']).toContain(label)
        await option.click()
        await expect(select).toHaveValue('1')
        await expect(selectControl(select)).toContainText(label)
        await page.keyboard.press('Escape')
        await selectControl(select).click()
        await expect(selectControl(select)).toContainText(label)
    })
}

test('associated async belongsTo clears an incompatible mechanic and saves the new association', async ({page}) => {
    const url = '/admin/resource/car-resource/car-form-page/1'
    const shop = page.locator('main select[name="shop_id"]')
    const mechanic = page.locator('main select[name="mechanic_id"]')
    const chooseShop = async id => {
        await selectControl(shop).click()
        await selectControl(shop).locator('input').fill('E2E shop')
        await chooseOption(page, shop, id)
    }
    const chooseMechanic = async id => {
        await selectControl(mechanic).click()
        await selectControl(mechanic).locator('input').fill('E2E mechanic')
        await chooseOption(page, mechanic, id)
    }
    const save = async () => {
        const [response] = await Promise.all([
            page.waitForResponse(response => response.url().includes('/car-resource/crud/1') && response.request().method() === 'POST'),
            page.locator('main form[data-component="car-resource"]').getByRole('button', {name: 'Save', exact: true}).click(),
        ])
        expect(response.status()).toBe(200)
    }
    await page.goto(url, {waitUntil: 'domcontentloaded'})
    await expect(mechanic).toHaveValue('1')
    try {
        await chooseShop('2')
        await expect(mechanic).toHaveValue('')
        await chooseMechanic('2')
        await expect(selectControl(mechanic)).toContainText('E2E mechanic 2')
        await save()
        await page.goto(url, {waitUntil: 'domcontentloaded'})
        await expect(shop).toHaveValue('2')
        await expect(mechanic).toHaveValue('2')
    } finally {
        await page.goto(url, {waitUntil: 'domcontentloaded'})
        await chooseShop('1')
        await chooseMechanic('1')
        await save()
    }
})

test('REST resource async role select persists through the backend API', async ({page}) => {
    const url = '/admin/resource/test-rest-user-resource/test-rest-user-form-page/1'
    await page.goto(url, {waitUntil: 'domcontentloaded'})
    const role = page.locator('main select[name="role_id"]')
    await chooseOption(page, role, '2')
    const [response] = await Promise.all([
        page.waitForResponse(response => response.url().includes('/test-rest-user-resource/crud/1') && response.request().method() === 'POST'),
        page.locator('main form[data-component="test-rest-user-resource"]').getByRole('button', {name: 'Save', exact: true}).click(),
    ])
    expect(response.status()).toBe(200)
    await page.goto(url, {waitUntil: 'domcontentloaded'})
    await expect(role).toHaveValue('2')
    await expect(selectControl(role)).toContainText('Manager')
})
