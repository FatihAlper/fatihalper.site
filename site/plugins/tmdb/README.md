# Kirby TMDB Integration

Version: `0.1b`

Reusable TMDB metadata module for Kirby film entries.

## Environment

Secrets must stay outside Panel content and frontend output.

- `TMDB_API_KEY`
- `TMDB_ACCESS_TOKEN`

## Provides

- `tmdbMovie($id)`
- `tmdbImageUrl($path, $size)`
- `tmdbDirector($movie)`
- `tmdbGenres($movie)`
- Page method: `$page->tmdb()`

## Kirby plugin id

`fatihalper/tmdb-integration`
