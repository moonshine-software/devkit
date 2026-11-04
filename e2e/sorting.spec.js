import {expect, listTable, test} from './fixtures.js'
import {resourcePage, resources} from './catalog.js'

// Sortable headers link to the list endpoint with the resource's own query parameter prefix (sort, p_sort, pr_sort).
const sortLinks = table => table.locator(':scope > thead th a[href*="sort="]')

const sortParam = href => [...new URL(href).searchParams.keys()].find(key => key.endsWith('sort'))

const rowKeys = async table => {
    await expect(table.locator(':scope > tbody > tr[data-row-key]').first()).toBeVisible()

    return table.locator(':scope > tbody > tr[data-row-key]').evaluateAll(rows => rows.map(row => row.dataset.rowKey))
}

// Opens a resource index list with a fresh query state; resources without a list table (a tree) return null.
const openIndex = async (page, key, query = 'reset=1&p_reset=1&pr_reset=1') => {
    await page.goto(`${resourcePage(key, 'index-page')}?${query}`)
    await page.waitForLoadState('networkidle')
    const table = listTable(page, key)

    return await table.count() ? table : null
}

const columnValues = (table, index) => table.locator(':scope > tbody > tr[data-row-key]')
    .evaluateAll((rows, index) => rows.map(row => row.children[index]?.innerText.trim() ?? ''), index)

const isOrdered = (values, direction) => values.every((value, i) => {
    if (i === 0) {
        return true
    }
    const [a, b] = direction === 'asc' ? [values[i - 1], value] : [value, values[i - 1]]
    const numeric = !Number.isNaN(Number(a)) && !Number.isNaN(Number(b))

    return numeric ? Number(a) <= Number(b) : a.localeCompare(b, undefined, {sensitivity: 'base'}) <= 0
})

// Clicks a sortable header and checks that the list request asked for the expected order and the rows follow it.
const sortBy = async (page, table, header, endpoint, expected) => {
    const name = (await header.innerText()).trim()
    const param = sortParam(await header.getAttribute('href'))
    const index = await header.evaluate(link => [...link.closest('tr').children].indexOf(link.closest('th')))

    const [response] = await Promise.all([
        page.waitForResponse(response => response.url().includes(endpoint)
            && new URL(response.url()).searchParams.get(param) === expected),
        header.click(),
    ])
    expect(response.status(), `${name} ${expected}`).toBe(200)
    await expect(table.locator(':scope > tbody > tr[data-row-key]').first()).toBeVisible()
    expect(isOrdered(await columnValues(table, index), expected.startsWith('-') ? 'desc' : 'asc'), `${name} ${expected}`).toBe(true)
}

// The REST resource is ordered by its external API, not by the database query.
for (const {key} of resources.filter(({key}) => key !== 'test-rest-user')) {
    test(`${key}: every sortable index header orders the list in both directions`, async ({page}) => {
        const table = await openIndex(page, key)
        const headers = table ? await sortLinks(table).count() : 0
        test.skip(headers === 0, 'The index list has no sortable columns')

        for (let i = 0; i < headers; i++) {
            const href = await sortLinks(table).nth(i).getAttribute('href')
            const first = new URL(href).searchParams.get(sortParam(href))
            const second = first.startsWith('-') ? first.slice(1) : `-${first}`

            await sortBy(page, table, sortLinks(table).nth(i), '/component/', first)
            await sortBy(page, table, sortLinks(table).nth(i), '/component/', second)
        }
    })

    test(`${key}: the index list keeps the default order for a column it does not offer for sorting`, async ({page}) => {
        const table = await openIndex(page, key)
        test.skip(!table || await sortLinks(table).count() === 0, 'The index list has no sortable columns')
        const param = sortParam(await sortLinks(table).first().getAttribute('href'))
        const prefix = param.slice(0, -'sort'.length)
        const defaultOrder = await rowKeys(table)

        // A column without a field (the users' password hash), an unknown column and a JSON path.
        for (const column of ['-password', '-missing_column', '-data->secret']) {
            const list = await openIndex(page, key, `${prefix}reset=1&${param}=${encodeURIComponent(column)}`)
            expect(await rowKeys(list), column).toEqual(defaultOrder)
        }
    })
}

for (const [resource, relation] of [['post', 'comments'], ['project', 'deployments']]) {
    test(`${resource}: the ${relation} relation table sorts by its sortable header and ignores other columns`, async ({page}) => {
        await page.goto(resourcePage(resource, 'form-page'))
        await page.waitForLoadState('networkidle')
        const table = page.locator('main table:visible', {has: page.locator(`thead a[href*="_relation=${relation}"]`)})
        const header = sortLinks(table).first()
        await expect(header).toBeVisible()
        const listUrl = new URL(await header.getAttribute('href'))

        await sortBy(page, table, header, '/has-many/list/', 'id')
        const ascending = await rowKeys(table)
        await sortBy(page, table, sortLinks(table).first(), '/has-many/list/', '-id')
        expect(await rowKeys(table)).toEqual([...ascending].reverse())

        // The relation list keeps its default order for columns its table does not offer for sorting.
        const listKeys = async sort => {
            sort === null ? listUrl.searchParams.delete('sort') : listUrl.searchParams.set('sort', sort)
            const response = await page.request.get(listUrl.href, {headers: {'X-Requested-With': 'XMLHttpRequest'}})
            expect(response.status(), String(sort)).toBe(200)

            return [...(await response.text()).matchAll(/<tr[^>]*data-row-key="([^"]+)"/g)].map(match => match[1])
        }
        const defaultOrder = await listKeys(null)
        for (const column of ['text', '-missing_column', '-data->secret']) {
            expect(await listKeys(column), column).toEqual(defaultOrder)
        }
    })
}
