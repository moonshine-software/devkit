# Devkit browser tests

Run from the devkit root:

```bash
npm run e2e
npm run e2e -- e2e/json.spec.js e2e/show-when.spec.js e2e/selects-deep.spec.js e2e/relationships.spec.js
```

The suite uses the package at `./moonshine`. To test PR #2067, point an isolated devkit copy at that checkout; keep the app's published `lang/` translations and publish the checkout's built assets. See the worktree setup in the root `AGENTS.md`.

Playwright starts Laravel on `E2E_PORT` (default 8099) and a local REST fixture on `E2E_REST_PORT` (default the next port). It rebuilds `database/e2e.sqlite`; it does not use the development database. Both PHP and the browser use UTC. The REST resource uses `REST_USERS_API_URL`; outside E2E its default remains `http://127.0.0.1:8001/api`.

`E2eSeeder` writes the registered resource/page inventory to `.playwright-mcp/e2e-inventory.json`. `catalog.js` and `pages.spec.js` cover all 17 resources: index, create, edit, and every registered detail page. They also cover all 11 standalone page types, profile identity, and guest login. The error page is tested through a real 404 response. The catalog check fails when a resource or page is added without corresponding coverage.

The focused suites exercise nested JSON persistence, row removal/reordering, showWhen/showWhenRow, date/range conditions, native/group/multiple/async selects, dependent Shop/Mechanic selects, MorphTo, MorphOne, MorphMany, and HasMany async modal validation, repeated creation, editing, and table refresh. REST page rendering and role persistence use the local HTTP fixture, not the external API service.

Use `test` from `fixtures.js` for new scenarios. It fails on uncaught browser errors, HTTP 5xx, and executed XSS fixtures, and attaches console warnings/errors and XHR/fetch statuses to the report. Failure traces and screenshots are stored under `.playwright-mcp/e2e-results`; the HTML report is in `playwright-report/`.
