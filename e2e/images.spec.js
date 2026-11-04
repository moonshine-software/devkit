import {chooseOption, expect, listTable, selectControl, test} from './fixtures.js'

test('select option images load from the current application', async ({page}) => {
    await page.goto('/admin/page/selects')
    const select = page.locator('form[data-component="reset-form"] select')
    await chooseOption(page, select, '2')
    const image = selectControl(select).locator('.item[data-value="2"] img')
    await expect(image).toBeVisible()
    await expect.poll(() => image.evaluate(img => img.complete && img.naturalWidth > 0)).toBe(true)
    expect(new URL(await image.getAttribute('src'), page.url()).origin).toBe(new URL(page.url()).origin)
})

test('REST users show a default avatar for an empty URL and preserve an existing image', async ({page}) => {
    await page.goto('/admin/resource/test-rest-user-resource/test-rest-user-index-page')
    const table = listTable(page, 'test-rest-user')
    await expect(table.locator(':scope > tbody > tr[data-row-key]')).toHaveCount(25)
    const emptyAvatar = table.locator('tr[data-row-key="1"] img').first()
    const existingAvatar = table.locator('tr[data-row-key="2"] img').first()
    await expect(emptyAvatar).toHaveAttribute('src', /\/vendor\/moonshine\/avatar\.jpg$/)
    await expect(existingAvatar).toHaveAttribute('src', /\/vendor\/moonshine\/logo-small\.svg$/)
    await expect.poll(() => table.locator('img').evaluateAll(
        images => images.every(img => img.complete && img.naturalWidth > 0),
    )).toBe(true)

    for (const [id, filename] of [[1, 'avatar.jpg'], [2, 'logo-small.svg']]) {
        await page.goto(`/admin/resource/test-rest-user-resource/test-rest-user-detail-page/${id}`)
        const image = page.locator('main img').first()
        await expect(image).toHaveAttribute('src', new RegExp(`/vendor/moonshine/${filename.replace('.', '\\.')}$`))
        await expect.poll(() => image.evaluate(img => img.complete && img.naturalWidth > 0)).toBe(true)
    }
})
