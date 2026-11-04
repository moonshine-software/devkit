import {expect, test} from './fixtures.js'

// Opening the theme generator sends a notification when the user has none.
const notify = async page => {
    await page.goto('/admin/page/theme-generator-page')
    await page.goto('/admin')
    await page.locator('.notifications-trigger').click()
    await expect(page.locator('.notifications-item').first()).toBeVisible()
}

test('notifications are marked as read through their CSRF protected forms', async ({page}) => {
    await notify(page)
    const [read] = await Promise.all([
        page.waitForResponse(response => /\/notifications\/[^/?]+$/.test(new URL(response.url()).pathname)
            && response.request().method() === 'POST'),
        page.locator('.notifications-item').first().locator('form.notifications-remove button').click(),
    ])
    expect(read.status()).toBe(302)
    await expect(page.locator('.notifications-item')).toHaveCount(0)

    await notify(page)
    const [readAll] = await Promise.all([
        page.waitForResponse(response => new URL(response.url()).pathname.endsWith('/notifications')
            && response.request().method() === 'POST'),
        page.locator('.notifications-read').click(),
    ])
    expect(readAll.status()).toBe(302)
    await expect(page.locator('.notifications-item')).toHaveCount(0)
})

test('a form posted with an invalid CSRF token is rejected', async ({page}) => {
    await notify(page)
    const form = page.locator('.notifications-item').first().locator('form.notifications-remove')
    await form.locator('input[name="_token"]').evaluate(input => input.value = 'invalid-token')

    const [response] = await Promise.all([
        page.waitForResponse(response => response.request().method() === 'POST'),
        form.locator('button').click(),
    ])
    expect(response.status()).toBe(419)
})

test.describe('without a session', () => {
    test.use({storageState: {cookies: [], origins: []}})

    test('the login and logout forms work with their CSRF tokens', async ({page}) => {
        await page.goto('/admin/login')
        await page.fill('input[name="username"]', process.env.E2E_USERNAME ?? 'dev@getmoonshine.app')
        await page.fill('input[name="password"]', process.env.E2E_PASSWORD ?? '12345')
        await page.click('button[type="submit"]')
        await expect(page).not.toHaveURL(/\/login/)

        await page.locator('.profile-main, .profile').first().click()
        const [logout] = await Promise.all([
            page.waitForResponse(response => response.request().method() === 'POST'
                && response.request().postData()?.includes('_method=delete')),
            page.getByRole('button', {name: /log out/i}).click(),
        ])
        expect(logout.status()).toBe(302)
        await expect(page).toHaveURL(/\/login/)

        await page.goto('/admin')
        await expect(page).toHaveURL(/\/login/)
    })
})
