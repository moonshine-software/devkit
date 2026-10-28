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

test('post detail and form pages render', async ({page}) => {
    for (const url of ['/admin/resource/post-resource/post-detail-page/1', '/admin/resource/post-resource/post-form-page/1']) {
        const response = await page.goto(url)

        expect(response?.status(), url).toBe(200)
    }
})

test('modal and off-canvas open', async ({page}) => {
    for (const name of ['Modal', 'OffCanvas']) {
        await page.goto('/admin/page/theme-generator-page')
        await page.getByRole('link', {name, exact: true}).or(page.getByRole('button', {name, exact: true})).first().click()

        await expect(page.locator(':is(.modal, .offcanvas):visible', {hasText: 'Content'}).first()).toBeVisible()
    }
})

test('input prefix and suffix render', async ({page}) => {
    await page.goto('/admin/page/forms')

    const decorations = await page.locator('.expansion').allTextContents()

    expect(decorations.map(text => text.trim())).toEqual(expect.arrayContaining(['USD', '$']))
})
