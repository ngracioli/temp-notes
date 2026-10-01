---
name: git-commit
description: Create Conventional Commits for staged changes. Use when the user asks to commit, write a commit message, or split changes into commits. Analyzes `git diff --staged`, proposes atomic commits, runs tests and linters first, and writes English messages following the project git guideline.
---

# Git Commit

Follow the Git Workflow and Language guidelines in AGENTS.md. Every message is in English.

## 1. Check the branch

- Run `git branch --show-current`. If it is `main`, stop and create a branch first: `git switch -c <feat|fix|chore>/<english-kebab-slug>`.

## 2. Inspect what is staged

- Run `git status --short` and `git diff --staged`.
- Nothing staged → show `git status` and ask the user what to stage. Do not run `git add -A` on your own.
- Staged `.env`, credentials, keys, or other secrets → unstage them (`git restore --staged <file>`) and warn the user.

## 3. Decide whether to split

- Group the staged hunks by intent. One commit = one intent = one type.
- If the diff mixes intents (e.g. a bug fix + an unrelated refactor, or a feature + a dependency bump), propose a split to the user listing each commit's files/hunks and message. After approval, unstage everything (`git restore --staged .`) and re-stage per commit with `git add <paths>` (use `git add -p` only if the user does it, interactive mode is unavailable to you).

## 4. Run checks before committing

Run, and fix failures before continuing:

1. `composer lint` then `composer lint:check`
2. `npm run check:fix` then `npm run check`
3. `npm run types:check`
4. `composer types:check`
5. `php artisan test --compact`

If a fixer changed files, re-stage only those files that were already part of the commit.

## 5. Write the message

```
<type>(<optional scope>): <description>

<optional body: why the change was made, wrapped at 72 chars>

<optional footer: BREAKING CHANGE: ..., Refs: #123>
```

- Types: `feat`, `fix`, `refactor`, `perf`, `test`, `docs`, `style`, `build`, `ci`, `chore`, `revert`.
- Scope: lowercase domain/area (`notes`, `auth`, `settings`, `deps`).
- Description: imperative, lowercase start, no trailing period, header ≤ 72 characters.
- Breaking change: `!` after type/scope and/or `BREAKING CHANGE:` footer.
- Picking the type: new user-facing behavior → `feat`; bug → `fix`; code change without behavior change → `refactor`; only tests → `test`; only docs or `.ai/` guidelines → `docs`; formatting only → `style`; dependencies/build config → `build`; `.github/workflows` → `ci`; anything else → `chore`.

## 6. Commit

- Use `git commit -m "<header>" -m "<body>"` (repeat `-m` per paragraph). Never use `--no-verify`.
- Show the user the resulting `git log --oneline -n <count>`.
- Never push to `main` and never force-push `main`.
