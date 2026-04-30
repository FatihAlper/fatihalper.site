<div align="center">

# fatihalper.site

Kirby CMS uzerinde kurgulanmis, icerigi Git disinda tutan, public code-only bir kisisel arsiv altyapisi.

[![TR](https://img.shields.io/badge/TR-Turkce-111827?style=for-the-badge)](#tr--turkce)
[![EN](https://img.shields.io/badge/EN-English-374151?style=for-the-badge)](#en--english)

[![Kirby CMS](https://img.shields.io/badge/Kirby-CMS-black?style=for-the-badge)](https://getkirby.com)
[![PHP](https://img.shields.io/badge/PHP-8.x-777bb4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net)
[![Last Commit](https://img.shields.io/github/last-commit/FatihAlper/fatihalper.site?style=for-the-badge)](https://github.com/FatihAlper/fatihalper.site/commits/main)
[![Code Only](https://img.shields.io/badge/repo-code--only-2f855a?style=for-the-badge)](#code-only-model)
[![Manual Deploy](https://img.shields.io/badge/deploy-cPanel%20Git-2563eb?style=for-the-badge)](#deployment-model)

<p>
  <a href="https://github.com/FatihAlper/fatihalper.site/commits/main"><strong>Commits</strong></a>
  |
  <a href="https://github.com/FatihAlper/fatihalper.site/blob/main/.cpanel.yml"><strong>Deploy Script</strong></a>
  |
  <a href="https://github.com/FatihAlper/fatihalper.site/blob/main/AGENTS.md"><strong>Agent Rules</strong></a>
  |
  <a href="https://getkirby.com/docs"><strong>Kirby Docs</strong></a>
</p>

</div>

## TR / Turkce

Bu repo, Kirby ile kurulan kisisel arsiv sitesinin public uygulama katmanidir. Kod, Panel mimarisi, template/snippet yapisi, pluginler, frontend assetleri ve deploy akisi Git uzerinden yonetilir; canli icerik ve ozel kimlik katmani host uzerinde kalir.

Kisa versiyon: repo sistemi tasir, arsivi degil.

### Sistem Ozeti

- Kirby Panel blueprintleri, pano ve icerik uretimi akisini birbirinden ayirir.
- Ana sayfa icin hero metni, arka plan, marquee galeri, tag wall, oda kartlari, vitrin nesnesi ve manifesto/quote bolumleri Panelden yonetilir.
- Yazi, kitap notu, film kaydi, playlist, fotograf albumu ve sanat projesi ayri Kirby template modelleri olarak tanimlidir.
- SEO, Open Graph, dil, analytics ve entegrasyon ayarlari field tabanli calisir.
- Spotify playlist sistemi API secretlarini `.env` disinda gostermeden playlist verisini guclendirir.
- TMDB entegrasyonu film metadata akisini frontend template icine gommeden destekler.
- Frontend PHP/Kirby-native template yapisi, vanilla CSS/JS ve hafif asset akisi uzerine kuruludur.
- Public Git gecmisi code-only tutulur; agentic editorler icin ek guardrail dosyalari vardir.

### Code-Only Model

Bu repository public oldugu icin asagidaki dosya ve klasorler Git disinda tutulur:

- `.env`
- `content/`
- `media/`
- `site/accounts/`
- `site/cache/`
- `site/sessions/`
- `site/logs/`
- `site/config/.license`
- deploy paketleri, loglar ve ham/ozel medya dosyalari

Beklenen calisma sekli: ajanlar ve gelistiriciler kodu, blueprintleri, template/snippetleri, pluginleri, assetleri ve deploy configini degistirir. Gercek icerik, hesaplar, medya, sessionlar ve secretlar Git'e girmez.

Commit oncesi kontrol:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File .\tools\check-code-only.ps1
```

Agent kurallari:

- `AGENTS.md`
- `.cursor/rules/code-only-public-repo.mdc`

### Deployment Model

Deploy bilincli olarak muhafazakar tutulur. `.cpanel.yml`, sadece secili uygulama yollarini `public_html` icine kopyalar ve canli veri katmanina dokunmaz.

Korunan host-local katman:

- icerik
- upload/generated media
- Kirby lisansi
- environment variable dosyalari
- Panel hesaplari
- runtime cache, session ve loglar

Deployment status cPanel Git Version Control uzerinden kontrol edilir. `Last Deployed SHA`, yayina almak istedigin son commit ile eslesmelidir.

### Proje Haritasi

```text
assets/                  Frontend CSS ve JavaScript
kirby/                   Kirby CMS core
site/blueprints/         Panel yapisi ve icerik modelleri
site/config/config.php   Runtime config ve entegrasyon ayarlari
site/plugins/            Proje pluginleri ve bundled plugin kodu
site/snippets/           Reusable view parcalari
site/templates/          Page template dosyalari
site/translations/       Ceviri kataloglari
tools/                   Validation ve guardrail scriptleri
.cpanel.yml              Shared-hosting deployment gorevleri
```

### Kendi Hostunda Kullanmak

Bu projeyi hostlamak veya ozellestirmek icin private runtime katmanini kendin saglamalisin:

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

Entegrasyonlara gore gereken env degiskenleri:

```text
APP_ENV=production
PANEL_LANGUAGE=tr
TMDB_API_KEY=
TMDB_ACCESS_TOKEN=
SPOTIFY_CLIENT_ID=
SPOTIFY_CLIENT_SECRET=
SPOTIFY_REFRESH_TOKEN=
```

Production icin gecerli Kirby lisans dosyasi su konumda host uzerinde bulunmalidir:

```text
site/config/.license
```

### cPanel Akisi

1. cPanel Git Version Control icinden bu public GitHub URL'i ile repo olustur.
2. `main` branchini pull et.
3. `Deploy HEAD Commit` butonuna bas.
4. `Last Deployed SHA` degerinin yayina almak istedigin commit ile ayni oldugunu kontrol et.

Ornek repo yolu:

```text
repositories/fatihalper.site
```

Deploy hedefi:

```text
$HOME/public_html/
```

## EN / English

This repository is the public application layer of a Kirby-powered personal archive site. Code, Panel architecture, templates, snippets, plugins, frontend assets and deployment flow are versioned through Git; live content and private identity data stay on the host.

Short version: this repo ships the system, not the archive.

### System Highlights

- Kirby Panel blueprints separate operational dashboards from editorial content creation.
- The home page exposes Panel controls for hero copy, background, marquee gallery, tag wall, room cards, featured object and manifesto/quote sections.
- Writing, book notes, film entries, playlists, photo albums and art projects are modeled as dedicated Kirby template types.
- SEO, Open Graph, language, analytics and integration settings are handled through structured fields.
- Spotify playlist support enriches playlist data while keeping API secrets out of the frontend and out of Git.
- TMDB support keeps film metadata workflows away from frontend templates.
- The frontend is Kirby/PHP-native, with small vanilla CSS/JS assets and no heavy build dependency.
- Public Git history is kept code-only, with guardrails for agentic coding tools.

### Code-Only Model

Because this repository is public, live and personal site data is intentionally ignored:

- `.env`
- `content/`
- `media/`
- `site/accounts/`
- `site/cache/`
- `site/sessions/`
- `site/logs/`
- `site/config/.license`
- deploy bundles, logs and private/raw media files

Expected workflow: agents and developers change code, blueprints, templates, snippets, plugins, assets and deployment config. Real content, accounts, uploads, sessions and secrets never enter Git.

Pre-commit check:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File .\tools\check-code-only.ps1
```

Agent rules:

- `AGENTS.md`
- `.cursor/rules/code-only-public-repo.mdc`

### Deployment Model

Deployment is intentionally conservative. `.cpanel.yml` copies selected application paths into `public_html` and leaves the live data layer untouched.

Host-local data preserved during deploy:

- content
- uploaded/generated media
- Kirby license
- environment files
- Panel accounts
- runtime cache, sessions and logs

Deployment status is checked from cPanel Git Version Control. `Last Deployed SHA` should match the commit you intended to publish.

### Project Map

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

### Self-Hosting

To host or customize this project, provide the private runtime layer yourself:

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

Environment variables depend on enabled integrations:

```text
APP_ENV=production
PANEL_LANGUAGE=tr
TMDB_API_KEY=
TMDB_ACCESS_TOKEN=
SPOTIFY_CLIENT_ID=
SPOTIFY_CLIENT_SECRET=
SPOTIFY_REFRESH_TOKEN=
```

For production, a valid Kirby license file must exist on the host:

```text
site/config/.license
```

### cPanel Flow

1. Create a cPanel Git Version Control repository from this public GitHub URL.
2. Pull the latest `main` branch.
3. Click `Deploy HEAD Commit`.
4. Confirm that `Last Deployed SHA` matches the commit you intended to publish.

Example repository path:

```text
repositories/fatihalper.site
```

Deployment target:

```text
$HOME/public_html/
```

## Kirby Attribution

This project is built with [Kirby CMS](https://getkirby.com), a commercial flat-file CMS by Bastian Allgeier GmbH. A valid Kirby license is required for production use and must be stored outside Git.

- [Kirby website](https://getkirby.com)
- [Kirby documentation](https://getkirby.com/docs)
- [Kirby license](https://getkirby.com/license)
