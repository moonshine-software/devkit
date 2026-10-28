import {expect, test as setup} from '@playwright/test'

// Credentials of the admin created by DatabaseSeeder.
setup('authenticate', async ({page}) => {
    await page.goto('/admin/login')
    await page.fill('input[name="username"]', process.env.E2E_USERNAME ?? 'dev@getmoonshine.app')
    await page.fill('input[name="password"]', process.env.E2E_PASSWORD ?? '12345')
    await page.click('button[type="submit"]')
    await expect(page).not.toHaveURL(/\/login/)
    await page.context().storageState({path: 'playwright/.auth/admin.json'})
})
