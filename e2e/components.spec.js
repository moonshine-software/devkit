import {expect, test} from './fixtures.js'

const background = locator => locator.evaluate(element => getComputedStyle(element).backgroundColor)

test('icon Blade tag renders its slot', async ({page}) => {
    await page.goto('/admin/page/components')

    // The slot content of an icon tag replaces the named icon.
    await expect(page.locator('[data-e2e="slot-icon"].icon-wrapper [data-e2e="slot-icon-svg"]')).toBeAttached()
})

test('filled action button links are primary', async ({page}) => {
    await page.goto('/admin/page/components')

    // Both as a Blade tag prop and as a component attribute.
    const plain = page.locator('a[data-e2e="plain-tag"]')

    await expect(plain).toHaveClass(/\bbtn\b/)
    await expect(plain).not.toHaveClass(/btn-primary/)

    for (const name of ['filled-tag', 'filled-attribute']) {
        const filled = page.locator(`a[data-e2e="${name}"]`)

        await expect(filled).toBeVisible()
        await expect(filled).toHaveClass(/btn-primary/)
        expect(await background(filled), name).not.toBe(await background(plain))
    }
})

test('component structure contains states, not prepared view data', async ({page}) => {
    const response = await page.request.get('/admin/page/components', {
        headers: {Accept: 'application/json', 'X-MS-Structure': 'true', 'X-MS-Without-Layout': 'true'},
    })

    expect(response.status()).toBe(200)

    const body = await response.text()

    expect(body).toContain('"type":"ActionButton"')
    expect(body).toContain('"type":"Rating"')

    // Attribute bags, rendered stars and nested action groups are only passed to the views.
    for (const key of ['tagAttributes', 'wrapperAttributes', 'formAttributes', 'actionGroup', 'stars', 'hasSlot']) {
        expect(body, key).not.toContain(`"${key}":`)
    }
})
