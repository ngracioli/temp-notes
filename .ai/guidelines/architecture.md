# Backend Architecture

## Request Flow

Route → Form Request (validation) → Controller (orchestration) → Action (business logic) → `Inertia::render()` or redirect.

## Controllers

- Controllers live in `app/Http/Controllers/<Domain>/` (e.g. `app/Http/Controllers/Settings/ProfileController.php`) and extend `App\Http\Controllers\Controller`.
- Controllers only orchestrate: type-hint a Form Request, call an Action, authorize, and return `Inertia::render('<domain>/<Page>', [...])` or `to_route('<name>')`.
- Controllers MUST NOT contain business rules, query building beyond a single scope/relation call, or inline `$request->validate()`.
- Prefer resourceful method names: `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`. Use a single-action invokable controller (`__invoke`) for non-CRUD endpoints.
- Static pages with no data use `Route::inertia()` instead of a controller (see `routes/web.php`).
- Success feedback uses `Inertia::flash('toast', ['type' => 'success', 'message' => __('...')])`, matching `ProfileController`.

## Validation

- All validation lives in Form Requests in `app/Http/Requests/<Domain>/<Verb><Noun>Request.php` (e.g. `app/Http/Requests/Notes/StoreNoteRequest.php`).
- Create them with `php artisan make:request <Domain>/<Name>Request --no-interaction`.
- Use array-based rules with a `@return array<string, ValidationRule|array<mixed>|string>` docblock, matching `app/Http/Requests/Settings/ProfileUpdateRequest.php`.
- Rules shared by several requests go in a trait in `app/Concerns/` (e.g. `ProfileValidationRules`).
- Controllers pass `$request->validated()` to Actions, never `$request->all()`.

## Actions

- Business logic lives in single-purpose Action classes: `app/Actions/<Domain>/<VerbNoun>.php` (e.g. `app/Actions/Notes/CreateNote.php`, `app/Actions/Notes/ExpireNote.php`).
- Create them with `php artisan make:class Actions/<Domain>/<VerbNoun> --no-interaction`.
- Each Action exposes exactly one public method, `handle()`, with typed parameters and an explicit return type. Dependencies are injected via constructor property promotion.
- Actions receive plain data (validated arrays, models, DTO-like values), never a `Request` object, so they can be reused from controllers, jobs, and commands.
- Wrap multi-write operations in `DB::transaction()` inside the Action.
- Exception: `app/Actions/Fortify/` classes implement Fortify contracts and keep Fortify's method names (`create`, `reset`, ...). Do not rename them.

## Authorization

- Authorization rules live in Policies: `app/Policies/<Model>Policy.php`, created with `php artisan make:policy <Model>Policy --model=<Model> --no-interaction`.
- Controllers call `Gate::authorize('<ability>', $model)` or use the `can:` middleware on routes. Form Request `authorize()` may delegate to the policy via `$this->user()->can(...)`.
- Never hard-code ownership checks (`$note->user_id === auth()->id()`) in controllers or Actions.

## Models and Queries

- Models live in `app/Models/`, each with a factory in `database/factories/`.
- Query constraints used in more than one place become Eloquent local scopes declared with the `#[Illuminate\Database\Eloquent\Attributes\Scope]` attribute on a `protected` method (e.g. `protected function expired(Builder $query): void`, called as `Note::expired()`).
- Eager load relations used in responses (`with()`, `load()`) to avoid N+1 queries.
- Use `casts()` for dates, enums, booleans, and JSON columns.

## Naming Conventions

| Item              | Convention                    | Example                      |
| ----------------- | ----------------------------- | ---------------------------- |
| Model             | singular PascalCase           | `Note`                       |
| Table             | plural snake_case             | `notes`, `note_tags`         |
| Column            | snake_case                    | `expires_at`, `user_id`      |
| Controller        | singular model + `Controller` | `NoteController`             |
| Method / variable | camelCase                     | `expireNote()`, `$expiresAt` |
| Route URI         | kebab-case                    | `/shared-notes`              |
| Route name        | dot-separated, resource style | `notes.index`, `notes.store` |
| Form Request      | `<Verb><Noun>Request`         | `StoreNoteRequest`           |
| Action            | `<Verb><Noun>`                | `CreateNote`                 |
| Policy            | `<Model>Policy`               | `NotePolicy`                 |
| Enum keys         | TitleCase                     | `NoteStatus::Expired`        |

## Routes

- General app routes go in `routes/web.php`; settings routes stay in `routes/settings.php`. A new domain with many routes may get `routes/<domain>.php` required from `routes/web.php`, as `settings.php` is.
- Every route has a name. Authenticated pages use the `['auth', 'verified']` middleware group.

## Dependencies

- Prefer native Laravel features (queues, events, notifications, policies, scopes, casts, validation) over third-party packages.
- Do not add Composer or npm dependencies without the user's approval.
