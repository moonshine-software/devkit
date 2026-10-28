# Agent Notes

## Project Layout

- The Laravel devkit app is the repository root.
- The local MoonShine package is `./moonshine` inside the devkit app.
- Agents may start either in the devkit root or inside `./moonshine`; check `pwd` before running commands.
- The devkit `composer.json` uses a path repository with `"symlink": true`, so edits in `./moonshine` are used by the Laravel app.

## Dependency Boundaries

- Devkit and `./moonshine` have separate Composer manifests and `vendor` directories. Run MoonShine's Composer, Pest, PHPStan, and Rector commands from `./moonshine`. Updating its dependencies does not update devkit's `vendor` directory.
- MoonShine `5.x` requires PHP `^8.4`. Keep the root and all `src/*/composer.json` requirements, README, Rector target, and CI PHP versions in sync.
- Laravel 11–13 remain supported. Symfony 7.4 and Pest 4 are still needed for Laravel 11–12 compatibility; raising the PHP minimum alone does not make it safe to require only Symfony 8 or Pest 5.
- Laravel 11 also supports the old application structure with providers in `config/app.php`. `ServiceProvider::addProviderToBootstrapFile()` exists since 11.0 and returns false when `bootstrap/providers.php` is missing; use that result to select the legacy registration path. Checking `bootstrap/app.php` does not distinguish the structures.
- Keep the `method_exists($this, 'optimizes')` guard in MoonShine's service provider: this API is absent in Laravel 11.0 and 11.20, which are still allowed by the package constraints.
- `moonshine/composer.lock` is ignored locally; `moonshine/src/UI/package-lock.json` is tracked. Verify JS dependency changes with a clean `npm ci`.
- A clean `composer outdated --direct` does not mean every transitive dependency is latest: FastExcel constrains OpenSpout to 4.x, Pest 5.1.3 blocks PHPUnit >13.3.1, and Laravel/ramsey UUID constrain brick/math. Do not bypass these upstream constraints with aliases or forced overrides.
- Laravel 11 compatibility resolution currently selects `11.x-dev` and reports framework advisories. Record the actual resolved version when reporting compatibility; do not describe this as a test of the latest stable Laravel 11 release.
- `./moonshine` and its worktrees are the main monorepo when origin is `moonshine-software/moonshine`; the presence of `src/UI/README.md` with a split-repository notice does not make this checkout a split. The manual commit/push restriction below applies to the separate package repositories, not the main monorepo.
- The split repositories are read-only for manual development: only explicitly requested branch creation is allowed; never make manual commits or pushes there. Code reaches them through the main repository's split workflow.
- The release workflow expects the subtree commit to already exist in each split repository.
- The tests, mutation, PHPStan, and JS-test workflows currently skip same-repository PRs and scheduled runs through their job-level `if`. A skipped check is not a successful validation; run checks locally or correct the workflow condition before relying on PR CI.
- After advancing main `5.x` from `4.x`, synchronize open 5.x PR branches too and verify GitHub mergeability. Dependency/Rector PRs may conflict with upstream enum handling and npm updates; preserve both source changes and rebuild distributed assets instead of resolving minified bundles by hand.

## PHP Tests

- Run `vendor/bin/pest` from `./moonshine`, using the intended PHP binary explicitly when checking compatibility. The default CLI PHP may be newer than the supported minimum.
- The test suite uses the MySQL database `moonshine_tests` configured in `phpunit.xml.dist`. SQLite is not an equivalent replacement: date-column behavior caused failures in `DateRangeFieldTest`.
- Do not run independent full test suites concurrently against the same test database; they reset shared database state.
- For temporary compatibility checkouts, copy the tests instead of symlinking them. Pest's directory-based setup and PHP's resolved `__DIR__` can otherwise point to different test roots.

## Local Task Tracking and Agent Notes

- Maintain the queue in `moonshine/TASKS.md`; work on one task at a time as requested by the user.
- Never commit or push `TASKS.md`. It is excluded through MoonShine's local `.git/info/exclude`; preserve that exclusion.
- Keep this file to what is needed to run and develop the project: commands, environment setup, and non-obvious constraints that would otherwise cause repeated mistakes. Do not add notes after ordinary tasks, reviews or investigations, and do not record PR histories, change summaries, test results, dates, commit hashes or anything else available from git, the code, READMEs or PR descriptions. Change this file only when the way to run or develop the project changes, or when an existing note is wrong or outdated; prefer editing an existing note to adding a new one.

