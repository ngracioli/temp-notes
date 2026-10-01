<!--
Sync Impact Report
- Version change: template → 1.0.0
- Principles defined: I. Test-First (NON-NEGOTIABLE), II. Thin Controllers, Actions, and Policies,
  III. Conventional Frontend, IV. English Only, V. Simplicity and Native Laravel
- Added sections: Workflow and Quality Gates, Governance
- Removed sections: none
- Templates: no changes required (plan-template "Constitution Check" reads this file at runtime)
- Deferred TODOs: none
-->

# Temp Notes Constitution

The rules live in `.ai/guidelines/` and are compiled into `AGENTS.md` by `php artisan boost:update`.
This constitution names the non-negotiable principles and points to those guidelines; it does not
copy them. Read the referenced guideline before planning or implementing.

## Core Principles

### I. Test-First (NON-NEGOTIABLE)

Every behavior change follows red → green → refactor with the `tdd` skill: one failing test, shown
failing for the right reason, before any production code. Each behavior is tested in exactly one
layer. See `.ai/guidelines/testing.md`.

### II. Thin Controllers, Actions, and Policies

Validation lives in Form Requests, business logic in single-purpose Actions, authorization in
Policies. Controllers only orchestrate. See `.ai/guidelines/architecture.md`.

### III. Conventional Frontend

Inertia + Vue pages are typed, use Wayfinder instead of hard-coded URLs, and reuse shadcn-vue
components before creating new ones. See `.ai/guidelines/frontend.md`.

### IV. English Only

Everything written to the repository is in English: code, UI text, tests, docs, specs, commits,
branches, and PRs. See `.ai/guidelines/language.md`.

### V. Simplicity and Native Laravel

Build only what the spec requires. Prefer native Laravel features; no new Composer or npm
dependency without the user's approval. See `.ai/guidelines/architecture.md` (Dependencies).

## Workflow and Quality Gates

- Spec Kit is used for new features only, following `.ai/guidelines/spec-driven.md`.
- Branches are `feat/<slug>` (or `fix/`, `chore/`); commits follow Conventional Commits; changes
  reach `main` only through a squash-merged PR opened with the `pull-request` skill, and merging
  requires the user's explicit approval. See `.ai/guidelines/git.md`.
- A task is done only when the Definition of Done passes (`composer ci:check`). Never commit with
  a red suite, secrets, or `.env` files. See `.ai/guidelines/testing.md`.

## Governance

- `.ai/guidelines/` is the single source of truth. If this constitution and a guideline conflict,
  the guideline wins and this file is corrected.
- Rule changes are made in the guideline, followed by `php artisan boost:update`. This file changes
  only when a principle is added, removed, or redefined, through `/speckit.constitution`.
- Every `plan.md` passes the Constitution Check against these principles; any deviation is
  justified in its Complexity Tracking section. PR self-review checks compliance.
- Versioning: MAJOR for removed or redefined principles, MINOR for added principles or sections,
  PATCH for wording.

**Version**: 1.0.0 | **Ratified**: 2026-10-01 | **Last Amended**: 2026-10-01
