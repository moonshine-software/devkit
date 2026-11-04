import {expect, listTable, test} from './fixtures.js'

const rows = table => table.locator(':scope > tbody > tr[data-row-key]')

// Rows other suites do not rely on: E2eSeeder fixtures carry "E2E" in their text.
const disposableKeys = async table => {
    await expect(rows(table).first()).toBeVisible()

    return rows(table).filter({hasNotText: 'E2E'}).evaluateAll(list => list.map(row => row.dataset.rowKey))
}

// Checks the rows, opens the bulk delete confirmation and submits it.
const massDelete = async (page, resource, table, keys) => {
    for (const key of keys) {
        await table.locator(`tr[data-row-key="${key}"] input.js-table-action-row`).check()
    }
    await page.locator(`main a[data-button-type="bulk-button"][data-for-component="index-table-${resource}-resource"]`).first().click()
    const modal = page.locator('.modal:visible')

    const [response] = await Promise.all([
        page.waitForResponse(response => response.url().endsWith(`/${resource}-resource/crud`)
            && response.request().method() === 'POST'),
        modal.getByRole('button', {name: /confirm|delete/i}).last().click(),
    ])
    expect(response.status()).toBe(200)
    expect(await response.request().headerValue('x-xsrf-token')).toBeTruthy()
}

test('mass deletion removes only the selected rows', async ({page}) => {
    await page.goto('/admin/resource/comment-resource/comment-index-page?reset=1')
    const table = listTable(page, 'comment')
    const [first, second, ...others] = await disposableKeys(table)

    await massDelete(page, 'comment', table, [first, second])

    await expect(table.locator(`tr[data-row-key="${first}"]`)).toHaveCount(0)
    await expect(table.locator(`tr[data-row-key="${second}"]`)).toHaveCount(0)
    await expect(table.locator(`tr[data-row-key="${others[0]}"]`)).toBeVisible()
})

test('mass deletion of soft deletable records uses the item query of the resource', async ({page}) => {
    // Tags list and look up their items with trashed records, so deleted tags stay in the list with a restore action.
    await page.goto('/admin/resource/tag-resource/tag-index-page?reset=1')
    const table = listTable(page, 'tag')
    const keys = (await disposableKeys(table)).slice(0, 2)

    await massDelete(page, 'tag', table, keys)

    for (const key of keys) {
        await expect(table.locator(`tr[data-row-key="${key}"]`).getByRole('link', {name: 'Restore', exact: true})).toBeVisible()
    }
})