## MoonShine 5 API migration

- Resource-level `HasFilters`, `HasHandlers`, and `HasQueryTags` are removed. Define filters, handlers, and query tags on `IndexPage`; consumers must use the page's contracts. Query-state storage (`getFilterParams`) remains in `ResourceQuery`.
- Import/export 3.x keeps `ImportExportConcern` and `HasImportExportContract` on the resource and adds `ImportExportHandlersConcern` on its index page. Move `export`, `import`, `isExportToCsv`, and `handlers` overrides to that page; use `getResource()` for resource data.
- MoonShine requires `moonshine/import-export: 3.x-dev` in both the root and Laravel package `require-dev`, installed through Packagist/GitHub. Composer names numeric branches `3.x-dev`, not `dev-3.x`. No local path repository is needed. Switch both requirements to `^3.0` after the 3.x release.
- For local integration testing of an external package, use a Composer path repository with mirroring (`symlink: false`). Pest Arch resolves real paths and otherwise scans symlinked packages outside `vendor` as MoonShine source, causing unrelated architecture failures.
- Testbench can retain published `App\MoonShine` forms in `moonshine/app` and references in `moonshine/vendor/orchestra/testbench-core/laravel/config/moonshine.php`. A stack trace through those files tests the published copy; refresh it from current source or use a fresh Testbench installation before attributing failures to the package.

## Blade Views And Performance

- Rule from the maintainer: no PHP logic in Blade. Compute values on the backend in `viewData()` or `prepareViewData()`; views only output prepared variables and use simple `@if`/`@foreach`.
- `prepareViewData(array $data)` (WithViewRenderer) is for values derived from the final attributes. Blade component tags call `withAttributes()` after the view is resolved, so the hook runs again from there and must not change the component. Attribute bags computed in `viewData()` lose tag attributes such as `x-show` on `<x-moonshine::icon>`.
- Laravel adds the tag's default slot in `renderComponent()`, after `withAttributes()`, so neither `viewData()` nor `prepareViewData()` sees `<x-moonshine::icon>…</x-moonshine::icon>` content; a slot flag computed there drops it (verified against PR #2085 at 1ee85ee07 on 2026-10-28). `renderView()` merges `prepareViewData()` into the view data only (since #2085 `2419fb0d9`); `toArray()` feeds `toStructure()`/`jsonSerialize()`, so keep render-only values (attribute bags, rendered HTML, nested components) out of `viewData()`.
- When a class view stops nesting a Blade component, reproduce its attributes exactly: drop the `@props` names of both views from the bag, escape `:attr` values with `BladeCompiler::sanitizeComponentAttribute()`, and keep the attribute order. Verify by comparing rendered HTML before/after with whitespace and generated ids normalized.
- Benchmark MoonShine changes in-process with interleaved A/B rounds against a baseline worktree; single runs vary by 10–20% on this machine. When copying a devkit variant for benchmarks, exclude only the top-level symlink (`rsync --exclude /moonshine`): an unanchored `--exclude moonshine` also drops `lang/vendor/moonshine` and `public/vendor/moonshine`, and missing translations made every page ~1.5–2× slower. Raw HTML size is misleading: the 737 KB Posts table response is 22 KB gzipped, and evaluating TomSelect/Sortable/tippy takes under 1 ms.
- Another session may run Pest against `moonshine_tests` at the same time. Worktrees for separate checks can use their own database (for example `moonshine_tests_perf`) in the ignored `phpunit.xml.dist`.

## Blade Presentation Boundary

- Prepare display values and resolve escaping preferences in component PHP (`viewData()` / `prepareBeforeRender()`); Blade should output prepared data, not read MoonShine configuration or choose global/local escaping policies. Preserve raw getters, serialized labels, and attribute values by using separate prepared display values. Keep direct Blade component usage working when moving preparation out of templates.

## Updating Assets

After changing MoonShine UI assets, publish fresh assets into the devkit app.

The `assets` make target is in `./moonshine/Makefile`. From the devkit root:

```bash
cd moonshine
make assets
```

If the agent starts in `./moonshine`, run it directly:

```bash
make assets
```

If `make assets` is not available in the current checkout, use the equivalent manual flow:

```bash
# From devkit root:
cd moonshine/src/UI
npm run build
cd ../../..
php artisan vendor:publish --tag=moonshine-assets --force
```

