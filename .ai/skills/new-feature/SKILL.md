---
name: new-feature
description: End-to-end checklist for building a new feature in this app (migration, model, form request, action, policy, controller, route, Vue page, Pest tests), driven by TDD. Use when the user asks to add a feature, CRUD, resource, page, or endpoint that spans backend and frontend.
---

# New Feature Checklist

Follow the Architecture, Frontend, Testing, Git and Language guidelines in AGENTS.md. This skill is driven by the `tdd` skill: activate it first, and start every step below with a failing test (RED), then the minimum code (GREEN), then refactor. Activate `laravel-best-practices`, `inertia-vue-development`, `wayfinder-development`, and `testing-best-practices` as you reach each layer. Everything written is in English.

The examples use a `Note` model in the `Notes` domain; replace with the real names.

## 0. Prepare

- [ ] Branch off `main`: `git switch -c feat/<english-kebab-slug>`.
- [ ] Confirm unclear requirements with the user before writing code.
- [ ] Inspect existing tables with the `database-schema` tool.
- [ ] Write the behavior list (tdd skill) and create the test file: `php artisan make:test --pest Notes/NoteControllerTest --no-interaction`.

## 1. Route + Controller (access)

- [ ] RED: `test('guests are redirected to the login page')` → `$this->get(route('notes.index'))->assertRedirect(route('login'))`. Expected failure: `Route [notes.index] not defined`.
- [ ] GREEN: register the route in `routes/web.php` inside the `['auth', 'verified']` group (kebab-case URI, named `notes.*`) pointing to `NoteController` (`php artisan make:controller Notes/NoteController --no-interaction`).
- [ ] Run `php artisan wayfinder:generate` after route changes.

## 2. Page Render (Inertia component + props)

- [ ] RED: `test('users see their notes')` with `assertInertia(fn (Assert $page) => $page->component('notes/Index')->has('notes', 2))`. Expected failure: wrong/missing component or missing `notes` prop, or missing `notes` table.
- [ ] GREEN, only what the test needs:
    - Migration: `php artisan make:migration create_notes_table --no-interaction` (plural snake_case table, snake_case columns, `foreignId('user_id')->constrained()->cascadeOnDelete()`).
    - Model + factory: `php artisan make:model Note --factory --no-interaction`; relations with return types; `casts()`.
    - Controller `index` returns `Inertia::render('notes/Index', [...])`.
    - Page `resources/js/pages/notes/Index.vue` with `<script setup lang="ts">` and typed props (shared types in `resources/js/types/notes.ts`, re-exported from `types/index.ts`). The Inertia assertion checks that the page file exists.
- [ ] REFACTOR: extract reused query constraints into `#[Scope]` scopes.

## 3. Validation (Form Request)

- [ ] RED, one test per rule: `test('a note requires a title')` → post invalid data, `assertSessionHasErrors('title')`. Expected failure: no errors in session (or missing route).
- [ ] GREEN: `php artisan make:request Notes/StoreNoteRequest --no-interaction`; array rules with the `@return array<string, ValidationRule|array<mixed>|string>` docblock; type-hint it in the controller.

## 4. Business Logic (Action)

- [ ] RED: `test('users can create a note')` → post valid data, `assertRedirect(route('notes.index'))`, `assertInertiaFlash('toast.type', 'success')`, `assertDatabaseHas('notes', [...])`. For isolated rules (e.g. TTL math), add a Unit test in `tests/Unit/Notes/`. Expected failure: `Class "App\Actions\Notes\CreateNote" not found` or missing row.
- [ ] GREEN: `php artisan make:class Actions/Notes/CreateNote --no-interaction`; single public `handle()` with typed params/return, no `Request`; controller passes `$request->validated()` and redirects with `Inertia::flash('toast', ['type' => 'success', 'message' => __('Note created.')])`.
- [ ] REFACTOR: `DB::transaction()` around multiple writes; move any logic left in the controller into the Action.

## 5. Authorization (Policy)

- [ ] RED: `test('users cannot update notes of other users')` → `assertForbidden()`. Expected failure: `200`/`302` instead of `403`.
- [ ] GREEN: `php artisan make:policy NotePolicy --model=Note --no-interaction`; implement only the tested ability; `Gate::authorize('update', $note)` in the controller.
- [ ] Repeat RED → GREEN per ability (`view`, `update`, `delete`).

## 6. Vue Page Behavior

- [ ] Every prop the page needs is first required by an `assertInertia` test (step 2 pattern) before you read it in the Vue file.
- [ ] Forms: `<Form v-bind="NoteController.store.form()">` (or `useForm` when programmatic control is needed); errors via `InputError`; the store/validation tests from steps 3–4 cover the submit flow.
- [ ] Links/URLs only via Wayfinder imports (`@/actions/...`, `@/routes/...`).
- [ ] Set breadcrumbs/layout with `defineOptions({ layout: { breadcrumbs: [...] } })`, as in `pages/settings/Profile.vue`.
- [ ] Reuse `components/ui/*` (shadcn-vue) and existing components; add a sidebar entry in `components/AppSidebar.vue` if needed.
- [ ] Client-side logic (computed values, formatting, state in a composable or component): RED with a Vitest test next to the file (`resources/js/composables/useNoteExpiry.test.ts`), then GREEN. Not for simple markup.
- [ ] Pure styling/layout changes need no new test.

## 7. Critical Flow (Pest Browser, only if the flow is critical)

- [ ] RED: one test in `tests/Browser/Notes/` for the main end-to-end path only, e.g. `it('creates a note and shows it in the list')` with `visit(route('notes.index'))->fill(...)->press(...)->assertSee(...)->assertNoJavaScriptErrors()`. Run `npm run build`, then `composer test:browser`. Expected failure: element or text not found.
- [ ] GREEN: wire whatever the UI still lacks. Do not repeat validation/authorization cases here; they live in Feature tests.

## 8. Verify and Commit

- [ ] Run the tdd skill's final checklist: `php artisan test --compact`, `npm run test`, `composer lint`, `composer types:check`, `npm run check:fix`, `npm run types:check`.
- [ ] Commit with the `git-commit` skill: one TDD cycle or a small group of related cycles per commit, test and implementation together (e.g. `feat(notes): list user notes`, then `feat(notes): validate note creation`).
