import {expect, listTable, test} from './fixtures.js'

test('a double click on a modal detail button sends one request and leaves the modal open', async ({page}) => {
    await page.goto('/admin/resource/shop-resource/shop-index-page?p_reset=1')
    const button = listTable(page, 'shop').locator('tbody tr[data-row-key] a.js-detail-button').first()
    await expect(button).toBeVisible()
    const url = await button.getAttribute('href')

    const requests = []
    page.on('request', request => {
        if (request.url() === url) {
            requests.push(request.method())
        }
    })

    // Each response toggles the modal, so a second request would close it again.
    await button.dblclick()
    const modal = page.locator('.modal:visible')
    await expect(modal).toBeVisible()
    await page.waitForLoadState('networkidle')

    expect(requests).toHaveLength(1)
    await expect(modal).toBeVisible()
})
