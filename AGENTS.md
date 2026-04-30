# Agent Guardrails

This repository is a public, code-only Kirby CMS baseline. Treat live content and private site identity as out of scope.

## Hard Rules

- Do not edit, create, rename, delete, stage or commit files in these paths:
  - `content/`
  - `media/`
  - `site/accounts/`
  - `site/cache/`
  - `site/sessions/`
  - `site/logs/`
  - `site/config/.license`
  - `_deploy*/`
- Do not read or print secrets from `.env`, `site/config/.license`, account files, sessions, cache or logs.
- Do not add personal/social/profile links, logos, favicons, uploaded media or generated media to Git.
- Keep repository changes limited to code, blueprints, templates, snippets, plugins, translations, build scripts and deploy config.
- Preserve Kirby field keys and content contracts unless the user explicitly requests a migration.
- Do not run destructive Git commands such as `reset --hard`, `checkout --`, `clean -fdx` or force-push unless explicitly requested.

## Safe Areas

Preferred edit targets:

- `.cpanel.yml`
- `.env.example`
- `.gitattributes`
- `.gitignore`
- `.htaccess`
- `assets/css/`
- `assets/js/`
- `site/blueprints/`
- `site/config/config.php`
- `site/docs/`
- `site/plugins/`
- `site/snippets/`
- `site/templates/`
- `site/translations/`
- `tools/`

## Before Commit

Run:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File .\tools\check-code-only.ps1
```

Commits should pass the repository guardrails and should not include ignored private paths or secret-looking values.

Optional Git hook:

```powershell
git config core.hooksPath .githooks
```

If Git hook execution is blocked by the local environment, unset it and keep using the manual check:

```powershell
git config --unset core.hooksPath
```
