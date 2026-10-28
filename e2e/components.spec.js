import {expect, test} from './fixtures.js'

const background = locator => locator.evaluate(element => getComputedStyle(element).backgroundColor)

test('custom view iterators survive serialization and rendering', async ({page}) => {
    await page.goto('/admin/page/components', {waitUntil: 'domcontentloaded'})

    const card = page.locator('[data-e2e="iterator-card"]')
    await expect(card.locator('th')).toHaveText('<b>Name</b>:')
    await expect(card.locator('td')).toHaveText('Alice')
    await expect(card.locator('th b')).toHaveCount(0)

    const footer = page.locator('[data-e2e="iterator-footer"]')
    await expect(footer.locator('a')).toHaveText('<b>Iterator help</b>')
    await expect(footer.locator('a b')).toHaveCount(0)
})

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

for (const variant of ['native', 'button']) {
    test(`custom ${variant} link labels preserve escaping and explicit slots`, async ({page}) => {
        await page.goto('/admin/page/components', {waitUntil: 'domcontentloaded'})

        const escaped = page.locator(`[data-e2e="custom-link-${variant}-escaped"]`)
        await expect(escaped).toBeVisible()
        await expect(escaped).toHaveText('<b>New & label</b>')
        await expect(escaped.locator('b')).toHaveCount(0)

        const trusted = page.locator(`[data-e2e="custom-link-${variant}-trusted"]`)
        await expect(trusted).toHaveText('New & label')
        await expect(trusted.locator('b')).toHaveText('New & label')

        const prepared = page.locator(`[data-e2e="custom-link-${variant}-prepared"]`)
        await expect(prepared).toHaveText('Prepared & label')
        await expect(prepared.locator('em')).toHaveText('Prepared & label')

        const slot = page.locator(`[data-e2e="custom-link-${variant}-slot"]`)
        await expect(slot).toHaveText('Explicit & slot')
        await expect(slot.locator('strong')).toHaveText('Explicit & slot')

        const blade = page.locator('[data-e2e="custom-link-blade-slot"]')
        await expect(blade).toHaveText('Blade & slot')
        await expect(blade.locator('strong')).toHaveText('Blade & slot')
    })
}
