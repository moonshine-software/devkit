import {expect, test} from './fixtures.js'

// Record names created by E2eSeeder contain an <img onerror> payload; they must be displayed as text.
const expectTextOnly = async (locator, name) => {
    await expect(locator).toContainText(`${name} <img`)
    await expect(locator.locator('img[src="x"]')).toHaveCount(0)
}

test('inline relation labels on the index page', async ({page}) => {
    await page.goto('/admin/resource/post-resource/post-index-page')

    await expectTextOnly(page.locator('main td', {hasText: 'E2E attached'}).last(), 'E2E attached')
})

test('relation preview on the detail page', async ({page}) => {
    await page.goto('/admin/resource/post-resource/post-detail-page/1')

    await expectTextOnly(page.locator('main'), 'E2E attached')
})

test('pivot titles and async search selection on the form page', async ({page}) => {
    await page.goto('/admin/resource/post-resource/post-form-page/1')

    await expectTextOnly(page.locator('.js-pivot-title', {hasText: 'E2E attached'}), 'E2E attached')

    await page.locator('[data-async-search-url] [x-model="query"]').pressSequentially('E2E search', {delay: 30})
    await page.locator('.dropdown-menu-link', {hasText: 'E2E search'}).first().click()

    const row = page.locator('.js-pivot-table table:not([data-skeleton]) > tbody > tr').last()

    await expectTextOnly(row.locator('.js-pivot-title'), 'E2E search')
    await expect(row.locator('.js-pivot-checker')).toBeChecked()
})
