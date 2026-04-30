# Kirby Content Transfer

Version: `0.1b`
License: `GPL-3.0-or-later`

Panel-driven content import/export module.

## Features

- Export `content/` as a ZIP bundle
- Download latest export from an authenticated Panel route
- Import a ZIP selected in the Panel
- Path traversal protection
- Optional overwrite mode
- Automatic backup before import
- Uses `ZipArchive` when available and falls back to `PharData`

## Kirby plugin id

`fatihalper/content-transfer`

## Notes

The import flow never reads secrets from `.env` and only accepts files inside the
uploaded ZIP whose paths start with `content/`.
