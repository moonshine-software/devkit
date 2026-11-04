import {chooseOption, expect, selectControl, test} from './fixtures.js'

// MoonShine's own client sends every async request through axios, which adds the XSRF header.
const expectAsync = async (response, status = 200) => {
    expect(response.status(), response.url()).toBe(status)
    expect(await response.request().headerValue('x-xsrf-token'), response.url()).toBeTruthy()
}

const waitForEndpoint = (page, path, method = 'GET') => page.waitForResponse(response => response.url().includes(path)
    && response.request().method() === method)

test('a reactive resource form fills the slug through the reactive endpoint', async ({page}) => {
    await page.goto('/admin/resource/post-resource/post-form-page/1')
    const form = page.locator('main form[data-component="post-resource"]').first()
    const name = form.locator('input[name="name"]')

    const [response] = await Promise.all([
        waitForEndpoint(page, '/reactive/post-form-page/post-resource', 'POST'),
        // The field is reactive with lazy: the request runs on change
        name.fill('E2E Reactive Title').then(() => name.blur()),
    ])
    await expectAsync(response)
    await expect(form.locator('input[name="slug"]')).toHaveValue('e2e-reactive-title')
})

test('a reactive page form computes values and rebuilds options on the server', async ({page}) => {
    await page.goto('/admin/page/forms')
    const form = page.locator('main form[data-component="reactive"]')
    const reactive = () => waitForEndpoint(page, '/reactive/forms', 'POST')

    for (const [column, value] of [['price', '3'], ['quantity', '4']]) {
        const [response] = await Promise.all([reactive(), form.locator(`input[name="${column}"]`).fill(value)])
        await expectAsync(response)
    }
    await expect(form.locator('input[name="total"]')).toHaveValue('12')

    const city = form.locator('select[name="city"]')
    for (const [country, cities] of [['france', ['paris', 'lyon']], ['italy', ['rome', 'milan']]]) {
        const [response] = await Promise.all([reactive(), chooseOption(page, form.locator('select[name="country"]'), country)])
        await expectAsync(response)
        await expect.poll(() => city.evaluate(select => [...select.options].map(option => option.value).filter(Boolean)))
            .toEqual(cities)
    }
})

test('async selects load their options from async methods with GET requests', async ({page}) => {
    // Loaded when the page initialises
    const [initial] = await Promise.all([
        waitForEndpoint(page, '/method/selects?method=select'),
        page.goto('/admin/page/selects'),
    ])
    await expectAsync(initial)

    // Loaded while typing
    await page.goto('/admin/page/fields')
    const select = page.locator('main select[name="select_async"]')
    const control = selectControl(select)
    await control.click()
    const [search] = await Promise.all([
        waitForEndpoint(page, '/method/fields?method=selectOptions'),
        control.locator('input').pressSequentially('Opt', {delay: 30}),
    ])
    await expectAsync(search)
    await expect(page.locator('.ts-dropdown .option:visible', {hasText: 'Option 2'})).toBeVisible()
})

test('an async form method that redirects applies the theme', async ({page}) => {
    await page.goto('/admin/page/theme-generator-page')

    const [response] = await Promise.all([
        waitForEndpoint(page, '/method/theme-generator-page', 'POST'),
        page.getByRole('button', {name: 'Apply', exact: true}).click(),
    ])
    // The method answers with a redirect back, which the browser follows inside the async request
    expect([200, 302]).toContain(response.status())
    expect(await response.request().headerValue('x-xsrf-token')).toBeTruthy()
})
