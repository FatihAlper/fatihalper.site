# Kirby Spotify Integration

Version: `0.1b`

Reusable Spotify module for Kirby playlist entries.

## Environment

Secrets must stay outside Panel content and frontend output.

- `SPOTIFY_CLIENT_ID`
- `SPOTIFY_CLIENT_SECRET`
- `SPOTIFY_REFRESH_TOKEN` when user-token flows are needed

## Provides

- Playlist metadata sync
- JSON track import
- Spotify connection status for the Panel
- Page method: `$page->spotifySyncStatus()`
- Site method: `$site->spotifyIntegrationStatus()`

## Kirby plugin id

`fatihalper/spotify-integration`
