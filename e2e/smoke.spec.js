import {expect, test} from './fixtures.js'

// Needs the separate REST API backend on 127.0.0.1:8001.
const skipped = ['test-rest-user-resource']

test('menu pages render', async ({page}) => {
    // Visits every menu page in one test.
    test.setTimeout(120_000)

    await page.goto('/admin')

    const urls = [...new Set(await page.$$eval('a[href]', links => links.map(link => link.href)))]
        .filter(url => /\/admin\/(resource\/[^/]+\/[^/?]+-index-page|page\/[^/?#]+)$/.test(url))
        .filter(url => !skipped.some(name => url.includes(name)))

    expect(urls.length).toBeGreaterThan(10)

    for (const url of urls) {
        await test.step(url, async () => {
            const response = await page.goto(url, {waitUntil: 'load'})

            expect(response?.status(), url).toBe(200)
        })
    }
})

test('resource detail and form pages render', async ({page}) => {
    for (const resource of ['post', 'comment', 'user', 'project']) {
        for (const type of ['detail', 'form']) {
            const url = `/admin/resource/${resource}-resource/${resource}-${type}-page/1`
            const response = await page.goto(url)

            expect(response?.status(), url).toBe(200)
            await expect(page.locator('main h1')).toBeVisible()
        }
    }
})

test('modal and off-canvas open', async ({page}) => {
    for (const name of ['Modal', 'OffCanvas']) {
        await page.goto('/admin/page/theme-generator-page')
        await page.getByRole('link', {name, exact: true}).or(page.getByRole('button', {name, exact: true})).first().click()

        const overlay = page.locator(':is(.modal, .offcanvas):visible', {hasText: 'Content'}).first()
        await expect(overlay).toBeVisible()
        await page.keyboard.press('Escape')
        await expect(overlay).toBeHidden()
    }
})

test('input decorations and disabled/readonly states render', async ({page}) => {
    await page.goto('/admin/page/forms')

    const decorations = await page.locator('.expansion').allTextContents()

    expect(decorations.map(text => text.trim())).toEqual(expect.arrayContaining(['USD', '$']))
    await expect(page.locator('form[data-component="disabled"] input[type="text"]').first()).toBeDisabled()
    await expect(page.locator('form[data-component="readonly"] input[type="text"]').first()).toHaveJSProperty('readOnly', true)
})
