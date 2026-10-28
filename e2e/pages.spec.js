import {expect, test} from './fixtures.js'
import {inventory, resourcePage, resources, standalone} from './catalog.js'

test('the page catalog covers the registered admin pages and resource page types', async () => {
    const data = inventory()
    expect(Object.keys(data.resources).sort()).toEqual(resources.map(({key}) => `${key}-resource`).sort())
    expect(Object.keys(data.pages).sort()).toEqual(standalone.map(([key]) => key).sort())
    for (const {key, detail} of resources) {
        expect(Object.keys(data.resources[`${key}-resource`].pages).sort()).toEqual(
            (detail ? ['index-page', 'form-page', 'detail-page'] : ['index-page', 'form-page']).sort(),
        )
    }
})

for (const {key, field, detail} of resources) {
    test(`${key}: index loads visible records`, async ({page}) => {
        const response = await page.goto(resourcePage(key, 'index-page'), {waitUntil: 'domcontentloaded'})
        expect(response.status()).toBe(200)
        await expect(page.locator('main h1')).toHaveText(inventory().resources[`${key}-resource`].title)
        if (key === 'category') {
            await expect(page.locator('main')).toContainText('E2E attached')
        } else {
            const table = page.locator('main table:visible').first()
            await expect(table.locator(':scope > tbody > tr[data-row-key]').first()).toBeVisible()
            await expect(table.locator(':scope > tbody > tr[data-row-key]').first()).not.toBeEmpty()
        }
    })

    for (const edit of [false, true]) {
        test(`${key}: ${edit ? 'edit' : 'create'} form exposes its fields and save action`, async ({page}) => {
            const response = await page.goto(resourcePage(key, 'form-page', edit), {waitUntil: 'domcontentloaded'})
            expect(response.status()).toBe(200)
            await expect(page.locator('main h1').first()).toBeVisible()
            const form = page.locator(`main form[data-component="${key}-resource"]`).first()
            const input = form.locator(`[name="${field}"]`)
            await expect(input).toBeVisible()
            await expect(form.getByRole('button', {name: 'Save', exact: true})).toBeVisible()
            if (edit && key !== 'test-rest-user') {
                await expect(input).toHaveValue(String(inventory().resources[`${key}-resource`].record))
            }
        })
    }

    if (detail) {
        test(`${key}: detail renders the record and navigation`, async ({page}) => {
            const response = await page.goto(resourcePage(key, 'detail-page'), {waitUntil: 'domcontentloaded'})
            expect(response.status()).toBe(200)
            await expect(page.locator('main h1').first()).toBeVisible()
            await expect(page.locator('main')).toContainText(key === 'test-rest-user'
                ? 'E2E API user 1'
                : String(inventory().resources[`${key}-resource`].record))
            await expect(page.locator('main a[href*="-form-page/"]').first()).toBeVisible()
        })
    }
}

for (const [key, title] of standalone) {
    test(`standalone: ${key} renders its content`, async ({page}) => {
        const path = key === 'error-page' ? '/admin/resource/e2e-missing-resource/index-page' : `/admin/page/${key}`
        const response = await page.goto(path, {waitUntil: 'domcontentloaded'})
        expect(response.status()).toBe(key === 'error-page' ? 404 : 200)
        await expect(page.locator('body')).toContainText(title)
        if (['fields', 'forms', 'json-page', 'selects', 'show-when', 'profile-page'].includes(key)) {
            await expect(page.locator('main input, main select, main textarea').first()).toBeAttached()
        }
    })
}

test('profile exposes the current admin identity and password section', async ({page}) => {
    await page.goto('/admin/page/profile-page', {waitUntil: 'domcontentloaded'})
    await expect(page.locator('main input[name="email"]')).toHaveValue('dev@getmoonshine.app')
    await expect(page.locator('main input[name="name"]')).toHaveValue('Admin')
})

test.describe('guest login page', () => {
    test.use({storageState: {cookies: [], origins: []}})
    test('protected resource redirects to a usable login form', async ({page}) => {
        await page.goto('/admin/resource/post-resource/post-index-page', {waitUntil: 'domcontentloaded'})
        await expect(page).toHaveURL(/\/admin\/login$/)
        await expect(page.locator('input[name="username"]')).toBeVisible()
        await expect(page.locator('input[name="password"]')).toBeVisible()
        await expect(page.getByRole('button', {name: 'Log in', exact: true})).toBeVisible()
    })
})