```bash
# From ./moonshine:
cd src/UI
npm run build
cd ../../..
php artisan vendor:publish --tag=moonshine-assets --force
```

The manual flow builds `./moonshine/src/UI` and publishes `./moonshine/src/UI/dist` into `public/vendor/moonshine`.

## Running The App

```bash
# Run from devkit root.
php artisan optimize:clear
php artisan serve --host=127.0.0.1 --port=8001
```

If the agent starts in `./moonshine`, go to the parent directory first:

```bash
cd ..
php artisan optimize:clear
php artisan serve --host=127.0.0.1 --port=8001
```

Use another port if `8001` is busy. The admin URL is:

`http://127.0.0.1:8001/admin`

## Admin Login

- Username: `dev@getmoonshine.app`
- Password: `12345`

## Browser Testing

Use Playwright MCP for live checks:

1. Open `http://127.0.0.1:8001/admin/login`.
2. Log in with the credentials above.
3. Open a resource/page URL directly when possible.
4. Use Playwright network logs to verify async requests and response bodies.
5. Check browser console warnings/errors after the interaction.
6. Save all screenshots into the `.playwright-mcp` directory.

## Playwright E2E

- Every rendering issue must be covered by a regression E2E scenario in devkit's `e2e/`. Add or extend a Playwright test that reproduces the issue and checks the affected visible content or interaction; verify that it fails before the fix and passes after it.
- Run `npm run e2e` from the devkit root (`npx playwright test e2e/smoke.spec.js` for one file). The suite starts its own server on port 8099 (`E2E_PORT`) against `database/e2e.sqlite`, rebuilt with `migrate:fresh --seed` and `E2eSeeder` on every run; the development database is not touched. It publishes MoonShine and MoonShineTree assets before starting. Failure screenshots and traces go to `.playwright-mcp/e2e-results`.
- Playwright starts `webServer` before `globalSetup`, so database preparation lives in the server command. Use `config:clear` there, not `optimize:clear`: the cache store is the database, which has no tables before migrating. Keep `--no-reload` (passes `DB_DATABASE` and `PHP_CLI_SERVER_WORKERS` to `php artisan serve`) and several workers; a single worker causes `ERR_ABORTED` and timeouts.
- Use the `test` fixture from `e2e/fixtures.js`: it fails on page errors, 5xx responses and the `E2E-XSS` console marker logged when E2eSeeder's `<img onerror>` names render as HTML.
- To test a worktree branch, use a devkit copy whose `moonshine` symlink points at the worktree (git cannot check out a branch already used by a worktree), copy `playwright.config.js`, `e2e/`, the seeder and the published `lang/` directory, symlink `node_modules`, and publish assets in the copy. `moonshine/ru` replaces the `moonshine` translation namespace, so without the app’s published English translations the UI displays raw keys and text-based E2E locators fail.
- If Post detail/form pages return 500 about missing `category_post` columns, the local database predates an edited migration; run `php artisan migrate:fresh --seed`.

## Known Gotchas

