# Testing and Quality Checks

## Tests (Pest)

- Every new Action and every new HTTP flow (route + controller method) gets a Feature test in `tests/Feature/<Domain>/` (e.g. `tests/Feature/Notes/CreateNoteTest.php`).
- Create tests with `php artisan make:test --pest <Domain>/<Name>Test --no-interaction`.
- `tests/Pest.php` already applies `RefreshDatabase` to `tests/Feature`; do not add it again per file.
- Test names and descriptions are English sentences describing behavior: `it('expires a note after its ttl')` or `test('guests cannot create notes')`.
- HTTP tests cover: the happy path, validation failures (`assertSessionHasErrors('field')`), authorization (another user gets `403`), and guest access (redirect to `login`).
- Use model factories and their states; never insert rows by hand.
- Call routes by name: `$this->post(route('notes.store'), [...])`.

## Commands

| Purpose                         | Command                                                                                  |
| ------------------------------- | ---------------------------------------------------------------------------------------- |
| Run one test file / filter      | `php artisan test --compact tests/Feature/Notes/CreateNoteTest.php` or `--filter=<name>` |
| Full PHP suite                  | `php artisan test --compact`                                                             |
| PHP format (fix)                | `composer lint` (Pint)                                                                   |
| PHP format (check)              | `composer lint:check`                                                                    |
| PHP static analysis             | `composer types:check` (PHPStan / Larastan)                                              |
| JS/TS/Vue lint + format (check) | `npm run check` (Vite+ `vp check`)                                                       |
| JS/TS/Vue lint + format (fix)   | `npm run check:fix`                                                                      |
| Vue/TS type check               | `npm run types:check` (`vue-tsc --noEmit`)                                               |
| Everything CI runs              | `composer ci:check`                                                                      |

- This project does not use ESLint or Prettier; linting and formatting of frontend code run through Vite+ (`vp check`), configured in `vite.config.ts`.

## Definition of Done

A task is done only when all of these pass:

1. `php artisan test --compact`
2. `composer lint` (then `composer lint:check` is clean)
3. `composer types:check`
4. `npm run check:fix` (then `npm run check` is clean)
5. `npm run types:check`

Run them before every commit. `composer ci:check` runs the same checks as GitHub Actions (`.github/workflows/tests.yml`).
