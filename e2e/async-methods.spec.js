import {expect, listTable, test} from './fixtures.js'

test('async methods reject a cross-site navigation with an authenticated session', async ({page, baseURL}) => {
    await page.goto('/admin/page/selects')
    const endpoint = new URL('/admin/method/selects?method=select', baseURL).href
    const attacker = 'http://localhost:8098/async-method-link'
    await page.route(attacker, route => route.fulfill({
        contentType: 'text/html',
        body: `<a href="${endpoint}">Open link</a>`,
    }))
    await page.goto(attacker)

    const [response] = await Promise.all([
        page.waitForResponse(response => response.url() === endpoint),
        page.getByRole('link', {name: 'Open link'}).click(),
    ])

    expect(new URL(await response.request().headerValue('referer')).origin).toBe(new URL(attacker).origin)
    expect(await response.request().headerValue('cookie')).toContain('laravel_session=')
    expect(response.status()).toBe(419)
})

test('async method buttons send the XSRF header on GET', async ({page}) => {
    await page.goto('/admin/resource/post-resource/post-index-page?p_reset=1&p_sort=-id')
    await expect(listTable(page, 'post').locator('tr[data-row-key="48"]')).toBeVisible()

    const [response] = await Promise.all([
        page.waitForResponse(response => response.url().includes('/method/post-index-page/')
            && new URL(response.url()).searchParams.get('method') === 'update48Row'),
        page.getByRole('link', {name: 'Update row ID 48', exact: true}).click(),
    ])

    expect(response.request().method()).toBe('GET')
    expect(await response.request().headerValue('x-xsrf-token')).toBeTruthy()
    expect(response.status()).toBe(200)
    await expect(listTable(page, 'post').locator('tr[data-row-key="48"]')).toBeVisible()
})
