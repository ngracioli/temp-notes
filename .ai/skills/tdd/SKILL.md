---
name: tdd
description: Mandatory red-green-refactor workflow for this project. Activate whenever you implement a feature, fix a bug, or change any behavior (routes, validation, actions, policies, pages, props), before writing production code. Requires showing a failing test output before implementing.
---

# Test-Driven Development

Follow the Testing guideline in AGENTS.md. Tests, names, and messages are in English.

## Before the First Cycle

- [ ] Split the request into a list of small behaviors, one sentence each (e.g. "guests are redirected to login", "a note requires a title", "a note expires after its ttl"). Share the list with the user.
- [ ] Pick exactly one layer per behavior using the "Which Test Layer" table in the Testing guideline: Pest Feature (default: backend rules, validation, authorization, Inertia props), Pest Unit (PHP logic without database), Vitest (isolated composable/component logic, not markup), Pest Browser (only critical end-to-end flows). Never cover the same behavior in two layers.
- [ ] For a bug fix, the first behavior is "the bug no longer happens".

## The Cycle (repeat per behavior)

### 1. RED

- [ ] Write ONE small test for the next behavior only.
- [ ] Run it alone: `php artisan test --compact --filter='<test description>'` (Pest) or `npx vp test run <path/to/File.test.ts>` (Vitest).
- [ ] Confirm it fails for the right reason:
    - Valid: failed assertion, `RouteNotFoundException`, `Class "App\Actions\Notes\CreateNote" not found`, 404 on a missing route, wrong Inertia component.
    - Invalid: PHP syntax error, typo in the test, missing `use` import, broken factory. Fix the test and run it again.
- [ ] Show the failure output to the user before writing any production code.

### 2. GREEN

- [ ] Write the minimum production code that makes this test pass. Hard-coded or naive is acceptable if no test demands more.
- [ ] Do not add fields, branches, or methods that no failing test requires.
- [ ] Run the filtered test until it passes, then the full suites: `php artisan test --compact` and `npm run test`. Everything must be green.

### 3. REFACTOR

- [ ] Improve names, remove duplication, and move code to its place per the Architecture guideline (Form Request, Action, Policy, scope).
- [ ] Do not change behavior. Do not touch tests except to clean up their own duplication.
- [ ] Run both full suites again: still green.

### 4. Next

- [ ] Mark the behavior done and start RED for the next one.

## Rules

- Never edit or delete an existing test to make it pass. Only if the requirement changed: say which requirement changed and why, and repeat it in the commit body.
- Never write production code while the suite is red for an unrelated reason; fix that first.
- One test per cycle. If a test needs several new production pieces (route + controller + page), that is fine as long as all of them exist only to satisfy that test.

## Final Checklist

- [ ] `php artisan test --compact` and `npm run test`: both suites green (if browser tests exist: `npm run build`, then `composer test:browser`).
- [ ] `composer lint` then `composer lint:check`.
- [ ] `composer types:check`.
- [ ] `npm run check:fix` then `npm run check`.
- [ ] `npm run types:check` (vue-tsc).
- [ ] Commit with the `git-commit` skill: one cycle or a small group of related cycles per Conventional Commit, test and implementation together (e.g. `feat(notes): expire notes after their ttl`).
