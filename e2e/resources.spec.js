import {EDITABLE_USER_EMAIL, expect, listTable, test} from './fixtures.js'

test('user form validates required fields and persists an update', async ({page}) => {
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
    }

    await name.fill('')
    await form.getByRole('button', {name: 'Save', exact: true}).click()
    expect(await name.evaluate(input => input.validity.valueMissing)).toBe(true)

    try {
        await name.fill('E2E updated user')
        await save()
        await page.goto(editUrl)
        await expect(name).toHaveValue('E2E updated user')
        await expect(form.locator('input[name="email"]')).toHaveValue(EDITABLE_USER_EMAIL)
    } finally {
        await page.goto(editUrl)
        await name.fill(original)
        await save()
    }
})

test('tag can be created, deleted, restored and permanently deleted', async ({page}) => {
    await page.goto('/admin/resource/tag-resource/tag-form-page')
    const name = 'E2E CRUD tag'
    await page.locator('main input[name="name"]').fill(name)
    const [created] = await Promise.all([
        page.waitForResponse(response => response.url().includes('/tag-resource/crud')
            && response.request().method() === 'POST'),
        page.getByRole('button', {name: 'Save', exact: true}).click(),
    ])
    expect(created.status()).toBe(201)
    await expect(page).toHaveURL(/\/tag-form-page\/\d+$/)
    const id = new URL(page.url()).pathname.split('/').at(-1)
    await expect(page.locator('main input[name="name"]')).toHaveValue(name)

    await page.goto('/admin/resource/tag-resource/tag-index-page')
    const row = listTable(page, 'tag').locator(`:scope > tbody > tr[data-row-key="${id}"]`)
    await expect(row).toContainText(name)
    await row.locator(`a[href*="/tag-resource/crud/${id}"]`).click()
    const [deleted] = await Promise.all([
        page.waitForResponse(response => response.url().includes(`/tag-resource/crud/${id}`)
            && response.request().method() === 'POST'),
        page.locator('.modal:visible').getByRole('button', {name: 'Confirm', exact: true}).click(),
    ])
    expect(deleted.status()).toBe(200)
    // TagResource intentionally includes trashed records; the available actions change.
    await expect(row.getByRole('link', {name: 'Restore', exact: true})).toBeVisible()

    for (const [label, method] of [['Restore', 'restore'], ['Soft delete', 'softDelete']]) {
        await row.getByRole('link', {name: label, exact: true}).click()
        const [response] = await Promise.all([
            page.waitForResponse(response => response.url().includes('/method/')
                && new URL(response.url()).searchParams.get('method') === method),
            page.locator('.modal:visible').getByRole('button', {name: 'Confirm', exact: true}).click(),
        ])
        expect(response.status()).toBe(200)
        await expect(row.getByRole('link', {name: label === 'Restore' ? 'Soft delete' : 'Restore', exact: true})).toBeVisible()
    }

    const [removed] = await Promise.all([
        page.waitForResponse(response => response.url().includes('/method/')
            && new URL(response.url()).searchParams.get('method') === 'forceDelete'),
        row.getByRole('link', {name: 'Force delete', exact: true}).click(),
    ])
    expect(removed.status()).toBe(200)
    await expect(row).toHaveCount(0)
    await page.reload()
    await expect(row).toHaveCount(0)
})

test('HasMany opens the child creation form in a modal', async ({page}) => {
    await page.goto('/admin/resource/post-resource/post-form-page/1')
    const [response] = await Promise.all([
        page.waitForResponse(response => response.url().includes('/has-many/form/')),
        page.locator('main a[href*="/has-many/form/"]:not([href*="_key="])').click(),
    ])
    expect(response.status()).toBe(200)
    const modal = page.locator('.modal:visible')
    await expect(modal.locator('select[name="user_id"]')).toBeVisible()
    await expect(modal.locator('textarea[name="text"]')).toBeVisible()
    await modal.locator('.modal-close').click()
    await expect(modal).toBeHidden()
})

test('project tags persist when attached and detached', async ({page}) => {
    const url = '/admin/resource/project-resource/project-form-page/1'
    const row = page.locator('main tr', {hasText: 'E2E relation tag'})
    const checkbox = row.locator('.js-pivot-checker')
    const save = async () => {
        const [response] = await Promise.all([
            page.waitForResponse(response => response.url().includes('/project-resource/crud/1')
                && response.request().method() === 'POST'),
            page.locator('form[data-component="project-resource"]').getByRole('button', {name: 'Save', exact: true}).click(),
        ])
        expect(response.status()).toBe(200)
        await page.goto(url)
    }

    await page.goto(url)
    await expect(checkbox).not.toBeChecked()
    try {
        await checkbox.check()
        await save()
        await expect(checkbox).toBeChecked()
    } finally {
        await page.goto(url)
        await checkbox.uncheck()
        await save()
    }
    await expect(checkbox).not.toBeChecked()
})

test('post pivot field persists after saving the form', async ({page}) => {
    const url = '/admin/resource/post-resource/post-form-page/1'
    await page.goto(url)
    const row = page.locator('main tr', {hasText: 'E2E attached'})
    const pivot = row.locator('input[name*="pivot_field"]')
    await pivot.fill('E2E pivot roundtrip')

    const [response] = await Promise.all([
        page.waitForResponse(response => response.url().includes('/post-resource/crud/1')
            && response.request().method() === 'POST'),
        page.getByRole('button', {name: 'Save', exact: true}).click(),
    ])
    expect(response.status()).toBe(200)
    await page.goto(url)
    await expect(pivot).toHaveValue('E2E pivot roundtrip')
})
