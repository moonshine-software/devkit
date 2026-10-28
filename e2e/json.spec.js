import {chooseOption, expect, test} from './fixtures.js'

const jsonForm = (page, type, filled = false) => page.locator(`main form:has(input[name="_type"][value="${type}"])`).nth(filled ? 1 : 0)
const field = (form, name) => form.locator(`[name="${name}"]`)
const rowOf = input => input.locator('xpath=ancestor::tr[1]')

const apply = async (page, form) => {
    const [response] = await Promise.all([
        page.waitForResponse(response => response.url().includes('/method/json-page') && response.request().method() === 'POST'),
        form.getByRole('button', {name: 'Save', exact: true}).click(),
    ])
    expect(response.status()).toBe(200)
    await expect(page.locator('.after-apply-json code')).toBeVisible()
    // Wait for the actual response HTML to reach the document before reading the result.
    const update = (await response.json()).htmlData.find(update => update.selector === '.after-apply-json')
    await expect.poll(async () => page.locator('.after-apply-json').innerHTML()).toBe(update.html)
    return JSON.parse(await page.locator('.after-apply-json').innerText())
}

test.beforeEach(async ({page}) => {
    await page.goto('/admin/page/json-page', {waitUntil: 'domcontentloaded'})
})

test('JSON key/value rows retain their keys and values after deleting the middle row', async ({page}) => {
    const form = jsonForm(page, 'key-value')
    for (const [key, value] of [['alpha', 'One'], ['remove', 'Two'], ['omega', 'Three']]) {
        await form.getByRole('link', {name: 'Add', exact: true}).click()
        await form.locator('input[name$="[key]"]').last().fill(key)
        await form.locator('input[name$="[value]"]').last().fill(value)
    }
    await rowOf(form.locator('input[name$="[key]"]').nth(1)).locator('a.btn-error').click()
    expect((await apply(page, form)).data).toEqual({alpha: 'One', omega: 'Three'})
})

test('JSON onlyValue reindexes values and submits an empty collection after the last removal', async ({page}) => {
    const form = jsonForm(page, 'only-value')
    for (const value of ['One', 'Two', 'Three']) {
        await form.getByRole('link', {name: 'Add', exact: true}).click()
        await form.locator('input[name$="[value]"]').last().fill(value)
    }
    await rowOf(form.locator('input[name$="[value]"]').nth(1)).locator('a.btn-error').click()
    expect((await apply(page, form)).data).toEqual(['One', 'Three'])
    while (await form.locator('input[name$="[value]"]').count()) {
        await rowOf(form.locator('input[name$="[value]"]').first()).locator('a.btn-error').click()
    }
    expect((await apply(page, form)).data).toEqual([])
})

test('JSON object submits nested fields, decimal numbers and a switcher', async ({page}) => {
    const form = jsonForm(page, 'object')
    for (const [name, value] of [['data[title]', 'Object'], ['data[value]', 'Value'],
        ['data[inner][one]', 'Nested one'], ['data[inner][two]', 'Nested two'], ['data[inner][number]', '2.5']]) {
        await field(form, name).fill(value)
    }
    await form.locator('input[type="checkbox"][name="data[inner][active]"]').locator('xpath=ancestor::label[1]').click()
    expect((await apply(page, form)).data).toEqual({title: 'Object', value: 'Value', inner: {
        one: 'Nested one', two: 'Nested two', number: 2.5, active: 1,
    }})
})

test('JSON showWhenRow stays scoped to its own row after remove and add', async ({page}) => {
    const form = jsonForm(page, 'default')
    const add = form.getByRole('link', {name: 'Add', exact: true})
    await add.click()
    await add.click()
    const titles = form.locator('input[name$="[title]"]')
    for (const title of await titles.all()) {
        await title.fill('test')
        await title.press('Tab')
    }
    const values = form.locator('input[name$="[value]"]')
    await expect(values).toHaveCount(2)
    await values.nth(1).fill('Second row')
    await titles.first().fill('hidden')
    await titles.first().press('Tab')
    await expect(values).toHaveCount(1)
    await expect(values.first()).toHaveValue('Second row')
    await rowOf(titles.first()).locator('a.btn-error').click()
    await add.click()
    await expect(values.first()).toHaveValue('Second row')
    await expect(values.first()).toHaveAttribute('name', 'data[1][value]')
    await titles.first().fill('hidden')
    await titles.first().press('Tab')
    await expect(values).toHaveCount(0)
    expect(await form.evaluate(el => new FormData(el).get('data[1][value]'))).toBeNull()
    await titles.first().fill('test')
    await titles.first().press('Tab')
    await expect(values.first()).toHaveValue('Second row')
    await expect(values.first()).toHaveAttribute('name', 'data[1][value]')
    expect((await apply(page, form)).data[0]).toEqual({title: 'test', value: 'Second row'})
})

