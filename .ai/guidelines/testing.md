# Testing and Quality Checks

## Test-Driven Development (mandatory)

Every behavior change (feature, bug fix, changed rule) follows red → green → refactor. Activate the `tdd` skill before starting.

- Never write production code without a failing test that requires it.
- **RED:** write ONE small test for the next behavior, in the layer chosen per "Which Test Layer". Run it alone (`php artisan test --compact --filter=<name>` or `npx vp test run <file>`) and confirm it fails for the right reason: a failing assertion or a missing class/route/component. A syntax error, missing import, or broken setup is not a valid red; fix the test and re-run. Show the failure output before writing production code.
- **GREEN:** write the minimum production code to make that test pass. No code for behaviors that no test requires yet. Run the full suites (`php artisan test --compact` and `npm run test`) and confirm everything passes.
- **REFACTOR:** improve names, duplication, and structure (per the Architecture guideline) while all tests stay green. Run the full suite again.
- Repeat for the next behavior.
- Bug fix: first write a test that reproduces the bug and fails; only then fix it.
- Never edit or delete a test to make it pass unless the requirement changed. When it did, state which requirement changed and why in your reply and in the commit body.
- Pure copy, styling, and layout-only changes are not behavior changes and need no new test.

## Which Test Layer

Each behavior is tested in exactly ONE layer. Never test the same behavior in more than one layer.

| Layer        | Location                                                      | Use for                                                                                                         | Do not use for                                                       |
| ------------ | ------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------- |
| Pest Feature | `tests/Feature/<Domain>/`                                     | Backend rules, validation, authorization, database state, redirects, flash data, Inertia component and props    | Client-side logic                                                    |
| Pest Unit    | `tests/Unit/<Domain>/`                                        | PHP logic that needs no database or container (e.g. TTL math in an Action)                                      | Anything touching the database or HTTP                               |
| Vitest       | `resources/js/**/<Name>.test.ts`, next to the file under test | Isolated logic in composables and components: calculations, state transitions, formatting                       | Simple markup, styling, or anything already covered by Inertia props |
| Pest Browser | `tests/Browser/<Domain>/`                                     | Only critical end-to-end flows (e.g. create a note and see it in the list); keep them few because they are slow | Validation rules, authorization, edge cases (use Feature tests)      |

- Default to Pest Feature. Pick another layer only when the table says so.
- `tests/Pest.php` applies `Tests\TestCase` and `RefreshDatabase` to `tests/Feature` and `tests/Browser`; do not add them again per file. `tests/Unit` does not boot Laravel; an Action that touches the database is tested in `tests/Feature/<Domain>/<Action>Test.php`.
- Create PHP tests with `php artisan make:test --pest <Domain>/<Name>Test --no-interaction` (add `--unit` for unit tests; move browser tests to `tests/Browser/<Domain>/`).
- Test names and descriptions are English sentences describing behavior: `it('expires a note after its ttl')` or `test('guests cannot create notes')`.
- HTTP tests cover: the happy path, validation failures (`assertSessionHasErrors('field')`), authorization (another user gets `403`), and guest access (redirect to `login`).
- Use model factories and their states; never insert rows by hand.
- Call routes by name: `$this->post(route('notes.store'), [...])`.

## Inertia Assertions (Feature)

```php
use Inertia\Testing\AssertableInertia as Assert;

$this->actingAs($user)
    ->get(route('notes.index'))
    ->assertInertia(fn (Assert $page) => $page
        ->component('notes/Index')
        ->has('notes', 3, fn (Assert $note) => $note
            ->where('id', $notes->first()->id)
            ->etc()
        )
    );
```

- `->component('notes/Index')` also fails when `resources/js/pages/notes/Index.vue` does not exist (`inertia.testing.ensure_pages_exist` is `true` in `config/inertia.php`), so it is a valid RED for a new page.
- Assert redirect flash data with `->assertInertiaFlash('toast.type', 'success')`.

## Vitest

- Run through Vite+ (`vp test`), configured in the `test` key of `vite.config.ts` (`happy-dom` environment).
- Import test APIs from `vite-plus/test`: `import { describe, expect, it } from 'vite-plus/test';` (see `resources/js/composables/useInitials.test.ts`).
- Mount components with `mount()` from `@vue/test-utils` and assert on emitted events and state, not on markup snapshots.

## Pest Browser

- Uses `pestphp/pest-plugin-browser` with Playwright (Chromium). First time on a machine: `npx playwright install chromium`.
- Run browser tests with `composer test:browser`, never `php artisan test --testsuite=Browser` directly: `pest-plugin-browser` stops its `sh -c` wrapper but leaves the `node playwright run-server` child alive, so the script kills it with `pkill` afterwards and keeps the test exit code. `composer test` (used by `composer ci:check`) does the same for the full suite.
- Browser tests run against built assets: run `npm run build` (or keep `npm run dev` running) before them.
- Use `visit(route('...'))`, interact with `fill()`/`click()`/`press()`, and assert visible results (`assertSee`, `assertPathIs`) plus `assertNoJavaScriptErrors()`.
- Screenshots of failures go to `tests/Browser/Screenshots/` (git-ignored).

## Commands

| Purpose                         | Command                                                             |
| ------------------------------- | ------------------------------------------------------------------- |
| Run one test (TDD cycle)        | `php artisan test --compact --filter=<name>`                        |
| Run one test file               | `php artisan test --compact tests/Feature/Notes/CreateNoteTest.php` |
| Full PHP suite                  | `php artisan test --compact`                                        |
| Browser tests only              | `composer test:browser`                                             |
| Vitest (once)                   | `npm run test` (`vp test run`)                                      |
| Vitest (watch, TDD cycle)       | `npx vp test`                                                       |
| PHP format (fix)                | `composer lint` (Pint)                                              |
| PHP format (check)              | `composer lint:check`                                               |
| PHP static analysis             | `composer types:check` (PHPStan / Larastan)                         |
| JS/TS/Vue lint + format (check) | `npm run check` (Vite+ `vp check`)                                  |
| JS/TS/Vue lint + format (fix)   | `npm run check:fix`                                                 |
| Vue/TS type check               | `npm run types:check` (`vue-tsc --noEmit`)                          |
| Everything CI runs              | `composer ci:check`                                                 |

- This project does not use ESLint or Prettier; linting and formatting of frontend code run through Vite+ (`vp check`), configured in `vite.config.ts`.

## Definition of Done and Commits

A task is done, and may be committed, only when all of these pass:

1. `php artisan test --compact` and `npm run test` (full suites green)
2. `composer lint` (then `composer lint:check` is clean)
3. `composer types:check`
4. `npm run check:fix` (then `npm run check` is clean)
5. `npm run types:check`

- Never commit with a red suite.
- One completed TDD cycle, or a small group of related cycles, = one Conventional Commit containing both the test and the implementation.
- `composer ci:check` runs the same checks as GitHub Actions (`.github/workflows/tests.yml`).
