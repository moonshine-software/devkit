import {expect, test} from './fixtures.js'

// Detail pages render fields in a vertical TableBuilder: labels are escaped by default.
test('vertical table labels on the detail page follow label escaping', async ({page}) => {
    await page.goto('/admin/resource/comment-resource/comment-detail-page/1')

    const table = page.locator('main table:visible').first()

    await expect(table).toContainText('E2E label <img src=x')
    await expect(table.locator('img[src="x"]')).toHaveCount(0)
    await expect(table.locator('.form-label b.e2e-raw-label')).toHaveText('E2E raw label')
})
