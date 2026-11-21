import {expect, test} from './fixtures.js'

const examplePage = '/admin/page/components'
const fragment = page => page.locator('#loading-example')
const updateRequest = url => url.searchParams.get('_fragment-load') === 'loading-example'
const methodRequest = method => url => url.searchParams.get('method') === method

async function openExample(page, query = '') {
    await page.goto(`${examplePage}${query}`, {waitUntil: 'domcontentloaded'})
    await page.locator('.modal:visible .modal-close').click()
    await page.locator('.offcanvas:visible .offcanvas-close').click()
}

async function holdRequests(page, matches) {
    const {promise, resolve} = Promise.withResolvers()
    const requests = []

    await page.route(matches, async route => {
        requests.push(route.request())
        await promise
        await route.continue()
    })

    return {requests, release: resolve}
}

async function expectBusy(fragment) {
    await expect(fragment).toHaveAttribute('aria-busy', 'true')
    await expect(fragment.locator('.spinner')).toBeVisible()
    await expect(fragment.locator('[data-fragment-content]')).toHaveJSProperty('inert', true)
}

async function expectIdle(fragment) {
    await expect(fragment).toHaveAttribute('aria-busy', 'false')
    await expect(fragment.locator('.spinner')).toBeHidden()
    await expect(fragment.locator('[data-fragment-content]')).toHaveJSProperty('inert', false)
}

test('a fragment update locks stale content and keeps its trigger disabled', async ({page}) => {
    const {requests, release} = await holdRequests(page, updateRequest)
    await openExample(page)

    const block = fragment(page)
    const trigger = page.getByRole('link', {name: 'Refresh fragment', exact: true})
    await expectIdle(block)

    try {
        await trigger.evaluate(button => {
            button.click()
            button.click()
        })
        await expectBusy(block)
        await expect(trigger).toBeDisabled()
        await expect(trigger.locator('.spinner')).toBeVisible()
        const action = block.getByText('Fragment action', {exact: true})
        await action.evaluate(element => element.focus())
        await expect(action).not.toBeFocused()
        await expect.poll(() => requests.length).toBe(1)
    } finally {
        release()
    }

    await expect(block).toContainText('Refreshed fragment')
    await expectIdle(block)
    await expect(trigger).toBeEnabled()
    await block.getByRole('link', {name: 'Fragment action'}).click()
    await expect(block.getByText('Activated')).toBeVisible()
    expect(requests).toHaveLength(1)
})

test('updateOnLoad shows the loader until the initial update finishes', async ({page}) => {
    const {requests, release} = await holdRequests(page, updateRequest)
    await openExample(page, '?load-fragment=1')

    try {
        await expectBusy(fragment(page))
        await expect(fragment(page)).toContainText('Original fragment')
    } finally {
        release()
    }

    await expect(fragment(page)).toContainText('Refreshed fragment')
    await expectIdle(fragment(page))
    expect(requests).toHaveLength(1)
})

test('an async button locks its fragment before a JSON response replaces it', async ({page}) => {
    const {requests, release} = await holdRequests(page, methodRequest('replaceExampleFragment'))
    await openExample(page)

    const trigger = page.getByRole('link', {name: 'Replace fragment'})

    try {
        await trigger.evaluate(button => {
            button.click()
            button.click()
        })
        await expectBusy(fragment(page))
        await expect(trigger).toBeDisabled()
    } finally {
        release()
    }

    await expect(fragment(page)).toContainText('Replaced fragment')
    await expectIdle(fragment(page))
    await expect(trigger).toBeEnabled()
    expect(requests).toHaveLength(1)
})

test('an async button stays disabled through the follow-up fragment request', async ({page}) => {
    const method = await holdRequests(page, methodRequest('refreshExampleFragment'))
    const update = await holdRequests(page, updateRequest)
    await openExample(page)
    const trigger = page.getByRole('link', {name: 'Refresh fragment via request'})

    try {
        await trigger.click()
        await expectBusy(fragment(page))
        await expect(trigger).toBeDisabled()
        method.release()

        await expect.poll(() => update.requests.length).toBe(1)
        await expectBusy(fragment(page))
        await expect(trigger).toBeDisabled()
        await expect(trigger.locator('.spinner')).toBeVisible()
    } finally {
        method.release()
        update.release()
    }

    await expect(fragment(page)).toContainText('Refreshed fragment')
    await expectIdle(fragment(page))
    await expect(trigger).toBeEnabled()
    expect(method.requests).toHaveLength(1)
    expect(update.requests).toHaveLength(1)
})

test('a form unlocks its fragment on validation failure and preserves the loader after success', async ({page}) => {
    const matches = methodRequest('submitExampleFragment')
    const {release} = await holdRequests(page, matches)
    await openExample(page)

    const submit = page.getByRole('button', {name: 'Submit fragment'})
    const form = page.locator('form').filter({has: submit})
    const invalidResponse = page.waitForResponse(response => matches(new URL(response.url())))

    try {
        await submit.click()
        await expectBusy(fragment(page))
        await expect(submit).toBeDisabled()
    } finally {
        release()
    }

    expect((await invalidResponse).status()).toBe(422)
    await expect(form.locator('.form-error')).toHaveText('The message field is required.')
    await expectIdle(fragment(page))
    await expect(submit).toBeEnabled()
    await expect(fragment(page)).toContainText('Original fragment')

    await page.unroute(matches)
    await form.getByRole('textbox', {name: 'Message'}).fill('Updated')

    for (let attempt = 0; attempt < 2; attempt++) {
        const {release} = await holdRequests(page, matches)
        try {
            await submit.click()
            await expectBusy(fragment(page))
        } finally {
            release()
        }

        await expect(fragment(page)).toContainText('Submitted fragment')
        await expectIdle(fragment(page))
        await expect(submit).toBeEnabled()
        await page.unroute(matches)
    }
})