- Documentation deployment in `moonshine-software/doc` uses repository Actions Variables `DEPLOY_PATH` (directory containing artisan) and `PHP_BINARY`; both 4.x and 5.x workflows trigger on pushes to either version and pass `github.ref_name` to `documentation:download --ver`. Keep one shared `documentation-deploy` concurrency group across branches with `cancel-in-progress: false`: the server command calls `cache:clear`, and simultaneous version deployments fail with FilesystemIterator missing-directory errors.
- Adding a documentation version requires changes in two repositories: `packages/doc` (Markdown and its deploy workflow) and `moonshine-software/moonshine-laravel.com`, which registers versions in `App\Documentation::VERSION` for the switcher, unsupported-version fallback, download/reindex commands and version route. Deploy the site support before merging doc changes whose workflow downloads the new branch.
- URL scheme sanitization applies to the link destination, not its displayed text. `Url` previews can safely display `javascript:alert(1)` while rendering `href="#"`; assert the parsed href and label separately instead of rejecting the string anywhere in HTML. On 5.x, use PHP 8.4+ `Dom\HTMLDocument` with a complete HTML document for preview assertions containing SVG icons: legacy `DOMDocument::loadHTML()` raises invalid-tag warnings for SVG, which Laravel converts to exceptions.
- Text/Textarea apply escaping differs between branches. On 4.x, `unescape()` also disables escaping on save unless `escapeOnApply()` is set explicitly; use `escapeOnApply(static fn (): bool => false)` to preserve submitted markup. Check the 5.x code before assuming the branches behave identically.
- Inherited File `accept` defaults must be prepared in `systemViewData()`, not `File::booted()`: eager boot access breaks `consoleMode()` construction without Core and snapshots deferred configuration too early.
- Do not assume `/admin/resource/{resource}/crud/{id}/edit` exists in this devkit; it may hit `CrudController::edit does not exist`. Use MoonShine resource page URLs generated by the resource/page classes.
- Publishing assets is required after rebuilding MoonShine UI; otherwise the browser may still load old `public/vendor/moonshine/assets/app.js`.
- `php artisan optimize:clear` clears MoonShine/Laravel caches and is useful after changing resource/page classes.
- The devkit worktree may already contain unrelated local changes. Do not revert them unless explicitly asked.
- `#[AsyncMethod]` permits HTTP dispatch of a custom method; it does not identify the operation's authorization ability. Distinguish custom-operation authorization from page access. `authorizeResourcePage()` alone does not determine the correct ability for an arbitrary method.
- MorphOne on 4.x inherits HasOne's separate child-resource form: `fields()` configures preview, while editing uses the child resource's form fields. On a fresh save request, generated parent Hidden inputs are applied only if the child form declares the inverse MorphTo or explicit Hidden fields for both morph columns; otherwise creating fails with an INSERT missing parent columns, while updating works. Check the child form definitions before attributing this to recent changes.
- Laravel resolves a class component view before calling `withAttributes()`, so `prepareBeforeRender()` and `viewData()` run before ordinary Blade attributes arrive. Accept display preferences that affect prepared data as constructor parameters; reading the attribute bag in either hook is too late.
- Nested Blade class components inherit the parent attribute bag, so internal constructor parameters must not collide with HTML attributes such as `type`. Laravel's default slot overrides component view data named `slot`.
- `render()` caches prepared view data; tests that change config or component state between renders must call `flushRenderCache()`.
- Card display-label maps must account for `customView()` overrides of values, escapeLabel and escapeValueLabels. Generic `toArray()` merges customViewData after `viewData()`, so prepare the map in `viewData()` using those overrides; `prepareBeforeRender()` alone does not cover direct serialization.
- Labels also feed column names, serialized component state and textarea `aria-label` attributes. Keep `getLabel()` and serialized labels as raw strings; apply display escaping at the HTML boundary. Replacing view-data labels with `HtmlString` can lose labels in JSON and bypass attribute escaping.
- `WithComponentAttributes::xIfOrShow()` implements field `xIf()` with string `beforeRender`/`afterRender` callbacks that emit `<template>` tags. When escaping those callbacks, preserve these generated wrappers. Field previews wrapped in `Link` may already be escaped; do not escape their value a second time as a link label.
- Configuration values use the existing `set/get('escapes.label')`-style convention, like `auth.pipelines`; do not introduce special nested-array mutation.
- Caching `ModelDataWrapper::toArray()` must account for writes through `getOriginal()`: `ModelResource::resolveSaveHandler` applies fields directly to that model and then reads the same wrapper. Likewise, a scoped PSR request snapshot becomes stale after `request()->merge()` in `FormPage::prepareForValidation()`. PR #2085 fix `5fce78c71` (inherited by #2086 at `7a4b1a25f`, 2026-10-28) keeps model serialization live and shares only the request wrapper: `AbstractRequest` accepts a Closure factory that resolves a fresh PSR request when input is read, while session errors avoid PSR conversion. `IconComposer` resolves the final Blade slot. Prepared attributes use final customView data. Earlier aggregate benchmarks included the removed model-array cache and need a new measurement.
- The installed `gh pr edit` can fail while loading deprecated GraphQL `projectCards`. To update a PR description, use `gh api --method PATCH repos/{owner}/{repo}/pulls/{number} --input body.json` with a JSON `body` field.
- Async tables may keep a hidden template table in the DOM. Browser assertions must target `main table:visible` or a specific cell, otherwise they can read the template.
- TinyMCE's evaluation/license console warning is expected in the devkit.
- TomSelect 2.6.2 `sync()` rereads native options with its default `text` label field, losing MoonShine's `label`; restore selected native values with `setValue` to preserve option metadata. 4.x locks TomSelect 2.4.3, whose `addOption()` ignores existing keys, so test with the locked dependencies before claiming a shipped bundle is affected.
- MutationObserver errors on Forms come from the `Electron Isolated Context` (browser extension), not the application document. Do not add speculative guards to TableBuilder/ActionButton for them.
- REST Users needs its separate API backend on 127.0.0.1:8001; cURL error 7 means it is not running.

