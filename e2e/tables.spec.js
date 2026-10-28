import {chooseOption, EDITABLE_USER_EMAIL, expect, listTable, test} from './fixtures.js'

test('pagination replaces the visible user rows asynchronously', async ({page}) => {
    await page.goto('/admin/resource/user-resource/user-index-page?u_reset=1')
    const rows = listTable(page, 'user').locator(':scope > tbody > tr')
    await expect(rows).toHaveCount(25)
    const firstPage = await rows.evaluateAll(rows => rows.map(row => row.dataset.rowKey))
    expect(firstPage.every(id => /^\d+$/.test(id))).toBe(true)

    const [response] = await Promise.all([
        page.waitForResponse(response => response.url().includes('/component/user-index-page/')
            && new URL(response.url()).searchParams.get('u_page') === '2'),
        page.locator('main a[href*="u_page=2"]').first().click(),
    ])

    expect(response.status()).toBe(200)
    await expect(page).toHaveURL(/u_page=2/)
    await expect(rows.first()).not.toHaveAttribute('data-row-key', firstPage[0])
    await expect(rows).toHaveCount(25)
    const secondPage = await rows.evaluateAll(rows => rows.map(row => row.dataset.rowKey))
    expect(secondPage.filter(id => firstPage.includes(id))).toEqual([])
})

test('sorting toggles the order of the post rows', async ({page}) => {
    await page.goto('/admin/resource/post-resource/post-index-page?p_reset=1&p_sort=id')
    const table = listTable(page, 'post')
    const rows = table.locator(':scope > tbody > tr[data-row-key]')
    await expect(rows.first()).toHaveAttribute('data-row-key', '1')

    await table.getByRole('link', {name: 'ID', exact: true}).click()
    await expect(page).toHaveURL(/p_sort=-id/)
    await expect(rows.first()).toHaveAttribute('data-row-key', '50')

    await table.getByRole('link', {name: 'ID', exact: true}).click()
    await expect(page).toHaveURL(/p_sort=id/)
    await expect(rows.first()).toHaveAttribute('data-row-key', '1')
})

test('column selection hides and restores the text column', async ({page}) => {
    await page.goto('/admin/resource/post-resource/post-index-page?p_reset=1')
    const table = listTable(page, 'post')
    await expect(table.locator(':scope > tbody > tr[data-row-key]')).toHaveCount(25)
    const dropdown = page.locator('main .dropdown').first()
    const heading = table.locator('th[data-column-selection="text"]')

    await dropdown.locator('button.dropdown-btn').click()
    // Switcher inputs are sr-only; use the visible label as a user would.
    await dropdown.getByText('Text', {exact: true}).click()
    await expect(heading).toBeHidden()
    await dropdown.getByText('Text', {exact: true}).click()
    await expect(heading).toBeVisible()
})

test('filter reset clears saved query state', async ({page}) => {
    await page.goto('/admin/resource/post-resource/post-index-page?p_reset=1')
    const rows = listTable(page, 'post').locator(':scope > tbody > tr[data-row-key]')
    await expect(rows).toHaveCount(25)

    await page.getByRole('link', {name: /^Filters/}).click()
    const drawer = page.locator('.offcanvas:visible')
    const select = drawer.locator('select')
    const userId = await select.locator('option', {hasText: EDITABLE_USER_EMAIL}).getAttribute('value')
    await chooseOption(page, select, userId)
    await drawer.getByRole('button', {name: 'Search', exact: true}).click()
    await expect(drawer).toBeHidden()
    await expect(rows).toHaveCount(1)
    await expect(rows.first()).toHaveAttribute('data-row-key', '1')

    await page.getByRole('link', {name: /^Filters/}).click()
    const [response] = await Promise.all([
        page.waitForResponse(response => response.url().includes('/component/post-index-page/')),
        page.locator('.offcanvas:visible').getByRole('link', {name: 'Reset', exact: true}).click(),
    ])
    expect(response.status()).toBe(200)

    // Also reproduced on baseline 5.x: Reset omits p_reset and ResourceQuery restores the saved filter.
    test.fail(true, 'Known 5.x bug: async Reset retains the saved filter when saveQueryState is enabled')
    await expect(rows).toHaveCount(25)
})

test('popover saves a field, refreshes its row and persists the value', async ({page}) => {
    const openPopover = async () => {
        await page.goto('/admin/resource/post-resource/post-index-page?p_reset=1&p_sort=id')
        const row = listTable(page, 'post').locator(':scope > tbody > tr[data-row-key="1"]')
        await expect(row).toBeVisible()
        await row.locator('.popover-trigger').first().click()
        return page.locator('[data-tippy-root]:visible')
    }

    const save = async value => {
        const popover = await openPopover()
        await chooseOption(page, popover.locator('select'), value)
        const [response, refresh] = await Promise.all([
            page.waitForResponse(response => response.url().includes('/update-field/column/post-resource/1')),
            page.waitForResponse(response => response.url().includes('/component/post-index-page/')
                && new URL(response.url()).searchParams.get('_key') === '1'),
            popover.getByRole('button', {name: 'Save', exact: true}).click(),
        ])
        expect(response.status()).toBe(204)
        expect(refresh.status()).toBe(200)
        await expect(listTable(page, 'post').locator(':scope > tbody > tr[data-row-key="1"]')).toContainText(value)
    }

    try {
        await save('Second post')
        await page.goto('/admin/resource/post-resource/post-form-page/1')
        await expect(page.locator('main input[name="name"]')).toHaveValue('Second post')
    } finally {
        await save('First post')
    }
})
