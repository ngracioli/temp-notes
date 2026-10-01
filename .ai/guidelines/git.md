# Git Workflow

## Commit Messages (Conventional Commits 1.0.0)

Format: `<type>(<optional scope>): <description>`

- Allowed types: `feat`, `fix`, `refactor`, `perf`, `test`, `docs`, `style`, `build`, `ci`, `chore`, `revert`.
- Scope is optional, lowercase, and names the domain or area: `notes`, `auth`, `settings`, `deps`.
- Description: imperative mood, lowercase first letter, no trailing period, max 72 characters for the whole header line.
    - Good: `feat(notes): add expiration date`
    - Bad: `Added expiration date.`, `feat: Adds Expiration`
- Body (optional): blank line after the header, explains _why_, wrapped at 72 characters.
- Breaking change: add `!` after the type/scope (`feat(api)!: remove v1 endpoints`) and/or a `BREAKING CHANGE: <description>` footer.
- One commit = one intent. If a change fits two types (e.g. a fix plus a refactor), split it into two commits.
- Commit messages are written in English (see the Language guideline).
- The `.githooks/commit-msg` hook enforces the header format. `composer setup` enables it; in an existing clone run `git config core.hooksPath .githooks` once.

## Branches (GitHub Flow)

- `main` is always deployable and passes CI.
- Work happens on short-lived branches: `feat/<slug>`, `fix/<slug>`, `chore/<slug>`; the slug is English kebab-case (`feat/note-expiration`).
- Changes reach `main` only through a pull request, merged with squash. The PR title follows the Conventional Commits format, because it becomes the squash commit message.

## Pull Requests

- Open, update, and merge PRs with the `pull-request` skill; the description follows `.github/pull_request_template.md`.
- Merge only through a PR, always with squash, and only after asking the user.
- The PR title follows Conventional Commits, because the squash turns it into the commit on `main`. The `pr-title` workflow (`.github/workflows/pr-title.yml`) enforces it with the same rule as the `commit-msg` hook.

## Forbidden

- Never commit `.env` or any file containing secrets.
- Never push directly to `main`.
- Never use `git push --force` (or `--force-with-lease`) on `main`.
- Never skip hooks with `--no-verify`.
