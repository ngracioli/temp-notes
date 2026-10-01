---
name: new-feature
description: End-to-end checklist for building a new feature in this app (migration, model, form request, action, policy, controller, route, Vue page, Pest tests). Use when the user asks to add a feature, CRUD, resource, page, or endpoint that spans backend and frontend.
---

# New Feature Checklist

Follow the Architecture, Frontend, Testing, Git and Language guidelines in AGENTS.md. Activate `laravel-best-practices`, `inertia-vue-development`, `wayfinder-development`, and `testing-best-practices` as you reach each layer. Everything written is in English.

The examples use a `Note` model in the `Notes` domain; replace with the real names.

## 0. Prepare

- [ ] Branch off `main`: `git switch -c feat/<english-kebab-slug>`.
- [ ] Confirm unclear requirements with the user before writing code.
- [ ] Inspect existing tables with the `database-schema` tool.

## 1. Migration

- [ ] `php artisan make:migration create_notes_table --no-interaction` (plural snake_case table, snake_case columns).
- [ ] Add foreign keys with `foreignId('user_id')->constrained()->cascadeOnDelete()` and indexes for filtered columns.
- [ ] `php artisan migrate`.

## 2. Model + Factory

- [ ] `php artisan make:model Note --factory --policy --no-interaction` (singular PascalCase).
- [ ] Define `$fillable`, `casts()`, relations with return types, and `#[Scope]` scopes for reused query constraints.
- [ ] Factory covers all required columns; add states for meaningful variants (e.g. `expired()`).

## 3. Form Requests

- [ ] `php artisan make:request Notes/StoreNoteRequest --no-interaction` (and `UpdateNoteRequest` if needed).
- [ ] Array rules with the `@return array<string, ValidationRule|array<mixed>|string>` docblock.
- [ ] `authorize()` delegates to the policy or returns `true` when the controller calls `Gate::authorize()`.

## 4. Actions

- [ ] `php artisan make:class Actions/Notes/CreateNote --no-interaction`; one Action per operation (`CreateNote`, `UpdateNote`, `ExpireNote`).
- [ ] Single public `handle()` method, typed parameters and return type, no `Request` objects.
- [ ] `DB::transaction()` around multiple writes.

## 5. Policy

- [ ] Implement abilities in `app/Policies/NotePolicy.php` (`view`, `update`, `delete`, ...) based on ownership/roles.

## 6. Controller

- [ ] `php artisan make:controller Notes/NoteController --no-interaction` (or `--invokable` for single actions).
- [ ] Each method: Form Request in → `Gate::authorize()` → Action → `Inertia::render('notes/Index', [...])` or `to_route('notes.index')`.
- [ ] Flash success with `Inertia::flash('toast', ['type' => 'success', 'message' => __('Note created.')])`.

## 7. Routes

- [ ] Register in `routes/web.php` inside the `['auth', 'verified']` group, kebab-case URIs, named routes (`notes.index`, `notes.store`, ...). `Route::resource()` is fine when all methods exist.
- [ ] `php artisan route:list --name=notes` to verify.
- [ ] `php artisan wayfinder:generate`.

## 8. Vue Page

- [ ] Page in `resources/js/pages/notes/Index.vue` (lowercase folder, PascalCase file), `<script setup lang="ts">`.
- [ ] Type the page props; add shared types to `resources/js/types/notes.ts` and re-export from `types/index.ts`.
- [ ] Set breadcrumbs/layout with `defineOptions({ layout: { breadcrumbs: [...] } })`, as in `pages/settings/Profile.vue`.
- [ ] Forms: `<Form v-bind="NoteController.store.form()">` (or `useForm` when programmatic control is needed); errors via `InputError`.
- [ ] Links/URLs only via Wayfinder imports (`@/actions/...`, `@/routes/...`).
- [ ] Reuse `components/ui/*` (shadcn-vue) and existing components before creating new ones.
- [ ] Add a sidebar entry in `components/AppSidebar.vue` if the feature needs navigation.

## 9. Tests

- [ ] `php artisan make:test --pest Notes/NoteControllerTest --no-interaction` covering: page renders, store/update/delete happy paths, validation errors, another user gets 403, guest is redirected to `login`.
- [ ] One test file per Action with non-trivial logic (e.g. `tests/Feature/Notes/ExpireNoteTest.php`).
- [ ] English behavior descriptions: `it('expires a note after its ttl')`.

## 10. Verify and Commit

- [ ] `php artisan test --compact`, `composer lint`, `composer types:check`, `npm run check:fix`, `npm run types:check` all pass.
- [ ] Commit with the `git-commit` skill; split migration/model/feature/tests only if they carry different intents (usually one `feat(notes): ...` commit).
