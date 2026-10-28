import {defineConfig, devices} from '@playwright/test'
import path from 'node:path'

const port = process.env.E2E_PORT ?? '8099'

// The suite runs its own server against a separate SQLite database rebuilt before every run,
// so the development database is never touched.
const database = path.resolve('database/e2e.sqlite')

export default defineConfig({
    testDir: './e2e',
    fullyParallel: false,
    workers: 1,
    forbidOnly: !!process.env.CI,
    retries: process.env.CI ? 1 : 0,
    reporter: [['list'], ['html', {open: 'never'}]],
    use: {
        baseURL: `http://127.0.0.1:${port}`,
        trace: 'retain-on-failure',
        screenshot: 'only-on-failure',
    },
    projects: [
        {name: 'setup', testMatch: /auth\.setup\.js/},
        {
            name: 'chromium',
            use: {...devices['Desktop Chrome'], viewport: {width: 1440, height: 1000}, storageState: 'playwright/.auth/admin.json'},
            dependencies: ['setup'],
        },
    ],
    webServer: {
        // Playwright starts the server before globalSetup, so the database is prepared here.
        // --no-reload passes DB_DATABASE through to the PHP server and enables several workers.
        command: [
            `touch ${database}`,
            'php artisan config:clear',
            'php artisan migrate:fresh --seed --force',
            'php artisan db:seed --class=E2eSeeder --force',
            `php artisan serve --host=127.0.0.1 --port=${port} --no-reload`,
        ].join(' && '),
        url: `http://127.0.0.1:${port}/admin/login`,
        env: {DB_CONNECTION: 'sqlite', DB_DATABASE: database, PHP_CLI_SERVER_WORKERS: '4'},
        reuseExistingServer: false,
        timeout: 180_000,
    },
})
