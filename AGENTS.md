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
- A clean `composer outdated --direct` does not mean every transitive dependency is latest. As checked on 2026-09-05, FastExcel constrains OpenSpout to 4.x, Pest 5.1.3 blocks PHPUnit >13.3.1, and Laravel/ramsey UUID constrain brick/math. Do not bypass these upstream constraints with aliases or forced overrides.
- Laravel 11 compatibility resolution currently selects `11.x-dev` and reports framework advisories. Record the actual resolved version when reporting compatibility; do not describe this as a test of the latest stable Laravel 11 release.
- The split repositories are read-only for manual development: only explicitly requested branch creation is allowed; never make manual commits or pushes there. Code reaches them through the main repository's split workflow.
- On 2026-09-05, main-repository `5.x` was fast-forwarded to `4.x` at `7b4a3691a`, and all nine split repositories received `5.x` refs at their existing `4.x` commits. PR #2058 includes `da4891106`, adding `5.x` alongside `4.x` in `split-commits.yml`; synchronization becomes active after merge. The release workflow expects the subtree commit to already exist in each split repository.
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
- When project investigation reveals a non-obvious constraint or a recurring source of confusion, record the verified finding and practical workaround in this `AGENTS.md`. Update outdated notes rather than accumulating conflicting instructions.

## MoonShine 5 API migration

- Resource-level `HasFilters`, `HasHandlers`, and `HasQueryTags` are removed. Define filters, handlers, and query tags on `IndexPage`; consumers must use the page's contracts. Query-state storage (`getFilterParams`) remains in `ResourceQuery`.
- Import/export 3.x keeps `ImportExportConcern` and `HasImportExportContract` on the resource and adds `ImportExportHandlersConcern` on its index page. Move `export`, `import`, `isExportToCsv`, and `handlers` overrides to that page; use `getResource()` for resource data.
- Import/export PR #8 is merged into `3.x` at `7163180`. MoonShine requires `moonshine/import-export: 3.x-dev` in both the root and Laravel package `require-dev`, installed through Packagist/GitHub. Composer names numeric branches `3.x-dev`, not `dev-3.x`. No local path repository is needed. Switch both requirements to `^3.0` after the 3.x release.
- For local integration testing of an external package, use a Composer path repository with mirroring (`symlink: false`). Pest Arch resolves real paths and otherwise scans symlinked packages outside `vendor` as MoonShine source, causing unrelated architecture failures.
- Testbench can retain published `App\MoonShine` forms in `moonshine/app` and references in `moonshine/vendor/orchestra/testbench-core/laravel/config/moonshine.php`. A stack trace through those files tests the published copy; refresh it from current source or use a fresh Testbench installation before attributing failures to the package.

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

## Known Gotchas

- Do not assume `/admin/resource/{resource}/crud/{id}/edit` exists in this devkit; it may hit `CrudController::edit does not exist`. Use MoonShine resource page URLs generated by the resource/page classes.
- Publishing assets is required after rebuilding MoonShine UI; otherwise the browser may still load old `public/vendor/moonshine/assets/app.js`.
- `php artisan optimize:clear` clears MoonShine/Laravel caches and is useful after changing resource/page classes.
- The devkit worktree may already contain unrelated local changes. Do not revert them unless explicitly asked.
- Async tables may keep a hidden template table in the DOM. Browser assertions must target `main table:visible`, otherwise pagination can appear unchanged even when the rendered rows update correctly.
- Dashboard Posts and Comment form /6 were rechecked after enum conversion fixes on 2026-09-05: rows and Blue labels render successfully. TinyMCE's evaluation/license warning remains. Keep browser/devkit findings separate from package test results.
- Select reset was fixed on local branch `codex/ui-reset-observer` and published independently in PR #2062 (`codex/fix-select-reset` → `5.x`): TomSelect 2.6.2 `sync()` rereads native options with its default `text` label field, losing MoonShine's `label`. Restore selected native values with `setValue` to preserve option metadata. Two regression cases and browser Default/Event reset checks pass.
- MutationObserver errors on Forms were traced through CDP to `Electron Isolated Context` (`isDefault: false`), not the application document context. Do not add speculative guards to MoonShine TableBuilder/ActionButton for these errors. Evidence: `.playwright-mcp/ui-reset-observer-2026-09-05.md`.
- REST Users needs its separate API backend on 127.0.0.1:8001; cURL error 7 means it is not running. See `.playwright-mcp/phpstan-browser-audit-2026-09-05.md` for the original audit scope.

