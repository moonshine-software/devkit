import {chooseOption, expect, selectControl, test} from './fixtures.js'

const saveResource = async (page, resource, id, form = page.locator(`main form[data-component="${resource}-resource"]`).first(), status = 200) => {
    const [response] = await Promise.all([
        page.waitForResponse(response => response.url().includes(`/${resource}-resource/crud/${id}`) && response.request().method() === 'POST'),
        form.getByRole('button', {name: 'Save', exact: true}).click(),
    ])
    expect(response.status()).toBe(status)
}

test('MorphTo switches its async search type, clears the old key and persists the new target', async ({page}) => {
    const url = '/admin/resource/poly-comment-resource/poly-comment-form-page/1'
    const type = page.locator('main select[name="commentable_type"]')
    const target = page.locator('main select[name="commentable_id"]')
    const selectTarget = async (model, label) => {
        await chooseOption(page, type, `App\\Models\\${model}`)
        await expect(target).toHaveValue('')
        await chooseOption(page, target, '1')
        await expect(selectControl(target)).toContainText(label)
    }
    await page.goto(url, {waitUntil: 'domcontentloaded'})
    await expect(type).toHaveValue('App\\Models\\Post')
    try {
        await selectTarget('Project', 'E2E project')
        await saveResource(page, 'poly-comment', 1)
        await page.goto(url, {waitUntil: 'domcontentloaded'})
        await expect(type).toHaveValue('App\\Models\\Project')
        await expect(target).toHaveValue('1')
        await expect(selectControl(target)).toContainText('E2E project')
    } finally {
        await page.goto(url, {waitUntil: 'domcontentloaded'})
        await selectTarget('Post', 'First post')
        await saveResource(page, 'poly-comment', 1)
    }
})

test('async multi relation select can attach and detach a tag after reload', async ({page}) => {
    const url = '/admin/resource/poly-comment-resource/poly-comment-form-page/1'
    await page.goto(url, {waitUntil: 'domcontentloaded'})
    const select = page.locator('main select[name="tags[]"]')
    const control = selectControl(select)
    await control.click()
    await control.locator('input').fill('E2E relation tag')
    const option = page.locator('.ts-dropdown .option:visible', {hasText: 'E2E relation tag'})
    await expect(option).toHaveCount(1)
    const id = await option.getAttribute('data-value')
    await option.click()
    await saveResource(page, 'poly-comment', 1)
    await page.goto(url, {waitUntil: 'domcontentloaded'})
    await expect(select).toHaveValues([id])
    await expect(control).toContainText('E2E relation tag')
    await control.locator('.item .remove').click()
    await saveResource(page, 'poly-comment', 1)
    await page.goto(url, {waitUntil: 'domcontentloaded'})
    await expect(select).toHaveValues([])
})

const openCommentModal = async page => {
    await page.locator('main a[href*="/has-many/form/"]:not([href*="_key="])').click()
    const modal = page.locator('.modal:visible')
    await expect(modal.locator('textarea[name="text"]')).toBeVisible()
    return modal
}

test('HasMany async modal reuses its loaded form without duplicating controls or losing the parent', async ({page}) => {
    await page.goto('/admin/resource/post-resource/post-form-page/1', {waitUntil: 'domcontentloaded'})
    for (let attempt = 0; attempt < 3; attempt++) {
        const modal = await openCommentModal(page)
        await expect(modal.locator('textarea[name="text"]')).toHaveValue(attempt === 0 ? '' : `Unsaved ${attempt - 1}`)
        await expect(modal.locator('select[name="user_id"]')).toHaveCount(1)
        await expect(modal.locator('input[type="hidden"][name="post_id"]')).toHaveValue('1')
        await modal.locator('textarea[name="text"]').fill(`Unsaved ${attempt}`)
        await modal.locator('.modal-close').click()
        await expect(modal).toBeHidden()
    }
})

test('HasMany async modal validates and creates two children without losing its parent or refresh listener', async ({page}) => {
    const url = '/admin/resource/post-resource/post-form-page/1'
    await page.goto(url, {waitUntil: 'domcontentloaded'})
    for (const text of ['E2E async child one', 'E2E async child two']) {
        const modal = await openCommentModal(page)
        const input = modal.locator('textarea[name="text"]')
        await modal.getByRole('button', {name: 'Save', exact: true}).click()
        expect(await input.evaluate(el => el.validity.valueMissing)).toBe(true)
        await chooseOption(page, modal.locator('select[name="user_id"]'), '1')
        await input.fill(text)
        const [saved, refreshed] = await Promise.all([
            page.waitForResponse(response => response.url().includes('/comment-resource/crud') && response.request().method() === 'POST'),
            page.waitForResponse(response => response.url().includes('/has-many/list/')),
            modal.getByRole('button', {name: 'Save', exact: true}).click(),
        ])
        expect(saved.status()).toBe(201)
        expect(refreshed.status()).toBe(200)
        await expect(modal).toBeHidden()
        await expect(page.locator('main table:visible')).toContainText([text])
    }
    await page.goto(url, {waitUntil: 'domcontentloaded'})
    await expect(page.locator('main')).toContainText('E2E async child one')
    await expect(page.locator('main')).toContainText('E2E async child two')
})

