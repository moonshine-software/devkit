import {expect, listTable, test} from './fixtures.js'

test('dashboard tabs render their resource tables', async ({page}) => {
    await page.goto('/admin?p_reset=1&u_reset=1&pr_reset=1')

    for (const [label, resource, count] of [['Users', 'user', 25], ['Posts', 'post', 25], ['Projects', 'project', 20], ['Users', 'user', 25]]) {
        const button = page.locator('main').getByRole('button', {name: label, exact: true})
        // The active tab disables pointer events.
        if (!(await button.getAttribute('class')).includes('_is-active')) {
            await button.click()
        }
        const table = listTable(page, resource)
        await expect(table).toBeVisible()
        await expect(table.locator(':scope > tbody > tr:not(.additionally)')).toHaveCount(count)
    }
})

test('collapse and tabs switch component visibility', async ({page}) => {
    await page.goto('/admin/page/components')
    // This demo opens a modal and an off-canvas on load.
    const modal = page.locator('.modal:visible')
    await modal.locator('.modal-close').click()
    await expect(modal).toBeHidden()
    const drawer = page.locator('.offcanvas:visible')
    await drawer.locator('.offcanvas-close').click()
    await expect(drawer).toBeHidden()

    const content = page.locator('main .accordion-content').first()
    const collapse = page.getByRole('button', {name: 'Collapse', exact: true})
    await expect(content).toBeHidden()
    await collapse.click()
    await expect(content).toBeVisible()
    await collapse.click()
    await expect(content).toBeHidden()

    const tab = page.locator('main').getByRole('button', {name: 'Tab 2', exact: true})
    await tab.click()
    await expect(tab).toHaveClass(/_is-active/)
})

test.describe('mobile layout', () => {
    test.use({viewport: {width: 390, height: 844}})

    for (const [name, url] of [['dashboard', '/admin'], ['post form', '/admin/resource/post-resource/post-form-page/1']]) {
        test(`${name} fits the viewport`, async ({page}) => {
            await page.goto(url)
            await expect(page.locator('main h1')).toBeVisible()
            expect(await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth)).toBeLessThanOrEqual(2)

            if (name === 'post form') {
                const input = page.locator('main input[name="name"]')
                await expect(input).toBeVisible()
                const bounds = await input.boundingBox()
                expect(bounds.x).toBeGreaterThanOrEqual(0)
                expect(bounds.x + bounds.width).toBeLessThanOrEqual(390)
            }
        })
    }
})
