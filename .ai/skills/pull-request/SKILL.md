---
name: pull-request
description: Prepare, open, review, update, and merge pull requests in this repository. Use whenever the user asks to open a PR, prepare a branch for review, self-review changes, write a PR title or description, watch PR checks, or merge a PR. Covers rebase on main, checks, self-review of the diff, PR size, Conventional Commits title, the PR template, CI follow-up, and squash merge with confirmation.
---

# Pull Request

Follow the Git Workflow, Testing, and Language guidelines in AGENTS.md. The PR is the permanent record of why a change was made, and the moment to review your own work, even on a solo project. Title and description are in English.

## 1. Branch

- [ ] `git branch --show-current` must be `feat/…`, `fix/…`, `chore/…` (or another Conventional Commits type), never `main`. On `main`: create a branch first.
- [ ] Commit pending work with the `git-commit` skill. `git status --short` must be clean.
- [ ] Rebase on the latest main: `git fetch origin && git rebase origin/main`. Resolve conflicts, then re-run the checks below. If the branch was already pushed, update it with `git push --force-with-lease` (allowed on your own branch, never on `main`).

## 2. Checks

- [ ] `composer ci:check` exits 0 (format, lint, types, Vitest, full Pest suite). If browser tests exist, run `npm run build` first.
- [ ] Any failure: fix it on this branch with a new commit, then run the checks again.

## 3. Self-Review

Read the whole diff as a reviewer: `git diff origin/main...HEAD` (and `git diff --stat origin/main...HEAD` for the overview). Fix everything below before opening:

- [ ] Dead code, commented-out code, unused imports, variables, methods, or files.
- [ ] Leftover debugging: `dd(`, `dump(`, `ray(`, `var_dump(`, `console.log(`, `debugger`, `->dump()`, `.only(`. Quick scan: `git diff origin/main...HEAD | grep -nE '^\+.*(dd\(|dump\(|ray\(|var_dump\(|console\.log\(|debugger|\.only\()'`.
- [ ] `TODO`/`FIXME` added without a reason; resolve them or explain them in Notes.
- [ ] Secrets: `.env`, keys, tokens, passwords, real customer data. `git diff --name-only origin/main...HEAD` must not list `.env` or credential files.
- [ ] Files that do not belong: screenshots, local configs, generated code (`resources/js/actions`, `resources/js/routes`, `resources/js/wayfinder`), editor folders.
- [ ] Guideline violations in `.ai/guidelines/` (architecture, frontend, testing, language): logic in controllers, inline validation, missing tests for new behavior (TDD), non-English text.

## 4. Size: One PR = One Goal

- [ ] The PR has a single objective that fits in the title.
- [ ] Count changed lines without lock files: `git diff --numstat origin/main...HEAD -- . ':!composer.lock' ':!package-lock.json' | awk '{s+=$1+$2} END {print s}'`.
- [ ] If the diff mixes goals (e.g. a feature plus an unrelated refactor) or exceeds ~400 changed lines, propose a split to the user: list the smaller PRs, their order, and which commits go in each. Split only after approval (new branches from `origin/main`, `git cherry-pick` the commits).

## 5. Title

- [ ] Conventional Commits, same rules as commit headers (see Git Workflow): `<type>(<optional scope>): <description>`, imperative, lowercase, no trailing period, max 72 characters. Example: `feat(notes): add expiration date`.
- [ ] It describes the whole PR, because the squash merge turns it into the single commit on `main`.
- [ ] CI validates it with `.github/workflows/pr-title.yml` (same rule as the local `commit-msg` hook).

## 6. Description

Use `.github/pull_request_template.md`. Write for someone with no context, including the author six months from now.

- **Summary:** what changes, in 1-3 sentences.
- **Why:** the problem or motivation; link issues if any.
- **Changes:** main points, grouped by area; mention migrations, new dependencies, and config changes explicitly.
- **How to test:** exact commands and manual steps, with expected results.
- **Screenshots:** for visual changes; otherwise "None".
- **Notes:** decisions, trade-offs, known limitations, risks, what was not tested, follow-ups. Be honest; never claim checks you did not run.
- **Checklist:** tick only what is true.

## 7. Open

- [ ] `gh auth status`. Not authenticated: stop and tell the user; do not try other ways to open the PR.
- [ ] `git push -u origin <branch>`.
- [ ] `gh pr create --base main --title "<title>" --body-file <file>` (write the body to a temp file from the template). Add `--draft` while the work is incomplete; mark it ready with `gh pr ready` when it is done.

## 8. After Opening

- [ ] Watch CI: `gh pr checks <number> --watch`.
- [ ] A check fails: read the log (`gh run view <run-id> --log-failed`), reproduce locally, fix on the same branch with a new Conventional Commit, push, and watch again until green.
- [ ] If the scope, approach, or test results change, update the description: `gh pr edit <number> --body-file <file>`.

## 9. Merge

- [ ] Only with CI green.
- [ ] ALWAYS ask the user before merging, even if they approved a previous PR.
- [ ] Always squash: `gh pr merge <number> --squash --delete-branch`.
- [ ] If the user says they already merged on GitHub, do not merge again; check with `gh pr view <number> --json state` and go to step 10.

## 10. After the Merge

- [ ] `git switch main && git pull --ff-only`.
- [ ] Delete the local branch: `git branch -D <branch>` (needed after squash, since git does not see it as merged).
- [ ] Delete the remote branch if it still exists: `git push origin --delete <branch>`, then `git fetch --prune`.
- [ ] Confirm: `git status -sb` shows `main...origin/main` with no divergence, and `git ls-remote --heads origin` no longer lists the branch.