test('HasMany async modal edits the existing child and refreshes the correct row', async ({page}) => {
    const url = '/admin/resource/post-resource/post-form-page/1'
    await page.goto(url, {waitUntil: 'domcontentloaded'})
    const row = page.locator('main tr', {hasText: 'E2E child comment'})
        .filter({has: page.locator('a[href*="/has-many/form/"]')})
    const [loaded] = await Promise.all([
        page.waitForResponse(response => response.url().includes('/has-many/form/') && response.url().includes('_key=')),
        row.locator('a[href*="/has-many/form/"]').click(),
    ])
    expect(loaded.status()).toBe(200)
    const modal = page.locator('.modal:visible')
    await expect(modal.locator('textarea[name="text"]')).toHaveValue('E2E child comment')
    await modal.locator('textarea[name="text"]').fill('E2E edited child comment')
    const [saved, refreshed] = await Promise.all([
        page.waitForResponse(response => response.url().includes('/comment-resource/crud/') && response.request().method() === 'POST'),
        page.waitForResponse(response => response.url().includes('/has-many/list/')),
        modal.getByRole('button', {name: 'Save', exact: true}).click(),
    ])
    expect(saved.status()).toBe(200)
    expect(refreshed.status()).toBe(200)
    await expect(modal).toBeHidden()
    await expect(page.locator('main tr', {hasText: 'E2E edited child comment'}).last()).toBeVisible()
    await page.goto(url, {waitUntil: 'domcontentloaded'})
    await expect(page.locator('main')).toContainText('E2E edited child comment')
})

test('MorphOne child form creates and updates its image with both parent morph columns', async ({page}) => {
    const url = '/admin/resource/project-resource/project-form-page/1'
    await page.goto(url, {waitUntil: 'domcontentloaded'})
    const imageUrl = new URL('/vendor/moonshine/avatar.jpg', page.url()).href
    const form = page.locator('main form[data-component="image-resource"]')
    await expect(form.locator('input[name="imageable_type"]')).toHaveValue('App\\Models\\Project')
    await expect(form.locator('input[name="imageable_id"]')).toHaveValue('1')
    await form.locator('input[name="url"]').fill(imageUrl)
    const [created] = await Promise.all([
        page.waitForResponse(response => response.url().includes('/image-resource/crud') && response.request().method() === 'POST'),
        form.getByRole('button', {name: 'Save', exact: true}).click(),
    ])
    expect(created.status()).toBe(204)
    await page.goto(url, {waitUntil: 'domcontentloaded'})
    await expect(form.locator('input[name="url"]')).toHaveValue(imageUrl)
    const id = await form.locator('input[name="id"]').inputValue()
    await form.locator('input[name="url"]').fill(`${imageUrl}?e2e=updated`)
    await saveResource(page, 'image', id, form)
    await page.goto(`/admin/resource/image-resource/image-form-page/${id}`, {waitUntil: 'domcontentloaded'})
    await expect(page.locator('main input[name="url"]')).toHaveValue(`${imageUrl}?e2e=updated`)
    await expect(page.locator('main select[name="imageable_type"]')).toHaveValue('App\\Models\\Project')
    await expect(page.locator('main select[name="imageable_id"]')).toHaveValue('1')
})

test('MorphMany async modal preserves its parent type on repeated creates', async ({page}) => {
    const url = '/admin/resource/project-resource/project-form-page/1'
    await page.goto(url, {waitUntil: 'domcontentloaded'})
    const ids = []
    for (const text of ['E2E project morph child one', 'E2E project morph child two']) {
        await page.locator('main a[href*="/has-many/form/"]:not([href*="_key="])').click()
        const modal = page.locator('.modal:visible')
        await expect(modal.locator('textarea[name="text"]')).toBeVisible()
        await expect(modal.locator('input[type="hidden"][name="commentable_type"]')).toHaveValue('App\\Models\\Project')
        await expect(modal.locator('input[type="hidden"][name="commentable_id"]')).toHaveValue('1')
        await expect(modal.locator('textarea[name="text"]')).toHaveValue('')
        await modal.locator('textarea[name="text"]').fill(text)
        const [saved, refreshed] = await Promise.all([
            page.waitForResponse(response => response.url().includes('/poly-comment-resource/crud') && response.request().method() === 'POST'),
            page.waitForResponse(response => response.url().includes('/has-many/list/') && response.url().includes('polyComments')),
            modal.getByRole('button', {name: 'Save', exact: true}).click(),
        ])
        expect(saved.status()).toBe(201)
        expect(refreshed.status()).toBe(200)
        await expect(modal).toBeHidden()
        const row = page.locator('main table:visible tr[data-row-key]', {hasText: text})
        await expect(row).toBeVisible()
        ids.push(await row.getAttribute('data-row-key'))
    }
    for (const id of ids) {
        await page.goto(`/admin/resource/poly-comment-resource/poly-comment-form-page/${id}`, {waitUntil: 'domcontentloaded'})
        await expect(page.locator('main select[name="commentable_type"]')).toHaveValue('App\\Models\\Project')
        await expect(page.locator('main select[name="commentable_id"]')).toHaveValue('1')
    }
})