test('mixed JSON retains nested key/value data and multiple select values in separate rows', async ({page}) => {
    const form = jsonForm(page, 'mixed-default', true)
    await field(form, 'data[1][object][inner][kv][1][key]').fill('e2e_nested_key')
    await field(form, 'data[1][object][inner][kv][1][value]').fill('Nested value')
    const select = field(form, 'data[1][object][inner][multiple][]')
    await chooseOption(page, select, '1')
    await chooseOption(page, select, '3')
    const result = (await apply(page, form)).data
    expect(result).toHaveLength(2)
    expect(result[0].object.inner.kv.e2e_nested_key).toBe('Nested value')
    expect(result[0].object.inner.multiple).toEqual(['1', '3'])
    expect(result[1].object.inner.kv['key 1 2']).toBe('value 1 2')
})

test('JSON creation limit prevents a third row', async ({page}) => {
    const form = jsonForm(page, 'create-button-options')
    const add = form.getByRole('link', {name: 'Add', exact: true}).first()
    await add.click()
    await add.click()
    await expect(form.locator('input[name^="icon_only["][name$="[title]"]')).toHaveCount(2)
    await add.click()
    await expect(form.locator('input[name^="icon_only["][name$="[title]"]')).toHaveCount(2)
})

test('JSON custom remove action and vertical layout submit the visible values', async ({page}) => {
    const form = jsonForm(page, 'display-options')
    await field(form, 'vertical_orientation[1][title]').fill('Vertical changed')
    await form.getByRole('link', {name: 'Remove', exact: true}).click()
    const result = await apply(page, form)
    expect(result.vertical_orientation).toEqual([{title: 'Vertical changed', value: 'Fields are stacked'}])
    expect(result.custom_buttons).toEqual([])
    expect(result.reorderable.map(row => row.title)).toEqual(['First', 'Second'])
})

test('JSON reorder submits the new order and reindexed names', async ({page}) => {
    const form = jsonForm(page, 'display-options')
    const first = rowOf(field(form, 'reorderable[1][title]'))
    const second = rowOf(field(form, 'reorderable[2][title]'))
    await first.locator('.handle').scrollIntoViewIfNeeded()
    const handle = await first.locator('.handle').boundingBox()
    const target = await second.boundingBox()
    await page.mouse.move(handle.x + handle.width / 2, handle.y + handle.height / 2)
    await page.mouse.down()
    await page.mouse.move(handle.x + handle.width / 2, target.y + target.height - 2, {steps: 15})
    await page.mouse.up()
    await expect(form.locator('input[name^="reorderable["][name$="[title]"]').first()).toHaveValue('Second')
    expect((await apply(page, form)).reorderable.map(row => row.title)).toEqual(['Second', 'First'])
})

test('nested JSON key/value fields persist through a real comment save and reload', async ({page}) => {
    const url = '/admin/resource/comment-resource/comment-form-page/1'
    await page.goto(url, {waitUntil: 'domcontentloaded'})
    const form = page.locator('main form[data-component="comment-resource"]')
    const json = form.locator('[data-field-selector="data"]').first()
    await json.getByRole('link', {name: 'Add', exact: true}).last().click()
    await field(form, 'data[1][title]').fill('E2E persisted JSON')
    await json.getByRole('link', {name: 'Add', exact: true}).first().click()
    await field(form, 'data[1][kv][1][key]').fill('e2e_key')
    await field(form, 'data[1][kv][1][value]').fill('Persisted value')
    const save = async () => {
        const [response] = await Promise.all([
            page.waitForResponse(response => response.url().includes('/comment-resource/crud/1') && response.request().method() === 'POST'),
            form.getByRole('button', {name: 'Save', exact: true}).click(),
        ])
        expect(response.status()).toBe(200)
    }
    await save()
    await page.goto(url, {waitUntil: 'domcontentloaded'})
    await expect(field(form, 'data[1][title]')).toHaveValue('E2E persisted JSON')
    await expect(field(form, 'data[1][kv][1][key]')).toHaveValue('e2e_key')
    await expect(field(form, 'data[1][kv][1][value]')).toHaveValue('Persisted value')
    await field(form, 'data[1][title]').fill('E2E updated JSON')
    await field(form, 'data[1][kv][1][value]').fill('Updated value')
    await save()
    await page.goto(url, {waitUntil: 'domcontentloaded'})
    await expect(field(form, 'data[1][title]')).toHaveValue('E2E updated JSON')
    await expect(field(form, 'data[1][kv][1][value]')).toHaveValue('Updated value')
})