## PHPStan

- Run the root analysis and all package scripts from `moonshine`; the shared configuration is `phpstan.neon.dist` and package paths live in `composer.json` scripts. All run at `max`; no source files are excluded. `configDirectories: [src/Laravel/config]` identifies the package config for Larastan, avoiding false `env()`-outside-config diagnostics.
- Larastan's Testbench bootstrap does not discover the root package's provider. `phpstan/bootstrap.php` registers `MoonShineServiceProvider` so analysis sees the real macros and Artisan signatures. Without it, command options and arguments fall back to broad unions. A separate Request stub conflicts with Larastan's own Request stub.
- Laravel's `value()` PHPDoc uses one `TArgs` template for every callback argument, which incorrectly rejects heterogeneous argument lists. `phpstan/helpers.stub` describes its actual mixed arguments; Larastan's existing ValueExtension still infers the callback's result.
- The installed Laravel Enumerable contract bounds `when()`'s return template to null, although Collection uses Conditionable and allows returned values. FieldsContract declares the Conditionable signatures explicitly so fluent field callbacks keep their types.
- PHPStan only analyses trait bodies through consuming classes. `phpstan/traits.php` provides analysis-only contexts for public traits and cross-package consumers; keep it in the root and all package Composer commands. Removing `trait.unused` ignores without adding these contexts leaves some code unchecked. This exposed real parent-relation typing errors in `ResourceWithParent`.
- Global PHPStan ignores are removed and unmatched-ignore reporting is enabled. The remaining inline exceptions document dynamic factory/caster boundaries, Eloquent's model-only collection generic, Laravel's by-reference `data_set`, and the older-Laravel provider guard. Do not replace these with false `@var` declarations or disable checks to lower the count.
- `Stringify::value()` validates dynamic string inputs: UnitEnum uses EnumToString (including custom toString), while scalar values, null, Stringable objects and resources retain native string conversion; arrays and other non-stringable objects raise TypeError. Relationship option labels and async search also accept enum-cast model columns, covered by EnumLabelsTest. Disk options are `array<string, mixed>` because filesystem adapters accept nested metadata and boolean options; do not restrict their values to strings.
- PhpStorm 2026.1.2 needs direct generic forwarding getters on Laravel ModelResource and CRUD pages for resource → page and page → resource → page autocomplete. The user verified this in the IDE. ModelResource template order is Model, IndexPage, FormPage, DetailPage; preserve these getters even when PHPStan can infer inherited types without them.
- Resource display columns may be Eloquent-cast enums. FormPage and DetailPage must pass breadcrumb labels through `EnumToString::convert()` before string conversion; a scalar-only `@var` and direct cast caused a verified HTTP 500 regression. `PageBreadcrumbsTest` covers the page → resource → cast attribute → Breadcrumbs rendering flow; testing the Breadcrumbs component alone did not catch this earlier failure.
- If sandbox approval for PHPStan's parallel worker sockets times out, `--debug` runs the same rules in-process without sockets. It disables the result cache and adds analysed paths before the final report, so allow a slower run and extract the final JSON object when collecting results.

- A fresh 4.x checkout needs the ignored `phpunit.xml.dist` copied/configured before Pest: otherwise it falls back to SQLite and lacks APP_KEY. PublishCommandTest also expects a checkout directory named `moonshine` in asset paths; use a parent directory to distinguish temporary worktrees. Reinstall Testbench after moving a prepared checkout to clear published path references.

- On 4.x, breadcrumbs, regular relationship option labels and relationship previews already convert enums. The confirmed enum bug is WithAsyncSearch::getAsyncSearchOption, for both the column and formatting callback. PR #2061 fixes only that method using EnumToString::convert(), preserving scalar casts, with 18 regression scenarios. Do not backport the 5.x PHPStan Stringify/config/generic changes for this fix.

- Select reset is version-dependent on 4.x: the lockfile pins TomSelect 2.4.3 (both regression cases pass before the fix), while the allowed 2.6.2 reproduces undefined in both cases. PR #2063 backports the reset fix without updating package.json/package-lock.json; all 94 JS tests pass on each version. Test with the actual locked dependencies before claiming the shipped 4.x bundle is affected. The version difference is in TomSelect addOption(): 2.4.3 ignores an existing option key, while 2.6.2 calls updateOption() and replaces its data. This exposes sync() rereading labels under the default text key instead of MoonShine's label key.
