import {readFileSync} from 'node:fs'

export const inventory = () => JSON.parse(readFileSync('.playwright-mcp/e2e-inventory.json', 'utf8'))

export const resources = [
    ['car', 'name'], ['category', 'name'], ['comment', 'text'], ['deployment', 'name'],
    ['environment', 'name'], ['image', 'url'], ['mechanic', 'name'],
    ['moon-shine-user-role', 'name', false], ['moon-shine-user', 'name', false],
    ['owner', 'name'], ['poly-comment', 'text'], ['post', 'name'], ['project', 'name'],
    ['shop', 'name'], ['tag', 'name'], ['test-rest-user', 'name'], ['user', 'name'],
].map(([key, field, detail = true]) => ({key, field, detail}))

export const standalone = [
    ['dashboard', 'Dashboard'], ['components', 'Components'], ['fields', 'Fields'],
    ['forms', 'Forms'], ['json-page', 'Json'], ['selects', 'Selects'],
    ['show-when', 'ShowWhen'], ['theme-generator-page', 'ThemeGeneratorPage'],
    ['profile-page', 'Profile'], ['login-page', 'Welcome to MoonShine'], ['error-page', '404'],
]

export const resourcePage = (key, type, edit = true) => {
    const data = inventory().resources[`${key}-resource`]
    const suffix = type === 'index-page' || type === 'form-page' && !edit ? '' : `/${data.id}`
    return `/admin/resource/${key}-resource/${data.pages[type]}${suffix}`
}
