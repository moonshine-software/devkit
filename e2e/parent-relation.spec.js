import {expect, listTable, test} from './fixtures.js'

const postIndex = '/admin/resource/post-resource/post-index-page'
const commentIndex = '/admin/resource/comment-resource/comment-index-page'

const rowKeys = async table => {
    await expect(table.locator('tbody tr[data-row-key]').first()).toBeVisible()

    return table.locator('tbody tr[data-row-key]').evaluateAll(rows => rows.map(row => row.dataset.rowKey).sort())
}

test('a HasMany related link lists only the comments of its post and keeps the filter when sorting', async ({page}) => {
    await page.goto(`${commentIndex}?p_reset=1`)
    const allComments = await rowKeys(listTable(page, 'comment'))

    await page.goto(`${postIndex}?p_reset=1&p_sort=id`)
    const post = listTable(page, 'post').locator('tbody tr[data-row-key="1"]')
    await expect(post).toContainText('First post')
    const link = post.locator('a[href*="_parentId=post-1"]')
    const expected = Number(await link.locator('.badge').innerText())
    expect(expected).toBeGreaterThan(0)
    expect(expected).toBeLessThan(allComments.length)

    await link.click()
    await expect(page).toHaveURL(/_parentId=post-1/)
    const comments = listTable(page, 'comment')
    await expect(comments.locator('tbody tr[data-row-key]')).toHaveCount(expected)
    await expect(comments).toContainText('E2E child comment')
    const filtered = await rowKeys(comments)

    const [response] = await Promise.all([
        page.waitForResponse(response => response.url().includes('/component/comment-index-page/')),
        comments.locator('thead th[data-column-selection="id"] a').click(),
    ])
    expect(new URL(response.url()).searchParams.get('_parentId')).toBe('post-1')
    await expect(comments.locator('tbody tr[data-row-key]')).toHaveCount(expected)
    expect(await rowKeys(comments)).toEqual(filtered)
})

test('a BelongsToMany parent filter with a UUID key lists only attached records', async ({page}) => {
    await page.goto('/admin/resource/post-resource/post-detail-page/1')
    const category = page.locator('main tr[data-row-key]', {hasText: 'E2E attached'}).last()
    const categoryId = await category.getAttribute('data-row-key')
    expect(categoryId).toMatch(/^[0-9a-f-]{36}$/)

    await page.goto(`${postIndex}?_parentId=categories-${categoryId}`)
    const posts = listTable(page, 'post')
    await expect(posts.locator('tbody tr[data-row-key]')).toHaveCount(1)
    await expect(posts.locator('tbody tr[data-row-key="1"]')).toContainText('First post')
})

test('a parent filter naming a model method or an unknown relation responds 404 without side effects', async ({page}) => {
    await page.goto(`${commentIndex}?p_reset=1`)
    const before = await rowKeys(listTable(page, 'comment'))

    // touch() would try to save an empty comment and delete() would act on the model instance.
    // The index page loads its table asynchronously, so the list request is the one that applies the filter.
    for (const name of ['touch', 'delete', 'missing_relation']) {
        const [response] = await Promise.all([
            page.waitForResponse(response => response.url().includes('/component/comment-index-page/')
                && new URL(response.url()).searchParams.get('_parentId') === `${name}-1`),
            page.goto(`${commentIndex}?_parentId=${name}-1`),
        ])
        expect.soft(response.status(), name).toBe(404)
    }

    await page.goto(`${commentIndex}?p_reset=1`)
    expect(await rowKeys(listTable(page, 'comment'))).toEqual(before)
})