## 4.x Checkouts

- A fresh 4.x checkout needs the ignored `phpunit.xml.dist` copied/configured before Pest: otherwise it falls back to SQLite and lacks APP_KEY. PublishCommandTest also expects a checkout directory named `moonshine` in asset paths; use a parent directory to distinguish temporary worktrees. Reinstall Testbench after moving a prepared checkout to clear published path references.
- On 4.x, breadcrumbs, relationship option labels and relationship previews already convert enums; only `WithAsyncSearch::getAsyncSearchOption` needed `EnumToString::convert()`. Do not backport the 5.x PHPStan Stringify/config/generic changes for 4.x enum fixes.

## PHPStan

- Run the root analysis and all package scripts from `moonshine`; the shared configuration is `phpstan.neon.dist` and package paths live in `composer.json` scripts. All run at `max`; no source files are excluded. `configDirectories: [src/Laravel/config]` identifies the package config for Larastan, avoiding false `env()`-outside-config diagnostics.
- Larastan's Testbench bootstrap does not discover the root package's provider. `phpstan/bootstrap.php` registers `MoonShineServiceProvider` so analysis sees the real macros and Artisan signatures. Without it, command options and arguments fall back to broad unions. A separate Request stub conflicts with Larastan's own Request stub.
- Laravel's `value()` PHPDoc uses one `TArgs` template for every callback argument, which incorrectly rejects heterogeneous argument lists. `phpstan/helpers.stub` describes its actual mixed arguments; Larastan's existing ValueExtension still infers the callback's result.
- The installed Laravel Enumerable contract bounds `when()`'s return template to null, although Collection uses Conditionable and allows returned values. FieldsContract declares the Conditionable signatures explicitly so fluent field callbacks keep their types.
- PHPStan only analyses trait bodies through consuming classes. `phpstan/traits.php` provides analysis-only contexts for public traits and cross-package consumers; keep it in the root and all package Composer commands. Removing `trait.unused` ignores without adding these contexts leaves some code unchecked. This exposed real parent-relation typing errors in `ResourceWithParent`.
- Global PHPStan ignores are removed and unmatched-ignore reporting is enabled. The remaining inline exceptions document dynamic factory/caster boundaries, Eloquent's model-only collection generic, Laravel's by-reference `data_set`, and the older-Laravel provider guard. Do not replace these with false `@var` declarations or disable checks to lower the count.
- `Stringify::value()` validates dynamic string inputs: UnitEnum uses EnumToString (including custom toString), while scalar values, null, Stringable objects and resources retain native string conversion; arrays and other non-stringable objects raise TypeError. Relationship option labels and async search also accept enum-cast model columns, covered by EnumLabelsTest. Disk options are `array<string, mixed>` because filesystem adapters accept nested metadata and boolean options; do not restrict their values to strings.
- PhpStorm 2026.1.2 needs direct generic forwarding getters on Laravel ModelResource and CRUD pages for resource → page and page → resource → page autocomplete. ModelResource template order is Model, IndexPage, FormPage, DetailPage; preserve these getters even when PHPStan can infer inherited types without them.
- Resource display columns may be Eloquent-cast enums. FormPage and DetailPage must pass breadcrumb labels through `EnumToString::convert()` before string conversion; a scalar-only `@var` and direct cast cause HTTP 500. Test the page → resource → cast attribute → Breadcrumbs flow (`PageBreadcrumbsTest`), not only the Breadcrumbs component.
- If sandbox approval for PHPStan's parallel worker sockets times out, `--debug` runs the same rules in-process without sockets. It disables the result cache and adds analysed paths before the final report, so allow a slower run and extract the final JSON object when collecting results.
- A local `src/UI/vendor` installation can make PHPStan analyse third-party files and resolve outdated MoonShine contracts during partial analysis. Use a temporary configuration including `phpstan.neon.dist` with `excludePaths` pointing only to that nested vendor directory; keep every MoonShine source file and `phpstan/traits.php` in scope. When analysing a copied HEAD snapshot, also add its Laravel config directory to `configDirectories`.
