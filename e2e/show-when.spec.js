import {chooseOption, expect, test} from './fixtures.js'

test.beforeEach(async ({page}) => {
    await page.goto('/admin/page/show-when', {waitUntil: 'domcontentloaded'})
})

test('nested JSON object conditions update in both directions', async ({page}) => {
    const innerTitle = page.locator('main input[name="object[inner_object][inner_title]"]')
    const title = page.locator('main input[name="object[title]"]')
    const value = page.locator('main input[name="object[value]"]')
    const innerValue = page.locator('main input[name="object[inner_object][inner_value]"]')
    for (const visible of [true, false, true]) {
        await innerTitle.fill(visible ? 'test' : 'other')
        await innerTitle.press('Tab')
        await expect(title).toBeVisible({visible})
        await value.fill(visible ? 'test' : 'other')
        await value.press('Tab')
        await expect(innerValue).toBeVisible({visible})
    }
})

test('date boundaries and numeric range conditions hide and restore dependent controls', async ({page}) => {
    const date = page.locator('main input[name="date"]')
    const title = page.locator('main input[name="title"]')
    for (const [value, visible] of [['2025-01-01', false], ['2025-06-15', true], ['2026-01-01', false], ['2025-06-15', true]]) {
        await date.fill(value)
        await date.press('Tab')
        await expect(title).toBeVisible({visible})
    }
    await title.fill('test')
    await title.press('Tab')
    for (const [name, value] of [['date_range[from]', '2025-06-01'], ['date_range[to]', '2025-08-01'],
        ['range[from]', '4'], ['range[to]', '4']]) {
        const input = page.locator(`main input[name="${name}"]`)
        await input.fill(value)
        await input.press('Tab')
    }
    const slider = page.locator('main [data-field-selector="range_slider"]').first()
    await expect(slider).toBeVisible()
    await page.locator('main input[name="range[from]"]').fill('2')
    await page.locator('main input[name="range[from]"]').press('Tab')
    await expect(slider).toBeHidden()
})

test('multiple select inclusion controls nested JSON rows independently', async ({page}) => {
    const date = page.locator('main input[name="date"]')
    await date.fill('2025-06-15')
    await date.press('Tab')
    await page.locator('main input[name="title"]').fill('test')
    await page.locator('main input[name="title"]').press('Tab')
    const section = page.locator('main select[name="section[]"]')
    const dependent = page.locator('main select[name="select[]"]').locator('..')
    await expect(dependent).toBeHidden()
    await chooseOption(page, section, '2')
    await expect(dependent).toBeVisible()

    const json = page.locator('main [data-field-selector="json"]').first()
    await json.getByRole('link', {name: 'Add', exact: true}).click()
    await json.getByRole('link', {name: 'Add', exact: true}).click()
    const rowSelects = page.locator('main select[name^="json["][name$="[section2][]"]')
    const childSelects = page.locator('main select[name^="json["][name$="[section3][]"]')
    await expect(rowSelects).toHaveCount(2)
    await chooseOption(page, rowSelects.first(), '2')
    await expect(childSelects.first().locator('..')).toBeVisible()
    await expect(childSelects.nth(1).locator('..')).toBeHidden()
})

test('cross-row showWhen follows the referenced row after adding JSON records', async ({page}) => {
    const json = page.locator('main [data-field-selector="data"]').first()
    await json.getByRole('link', {name: 'Add', exact: true}).last().click()
    await json.getByRole('link', {name: 'Add', exact: true}).last().click()
    const trigger = page.locator('main input[name="data[1][title]"]')
    const secondRow = page.locator('main input[name="data[2][title]"]').locator('xpath=ancestor::tr[1]')
    const nested = secondRow.getByRole('link', {name: 'Add', exact: true})
    await trigger.fill('test')
    await trigger.press('Tab')
    await expect(json.getByRole('link', {name: 'Add', exact: true})).toHaveCount(3)
    // Both row-local nested KV editors follow the explicitly referenced first row.
    await expect(nested).toBeVisible()
    await trigger.fill('other')
    await trigger.press('Tab')
    await expect(nested).toBeHidden()
})
