# fatihalper.site

Public code baseline for a personal archive site built with [Kirby CMS](https://getkirby.com).

This repository intentionally contains only the application code, Kirby blueprints, templates, snippets, plugins, frontend assets and cPanel deployment configuration. Live content, private identity assets, generated media, accounts, sessions, logs and secrets are kept outside Git.

## Stack

- [Kirby CMS](https://getkirby.com) as the flat-file content management system
- Kirby Panel blueprints for editorial workflows
- PHP templates and snippets for the frontend
- Vanilla CSS/JavaScript assets
- cPanel Git Version Control deployment via `.cpanel.yml`

## Project Shape

- `site/blueprints/`: Panel structure, page models and field definitions
- `site/templates/`: Kirby page templates
- `site/snippets/`: reusable frontend and layout snippets
- `site/plugins/`: project-specific Kirby plugins and bundled plugin code
- `assets/`: frontend CSS and JavaScript
- `tools/`: local validation and guardrail scripts
- `.cpanel.yml`: safe cPanel deployment tasks for shared hosting

The home page editing flow is exposed through `site/blueprints/site.yml` and the `home` page blueprint. It keeps homepage controls such as hero, marquee gallery, tag wall and manifesto/quote sections available from the Panel without committing live content to this repository.

## Code-Only Policy

The repository is public and should remain code-only. The following are intentionally ignored:

- `.env`
- `content/`
- `media/`
- `site/accounts/`
- `site/cache/`
- `site/sessions/`
- `site/logs/`
- `site/config/.license`
- deploy bundles, logs and raw/private media files

Before committing, run:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File .\tools\check-code-only.ps1
```

Additional agent instructions live in `AGENTS.md` and `.cursor/rules/code-only-public-repo.mdc`.

## Deployment

cPanel pulls this repository into a separate repository directory and deploys selected code paths into `public_html`.

The deployment script copies only application code and leaves live data untouched:

- keeps `content/`, `media/`, `.env`, Kirby license, accounts, cache, sessions and logs on the host
- updates blueprints, templates, snippets, plugins, assets, Kirby core files and config code

After pushing code changes:

1. Open cPanel Git Version Control.
2. Select the repository.
3. Run `Update from Remote`.
4. Run `Deploy HEAD Commit`.

## Kirby Attribution

This project is built on Kirby CMS. Kirby is a commercial CMS by Bastian Allgeier GmbH. A valid Kirby license is required for production use and must be stored outside Git in `site/config/.license`.

Kirby links:

- [Kirby website](https://getkirby.com)
- [Kirby documentation](https://getkirby.com/docs)
- [Kirby license](https://getkirby.com/license)
