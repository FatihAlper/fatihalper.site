<div align="center">

# fatihalper.site

Kirby CMS uzerinde kurgulanmis, icerigi Git disinda tutan, public code-only bir kisisel arsiv altyapisi.

[![Kirby CMS](https://img.shields.io/badge/Kirby-CMS-black?style=for-the-badge)](https://getkirby.com)
[![PHP](https://img.shields.io/badge/PHP-8.x-777bb4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net)
[![Last Commit](https://img.shields.io/github/last-commit/FatihAlper/fatihalper.site?style=for-the-badge)](https://github.com/FatihAlper/fatihalper.site/commits/main)
[![Code Only](https://img.shields.io/badge/repo-code--only-2f855a?style=for-the-badge)](#code-only-model)
[![Manual Deploy](https://img.shields.io/badge/deploy-cPanel%20Git-2563eb?style=for-the-badge)](#deployment-model)

<p>
  <a href="https://github.com/FatihAlper/fatihalper.site/commits/main"><strong>Commits</strong></a>
  ·
  <a href="https://github.com/FatihAlper/fatihalper.site/blob/main/.cpanel.yml"><strong>Deploy Script</strong></a>
  ·
  <a href="https://github.com/FatihAlper/fatihalper.site/blob/main/AGENTS.md"><strong>Agent Rules</strong></a>
  ·
  <a href="https://getkirby.com/docs"><strong>Kirby Docs</strong></a>
</p>

</div>

## What This Is

This repository is the public application layer of a Kirby-powered personal archive. It is designed for a site where writing, cinema notes, playlists, photography, visual research and home page curation share the same editorial system without exposing private content in Git.

The repo contains code, structure and workflow. The live archive itself stays on the host.

## System Highlights

- Kirby Panel blueprints separate production dashboards from content creation workflows.
- The home page has dedicated Panel controls for hero text, visual background, marquee gallery, tag wall, rooms, featured object and manifesto/quote sections.
- Archive content types are modeled as first-class Kirby templates: writing, book review, film review, playlist, photo album and art project.
- SEO, Open Graph, language, analytics and integration settings are handled through structured fields instead of hard-coded content.
- Spotify playlist support enriches playlist data while keeping API secrets in `.env`.
- TMDB integration support keeps film metadata workflows outside frontend templates.
- Frontend templates are PHP/Kirby-native, with small vanilla CSS/JS assets and no heavyweight build dependency.
- Public Git history is intentionally code-only, with guardrails for agentic coding tools.

## Code-Only Model

This is a public repository, so it deliberately excludes live and personal site data.

Ignored and host-local:

- `.env`
- `content/`
- `media/`
- `site/accounts/`
- `site/cache/`
- `site/sessions/`
- `site/logs/`
- `site/config/.license`
- deploy bundles, logs and raw/private media files

The intended workflow is simple: agents and developers change code, blueprints, templates, snippets, plugins, assets and deployment config. Real content, accounts, uploaded media, sessions and secrets remain outside version control.

Before committing:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File .\tools\check-code-only.ps1
```

Agent-facing rules live in:

- `AGENTS.md`
- `.cursor/rules/code-only-public-repo.mdc`

## Deployment Model

Deployment is intentionally conservative.

The cPanel deploy script copies selected application paths into `public_html` and leaves live data untouched. It updates the code layer while preserving:

- content
- uploaded/generated media
- Kirby license
- environment variables
- Panel accounts
- runtime cache, sessions and logs

Deployment status is checked from cPanel Git Version Control. The latest deployed SHA should match the latest pushed commit you want live.

## Project Map

```text
assets/                  Frontend CSS and JavaScript
kirby/                   Kirby CMS core
site/blueprints/         Panel structure and content models
site/config/config.php   Runtime config and integration options
site/plugins/            Project plugins and bundled plugin code
site/snippets/           Reusable view fragments
site/templates/          Page templates
site/translations/       Translation catalogs
tools/                   Validation and guardrail scripts
.cpanel.yml              Shared-hosting deployment tasks
```

## Self-Hosting Notes

To host or customize this project, you need to provide the private runtime layer yourself:

```text
.env
content/
media/
site/accounts/
site/config/.license
site/cache/
site/sessions/
site/logs/
```

Required environment variables depend on the enabled integrations:

```text
APP_ENV=production
PANEL_LANGUAGE=tr
TMDB_API_KEY=
TMDB_ACCESS_TOKEN=
SPOTIFY_CLIENT_ID=
SPOTIFY_CLIENT_SECRET=
SPOTIFY_REFRESH_TOKEN=
```

For production, also add a valid Kirby license file at:

```text
site/config/.license
```

## cPanel Flow

1. Create a cPanel Git Version Control repository from this public GitHub URL.
2. Pull the latest `main` branch.
3. Click `Deploy HEAD Commit`.
4. Confirm that `Last Deployed SHA` matches the commit you intended to publish.

The repository path can be separate from the document root, for example:

```text
repositories/fatihalper.site
```

The deploy target in `.cpanel.yml` is:

```text
$HOME/public_html/
```

## Kirby Attribution

This project is built with [Kirby CMS](https://getkirby.com), a commercial flat-file CMS by Bastian Allgeier GmbH. A valid Kirby license is required for production use and must be stored outside Git.

- [Kirby website](https://getkirby.com)
- [Kirby documentation](https://getkirby.com/docs)
- [Kirby license](https://getkirby.com/license)
